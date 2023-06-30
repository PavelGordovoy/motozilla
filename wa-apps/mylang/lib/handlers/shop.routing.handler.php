<?php

class mylangShopRoutingHandler extends mylangShopEventHandler
{
    public function execute(&$params)
    {
        if (!mylangHelper::checkSite()||$this->checkVersion()) {
            return;
        }
        return [
            0 => [
            'url' => 'search/',
            'app' => 'mylang',
            'module' => 'shop',
            'action' => 'search',
            'plugin' => null, //fw 1.6.0
            ],
        ];
    }
}
