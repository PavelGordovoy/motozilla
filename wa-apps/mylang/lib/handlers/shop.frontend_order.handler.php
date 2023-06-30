<?php

class mylangShopFrontend_orderHandler extends mylangShopEventHandler
{
    public function execute(&$params)
    {
        if ($this->checkVersion()) {
            return;
        }
        waLocale::setStrings(mylangViewHelper::customStrings()); //TODO:Check locale
    }
}
