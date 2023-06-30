<?php

class mylangShopBackend_category_dialogHandler extends mylangShopEventHandler
{
    public function execute(&$params = null)
    {
        if (!$params || $this->checkVersion()) {
            return false;
        }
        $translate = new mylangTranslate();
        $translation = json_encode($translate->category($params['id']));
        $main = mylangHelper::getSettings('main_language');
        if (empty($main)) {
            $main = json_encode(['name'=>_w('None')]);
        } else {
            $main = json_encode(waLocale::getInfo($main));
        }
        $locales = json_encode(mylangLocale::getAll('name'));
        $provider = json_encode(mylangHelper::getSettings('main_provider'));
        $path = wa()->getAppStaticUrl('mylang');
        $strings = [
            'All' => _wd('mylang', 'All'),
            'None' => _wd('mylang', 'None'),
            'Description' => _wd('mylang', 'Description'),
            'You can translate tags in' => _wd('mylang', 'You can translate tags in'),
            'Mylang app' => _wd('mylang', 'Mylang app'),
            'Translate' => _wd('mylang', 'Translate'),
        ];
        $strings = json_encode($strings);
        $version = wa()->getVersion('mylang');
        $html = <<<HTML
        <script type="text/javascript" src="{$path}js/mylang.js?{$version}"></script>
        <script>
            $.wa.locale = $.extend($.wa.locale, {$strings})
            $.mylang.data = {$translation}
            $.mylang.locales = {$locales}
            $.mylang.main = {$main}
            $.mylang.provider = {$provider}
            $(document).ready(function() {
                $.mylang.init("category");
            });
        </script>
        <select class="s-mylang-select"></select>
HTML;
        return $html;
    }
}
