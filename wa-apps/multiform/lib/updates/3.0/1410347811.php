<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

$model = new waModel();

/* multiform_form */
try {
    $model->exec("ALTER TABLE `multiform_form` MODIFY COLUMN name VARCHAR(255) NOT NULL DEFAULT  ''");
} catch (waDbException $e) {

}
try {
    $model->exec("SELECT `title` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `title` VARCHAR(255) DEFAULT NULL");
}
try {
    $model->exec("ALTER TABLE `multiform_form` MODIFY COLUMN status TINYINT(1) NOT NULL DEFAULT  '1'");
} catch (waDbException $e) {

}
try {
    $model->exec("SELECT `description` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `description` TEXT");
}
try {
    $model->exec("SELECT `type` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `type` TINYINT(1) NOT NULL DEFAULT '1'");
}
try {
    $model->exec("SELECT `start` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `start` DATETIME DEFAULT NULL");
}
try {
    $model->exec("SELECT `end` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `end` DATETIME DEFAULT NULL");
}
try {
    $model->exec("SELECT `create_datetime` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `create_datetime` DATETIME DEFAULT NULL");
}
try {
    $model->exec("SELECT `create_contact_id` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `create_contact_id` INT(11) DEFAULT '0'");
}
try {
    $model->exec("SELECT `hash` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `hash` VARCHAR(15), ADD INDEX `hash` (`hash`)");
}
try {
    $key = $model->query("SHOW INDEX FROM `multiform_form` WHERE Key_name = 'url'")->fetchField();
    if (!$key) {
        $model->exec("ALTER TABLE `multiform_form` ADD INDEX `url` (`url`)");
    }
} catch (waDbException $e) {
}
try {
    $model->exec("SELECT `meta_title` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `meta_title` VARCHAR(255) DEFAULT ''");
}
try {
    $model->exec("SELECT `meta_keywords` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `meta_keywords` TEXT");
}
try {
    $model->exec("SELECT `meta_description` FROM `multiform_form` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_form` ADD `meta_description` TEXT");
}

/* multiform_field */
try {
    $model->exec("ALTER TABLE `multiform_field` CHANGE name title VARCHAR(255) NOT NULL DEFAULT ''");
} catch (waDbException $e) {

}
try {
    $model->exec("SELECT `title_pos` FROM `multiform_field` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field` ADD `title_pos` VARCHAR(4) NOT NULL DEFAULT ''");
}
try {
    $model->exec("ALTER TABLE `multiform_field` MODIFY COLUMN status TINYINT(1) NOT NULL DEFAULT '1'");
} catch (waDbException $e) {

}
try {
    $model->exec("ALTER TABLE `multiform_field` MODIFY COLUMN required TINYINT(1) NOT NULL DEFAULT  '0'");
} catch (waDbException $e) {

}
try {
    $model->exec("SELECT `hidden` FROM `multiform_field` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field` ADD `hidden` TINYINT(1) NOT NULL DEFAULT '0'");
}
// Изменяем тип поля у `type` с ENUM на VARCHAR
try {
    $database = wa()->getConfig()->getDatabase();
    $sql = "SELECT data_type FROM information_schema.columns WHERE ";
    if (isset($database['default']) && !empty($database['default']['database'])) {
        $sql .= "table_schema = '" . $database['default']['database'] . "' AND ";
    }
    $sql .= "table_name = 'multiform_field' AND column_name = 'type'";
    $field_type = $model->query($sql)->fetchField();
    if ($field_type == 'enum') {
        // Создаем временную колонку
        $model->exec("ALTER TABLE `multiform_field` ADD `new_type` VARCHAR (10) NOT NULL");
        // Копируем данные во временную колонку
        $model->exec("UPDATE `multiform_field` SET `new_type` = `type`");
        // Меняем название у старой колонки
        $model->exec("ALTER TABLE multiform_field CHANGE `type` `oldtype` enum('input','textarea','number','email','phone','date','time','checkbox','radio','select','file','rating','html','hidden','list','page_break','formula')");
        // Меняем название новой колонки
        $model->exec("ALTER TABLE `multiform_field` CHANGE `new_type` `type` VARCHAR (10) NOT NULL");
        // Удаляем старую колонку
        $model->exec("ALTER TABLE `multiform_field` DROP `oldtype`");
    }
} catch (waDbException $e) {

}
try {
    $model->exec("ALTER TABLE `multiform_field` CHANGE helper description TEXT");
} catch (waDbException $e) {

}
try {
    $model->exec("SELECT `disabled` FROM `multiform_field` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field` ADD `disabled` TINYINT(1) NOT NULL DEFAULT '0'");
}
try {
    $model->exec("SELECT `private` FROM `multiform_field` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field` ADD `private` TINYINT(1) NOT NULL DEFAULT '0'");
}
try {
    $model->exec("SELECT `unique_id` FROM `multiform_field` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field` ADD `unique_id` INT(11) NOT NULL, ADD INDEX `unique_id` (`unique_id`)");
}

/* multiform_field_options */
try {
    $model->exec("SELECT `image` FROM `multiform_field_options` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field_options` ADD `image` VARCHAR(30) NOT NULL DEFAULT ''");
}
try {
    $model->exec("SELECT `checked` FROM `multiform_field_options` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field_options` ADD `checked` TINYINT(1) NOT NULL DEFAULT '0'");
}
try {
    $model->exec("SELECT `ext` FROM `multiform_field_options` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field_options` ADD `ext` VARCHAR(30) NOT NULL DEFAULT ''");
}
try {
    $model->exec("SELECT `key` FROM `multiform_field_options` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field_options` ADD `key` VARCHAR(255) NOT NULL DEFAULT ''");
}
try {
    $model->exec("SELECT `formula` FROM `multiform_field_options` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_field_options` ADD `formula` VARCHAR(255) DEFAULT ''");
}

/* multiform_files */
try {
    $model->exec("ALTER TABLE `multiform_files` MODIFY COLUMN record_id INT(11) NOT NULL");
} catch (waDbException $e) {

}
try {
    $key = $model->query("SHOW INDEX FROM `multiform_files` WHERE Key_name = 'record_id'")->fetchField();
    if (!$key) {
        $model->exec("ALTER TABLE `multiform_files` ADD INDEX `record_id` (`record_id`)");
    }
} catch (waDbException $e) {
}
try {
    $model->exec("ALTER TABLE `multiform_files` MODIFY COLUMN filename VARCHAR(255) NOT NULL");
} catch (waDbException $e) {

}
try {
    $model->exec("SELECT `hash` FROM `multiform_files` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `multiform_files` ADD `hash` VARCHAR(20) NOT NULL");
}