<?php

$model = new waModel();

try {
    $model->query('SELECT sort_products FROM shop_productmotos WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE  `shop_productmotos` ADD `sort_products` VARCHAR(32) NULL DEFAULT NULL");
}

try {
    $model->query('SELECT enable_sorting FROM shop_productmotos WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `shop_productmotos` ADD `enable_sorting` TINYINT(1) NOT NULL DEFAULT 0");
}

try {
    $model->exec("ALTER TABLE `shop_productmotos` ADD INDEX  `url` (`url`)");
} catch (waDbException $e) {
}