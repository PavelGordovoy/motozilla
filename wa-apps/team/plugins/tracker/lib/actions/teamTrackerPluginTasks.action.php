<?php

class teamTrackerPluginTasksAction extends teamContentViewAction
{
	protected function preExecute()
	{
		$this->plugin = wa('team')->getPlugin('tracker');
		$this->settings = $this->plugin->getSettings();
		$this->model = $this->plugin->model;
		$this->view->assign('plugin', $this->plugin);
	}
	
	public function execute()
	{
		$page = waRequest::get('page', 1, 'int');
		$show = waRequest::get('show', 'default');
		$status = waRequest::get('status');
		$group_id = waRequest::get('group');
		$user_groups = $this->plugin->getUserGroups();
		
		$conditions = array();
		if(waRequest::get('level'))
			$conditions['level'] = (int) waRequest::get('level');
		if(in_array($status, array('new', 'checking', 'processing', 'deleted', 'done')))
			$conditions['status'] = $status;
		
		if(count($conditions))
			$url_params = '&' . http_build_query($conditions);
		
		switch($show) {
			case 'my':
				$title = 'Задачи для меня';
				$tasks = $this->model->getForContacts(wa()->getUser()->get('id'), $conditions);
				break;
			case 'group':
				if(in_array($group_id, array_keys($user_groups))) {
					$title = 'Задачи для ' . ifset($user_groups[$group_id]['name'], '?');
					$tasks = $this->model->getForGroups($group_id, $conditions);
					break;
				}
			case 'all':
				if(teamHelper::hasRights('tracker.view_any') && waRequest::get('show') == 'all') {
					$title = 'Все существующие задачи';
					$tasks = $this->model->getAll($conditions);
					break;
				}
			default:
				$title = 'Задачи';
				$tasks = $this->model->get(wa()->getUser()->get('id'), array_keys($user_groups), $conditions, isset($conditions['status']) ? false : true);
				break;
		}
		
		if(ifempty($conditions['level']) == 5)
			$title .= ' (Срочно!)';
		if(ifempty($conditions['status']))
			$title .= ' (' . teamTrackerPlugin::$status[$conditions['status']] . ' )';

		$unread = $this->plugin->getUnread();
		$view = $this->model->view($tasks, $page, $this->settings['count_on_page'], array('until_date' => 'asc', 'level' => 'desc'), $unread);
		$this->view->assign('title', $title);
		$this->view->assign('url_params', ifempty($url_params));
		$this->view->assign('tasks', $view['tasks']);
		$this->view->assign('page', $view['page']);
		$this->view->assign('pages', $view['pages']);
		$this->view->assign('pagination', $view['pagination']);
		$this->view->assign('total_tasks', $view['total_tasks']);
	}
}