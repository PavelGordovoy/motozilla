<?php

class shopTataCli extends waCliController
{
    public function execute() {
        $today = date("d.m.Y");
        $r = file_get_contents('https://motoblok-tata.com.ua/1c_2/Prices/zap/%CF%F0%E0%E9%F1-%EB%E8%F1%F2%20%C7%E0%EF%F7%E0%F1%F2%E8%20%D2%C0%D2%C0%20%EE%F2%20'.$today.'.xlsx?v=668');
        file_put_contents('/home/admin/web/motozilla.com.ua/public_html/importprice/TATA.xlsx', $r);
    }
}