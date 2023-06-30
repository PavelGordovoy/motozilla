<?php

class mylangShopProduct_deleteHandler extends waEventHandler
{
    public function execute(&$params)
    {
        $model = new mylangModel();
        $ids = implode(',', $params['ids']);
        $model-> exec("DELETE FROM `mylang` WHERE `type`='product' and `type_id` IN (".$ids.");");
    }
}
