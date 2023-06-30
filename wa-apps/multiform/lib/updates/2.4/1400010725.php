<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
try {
    $sql = "CREATE TABLE IF NOT EXISTS `multiform_mask` (                         
                  `id` int(11) NOT NULL AUTO_INCREMENT,                 
                  `name` varchar(50) NOT NULL,                          
                  `mask` varchar(255) NOT NULL,                         
                  `error` varchar(255) DEFAULT '',                      
                  PRIMARY KEY (`id`)                                    
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
    $model->exec($sql);
} catch (waDbException $e) {
    
}

try {
    $model->exec("SELECT mask_id FROM `multiform_field` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field` ADD mask_id INT (3) NOT NULL DEFAULT  '0'");
}

try {
    $model->exec("SELECT code FROM `multiform_field` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field` ADD code VARCHAR (50) NOT NULL DEFAULT  ''");
}

try {
    $model->exec("SELECT helper FROM `multiform_field` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field` ADD helper TEXT");
}

try {
    $model->exec("SELECT class FROM `multiform_field` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field` ADD class VARCHAR (50) NOT NULL DEFAULT  ''");
}

try {
    $model->exec("SELECT callback_text FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD callback_text TEXT");
}

try {
    $model->exec("SELECT email_title FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD email_title VARCHAR(50) NOT NULL DEFAULT  ''");
}

try {
    $model->exec("SELECT email_from FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD email_from VARCHAR(50) NOT NULL DEFAULT  ''");
}

try {
    // Получаем все значения поля enum
    $columns = $model->query("SHOW COLUMNS FROM multiform_field WHERE Field = 'type'")->fetchAssoc();
    if (isset($columns['Type'])) {
        $type = $columns['Type'];
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        foreach (explode(',', $matches[1]) as $value) {
            $enum[] = trim($value, "'");
        }
        // Если не существует поля html, то добавляем его
        if (!in_array("html", $enum)) {
            $model->exec("ALTER TABLE multiform_field MODIFY COLUMN `type` enum('input','textarea','checkbox','radio','select','file','html')");
        }
    }
} catch (waDbException $e) {
    
}