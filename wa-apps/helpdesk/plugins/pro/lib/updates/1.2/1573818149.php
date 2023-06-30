<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
 
$model = new waModel();
try {
    $model->exec("SELECT `type` FROM `helpdesk_pro_constructor` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `helpdesk_pro_constructor` ADD `type` VARCHAR (50) NOT NULL DEFAULT ''");
}