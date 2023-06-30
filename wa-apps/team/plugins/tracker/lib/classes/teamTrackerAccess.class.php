<?php

class teamTrackerAccess extends teamHelper
{
	public static function canAddTask()
	{
		if(self::hasRights('tracker_add_for.all'))
			return true;
	
		foreach(self::getWaGroups() as $id => $g)
			if(self::hasRights('tracker_add_for.' . $id))
				return true;
		
		return false;
	}
	
	public static function canAddTaskFor($type = 0)
	{
		if(self::hasRights('tracker_add_for.all'))
			return true;

		if(in_array($type, array('group', 'location', 'contact'))) {
			switch($type) {
				case 'group':
					$groups = teamHelper::getVisibleGroups();
					foreach($groups as $id => $g)
						if(self::hasRights('tracker_add_for.' . $id))
							return true;
						
					return false;
					break;
				case 'location':
					$locations = teamHelper::getVisibleLocations();
					foreach($locations as $id => $l)
						if(self::hasRights('tracker_add_for.' . $id))
							return true;
						
					return false;
					break;
				case 'contact':
					return self::hasRights('tracker_add_for.0');
					break;
			}
		}
		
		return self::hasRights('tracker_add_for.' . $type);
	}
	
	public static function isTaskAssignedFor($id, $contact_id = null, $group_ids = null)
	{
		if(!$contact_id)
			$contact_id = wa()->getUser()->getId();
		if(!$group_ids)
			$group_ids = teamTrackerPlugin::getUserGroups($contact_id);
		
		$task = wao(new teamTrackerTasksModel())->getById($id);
		
		if($task['contributor_contact_id'] == $contact_id)
			return true;
		if(in_array($task['contributor_group_id'], array_keys($group_ids)))
			return true;
		
		return false;
	}
	
	public static function canViewTask($id, $contact_id = null, $group_ids = null)
	{
		if(teamHelper::hasRights('tracker.view_any'))
			return true;
		
		return self::isTaskAssignedFor($id, $contact_id, $group_ids);
	}
	
	public static function canChangeStatus($id, $contact_id = null, $group_ids = null)
	{
		if(teamHelper::hasRights('tracker.mark_any'))
			return true;

		return teamHelper::hasRights('tracker.mark_assigned_to_me') && self::isTaskAssignedFor($id, $contact_id, $group_ids);
	}
	
	public static function canEditTask($id, $contact_id = null)
	{
		if(teamHelper::hasRights('tracker.edit_any'))
			return true;
		
		if(!$contact_id)
			$contact_id = wa()->getUser()->getId();
		
		$task = wao(new teamTrackerTasksModel())->getById($id);
		if(teamHelper::hasRights('tracker.edit_own') && $task['manager_contact_id'] == $contact_id)
			return true;
	
		return false;
	}
	
	public static function canDeleteTask($id, $contact_id = null)
	{
		if(teamHelper::hasRights('tracker.delete_any'))
			return true;
		
		if(!$contact_id)
			$contact_id = wa()->getUser()->getId();
		
		$task = wao(new teamTrackerTasksModel())->getById($id);
		if(teamHelper::hasRights('tracker.delete_own') && $task['manager_contact_id'] == $contact_id)
			return true;
	
		return false;
	}
	
	public static function canCommentTask($id, $contact_id = null, $group_ids = null)
	{
		if(teamHelper::hasRights('tracker.comment_any'))
			return true;
		
		return self::isTaskAssignedFor($id, $contact_id, $group_ids);
	}
	
	public static function canDeleteComment($id, $contact_id = null)
	{
		if(teamHelper::hasRights('tracker.delete_any_comment'))
			return true;
		
		if(!$contact_id)
			$contact_id = wa()->getUser()->getId();
		
		$comments_model = new teamTrackerCommentsModel();
		$comment = $comments_model->getById($id);
		return $comment['contact_id'] == $contact_id;
	}
}