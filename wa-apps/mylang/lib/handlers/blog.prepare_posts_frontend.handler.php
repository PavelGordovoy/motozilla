<?php

class mylangBlogPrepare_posts_frontendHandler extends waEventHandler
{
    public function execute(&$params)
    {
        if (mylangHelper::checkSite()) {
            $params = mylangHelper::getPagesByLocale($params, mylangLocale::currentLocale());
        }
    }
}
