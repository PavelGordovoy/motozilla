<?php

$model = new waAppSettingsModel();
$api = $model->get('mylang', 'microsoftapikey', '');
if ($api) {
    $model->set('mylang.microsoft', 'api', $api);
    waLog::log('Microsoft API Key migrated', 'mylangMicrosoft.log');
}