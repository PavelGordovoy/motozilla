<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();

try {
    $model->exec("SELECT `id` FROM `multiform_record_values` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_record_values` DROP PRIMARY KEY, 
                      ADD `id` INT (11) NOT NULL AUTO_INCREMENT PRIMARY KEY");
}

try {
    $key = $model->query("SHOW INDEX FROM `multiform_record_values` WHERE Key_name = 'record_id'")->fetchField();
    if (!$key) {
        $model->exec("ALTER TABLE `multiform_record_values` ADD UNIQUE KEY `record_id` (`record_id`, `field_id`, `cloned_index`, `ext`)");
    }
} catch (waDbException $e) {
}

try {
    $model->exec("SELECT `field_id` FROM `multiform_files` WHERE 0");

    $mrv = new multiformRecordValuesModel();
    $mf = new multiformFilesModel();
    $files = $mf->getAll();
    if ($files) {
        foreach ($files as $file) {
            if ($file['field_id'] !== '0') {
                $field = $mrv->getByField([
                    'field_id' => $file['field_id'],
                    'record_id' => $file['record_id'],
                    'ext' => 'file'
                ]);
                if ($field) {
                    $mf->updateById($file['id'], [
                        'record_id' => $field['id']
                    ]);
                }
            }
        }
    }
} catch (waDbException $ex) {

}

try {
    $model->exec("ALTER TABLE multiform_files DROP COLUMN `field_id`");
} catch (waDbException $ex) {

}