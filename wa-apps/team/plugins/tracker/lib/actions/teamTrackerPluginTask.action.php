<?php

class teamTrackerPluginTaskAction extends teamContentViewAction
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
		$id = waRequest::get('id', 0, 'int');
		if(teamTrackerAccess::canViewTask($id))
			$task = $this->model->getTask($id);
		
		$unread = $this->plugin->isUnread($id);

		if($unread) {
			$this->plugin->model->unread->deleteById($unread);
		}
		
		$this->view->assign('task', ifset($task));
		if(isset($task)) {
			$discussions = $this->model->getTaskDiscussions($id);
			$this->view->assign('discussions', $discussions);
		}
		$this->view->assign('title', ifset($task['title'], 'Задача №' . $task['id']));
		$this->view->assign('id', $id);
	}
}