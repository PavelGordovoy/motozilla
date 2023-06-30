<?php

class mylangPhotosBackend_photoHandler extends waEventHandler
{
    public function execute(&$params) // array[id]
    {
        $translate = new mylangTranslate();
        $locales = mylangLocale::getAll('name');
        $translated = json_encode($translate->translate($params, null, 'photo', 'photos'));
        $js = wa()->getAppStaticUrl('mylang').'js/mylang_photos.js';
        $view = wa()->getView();

        $bottom = '
        <form id="mylang_photo_form">
        {$wa->csrf()}
        <table class="zebra" id="mylang">
        <thead>
            <th class="min-width"></th>
            <th>[`Name`]</th>
            <th>[`Description`]</th>
        </thead>
        {foreach $locales as $key=>$value}
            <tr>
                <td>{$value}</td>
                <td><input class="long" name="mylang[photo][{$key}][{$params}][name]"></td>
                <td><input class="long" name="mylang[photo][{$key}][{$params}][description]"></td>
            </tr>
        {/foreach}
        </table>
        <hr>
        <input type="submit" class="button green" value="[`Save`]"/><span>
        <span id="mylang-photosave-message" class="hidden">[`Saved`]</span>
        </form>
        <script>
            $.getScript("{$js}").done(function(){
                $.mylang.data = {$translated};
                $.mylang.locales = {$locales|json_encode}
                $.mylang.init();
                $.mylang.fill("photo");
            })
        </script>
';
        $view->assign(compact('js', 'translated', 'locales', 'params'));
        $bottom = $view->fetch("string:".$bottom);
        return [
            'bottom'    => $bottom,
        ];
    }
}
