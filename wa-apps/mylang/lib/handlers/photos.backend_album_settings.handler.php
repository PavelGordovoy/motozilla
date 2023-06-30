<?php

class mylangPhotosBackend_album_settingsHandler extends waEventHandler
{
    public function execute(&$params) // array[id]
    {
        $locales = json_encode(mylangLocale::getAll('name'));
        $js = wa()->getAppStaticUrl('mylang').'js/mylang_photos.js';
        $album_id = $params['id'];
        $translate = new mylangTranslate();
        $translated = json_encode($translate->translate($album_id, null, 'album', 'photos'));
        $top = <<<HTML
            <script>
            $.getScript('$js').done(function(){
                $.mylang.data = {$translated};
                $.mylang.locales = {$locales}
                $.mylang.init();
                $.mylang.albumInit();
            })
            </script>
HTML;
        return [
            'top'    => $top,
        ];
    }
}
