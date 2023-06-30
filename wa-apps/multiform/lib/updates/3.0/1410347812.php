<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
wa('multiform');

$mfp = new multiformFieldParamsModel();

/* multiform_field */
try {
    $mf = new multiformFieldModel();
    $mm = new multiformMaskModel();
    $fields = $mf->getAll();
    $masks = $mm->getMasks();
    if ($fields) {
        $data = array();
        foreach ($fields as $f) {
            if (($f['type'] == 'input' || $f['type'] == 'textarea') && !empty($f['max_length'])) {
                $data[] = array(
                    'field_id' => $f['id'],
                    'param' => 'validation',
                    'ext' => 'maxlength',
                    'value' => $f['max_length']
                );
            }
            if (($f['type'] == 'input' || $f['type'] == 'textarea') && !empty($f['mask_id'])) {
                $data[] = array(
                    'field_id' => $f['id'],
                    'param' => 'validation',
                    'ext' => 'mask',
                    'value' => 1
                );
                $data[] = array(
                    'field_id' => $f['id'],
                    'param' => 'validation',
                    'ext' => 'regexp',
                    'value' => !empty($masks[$f['mask_id']]) ? $masks[$f['mask_id']]['mask'] : ""
                );
                $data[] = array(
                    'field_id' => $f['id'],
                    'param' => 'validation',
                    'ext' => 'regexp_error',
                    'value' => !empty($masks[$f['mask_id']]) ? $masks[$f['mask_id']]['error'] : ""
                );
            }
            if (!empty($f['description'])) {
                $mf->updateById($f['id'], array("description" => str_replace('{$wa_url}', wa()->getRootUrl(true), $f['description'])));
            }
        }
        if ($data) {
            $mfp->multipleInsert($data);
        }
    }
} catch (waDbException $e) {
    
}
try {
    $model->exec("ALTER TABLE multiform_field DROP COLUMN max_length");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_field DROP COLUMN mask_id");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_field DROP COLUMN multiple");
} catch (waDbException $ex) {
    
}

/* multiform_field_extensions */
try {
    $model->exec("SELECT `field_id` FROM `multiform_field_extensions` WHERE 0");
    $mfe = new multiformFieldExtensionsModel();
    $extensions = array();
    foreach ($mfe->query("SELECT * FROM {$mfe->getTableName()}") as $r) {
        $extensions[$r['field_id']][] = $r['extension'];
    }
    if ($extensions) {
        $ext_data = array();
        foreach ($extensions as $field_id => $ext) {
            $ext_data[] = array(
                'field_id' => $field_id,
                'param' => 'validation',
                'ext' => 'file_extension',
                'value' => serialize(array_values($ext))
            );
        }
        if ($ext_data) {
            $mfp->multipleInsert($ext_data);
        }
    }
    $model->exec("DROP TABLE IF EXISTS multiform_field_extensions");
} catch (waDbException $ex) {
    
}

