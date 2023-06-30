<?php

class mylangShopBackend_product_editHandler extends mylangShopEventHandler
{
    public function execute(&$params = null)
    {
        if (!$params || $this->checkVersion()) {
            return false;
        }
        wa('shop');
        $model = new shopProductPagesModel();
        if (!$model->fieldExists('mylang_locale')) {
            $model->exec('ALTER TABLE ' .$model->getTableName(). ' ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL'
            );
            //First run - empty fields.
            return [
                'basics' =>'<script>$.mylang.pages =[];</script>',
            ];
        }
        $sql = 'SELECT `id`, `mylang_locale` FROM ' . $model->getTableName() . ' WHERE `product_id` = i:0';
        $pages_locale = $model->query($sql, $params['id'])->fetchAll('id', true);
        if (!$pages_locale) {
            $pages_locale = [];
        }
        $pages_locale = '<script>$.mylang.pages =' . json_encode($pages_locale) . ';</script>';
        return [
            'basics' => $pages_locale,
        ];
    }
}
