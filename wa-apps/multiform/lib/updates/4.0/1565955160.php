<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

$model = new waModel();

try {
    $model->exec("SELECT `cloned_index` FROM `multiform_record_values` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_record_values` ADD `cloned_index` VARCHAR(50) DEFAULT '0'");
}