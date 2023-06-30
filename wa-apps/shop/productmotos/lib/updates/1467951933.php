<?php

$model = new waModel();
try {
    $model->query('SELECT h1 FROM shop_productmotos WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_productmotos ADD h1 VARCHAR(255) NULL");
}