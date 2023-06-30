<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
// Чистка мусора
$file = wa()->getAppPath("wa-apps/", "multiform");

try {
    if (file_exists($file)) {
        waFiles::delete($file, true);
    }
} catch (Exception $e) {
    
}