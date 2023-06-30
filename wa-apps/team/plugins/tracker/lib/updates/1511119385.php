<?php
$model = new waModel();

$model->exec("CREATE TABLE IF NOT EXISTS `team_tracker_unread` (
  `id` int(11) NOT NULL,
  `type` varchar(11) NOT NULL DEFAULT 'task',
  `object_id` int(11) NOT NULL,
  `action` varchar(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `notification_showed` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ");