<?php
return array(
    'team_tracker_log' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'task_id' => array('int', 9, 'null' => 0),
        'before_status' => array('varchar', 10, 'null' => 0),
        'after_status' => array('varchar', 10, 'null' => 0),
        'before_level' => array('int', 1, 'null' => 0),
        'after_level' => array('int', 1, 'null' => 0),
        'date' => array('datetime', 'null' => 0),
        'contact_id' => array('int', 9, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'team_tracker_tasks' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'title' => array('varchar', 255, 'null' => 0),
        'level' => array('int', 1, 'null' => 0),
        'text' => array('text', 'null' => 0),
        'manager_contact_id' => array('int', 9, 'null' => 0),
        'contributor_group_id' => array('int', 9, 'null' => 0),
        'contributor_contact_id' => array('int', 9, 'null' => 0),
        'status' => array('varchar', 10, 'null' => 0),
        'date' => array('datetime', 'null' => 0),
        'until_date' => array('date', 'null' => 0),
        'until_time' => array('time', 'null' => 0),
        'last_change_date' => array('datetime', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'team_tracker_comments' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'task_id' => array('int', 9, 'null' => 0),
        'contact_id' => array('int', 9, 'null' => 0),
        'date' => array('datetime', 'null' => 0),
        'text' => array('text', 'null' => 0),
        'parent_comment_id' => array('int', 9, 'null' => 0, 'default' => '0'),
        'has_replies' => array('int', 1, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'team_tracker_unread' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'type' => array('varchar', 11, 'null' => 0, 'default' => 'task'),
        'object_id' => array('int', 11, 'null' => 0),
        'action' => array('varchar', 11, 'null' => 0),
        'contact_id' => array('int', 11, 'null' => 0),
        'group_id' => array('int', 11, 'null' => 0),
        'notification_showed' => array('int', 1, 'null' => 0, 'default' => '1'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
);
