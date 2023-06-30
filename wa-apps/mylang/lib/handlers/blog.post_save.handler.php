<?php

class mylangBlogPost_saveHandler extends waEventHandler
{
    public function execute(&$params)
    {
        $model = new blogPostModel();
        if (!array_key_exists('mylang_locale', $params)) {
            if (!$model->fieldExists('mylang_locale')) {
                $model->query("ALTER TABLE ".$model->getTableName()." ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
            }
        }
        return $model->updateById($params['id'], ['mylang_locale'=>ifset($params['plugin']['mylang_locale'])]);
    }
}
