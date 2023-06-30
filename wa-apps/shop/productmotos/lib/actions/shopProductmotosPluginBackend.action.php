<?php

class shopProductmotosPluginBackendAction extends waViewAction
{
    public function execute()
    {
        $this->view->assign('motos', shopProductmotosPlugin::getMotos());
    }
}