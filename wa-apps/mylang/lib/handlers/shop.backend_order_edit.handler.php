<?php

class mylangShopBackend_order_editHandler extends mylangShopEventHandler
{
    public function execute(&$params)
    {
        if ($this->checkVersion()) {
            return false;
        }
        $locales = mylangLocale::getActive();
        $locales[''] = _w('None');
        $order_locale = ifset($params, 'params', 'mylang_order_locale', '');
        $html = <<<HTML
<div class="float-right" style="margin-left: 10px; border-width: 1px;" id="mylang_order_locale_selector">
    {html_options name='mylang[order][locale]' options=\$locales selected='{$order_locale}'}
</div>
<script>
$(function(){
    if ($('#order-storefront')) {
        $('#mylang_order_locale_selector').insertAfter($('#order-storefront').parent());
    }
})
</script>
HTML;
        $view = wa()->getView();
        $view->assign('locales', $locales);
        return $view->fetch('string:'.$html);
    }
}
