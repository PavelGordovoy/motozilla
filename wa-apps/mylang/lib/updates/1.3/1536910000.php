<?php

wa('mylang');
$model = new mylangModel();
try {
$model->exec('ALTER TABLE mylang DROP INDEX locale;');
} catch (waDbException $e) {
    waLog::log($e->getMessage(), 'mylang_update.log');
}

try {
    $model->exec('ALTER TABLE `mylang` ADD UNIQUE `locale` (`locale`, `type`, `subtype`, `type_id`, `app_id`);');
} catch (waDbException $e) {
    waLog::log($e->getMessage(), 'mylang_update.log');
}
