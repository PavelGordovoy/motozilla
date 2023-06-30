<?php

class mylangSiteBackend_page_editHandler extends waEventHandler
{
    public function execute(&$params = null)
    {
        if (!array_key_exists('mylang_locale', $params)) {
            $model = new sitePageModel();
            if (!$model->fieldExists('mylang_locale')) {
                $model->query("ALTER TABLE ".$model->getTableName()." ADD `mylang_locale` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
            }
        }
        $select = mylangHelper::pageLocaleSelect($params, 'info');
        $w_locale = _w('Locale');
        $html = <<<HTML
        <script>
        var mylang = {
            disabled: false,
        };
        $("#wa-page-form").on("mousedown", "#wa-page-settings-toggle, #wa-page-advanced-params-link", function (e) {
            e.stopImmediatePropagation();
            if(mylang.disabled) return false;
            if ($('select[name*="mylang_locale"]:first', '#wa-page-settings').length < 1) {
                var sel = '<div class="field"><div class="name bold">$w_locale</div><div class="value">{$select}</div></div>';
                $('.wa-page-app-url:first', '#wa-page-settings').after(sel);
                $('#wa-page-advanced-params-link').click();
            }
            mylang.disabled = true;
            return false;
        });
    </script>
HTML;
        return ['action_button_li'=>$html];
    }
}
