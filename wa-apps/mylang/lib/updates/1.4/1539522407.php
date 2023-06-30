<?php

wa('mylang');
$model = new mylangModel();
$sql1 = 'UPDATE '.$model->getTableName().' SET text = REPLACE(text, "< p>", "<p>") WHERE text LIKE "%< p>%";';
$sql2 = 'UPDATE '.$model->getTableName().' SET text = REPLACE(text, "</ p>", "</p>") WHERE text LIKE "%</ p>%";';
$model->exec($sql1);
$model->exec($sql2);
