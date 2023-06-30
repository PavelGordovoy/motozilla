<?php

class mylangBlogFrontend_postHandler extends waEventHandler
{
    public function execute(&$params)
    {
        mylangRouting::redirect();
        if (mylangHelper::checkSite()) {
            $params = mylangHelper::getPagesByLocale($params, mylangLocale::currentLocale());
        }
       // waLocale::setStrings(mylangViewHelper::customStrings());
    }
}
