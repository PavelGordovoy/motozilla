<?php

class teamTrackerPluginTaskDeleteCommentController extends waJsonController
{
	public function execute()
	{
		$task_id = waRequest::post('task_id', 0, 'int');
		$comment_id = waRequest::post('comment_id', 0, 'int');
		
		if(teamTrackerAccess::canDeleteComment($comment_id)) {
			$comments_model = new teamTrackerCommentsModel();
			$this->response = $comments_model->deleteByField(array(
				'task_id' => $task_id,
				'id' => $comment_id
			));
		} else
			$this->setError('Вы не можете удалить этот комментарий.');
	}
}