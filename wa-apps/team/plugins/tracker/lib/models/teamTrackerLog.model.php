<?php

class teamTrackerLogModel extends waModel
{
	protected $table = 'team_tracker_log';
	
	public function getLog($id)
	{
		$log = $this->getByField('task_id', $id, true);
		foreach($log as $id => $data)
			$log[$id]['contact'] = new waContact($data['contact_id']);

		return $log;
	}
}