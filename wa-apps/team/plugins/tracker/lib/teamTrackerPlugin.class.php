<?php

class teamTrackerPlugin extends waPlugin
{
	public static $status = array(
		'new' => 'Новые',
		'checking' => 'На рассмотрении',
		'processing' => 'В процессе',
		'deleted' => 'Отклонено',
		'done' => 'Выполнено'
	);
	
	public static $status_icons = array(
		'new' => 'status-green',
		'checking' => 'status-yellow',
		'processing' => 'progress',
		'deleted' => 'no',
		'done' => 'yes'
	);
	
	public static $level = array(
		1 => 'Не критично',
		2 => 'Желательно',
		3 => 'Важно',
		4 => 'Необходимо',
		5 => 'Срочно'
	);
	
	public static $level_semantic_ui_class = array(
		1 => 'grey',
		2 => 'blue',
		3 => 'green',
		4 => 'orange',
		5 => 'red'
	);
	
	public static $status_semantic_ui_class = array(
		'new' => 'visible',
		'checking' => 'warning',
		'processing' => 'info',
		'deleted' => 'negative',
		'done' => 'success'
	);
	
	public function __construct($info = array()) {
		parent::__construct($info);
		$this->model = new teamTrackerTasksModel();
		if($this->getSettings('on')) {
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/message.min.css', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/label.min.css', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/step.min.css', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/icon.min.css', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/image.min.css', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/breadcrumb.min.css', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/segment.min.css', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/button.min.css', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/comment.min.css', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/divider.min.css', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/form.min.css', 'team');
			wa()->getResponse()->addJs('plugins/tracker/js/semantic-ui/form.min.js', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/semantic-ui/dropdown.min.css', 'team');
			wa()->getResponse()->addJs('plugins/tracker/js/semantic-ui/dropdown.min.js', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/tracker.css', 'team');
			
			wa()->getResponse()->addJs('plugins/tracker/js/pickmeup/pickmeup.js', 'team');
			wa()->getResponse()->addCss('plugins/tracker/css/pickmeup/pickmeup.css', 'team');
		}
	}
	
	public static function getUnread($type = 'task', $action = 'new')
	{
		$contact_groups = self::getUserGroups(null, true);
		$contact_id = wa()->getUser()->get('id');

		$unread = wao(new teamTrackerUnreadModel())->query("SELECT * FROM team_tracker_unread WHERE
			type = '{$type}' AND action = '{$action}' AND (contact_id = ? OR group_id IN (?))", $contact_id, ifempty($contact_groups, -1))->fetchAll('id');
		return $unread;
	}
	
	public static function isUnread($id, $type = 'task', $action = 'new', $unread_array = null)
	{
		if(gettype($unread_array) == 'array')
			$unread = $unread_array;
		else
			$unread = self::getUnread($type, $action);
		
		foreach($unread as $data_id => $data)
			if($data['object_id'] == $id) {
				return $data_id;
				break;
			}
		
		return false;
	}
	
	public function backendSidebar()
	{
		if($this->getSettings('on')) {
			$view = wa()->getView();
			$view->assign('backend_url', wa('team')->getUrl());
			
			$new_unread = self::getUnread();

			$new_unread_count = count($new_unread);
			
			$view->assign('new_unread_count', ifempty($new_unread_count));
			$sidebar = $view->fetch(wa()->getAppPath('plugins/tracker/templates/sidebar.html'));
			return array(
				'section' => $sidebar
			);
		}
	}
	
	public static function getUserGroups($contact_ids = null, $ids = false)
	{
		if(!$contact_ids)
			$contact_ids = wa()->getUser()->get('id');
		
		$sql = "SELECT g.id, g.name, g.icon, ug.contact_id, ug.group_id as group_id
				FROM wa_user_groups ug
					LEFT JOIN wa_group g
						ON ug.group_id = g.id
				WHERE ug.contact_id IN (:contact_ids)
				ORDER BY g.id";
		$user_groups = wao(new waUserGroupsModel())->query($sql, array(
			'contact_ids' => $contact_ids
		))->fetchAll('id', true);
		
		if($ids) {
			$user_group_ids = array();
			foreach($user_groups as $user_group)
				$user_group_ids[] = $user_group['group_id'];
			return $user_group_ids;
		} else
			return $user_groups;
	}
	
	protected function getNotAdminGroupNames()
	{
		$sql = "SELECT g.id, g.name
				FROM wa_group g
					LEFT JOIN wa_contact_rights r
						ON r.group_id=g.id
							AND r.app_id='webasyst'
							AND r.name='backend'
							AND r.value=1
				WHERE r.app_id IS NULL
				ORDER BY g.type, g.sort, g.name";
		return wao(new waModel())->query($sql)->fetchAll('id', true);
	}
	
	public function rightsConfig(waRightConfig $rights_config)
	{
		if($this->getSettings('on')) {
			$group_names = $this->getNotAdminGroupNames();
			$group_names[0] = 'Лично пользователям';
			
			$rights_config->addItem('tracker_add_for', 'Создавать задачи для...', 'list', array('items' => $group_names, 'hint1' => 'all_checkbox', 'cssclass' => 'tracker'));
			$rights_config->addItem('tracker', 'Задачи', 'list', array('items' => array(
				'view_any' => 'Просматривать любые задачи',
				'commeny_any' => 'Комментировать любые задачи',
				'delete_any_comment' => 'Удалять любые комментарии',
				'edit_own' => 'Редактировать свои задачи',
				'edit_any' => 'Редактировать любые задачи',
				'mark_assigned_to_me' => 'Изменять статус задач, назначенных себе',
				'mark_any' => 'Изменять статус любым задачам',
				'delete_own' => 'Удалять свои задачи',
				'delete_any' => 'Удалять любые задачи',
			), 'cssclass' => 'tracker'));
		}
	}
}
