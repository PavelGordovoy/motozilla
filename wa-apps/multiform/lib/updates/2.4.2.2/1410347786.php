<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();

try {
    $model->exec("ALTER TABLE `multiform_form` MODIFY COLUMN email VARCHAR(500) NOT NULL DEFAULT  ''");
} catch (waDbException $e) {
}

