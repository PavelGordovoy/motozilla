<?php
/**Clean vendors**/
$files = [
    'chosen.jquery.min.js',
    'chosen.min.css',
    'chosen-sprite@2x.png',
    'chosen-sprite.png',
    'TrekkSoft',
];

$path = wa()->getAppPath('lib/vendors/', 'mylang');
foreach ($files as $file) {
    waFiles::delete($path.$file, true);
}
