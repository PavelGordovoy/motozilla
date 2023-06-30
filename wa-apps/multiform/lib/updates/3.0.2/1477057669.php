<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

$model = new waModel();
try {
    $model->exec("ALTER TABLE `multiform_field` MODIFY COLUMN type VARCHAR(50) NOT NULL");
} catch (waDbException $e) {
    
}