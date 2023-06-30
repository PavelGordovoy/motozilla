<?php
//site
if (wa()->appExists('site')) {
    wa('site');
    $model = new sitePageModel();
    if (!$model->fieldExists('mylang_locale')) {
        $model->query("ALTER TABLE `site_page` ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
    }
}
//shop
if (wa()->appExists('shop')) {
    wa('shop');
    //Page
    $model = new shopPageModel();
    if (!$model->fieldExists('mylang_locale')) {
        $model->query("ALTER TABLE ".$model->getTableName()." ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
    }
    //Product
    $model = new shopProductPagesModel();
    if (!$model->fieldExists('mylang_locale')) {
        $model->exec("ALTER TABLE ".$model->getTableName()." ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
    }
}
//blog
if (wa()->appExists('blog')) {
    wa('blog');
    //Blog
    $model = new blogBlogModel();
    if (!$model->fieldExists('mylang_locale')) {
        $model->query("ALTER TABLE ".$model->getTableName()." ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
    }
    //Post
    $model = new blogPostModel();
    if (!$model->fieldExists('mylang_locale')) {
        $model->query("ALTER TABLE ".$model->getTableName()." ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
    }
    //Page
    $model = new blogPageModel();
    if (!$model->fieldExists('mylang_locale')) {
        $model->query("ALTER TABLE ".$model->getTableName()." ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
    }
}
