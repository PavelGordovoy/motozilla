<?php

$path = realpath(dirname(__FILE__) . "/../../../../../");
$config_path = $path . "/wa-config/SystemConfig.class.php";
if (!file_exists($config_path)) {
    header("Location: ../../../wa-apps/multiform/img/image-not-found.png");
    exit;
}

require_once($config_path);
$config = new SystemConfig();
waSystem::getInstance(null, $config);
$app_config = wa('multiform')->getConfig();
$request_file = $app_config->getRequestUrl(true, true);
$request_file = preg_replace("@^thumb.php(/field)?/?@", '', $request_file);
$public_path = wa()->getDataPath('fields/', true, 'multiform');

$main_thumb_file = false;
$size = false;
$enable_2x = false;

if (preg_match('#([0-9]+)/([0-9]+)/([a-zA-Z0-9_\.-]+)\.(\d+(?:x\d+)?)(@2x)?\.([a-z]{3,4})#i', $request_file, $matches)) {
    $field_id = $matches[1];
    $option_id = $matches[2];
    $original_file = $matches[3] . '.original.' . $matches[6];
    $size = $matches[4];

    // TODO enable_2x  && $app_config->getOption('enable_2x')
    if ($matches[5]) {
        $enable_2x = true;
        $size = explode('x', $size);
        foreach ($size as &$s) {
            $s *= 2;
        }
        unset($s);
        $size = implode('x', $size);
    }
}
wa()->getStorage()->close();
$original_path = $public_path . $field_id . '/' . $option_id . '/' . $original_file;
$thumb_path = $public_path . $request_file;
if (file_exists($original_path) && !file_exists($thumb_path)) {
    $thumbs_dir = dirname($thumb_path);
    if (!file_exists($thumbs_dir)) {
        waFiles::create($thumbs_dir);
    }
    $image = multiformImage::generateThumb($original_path, $size);
    if ($image) {
        $image->save($thumb_path, $app_config->getSaveQuality($enable_2x));
        clearstatcache();
    }
}

if (file_exists($thumb_path)) {
    waFiles::readFile($thumb_path);
} else {
    header("HTTP/1.0 404 Not Found");
    exit;
}
