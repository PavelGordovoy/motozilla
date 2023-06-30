<?php

class teamTrackerCommentsModel extends waModel
{
	protected $table = 'team_tracker_comments';
	
	public function getComments($id)
	{
		$comments = $this->getByField('task_id', $id, true);
		foreach($comments as $id => $comment)
			$comments[$id]['contact'] = new waContact($comment['contact_id']);
		
		return $comments;
	}
	
	public static function getReplies($comment_id)
	{
		$replies = wao(new teamTrackerCommentsModel())->getByField('parent_comment_id', $comment_id, 'date');
		foreach($replies as $id => $reply)
			$replies[$id]['contact'] = new waContact($reply['contact_id']);

		ksort($replies);
		return $replies;
	}
}