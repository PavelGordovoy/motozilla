<?php

class teamTrackerPluginTaskEditController extends waJsonController
{
	public static $error_codes = array(
		1 => array('field' => 'title', 'message' => 'Минимум 3 символа в поле "Заголовок"'),
		2 => array('field' => 'text', 'message' => 'Минимум 3 символа в поле "Текст задачи"'),
		3 => array('field' => 'until-date', 'message' => 'Дата "Выполнить до" не может быть прошедшей'),
		4 => array('field' => 'until-date', 'message' => 'Дата "Выполнить до" не может быть сегодняшней'),
		5 => 'Вы не можете назначить этого исполнителя задачи: ',
		6 => 'Выберите исполнителя задачи!'
	);
	
	protected function error($code, $add = '')
	{
		return $this->setError(array(
			'code' => $code,
			'field' => gettype(self::$error_codes[$code]) == 'array' ? self::$error_codes[$code]['field'] : null,
			'message' => (gettype(self::$error_codes[$code]) == 'string' ? self::$error_codes[$code] : self::$error_codes[$code]['message']) . $add
		));
	}
	
	protected function preExecute()
	{
		$this->plugin = wa('team')->getPlugin('tracker');
		$this->settings = $this->plugin->getSettings();
		$this->model = $this->plugin->model;
	}
	
	public function execute()
	{
		$id = waRequest::get('id');
		
		if(teamTrackerAccess::canEditTask($id)) {
			$title = waRequest::post('title');
			if(strlen($title) < 3)
				return $this->error(1);
			
			$text = waRequest::post('text');
			if(strlen($text) < 3)
				return $this->error(2);
			$level = waRequest::post('level', 3, 'int');
			if($level > 5)
				$level = 5;
			if($level < 1)
				$level = 1;
			/* $until_date = waRequest::post('until_date');
			if($until_date < date('Y-m-d'))
				return $this->error(3);
			if($until_date == date('Y-m-d'))
				return $this->error(4); */
			
			$contributor = waRequest::post('contributor');
			if(substr($contributor, 0, 7) == 'contact') {
				$contributor_contact_id = substr($contributor, 8);
				$contributor = 'contact';
				if(!teamTrackerAccess::canAddTaskFor('contact'))
					return $this->error(5, 'Contact, id: ' . $contributor_contact_id);
			}
			
			if(substr($contributor, 0, 5) == 'group') {
				$contributor_group_id = substr($contributor, 6);
				$contributor = 'group';
				if(!teamTrackerAccess::canAddTaskFor($contributor_group_id))
					return $this->error(5, 'Group, id: ' . $contributor_group_id);
			}
			
			$task = $this->model->getById($id);
			if($task['level'] != $level)
				$this->model->log->insert(array(
					'task_id' => $id,
					'before_status' => $task['status'],
					'after_status' => $task['status'],
					'before_level' => $task['level'],
					'after_level' => $level,
					'date' => date('Y-m-d H:i:s'),
					'contact_id' => $this->getUserId()
				));
				
			$this->response = $this->model->updateById($id, array(
				'title' => $title,
				'level' => $level,
				'text' => $text,
				'contributor_group_id' => ifset($contributor_group_id, 0),
				'contributor_contact_id' => ifset($contributor_contact_id, 0),
				'last_change_date' => date('Y-m-d H:i:s'),
				// 'until_date' => $until_date
			));
		} else
			$this->setErrors('Недостаточно прав для редактирования задачи.');
	}
}