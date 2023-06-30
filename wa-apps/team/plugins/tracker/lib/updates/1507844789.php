<?php
$model = new waModel();

$model->exec("ALTER TABLE `team_tracker_tasks` ADD `until_time` TIME NOT NULL AFTER `until_date`;");