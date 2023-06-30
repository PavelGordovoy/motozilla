<?php
//Clean vendors
$files = [
    'lib/vendors/gettext/languages/bin/export.cmd',
    'lib/vendors/gettext/languages/bin/export.sh',
    'lib/vendors/gettext/languages/bin/export.php',
];

$path = wa()->getAppPath('', 'mylang');
foreach ($files as $file) {
    waFiles::delete($path.'/'.$file, true);
}
