<?php

class mylangMenuFrontend_menuHandler extends waEventHandler
{
    public function execute(&$params)
    {
        if (!mylangHelper::getSettings('app_menu') || !mylangHelper::checkSite()) {
            return;
        }
        $params = mylangViewHelper::categories($params);
    }
}
