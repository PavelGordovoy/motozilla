<?php
/**
 * Created by PhpStorm.
 * User: Demollc
 * Date: 18.12.2018
 * Time: 14:35
 */
class mylangShopNotifications_sendBeforeHandler
{
    /* $params = [
             'event' => 'order.edit'$event'',
             'notifications' => $notifications,
             'data'  => &$data,
         ];
    */
    public function execute(&$params)
    {
        if ($params['event'] === 'order.edit') {
            $order = new shopOrder($params['data']['order']['id']);
            $order_items['items'] = $order['items'];
            $order_params = $order['params'];
            $locale = ifset($order_params, 'mylang_order_locale', '');
            $fixed = ifset($order_params, 'mylang_items_fixed', false);
            //Skip if already fixed items
            if (!$fixed && $locale) {
                $translate = new mylangTranslate();
                $items = $translate->order($order_items, $locale);
                //Fix database
                $model = new shopOrderItemsModel();
                /** @var array $items ['items'] */
                foreach ($items['items'] as $item) {
                    $model->updateByField('id', $item['id'], ['name' => $item['name']]);
                }
                $params['data']['order'] = $items;
                $this->updateParams($params, $locale);
            }
        }
    }

    private function updateParams($params, $locale)
    {
        $model = new shopOrderParamsModel();
        $model->setOne($params['data']['order']['id'], 'mylang_items_fixed', true);
    }
}
