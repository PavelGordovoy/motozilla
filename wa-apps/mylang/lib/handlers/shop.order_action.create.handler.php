<?php

class mylangShopOrder_actionCreateHandler extends mylangShopEventHandler
{
    public function execute(&$params)
    {
        if (!mylangHelper::checkSite()||$this->checkVersion()) {
            return false;
        }
        $env = wa()->getEnv();
        if ($params['action_id'] === 'create') {
            $locale = mylangLocale::currentLocale();
            if ($env === 'backend') { //use selector
                $post = waRequest::post('mylang', [], 'array');
                $locale = ifset($post, 'order', 'locale', '');
            }
            if (!empty($locale)) {
                $translate = new mylangTranslate();
                $model = new shopOrderItemsModel();
                $sql = 'SELECT oi.*, sp.sku_type from ' . $model->getTableName() . ' as oi left join shop_product as sp on oi.product_id = sp.id WHERE `order_id` = ?';
                $items = $model->query($sql, $params['order_id'])->fetchAll('id');
                $items = $translate->order(['items' => $items], $locale);
                /** @var array $items ['items'] */
                foreach ($items['items'] as $item) {
                    $model->updateByField('id', $item['id'], ['name' => $item['name']]);
                }
                $this->updateParams($params, $locale);
                return true;
            }
        }
    }

    /**
     * @param $params []
     * @param $locale string
     * @throws waDbException
     * @throws waException
     */
    private function updateParams($params, $locale)
    {
        $model = new shopOrderParamsModel();
        //Set locale to the order params
        $model->setOne($params['order_id'], 'mylang_order_locale', $locale);
        $env = wa()->getEnv();
        if($env === 'frontend') {
            $model->setOne($params['order_id'], 'mylang_items_fixed', true);
        }
    }
}
