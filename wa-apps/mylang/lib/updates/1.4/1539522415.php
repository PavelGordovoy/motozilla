<?php
//1.4.1
//fix null app_id
waLog::log('1.4.1:Phase 1', 'mylang_update.log');
$model = new mylangModel();
$shop_types = ['product', 'category', 'service', 'service_value', 'feature', 'feature_value', 'tag', 'promos'];
try {
    $sql = "UPDATE " . $model->getTableName() . " SET app_id = 'shop' WHERE type IN (s:shop_types) AND app_id IS NULL";
    $model->exec($sql, ['shop_types' => $shop_types]);
} catch (waDbException $e) {
    waLog::log($e->getMessage(), 'mylang_update.log');
}
waLog::log('1.4.1:Phase 2', 'mylang_update.log');
//Fix duplicates
$sql = "SELECT id, concat(locale, type,subtype,type_id) as idx2 from mylang";
$data = $model->query($sql)->fetchAll('idx2', 2);
function arcount($element)
{
    return (count($element) > 1);
}

$data = array_filter($data, 'arcount');
$bad = $good = [];
foreach ($data as $id) {
    $good[] = end($id);
    array_pop($id);
    $bad = array_merge($bad, $id);
}
waLog::log('1.4.1:Phase 3', 'mylang_update.log');
$model->deleteById($bad);
try {
    $model->updateById($good, ['app_id' => 'shop']);
} catch (waDbException $e) {
    waLog::log($e->getMessage(), 'mylang_update.log');
}

//final fix if any
try {
    $sql = "UPDATE " . $model->getTableName() . " SET app_id = 'shop' WHERE type IN (s:shop_types) AND app_id IS NULL";
    $model->exec($sql, ['shop_types' => $shop_types]);
} catch (waDbException $e) {
    waLog::log($e->getMessage(), 'mylang_update.log');
}
waLog::log('1.4.1:Phase 4', 'mylang_update.log');