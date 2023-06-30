<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
try {
    // Получаем все значения поля enum
    $columns = $model->query("SHOW COLUMNS FROM multiform_field WHERE Field = 'type'")->fetchAssoc();
    if (isset($columns['Type'])) {
        $type = $columns['Type'];
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        foreach (explode(',', $matches[1]) as $value) {
            $enum[] = trim($value, "'");
        }
        // Если не существует поля file, то добавляем его
        if (!in_array("file", $enum)) {
            $model->exec("ALTER TABLE multiform_field MODIFY COLUMN `type` enum('input','textarea','checkbox','radio','select','file')");
        }
    }
} catch (waDbException $e) {
    
}
// Проверяем существование поля multiple
try {
    $model->exec("SELECT multiple FROM multiform_field WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE multiform_field ADD multiple INT (1) NOT NULL DEFAULT '1'");
}

// Проверяем существование поля record_id
try {
    $model->exec("SELECT record_id FROM multiform_records WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE multiform_records ADD record_id INT (11) NOT NULL AUTO_INCREMENT PRIMARY KEY");
}

try {
    $sql = <<<SQL
  CREATE TABLE IF NOT EXISTS `multiform_field_extensions` (
    `field_id` int(11) NOT NULL,
    `extension` varchar(5) NOT NULL,
    KEY `field_id` (`field_id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;
    $model->exec($sql);

    $sql2 = <<<SQL
    CREATE TABLE IF NOT EXISTS `multiform_files` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `field_id` int(11) NOT NULL,
        `record_id` varchar(15) NOT NULL,
        `filename` varchar(30) NOT NULL,
        `create_datetime` datetime NOT NULL,
        `ip` varchar(15) NOT NULL,
        `status` int(1) NOT NULL DEFAULT '1',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8        
SQL;
    $model->exec($sql2);
} catch (waDbException $e) {
    
}