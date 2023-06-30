<?php

class mylangBlogBackend_blog_editHandler extends waEventHandler
{
    public function execute(&$params)
    {
        if (!array_key_exists('mylang_locale', $params)) {
            $model = new blogBlogModel();
            if (!$model->fieldExists('mylang_locale')) {
                $model->query("ALTER TABLE ".$model->getTableName()." ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
            }
        }
        $select = mylangHelper::pageLocaleSelect($params, 'settings');
        $w_locale = _w('Locale');
        $html = <<<HTML
        <div class="field">
            <div class="name">$w_locale</div>
            <div class="value bold">{$select}</div>
        </div>
HTML;
        return ['settings' => $html];
    }
}
