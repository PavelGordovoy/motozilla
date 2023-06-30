<?php

class teamTrackerPluginTasksAddNewController extends waJsonController
{
	public static $error_codes = array(
		1 => array('field' => 'title', 'message' => 'Минимум 3 символа в поле "Заголовок"'),
		2 => array('field' => 'text', 'message' => 'Минимум 3 символа в поле "Текст задачи"'),
		3 => array('field' => 'until-date', 'message' => 'Дата "Выполнить до" не может быть прошедшей'),
		4 => array('field' => 'until-date', 'message' => 'Дата "Выполнить до" не может быть сегодняшней'),
		5 => 'Вы не можете назначить этого исполнителя задачи: ',
		6 => 'Выберите исполнителя задачи!',
		7 => 'Укажите время "Выполнить до..." в правильном формате!'
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
		if(teamTrackerAccess::canAddTask()) {
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
			$until_date = waRequest::post('until_date');
			$until_time = waRequest::post('until_time');
			/* if($until_date < date('Y-m-d'))
				return $this->error(3);
			if($until_date == date('Y-m-d'))
				return $this->error(4); */
			
			if(!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $until_time))
				return $this->error(7);
			
			$contributor_contact_id = waRequest::post('contributor_contact_id', 0, 'int');
			$contributor_group_id = waRequest::post('contributor_group_id', 0, 'int');
			
			if(!$contributor_contact_id && !$contributor_group_id)
				return $this->error(6);
			
			if($contributor_contact_id && !$contributor_group_id) { // Исполнитель - пользователь
				$contributor = 'contact';
				if(!teamTrackerAccess::canAddTaskFor('contact'))
					return $this->error(5, 'Contact, id: ' . $contributor_contact_id);
			}
			
			if(!$contributor_contact_id && $contributor_group_id) { // Исполнитель - группа/офис
				$contributor = 'group';
				if(!teamTrackerAccess::canAddTaskFor($contributor_group_id))
					return $this->error(5, 'Group, id: ' . $contributor_group_id);
			}
			
			$this->response = $this->model->insert(array(
				'title' => $title,
				'level' => $level,
				'text' => $text,
				'manager_contact_id' => $this->getUserId(),
				'contributor_group_id' => $contributor_group_id,
				'contributor_contact_id' => $contributor_contact_id,
				'status' => 'new',
				'date' => date('Y-m-d H:i:s'),
				'until_date' => $until_date,
				'until_time' => $until_time,
				'last_change_date' => date('Y-m-d H:i:s'),
			));
			
			$this->model->unread->insert(array(
				'type' => 'task',
				'object_id' => $this->response,
				'action' => 'new',
				'contact_id' => $contributor_contact_id,
				'group_id' => $contributor_group_id
			));
		} else
			$this->setError(array(
				'code' => 0,
				'message' => 'Недостаточно прав для создания новой задачи.'
			));
	}
}