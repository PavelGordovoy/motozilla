<?php

$path = wa()->getAppPath(null, 'mini');

$files = [
    '/css/protip.min.css',
    '/js/protip.min.js',
    '/lib/actions/backend/miniBackend.action.php',
    '/lib/actions/backend/miniBackendMaster.controller.php',
    '/lib/actions/backend/miniBackendPagespeed.controller.php',
    '/lib/actions/backend/miniBackendPagespeedlong.controller.php',
    '/lib/actions/backend/miniBackendSettings.controller.php',
    '/lib/classes/miniCompileHtml.class.php',
    '/lib/classes/miniCss.class.php',
    '/lib/classes/miniJs.class.php',
    '/templates/actions/',
    '/lib/vendors/jmathai',
    '/lib/vendors/closure.php'
];

foreach ($files as $file) {
    try {
        waFiles::delete($path . $file);
    } catch (Exception $e) {

    }
}