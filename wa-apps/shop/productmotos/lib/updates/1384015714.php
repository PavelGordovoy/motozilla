<?php

$model = new waModel();

try {
    $model->query('SELECT seo_description FROM shop_productmotos WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_productmotos ADD seo_description TEXT NULL");
}