<?php
//fix old versions data
wa('mylang');
$model = new mylangModel();
$shop_types = ['product', 'category', 'service', 'service_value', 'feature', 'feature_value', 'tag', 'promos'];
try {
    $sql = "UPDATE ".$model->getTableName()." SET app_id = 'shop' WHERE type IN (s:shop_types) AND app_id IS NULL";
    $model->exec($sql, ['shop_types' => $shop_types]);
} catch (waDbException $e) {
    waLog::log($e->getMessage(), 'mylang_update.log');
}