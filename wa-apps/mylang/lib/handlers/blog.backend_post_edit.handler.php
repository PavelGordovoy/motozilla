<?php

class mylangBlogBackend_post_editHandler extends waEventHandler
{
    public function execute(&$params)
    {
        if (!array_key_exists('mylang_locale', $params)) {
            $model = new blogPostModel();
            if (!$model->fieldExists('mylang_locale')) {
                $model->query("ALTER TABLE ".$model->getTableName()." ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
            }
        }
        $select = '<div class="block"><h6>'._w('Locale').'</h6>';
        $select .= mylangHelper::pageLocaleSelect($params, 'plugin');
        $select .='</div>';
        return ['sidebar' => $select];
    }
}
