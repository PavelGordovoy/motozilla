<?php

class mylangPhotosFrontend_layoutHandler extends waEventHandler
{
    public function execute(&$params) //null
    {
        mylangHelper::addSmartyFilter();
        mylangRouting::redirect();
        if (!mylangHelper::checkSite()) {
            return;
        }
    }
}
