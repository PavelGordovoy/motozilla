<?php

class mylangBlogPrepare_blogs_frontendHandler extends waEventHandler
{
    public function execute(&$params = null)
    {
        mylangHelper::addSmartyFilter();
        if (!mylangHelper::checkSite()) {
            return;
        }
        mylangRouting::redirect();
    }
}
