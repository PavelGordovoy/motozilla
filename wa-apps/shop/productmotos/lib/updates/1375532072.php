<?php

$path = wa()->getDataPath('motos', true, 'shop');
waFiles::write($path.'/thumb.php', '<?php
$file = realpath(dirname(__FILE__)."/../../../../")."/wa-apps/shop/plugins/productmotos/lib/thumb.php";

if (file_exists($file)) {
    include($file);
} else {
    header("HTTP/1.0 404 Not Found");
}
');