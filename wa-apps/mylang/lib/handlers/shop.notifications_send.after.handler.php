<?php
/**
 * Created by PhpStorm.
 * User: Demollc
 * Date: 18.12.2018
 * Time: 14:35
 */
class mylangShopNotifications_sendAfterHandler
{
   /* $params = [
            'event' => $event,
            'notifications' => $notifications,
            'data'  => &$data,
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