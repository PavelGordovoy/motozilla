<?php

/**
 * Class mylangSiteActions
 */
class mylangSiteActions extends waViewActions
{
    public function defaultAction()
    {
        $this->setLayout(new mylangBackendLayout());
    }

    public function authAction()
    {
        $config = $this->getConfig()->getAuth();
        $keys = [];
        if (!empty($config)) {
            foreach ($config as $site) {
                if(array_key_exists('fields', $site)) {
                    $keys += array_column($site['fields'], 'caption');
                }
            }
        }
        $this->view->assign('keys', $keys);
    }

    public function settingsAction()
    {
        $this->view->assign('settings', mylangHelper::getSettings());
    }
}
