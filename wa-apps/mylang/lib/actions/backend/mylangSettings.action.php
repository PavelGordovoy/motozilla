<?php

class mylangSettingsAction extends waViewAction
{
    public function execute()
    {
        if (!$this->getUser()->isAdmin('mylang')) {
            throw new waException(_w('Access denied'));
        }
        $ajax = waRequest::isXMLHttpRequest();
        if (!$ajax) {
            $this->setLayout(new mylangBackendLayout());
        }
        $settings = mylangHelper::getSettings();
        $supported_locales = ifempty($settings['locales']) ? mylangLocale::checkServerLocale($settings['locales']) : [];
        $settings['locales'] = ifempty($settings['locales'], []);
        $settings['domains'] = ifempty($settings['domains'], []);
        $settings['url'] = '?module=settings';
        $locales = waLocale::getAll('name');
        wa()->getResponse()->addJs('js/chosen.jquery.min.js', 'mylang');
        wa()->getResponse()->addCss('css/chosen.min.css', 'mylang');
        wa('site');
        $domains = siteHelper::getDomains(1);
        $fulllist = mylangLocale::getFullList(true);
        $currency = waCurrency::getAll();
        foreach ($currency as $key => &$c) {
            $c .=' ('.$key.')';
        }
        $currency[''] = _w('Select currency');
        ksort($currency);
        $separator = [ //not used
            ' ',
            "'",
            ',',
            '.',
            '·',
            ' ',
            ' ',
            '˙',
            '٫',
            '٬',
            '⎖',
        ];
        $weekday = [
            1 => _ws('Monday'),
            2 => _ws('Tuesday'),
            3 => _ws('Wednesday'),
            4 => _ws('Thursday'),
            5 => _ws('Friday'),
            6 => _ws('Saturday'),
            7 => _ws('Sunday'),
        ];
        /* @var $providers array*/
        $providers = wa('mylang')->getConfig()->getProviders(true);
        $version = wa('mylang')->getVersion('mylang');
        $this->view->assign(compact('settings', 'locales', 'providers', 'domains', 'ajax', 'fulllist', 'currency', 'separator', 'weekday', 'supported_locales', 'version'));
    }
}
