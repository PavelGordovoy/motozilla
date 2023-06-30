<?php

class mylangShopProduct_sku_deleteHandler extends waEventHandler
{
    public function execute(&$params)
    {
        $model = new mylangModel();
        $id = 'skuname_'.$params['id'];
        $model-> exec("DELETE FROM `mylang` WHERE `subtype`=?;", $id);
    }
}
