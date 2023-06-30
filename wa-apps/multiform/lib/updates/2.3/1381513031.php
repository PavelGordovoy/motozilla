<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
// Поле CSS
try {
    $model->exec("SELECT css FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD css VARCHAR (100) NOT NULL DEFAULT  ''");
}
// Поле Email
try {
    $model->exec("SELECT email FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD email VARCHAR (50) NOT NULL DEFAULT  ''");
}
// Поле Email_sender
try {
    $model->exec("SELECT email_sender FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD email_sender VARCHAR (50) NOT NULL DEFAULT  ''");
}
// Поле Website_sender
try {
    $model->exec("SELECT website_sender FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD website_sender VARCHAR (50) NOT NULL DEFAULT  ''");
}
// Поле Callback
try {
    $model->exec("SELECT callback FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD callback TEXT");
}

try {
    $sql = <<<SQL
   CREATE TABLE IF NOT EXISTS `multiform_params` (
    `field` varchar(30) NOT NULL,
    `ext` varchar(30) NOT NULL DEFAULT '',
    `value` varchar(255) NOT NULL,
    UNIQUE KEY `field_ext` (`field`,`ext`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;
    $model->exec($sql);
} catch (waDbException $e) {
    
}