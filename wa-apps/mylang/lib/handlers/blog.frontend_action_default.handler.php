<?php

class mylangBlogFrontend_action_defaultHandler extends waEventHandler
{
    public function execute(&$params)
    {
        mylangRouting::redirect();
        mylangHelper::addSmartyFilter();
        if (!mylangHelper::checkSite(false)) {
            return;
        }
    }
}
