<?php

class mylangSettingsActions extends waViewActions
{
    public function getLocaleAction()
    {
        $locale = waRequest::get('locale', null, 'string_trim');
        $info = waLocale::getInfo($locale);
        
        $weekday = [
            1 => _ws('Monday'),
            2 => _ws('Tuesday'),
            3 => _ws('Wednesday'),
            4 => _ws('Thursday'),
            5 => _ws('Friday'),
            6 => _ws('Saturday'),
            7 => _ws('Sunday'),
        ];
        $currency = waCurrency::getAll();
        foreach ($currency as $key => &$c) {
            $c .=' ('.$key.')';
        }
        $currency[''] = _w('Select currency');
        ksort($currency);
        $supported_locales = mylangLocale::checkServerLocale($locale);
        $this->view->assign(compact('weekday', 'locale', 'currency', 'info', 'supported_locales'));
    }
}
