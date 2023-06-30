<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

$model = new waModel();

try {
    $model->exec("SELECT `extra_tab` FROM `multiform_condition` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_condition` ADD `extra_tab` VARCHAR(20) DEFAULT NULL");
}