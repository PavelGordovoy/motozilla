<?php

$model = new waAppSettingsModel();
$api = $model->get('mylang', 'googleapikey', '');
if ($api) {
    $model->set('mylang.google', 'api', $api);
    waLog::log('Google APi Key migrated', 'mylangGoogle.log');
}