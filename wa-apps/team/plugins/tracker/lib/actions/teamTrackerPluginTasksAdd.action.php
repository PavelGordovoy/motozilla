<?php

class teamTrackerPluginTasksAddAction extends teamContentViewAction
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
		$title = 'Новая задача';
		
		$this->view->assign('title', $title);
	}
}