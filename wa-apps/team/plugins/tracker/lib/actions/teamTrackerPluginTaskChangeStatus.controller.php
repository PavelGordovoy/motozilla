<?php

class teamTrackerPluginTaskChangeStatusController extends waJsonController
{
	public function execute()
	{
		$id = waRequest::post('id', 0, 'int');
		$status = waRequest::post('status');
		
		if(teamTrackerAccess::canChangeStatus($id)) {
			if(in_array($status, array('new', 'checking', 'processing', 'done', 'deleted'))) {
				$model = new teamTrackerTasksModel();
				$task = $model->getById($id);
				$model->log->insert(array(
					'task_id' => $id,
					'before_status' => $task['status'],
					'after_status' => $status,
					'before_level' => $task['level'],
					'after_level' => $task['level'],
					'date' => date('Y-m-d H:i:s'),
					'contact_id' => wa()->getUser()->getId()
				));
				$this->response = $model->updateById($id, array(
					'status' => $status
				));
			} else
				$this->setError('Такого статуса нет: ' . $status);
		} else
			$this->setError('У вас нет прав на изменение статуса этой задачи.');
	}
}