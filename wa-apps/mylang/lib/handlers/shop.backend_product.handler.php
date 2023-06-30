<?php

class mylangShopBackend_productHandler extends mylangShopEventHandler
{
    public function execute(&$params = null)
    {
        if (!$params || $this->checkVersion()) {
            return false;
        }
        $translate = new mylangTranslate();
        $translation = json_encode($translate->productsIds($params['id']));
        $main = mylangHelper::getSettings('main_language');
        if (empty($main)) {
            $main = json_encode(['name'=>_w('None')]);
        } else {
            $main = json_encode(waLocale::getInfo($main));
        }
        $provider = json_encode(mylangHelper::getSettings('main_provider'));
        $locales = json_encode(mylangLocale::getActive());
        $locales_full = json_encode(waLocale::getAll('name'));
        $path = wa()->getAppStaticUrl('mylang');
        $strings = [
            'All' => _wd('mylang', 'All'),
            'None' => _wd('mylang', 'None'),
            'Description' => _wd('mylang', 'Description'),
            'You can translate tags in' => _wd('mylang', 'You can translate tags in'),
            'Mylang app' => _wd('mylang', 'Mylang app'),
            'Translate' => _wd('mylang', 'Translate'),
            'Locale' => _wd('mylang', 'Locale'),
            'Everyone' => _wd('mylang', 'Everyone'),
        ];
        $strings = json_encode($strings);
        $version = wa()->getVersion('mylang');
        $html = <<<HTML
        <script type="text/javascript" src="{$path}js/mylang.js?{$version}"></script>
        <script>
            $.wa.locale = $.extend($.wa.locale, {$strings})
            $.mylang.data = {$translation}
            $.mylang.locales = {$locales}
            $.mylang.localesAll = {$locales_full}
            $.mylang.main = {$main}
            $.mylang.handler ="product";
            $.mylang.provider = {$provider}
            $.mylang.init();
        </script>
        <select class="s-mylang-select"></select>
        <style>input[data-mylang]{width:60% !important; max-width:800px} div[data-mylang]{margin-bottom:5px !important}</style>
HTML;
        return [
            'title_suffix' => $html,
        ];
    }
}
