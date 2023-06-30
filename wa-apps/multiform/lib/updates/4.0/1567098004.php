<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

// Устанавливаем корректный размер для полей, у которых заголовок скрыт или назодится сверху
$model = new waModel();

try {
    $model->exec("ALTER TABLE `multiform_field_params` MODIFY COLUMN `param` VARCHAR(50) NOT NULL");
    $model->exec("ALTER TABLE `multiform_field_params` MODIFY COLUMN `ext` VARCHAR(50) NOT NULL");
    $primary = $model->query("SHOW INDEX FROM `multiform_field_params` WHERE Key_name = 'PRIMARY'")->fetchField();
    if (!$primary) {
        $model->exec("ALTER TABLE `multiform_field_params` ADD PRIMARY KEY (`field_id`, `param`, `ext`)");
    }
} catch (waDbException $e) {

}
