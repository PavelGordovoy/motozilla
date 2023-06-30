<?php

class mylangShopFrontend_compareHandler extends mylangShopEventHandler
{
    public function execute(&$params)
    {
        if (!mylangHelper::checkSite()||$this->checkVersion()) {
            return $params;
        }
        if (!empty($params['features'])) {
            $params['features'] = mylangTranslate::features($params['features']);
        }
        return $params;
    }
}