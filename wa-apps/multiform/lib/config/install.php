<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
$app_settings = new waAppSettingsModel();
try {
    $email_mask = $model->query("insert into `multiform_mask` (`name`, `mask`, `error`) values('Email','".$model->escape('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/')."','" . _w("Print valid email address") . "');")->lastInsertId();
} catch (waDbException $e) {
    
}

try {
    $digits_mask = $model->query("insert into `multiform_mask` (`name`, `mask`, `error`) values('" . _w("Only digits") . "','".$model->escape('/^\d+$/')."','" . _w("Print only digits") . "');")->lastInsertId();
} catch (waDbException $e) {
    
}
$app_settings->set('multiform', 'regexp_init', 1);

$path = wa()->getDataPath('fields', true, 'multiform');
waFiles::write($path.'/thumb.php', '<?php
$file = realpath(dirname(__FILE__)."/../../../../")."/wa-apps/multiform/lib/config/data/thumb.php";

if (file_exists($file)) {
    include($file);
} else {
    header("HTTP/1.0 404 Not Found");
}
');
waFiles::copy(wa()->getAppPath('lib/config/data/.htaccess', 'multiform'), $path.'/.htaccess');