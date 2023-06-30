<?php

class mylangSettingsProvidersAction extends waViewAction
{

    public function execute()
    {
        $plugins = wa('mylang')->getConfig()->getProviders();
        $this->view->assign('plugins', $plugins);
    }
}