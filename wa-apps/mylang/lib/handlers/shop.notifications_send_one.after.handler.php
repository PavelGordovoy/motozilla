<?php
/**
 * Created by PhpStorm.
 * User: Demollc
 * Date: 18.12.2018
 * Time: 14:35
 */
class mylangShopNotifications_send_oneAfterHandler
{
   /* $params = [
            'id' => $id,
            'notifications' => $n,
            'data'  => &$data,
            'to' => $to,
        ];
   */
    public function execute(&$params)
    {
        if (!mylangHelper::checkSite()) {
            return;
        }
        $locale = mylangLocale::currentLocale();
        $items = $params['data']['order'];
        $translate = new mylangTranslate();
        $items = $translate->order($items, $locale);
        $params['data']['order'] = $items;
    }
}