/* multiform_form */
try {
    $m = new multiformFormModel();
    $mp = new multiformFormParamsModel();
    $forms = $m->getAll();
    if ($forms) {
        $params = array();
        foreach ($forms as $form) {
            if (!empty($form['email'])) {
                $params[] = array(
                    'form_id' => $form['id'],
                    'param' => 'email',
                    'ext' => 'notifications',
                    'value' => $form['email']
                );
            }
            if (!empty($form['email_sender'])) {
                $params[] = array(
                    'form_id' => $form['id'],
                    'param' => 'email_sender',
                    'ext' => 'notifications',
                    'value' => $form['email_sender']
                );
            }
            if (!empty($form['email_title'])) {
                $params[] = array(
                    'form_id' => $form['id'],
                    'param' => 'email_subject_record',
                    'ext' => 'notifications',
                    'value' => $form['email_title']
                );
            }
            if (!empty($form['email_from'])) {
                $params[] = array(
                    'form_id' => $form['id'],
                    'param' => 'email_sender_name_record',
                    'ext' => 'notifications',
                    'value' => $form['email_from']
                );
            }
            if (!empty($form['callback'])) {
                $params[] = array(
                    'form_id' => $form['id'],
                    'param' => 'js_callback',
                    'ext' => 'settings',
                    'value' => $form['callback']
                );
            }

            if (!empty($form['css'])) {
                $params[] = array(
                    'form_id' => $form['id'],
                    'param' => 'css_class',
                    'ext' => 'settings',
                    'value' => $form['css']
                );
            }
            if (!empty($form['callback_text'])) {
                $params[] = array(
                    'form_id' => $form['id'],
                    'param' => 'callback_text',
                    'ext' => 'settings',
                    'value' => str_ireplace('%recordId%', '{Request:ID}', $form['callback_text'])
                );
            }
        }
        if (!empty($params)) {
            $mp->multipleInsert($params);
        }
    }
} catch (waDbException $e) {
    
}
try {
    $model->exec("ALTER TABLE multiform_form DROP COLUMN email");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_form DROP COLUMN email_sender");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_form DROP COLUMN css");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_form DROP COLUMN website_sender");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_form DROP COLUMN callback");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_form DROP COLUMN callback_text");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_form DROP COLUMN email_title");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_form DROP COLUMN email_from");
} catch (waDbException $ex) {
    
}
try {
    // Генерируем поле hash у всех форм
    $form_model = new multiformFormModel();
    $forms_all = $form_model->getAll();
    foreach ($forms_all as $form) {
        if (empty($form['hash'])) {
            $hash = md5($form['id'] . $form['url']);
            $form_hash = substr($hash, rand(0, 7), 7) . substr($hash, rand(15, 22), 7);
            $form_model->updateById($form['id'], array("hash" => $form_hash));
        }
    }
} catch (waDbException $e) {
    
}

/* multiform_params */
try {
    $model->exec("DROP TABLE IF EXISTS multiform_params");
} catch (waDbException $ex) {
    
}

/* multiform_files */
try {
    $model->exec("ALTER TABLE multiform_files DROP COLUMN create_datetime");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_files DROP COLUMN ip");
} catch (waDbException $ex) {
    
}
try {
    $model->exec("ALTER TABLE multiform_files DROP COLUMN status");
} catch (waDbException $ex) {
    
}
try {
    $files_model = new multiformFilesModel();
    $files = $files_model->getAll();
    $hashes = array();
    if (!empty($files)) {
        foreach ($files as $file) {
            if (empty($file['hash'])) {
                $hashes[] = $file['hash'];
            }
        }
        foreach ($files as $file) {
            if (empty($file['hash'])) {
                // Создаем уникальный хеш для файла
                do {
                    $hash = substr(md5(uniqid()), rand(0, 9), rand(18, 20));
                } while (in_array($hash, $hashes));
                $files_model->updateById($file['id'], array("hash" => $hash));
            }
        }
    }
} catch (waDbException $ex) {
    
}
try {
    $key = $model->query("SHOW INDEX FROM `multiform_files` WHERE Key_name = 'hash'")->fetchField();
    if (!$key) {
        $model->exec("ALTER TABLE `multiform_files` ADD UNIQUE `hash` (`hash`)");
    }
} catch (waDbException $e) {
}

try {
    $path = wa()->getDataPath('fields', true, 'multiform');
    if (!file_exists($path . '/thumb.php')) {
        waFiles::write($path . '/thumb.php', '<?php
$file = realpath(dirname(__FILE__)."/../../../../")."/wa-apps/multiform/lib/config/data/thumb.php";

if (file_exists($file)) {
    include($file);
} else {
    header("HTTP/1.0 404 Not Found");
}
');
        waFiles::copy(wa()->getAppPath('lib/config/data/.htaccess', 'multiform'), $path . '/.htaccess');
    }
} catch (Exception $ex) {
    
}