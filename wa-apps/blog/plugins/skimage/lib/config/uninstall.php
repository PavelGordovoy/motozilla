<?php

/*Удаляем таблицы плагина*/
$model = new waModel();
$model->query('DROP TABLE IF EXISTS `blog_skimage_groups`');
$model->query('DROP TABLE IF EXISTS `blog_skimage_data`');

waFiles::delete(wa()->getDataPath("skimage/", true, 'blog'), false);
waFiles::delete(wa()->getDataPath("skimage/", false, 'blog'), false);