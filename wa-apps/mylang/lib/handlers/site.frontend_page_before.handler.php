<?php

class mylangSiteFrontend_page_beforeHandler extends waEventHandler
{
    public function execute(&$params)
    {
        waConfig::set('mylang_this_domain', $params['domain']);
        mylangRouting::redirect();
        if (!mylangHelper::checkSite(false)) {
            return;
        }
    }
}
