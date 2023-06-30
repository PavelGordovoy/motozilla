<?php

class mylangShopFrontend_my_orderHandler extends mylangShopEventHandler
{
    private $products = [];
    private $services = [];

    /**
     * @param $params
     * @throws waException
     */
    public function execute(&$params)
    {
        if (!mylangHelper::checkSite()||$this->checkVersion()) {
            return;
        }
        $view = wa()->getView();
        $translate = new mylangTranslate();
        $params = $translate->order($params);
        $view->assign('params', $params);
    }
}
