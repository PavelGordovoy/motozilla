<?php

class teamTrackerPluginTaskDeleteController extends waJsonController
{
	protected function preExecute()
	{
		$this->plugin = wa('team')->getPlugin('tracker');
		$this->settings = $this->plugin->getSettings();
		$this->model = $this->plugin->model;
	}
	
	public function execute()
	{
		$id = waRequest::get('id');
		
		if(teamTrackerAccess::canDeleteTask($id)) {
			$this->response = $this->model->deleteById($id);
		} else
			$this->setErrors('Недостаточно прав для удаления задачи.');
	}
}