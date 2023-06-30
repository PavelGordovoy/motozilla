<?php

class mylangHubFrontend_navHandler extends mylangEventHandler
{
    public function execute(&$params)
    {
        //Пока только смена языка без редиректа.
        mylangHelper::addSmartyFilter();
        mylangRouting::redirect();
        if (!mylangHelper::checkSite(false)) {
            return;
        }
    }
}