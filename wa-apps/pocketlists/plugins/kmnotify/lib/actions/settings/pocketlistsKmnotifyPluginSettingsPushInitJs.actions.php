<?php

/**
 * Class pocketlistsKmnotifyPluginSettingsPushInitJs
 */
class pocketlistsKmnotifyPluginSettingsPushInitJs extends pocketlistsViewActions
{
    public function initJsAction()
    {
        $this->getResponse()->addHeader('Content-type', 'application/javascript');
        $this->getResponse()->sendHeaders();

        try {
            $push = wa()->getPush();
            if (!$push->isEnabled()) {
                return;
            }
            $init_js = $push->getInitJs();
            echo $init_js;
        } catch (waException $e){}
    }
}
