<?php

class mylangShopFrontend_headHandler extends mylangShopEventHandler
{
    /* @param null $params
     * @deprecated 3.0
     */
    public function execute(&$params = null)
    {
        /*if (wa()->getEnv() === 'frontend' && wa()->getVersion() < 7) {
            $redirect_url = null;
            $currency = waRequest::get('currency', null, 'string');
            if ($currency) {
                if (wa()->getConfig()->getCurrencies([$currency])) {
                    wa()->getStorage()->set('shop/currency', $currency);
                    wa()->getStorage()->remove('shop/cart');
                }
                $redirect_url = wa()->getConfig()->getCurrentUrl();
                $redirect_url = preg_replace('~&?currency='.$currency.'~i', '', $redirect_url);
            }
            $locale = waRequest::get('locale', null, 'string');
            if ($locale && waLocale::getInfo($locale)) {
                if (!$redirect_url) {
                    $redirect_url = wa()->getConfig()->getCurrentUrl();
                }
                wa()->getStorage()->set('locale', $locale);
                $redirect_url = preg_replace('~&?locale='.$locale.'~i', '', $redirect_url);
            }

            if ($redirect_url) {
                wa()->getResponse()->redirect(rtrim($redirect_url, '?&'));
                return;
            }
        }*/
       // waLocale::setStrings(mylangViewHelper::customStrings());
    }
}
