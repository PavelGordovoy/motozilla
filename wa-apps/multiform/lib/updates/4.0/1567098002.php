<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

// Удаление ненужных файлов
$files = array(
    dirname(__FILE__) . '/../../../lib/actions/backend/multiformBackendDelete.controller.php',
    dirname(__FILE__) . '/../../../lib/actions/records/',
    dirname(__FILE__) . '/../../../lib/models/multiformRecords.model.php',
    dirname(__FILE__) . '/../../../templates/actions/records/',
    dirname(__FILE__) . '/../../../js/multiform-gap.js',
    dirname(__FILE__) . '/../../../js/records/',
    dirname(__FILE__) . '/../../../css/multiform-gap.css',
);
foreach ($files as $file) {
    try {
        waFiles::delete($file, true);
    } catch (waException $e) {

    }
}