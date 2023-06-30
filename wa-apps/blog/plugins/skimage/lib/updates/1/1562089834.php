<?php

$model = new waModel();

try{
    $sql = "ALTER TABLE `blog_skimage_data` ADD `query` VARCHAR(64) NOT NULL DEFAULT '' AFTER `size`;";

    $model->query($sql);

}catch(waDbException $e){ }
