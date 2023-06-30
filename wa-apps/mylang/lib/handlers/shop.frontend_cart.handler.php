<?php

class mylangShopFrontend_cartHandler extends mylangShopEventHandler
{
    /*
    * $params are null
    */
    private $products = [];

    /**
     * @param $params
     * @throws waDbException
     * @throws waException
     */
    public function execute(&$params)
    {
        if (!mylangHelper::checkSite() || $this->checkVersion()) {
            return;
        }
        $view = wa()->getView();
        $cart = $view->getVars('cart');
        $items = $cart['items'];
        if (empty($items)) {
            return; //empty cart
        }
        $translate = new mylangTranslate();
        $skufeatures = [];
        foreach ($items as &$item) {
            if (!isset($this->products[$item['product_id']])) {
                $product = new shopProduct($item['product_id']);
                $this->products[$item['product_id']] = $product->getSkuFeatures();
            }
            if ($item['product']['sku_type'] === '1') {
                $skufeatures[] = $item['sku_id'];
            }
            //dark magic as id is used for checking
            foreach ($item['services'] as $key => &$service) {
                if (isset($service['id'])) {
                    $service['checked'] = $service['id'];
                }
                $service['id'] = $key;
            }
            unset($service);
            $item['services'] = $translate->services($item['services']);
            foreach ($item['services'] as $key => &$service) {
                unset($service['id']);
                if (isset($service['checked'])) {
                    $service['id'] = $service['checked'];
                }
            }
            unset($service);
        }
        if ($skufeatures) {
            $skufeatures = implode(',', $skufeatures);
            $model = new waModel();
            $sql = 'SELECT sku_id, code, text FROM `shop_product_features` as spf LEFT JOIN shop_feature as sf on feature_id=sf.id LEFT JOIN mylang as m on m.subtype = sf.type AND m.type_id = spf.feature_value_id WHERE `sku_id` IN(' .$skufeatures.") and m.type='feature_value'";
            $data = $model->query($sql, $skufeatures)->fetchAll();
            $skufeatures = [];
            foreach ($data as $sku) {
                $skufeatures[$sku['sku_id']][$sku['code']] = $sku['text'];
            }
        }
        foreach ($items as &$item) {
            if (!empty($skufeatures) && $item['product']['sku_type'] === 1) {
                $features[$item['sku_id']] = array_replace_recursive($this->products[$item['product_id']][$item['sku_id']], $skufeatures[$item['sku_id']]);
                foreach ($features[$item['sku_id']] as &$f) {
                    if (is_object($f)) {
                        $f = $f['value'];
                    }
                }
                unset($f);
                $item['sku_name'] = implode(', ', $features[$item['sku_id']]);
            } elseif ($item['product']['sku_type'] === '0' && isset($item['product']['skuname_'.$item['sku_id']])) {
                $item['sku_name'] = $item['product']['skuname_'.$item['sku_id']];
                $item['name'] = $item['product']['name'] .'('. $item['product']['skuname_'.$item['sku_id']].')';
            }
        }
        unset($item);
        $cart['items'] = $items;
        $view->assign('cart', $cart);
    }
}
