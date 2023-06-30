<?php

class shopPriceRoundingCli extends waCliController
{
    public function execute()
    {
       $model = new waModel();
       $model->query('UPDATE `shop_product` SET `price`=CEIL(price), max_price=CEIL(max_price), min_price=ceil(min_price);');
    }
}