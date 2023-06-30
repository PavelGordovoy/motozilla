<?php

class teamTrackerPluginTaskCommentController extends waJsonController
{
	public function execute()
	{
		$task_id = waRequest::post('task_id', 0, 'int');
		
		if(teamTrackerAccess::canCommentTask($task_id)) {
			$text = waRequest::post('text');
			$parent_id = waRequest::post('parent_id', 0, 'int');
			
			$comments_model = new teamTrackerCommentsModel();
			$id = $comments_model->insert(array(
				'task_id' => $task_id,
				'contact_id' => wa()->getUser()->getId(),
				'text' => $text,
				'parent_comment_id' => $parent_id,
				'has_replies' => 0,
				'date' => date('Y-m-d H:i:s')
			));
			if($parent_id)
				$comments_model->updateById($parent_id, array(
					'has_replies' => 1
				));
			
			$this->response = array(
				'firstname' => wa()->getUser()->get('firstname'),
				'name' => wa()->getUser()->get('name'),
				'avatar' => wa()->getUser()->getPhoto(),
				'text' => $text,
				'parent_id' => $parent_id,
				'id' => $id
			);
		} else
			$this->setError('Вы не можете комментировать эту задачу.');
	}
}