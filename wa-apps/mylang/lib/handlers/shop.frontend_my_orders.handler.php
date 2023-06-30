<?php

class mylangShopFrontend_my_ordersHandler extends mylangShopEventHandler
{
    
    public function execute(&$params)
    {
        if (!mylangHelper::checkSite()||$this->checkVersion()) {
            return;
        }
        $view = wa()->getView();
        $translate = new mylangTranslate();
        foreach ($params as &$order) {
            $order = $translate->order($order);
        }
        $view->assign('params', $params);
    }
}
