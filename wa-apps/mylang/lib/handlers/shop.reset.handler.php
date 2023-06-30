<?php

class mylangShopResetHandler extends mylangShopEventHandler
{

    public function execute(&$params)
    {
        $model = new mylangModel();
        $model->exec('DELETE from TABLE `mylang` where `app_id`="shop";');
    }
}
