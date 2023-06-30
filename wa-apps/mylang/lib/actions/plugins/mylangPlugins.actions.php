<?php

class mylangPluginsActions extends waPluginsActions
{
    protected $plugins_hash = '#';
    protected $is_ajax = false;

    public function defaultAction()
    {
        if (!$this->getUser()->isAdmin($this->getApp())) {
            throw new waRightsException(_w('Access denied'));
        }
        $this->setLayout(new mylangBackendLayout());
        $this->getResponse()->setTitle(_w('Plugin settings page'));
        parent::defaultAction();
    }
}
