<?php
$model = new waModel();
try{
    $model->query('SELECT `in_sendpulse`, `sync_with_sendpulse` FROM `wa_contact` WHERE 0');
}catch (waDbException $e){
    $model->exec('ALTER TABLE `wa_contact` ADD `in_sendpulse` INT(1) UNSIGNED NULL DEFAULT NULL');
    $model->exec('ALTER TABLE `wa_contact` ADD `sync_with_sendpulse` INT(1) UNSIGNED NULL DEFAULT 1');
}
