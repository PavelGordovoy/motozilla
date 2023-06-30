<?php

$model = new waModel();
try {
    $model->query('SELECT params FROM shop_productmotos WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_productmotos ADD params TEXT NULL");
}