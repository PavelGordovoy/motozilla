<?php

class mylangSettingsSelectorAction extends waViewAction
{
    public function execute()
    {
        $locales = waLocale::getAll('name');
        wa('site');
        $domains = siteHelper::getDomains(1);
        $rules = new mylangRules();
        $redirect = $rules->getAll();
        $this->view->assign(compact('redirect', 'domains', 'locales'));
    }
}
