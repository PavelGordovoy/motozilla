<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
try {
    // Проверяем, существует ли индекс form_id    
    $index_exist = $model->query("SHOW INDEX FROM multiform_field  WHERE Key_name = 'form_id'")->fetch();
    if (!$index_exist) {
        $model->exec("ALTER TABLE multiform_field ADD INDEX `form_id` (`form_id`)");
    }
} catch (waDbException $e) {
    
}

try {
    $index_exist2 = $model->query("SHOW INDEX FROM multiform_records  WHERE Key_name = 'form_id'")->fetch();
    if (!$index_exist2) {
        $model->exec("ALTER TABLE multiform_records ADD INDEX `form_id` (`form_id`)");
    }
} catch (waDbException $e) {
    
}

try {
    $sql = <<<SQL
  CREATE TABLE IF NOT EXISTS `multiform_form` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `url` varchar(20) NOT NULL DEFAULT '',
    `name` varchar(30) NOT NULL DEFAULT '',
    `status` int(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;
    $model->exec($sql);
} catch (waDbException $e) {
    
}