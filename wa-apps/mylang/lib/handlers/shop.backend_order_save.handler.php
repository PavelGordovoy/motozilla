<?php

/**
 * Class mylangShopBackend_order_saveHandler
 */
class mylangShopBackend_order_saveHandler extends mylangShopEventHandler
{
    /**
     * $params before shopOrder->save()
     * @param array $params [order_data]
     * @return array|bool
     */
    public function execute(&$params)
    {
        if ($this->checkVersion()) {
            return false;
        }
        $params['order_data']['params']['mylang_items_fixed'] = false;
        $order_params = waRequest::post('mylang', '','array');
        $params['order_data']['params']['mylang_order_locale'] = ifset($order_params, 'order', 'locale', '');
        return $params;
    }
}
