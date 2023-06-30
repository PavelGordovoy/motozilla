<?php

class shopSksCli extends waCliController
{
    public function execute() {
        $c = file_get_contents('http://sks-service.org/ajax_dwprice.php?passw=8822');
        if(preg_match('/action="([^"]+)"/', $c, $m)) {
            $c2 = file_get_contents('http://sks-service.org/'.$m[1]);
            file_put_contents('/home/admin/web/motozilla.com.ua/public_html/importprice/SKS.xls', $c2);
        }
    }
}