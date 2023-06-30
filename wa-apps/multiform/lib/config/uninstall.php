<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
try {
    // Удаление прикрепленных файлов
    $path = wa()->getDataPath("files/", false, 'multiform', false);
    waFiles::delete($path, false);
} catch (Exception $e) {
    
}
try {
    $path2 = wa()->getDataPath("fields/", false, 'multiform', false);
    waFiles::delete($path2, false);
} catch (Exception $e) {
    
}