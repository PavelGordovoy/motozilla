<?php

class mylangSiteFrontend_navHandler extends waEventHandler
{
    public function execute(&$params) //null
    {
        mylangHelper::addSmartyFilter();
        if (!mylangHelper::checkSite()) {
            return;
        }
        mylangRouting::redirect();
    }
}
