<?php

class teamTrackerTasksModel extends waModel
{
	protected $table = 'team_tracker_tasks';
	
	public function __construct($type = null, $writable = false)
	{
		$this->comments = new teamTrackerCommentsModel();
		$this->log = new teamTrackerLogModel();
		$this->unread = new teamTrackerUnreadModel();
		
		parent::__construct($type, $writable);
	}
	
	public function getForContacts($contact_ids = 'all', $conditions = null, $active = false)
	{
		if($contact_ids == 'all')
			$where = 1;
		else
			$where = "t.contributor_contact_id IN (:contact_ids)";
		
		if($active) {
			if(isset($conditions['status']))
				unset($conditions['status']);
			$where .= " AND status != 'done' AND status != 'deleted'";
		}
			
		if($conditions && gettype($conditions) == 'array')
			foreach($conditions as $condition => $value)
				$where .= " AND {$condition} = '{$value}'";
		
		$sql = "SELECT t.*, TIME_FORMAT(t.until_time, '%H:%i') AS until_time, 'contact' type, c.name, cc.name manager_name
				FROM {$this->getTableName()} t
					LEFT JOIN wa_contact cc
						ON cc.id = t.manager_contact_id
					LEFT JOIN wa_contact c
						ON c.id = t.contributor_contact_id
				WHERE
					t.contributor_contact_id != 0
					AND t.contributor_group_id = 0
					AND {$where}
				ORDER BY t.until_date";
		return $this->query($sql, array(
			'contact_ids' => $contact_ids
		))->fetchAll();
	}
	
	public function getForGroups($group_ids = 'all', $conditions = null, $active = false)
	{
		if(gettype($group_ids) == 'array' && count($group_ids) == 0)
			return array();
		
		if($group_ids == 'all')
			$where = 1;
		else
			$where = "t.contributor_group_id IN (:group_ids)";
		
		if($active) {
			if(isset($conditions['status']))
				unset($conditions['status']);
			$where .= " AND status != 'done' AND status != 'deleted'";
		}
			
		if($conditions && gettype($conditions) == 'array')
			foreach($conditions as $condition => $value)
				$where .= " AND {$condition} = '{$value}'";

		
		$sql = "SELECT t.*, TIME_FORMAT(t.until_time, '%H:%i') AS until_time, 'group' type, g.name, g.icon, g.type group_type, c.name manager_name
				FROM {$this->getTableName()} t
					LEFT JOIN wa_contact c
						ON c.id = t.manager_contact_id
					LEFT JOIN wa_group g
						ON g.id = t.contributor_group_id
				WHERE
					t.contributor_group_id != 0
					AND t.contributor_contact_id = 0
					AND {$where}
				ORDER BY t.until_date";
		return $this->query($sql, array(
			'group_ids' => $group_ids
		))->fetchAll();
	}
	
	public function get($contact_ids, $group_ids, $conditions = null, $active = false)
	{
		return array_merge($this->getForContacts($contact_ids, $conditions, $active), $this->getForGroups($group_ids, $conditions, $active));
	}
	
	public function getAll($conditions = null, $active = false)
	{
		return array_merge($this->getForContacts('all', $conditions, $active), $this->getForGroups('all', $conditions, $active));
	}
	
	public function getTask($id)
	{
		$sql = "SELECT t.*, TIME_FORMAT(t.until_time, '%H:%i') AS until_time, c.name manager_name
			FROM {$this->getTableName()} t
				LEFT JOIN wa_contact c
					ON c.id = t.manager_contact_id
			WHERE
				t.id = ?";
		$result = $this->query($sql, $id)->fetch();
		if(!$result)
			return false;
		
		$result['type'] = $result['contributor_contact_id'] != 0 ? 'contact' : 'group';
		return $this->view(array($result), 'once');
	}
	
	public static function sortDiscussions($a, $b)
	{
		return self::desc($a, $b, 'date');
	}
	
	public function getTaskDiscussions($task_id)
	{
		$comments = $this->comments->getComments($task_id);
		foreach($comments as $id => $comment)
			$comments[$id]['type'] = 'comment';
		
		$log = $this->log->getLog($task_id);
		foreach($log as $id => $data)
			$log[$id]['type'] = 'log';

		$discussions = array_merge($log, $comments);
		usort($discussions, 'teamTrackerTasksModel::sortDiscussions');
		return $discussions;
	}
	
	public static function sortByLevelDesc($a, $b)
	{
		return self::desc($a, $b, 'level');
	}
	
	public static function sortByUntil_dateAsc($a, $b)
	{
		return self::asc($a, $b, 'until_date');
	}
	
	public static function asc($a, $b, $field) // По возрастанию
	{
		if ($a[$field] == $b[$field])
			return 0;
		
		return ($a[$field] < $b[$field]) ? -1 : 1;
	}
	
	public static function desc($a, $b, $field) // По убыванию
	{
		if ($a[$field] == $b[$field])
			return 0;
		
		return ($a[$field] > $b[$field]) ? -1 : 1;
	}
	
	public static function view($source_tasks, $page = 1, $per_page = 10, $order_by = array('until_date' => 'asc', 'level' => 'desc'), $unread = null)
	{
		if(gettype($page) == 'integer' && $page < 1)
			$page = 1;
		if($per_page < 1)
			$per_page = 10;
		
		if(count($source_tasks) > 1)
			foreach($order_by as $by => $sort_type)
				usort($source_tasks, 'teamTrackerTasksModel::sortBy' . ucfirst($by) . ucfirst($sort_type));
		
		$expired_tasks = array();
		foreach($source_tasks as $id => $task) {
			$task['manager_contact'] = new waContact($task['manager_contact_id']);
			if($task['type'] == 'contact') {
				$task['contributor_contact'] = new waContact($task['contributor_contact_id']);
				if(empty($task['name']))
					$task['name'] = $task['contributor_contact']->get('name');
			} else {
				if(empty($task['name']))
					$task['name'] = wao(new waGroupModel())->getName($task['contributor_group_id']);
			}

			if(date('Y-m-d') > $task['until_date']) {
				$task['expired'] = true;
				$expired_tasks[$id] = $task;
				unset($source_tasks[$id]);
			} else {
				$task['expired'] = false;
				$source_tasks[$id] = $task;
			}
		}
		
		$tasks = array_merge($source_tasks, $expired_tasks);
		$pages = floor((count($tasks) - 1) / $per_page) + 1;
		if($page > $pages)
			$page = $pages;
		
		foreach($tasks as &$task) {
			$task['unread'] = teamTrackerPlugin::isUnread($task['id'], 'task', 'new', $unread);
		}
		
		if(count($tasks) == 1 && $page == 'once')
			return reset($tasks);
		else
			return array(
				'tasks' => array_slice($tasks, ($page * $per_page) - $per_page, $per_page),
				'total_tasks' => count($tasks),
				'page' => $page,
				'pages' => $pages,
				'pagination' => self::assignPagination($page, $per_page, $pages)
			);
	}
	
	public static function assignPagination($page, $per_page, $pages)
	{
		$pagination = array();
		$dots_added = false;
		for($i = 1; $i <= $pages; $i++) {
			if ($i < 2) {
				$pagination[$i] = ($i-1)*$per_page;
				$dots_added = false;
			} else if (abs($i-$page) < 2) {
				$pagination[$i] = ($i-1)*$per_page;
				$dots_added = false;
			} else if ($pages - $i < 1) {
				$pagination[$i] = ($i-1)*$per_page;
				$dots_added = false;
			} else if (!$dots_added) {
				$dots_added = true;
				$pagination[$i] = false;
			}
		}

		return $pagination;
	}
}