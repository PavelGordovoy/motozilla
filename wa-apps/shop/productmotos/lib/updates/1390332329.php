<?php

$model = new waModel();

// url
try {
    $model->query("SELECT url FROM shop_productmotos WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_productmotos ADD url VARCHAR (255) NULL");
}


// filter
try {
    $model->query("SELECT filter FROM shop_productmotos WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_productmotos ADD filter TEXT NULL");
}


// hidden
try {
    $model->query("SELECT hidden FROM shop_productmotos WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_productmotos ADD hidden TINYINT(1) NOT NULL DEFAULT 0");
}
