<?php

$files = [
    'lib/classes/mylangTranslateGoogle.class.php',
    'lib/classes/mylangTranslateYandex.class.php',
    'lib/classes/mylangTranslateMicrosoft.class.php'
];

foreach ($files as $file) {
    $path = wa()->getAppPath($file, 'mylang');
    waFiles::delete($path, true);
}

$app_settings_model = new waAppSettingsModel();
$app_settings_model->set('mylang', 'welcome', true);