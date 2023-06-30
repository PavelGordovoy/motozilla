<?php

class mylangShopFrontend_productsHandler extends mylangShopEventHandler
{
    public function execute(&$params = null)
    {
        if (!isset($params['products']) || !mylangHelper::checkSite() || $this->checkVersion()) {
            return;
        }
        $single = is_object(reset($params['products'])) ?true:false;//Страница одного товара или нет
        $translate = new mylangTranslate();
        $products = $translate->productsIds(array_keys($params['products']), mylangLocale::currentLocale());
        foreach ($products as $entry) {
            if ($single && stripos($entry['subtype'], 'skuname_')!==false) { //Перевод Sku
                $params['skus'][str_ireplace('skuname_', '', $entry['subtype'])]['name'] = $entry['text'];
            } else {
                $params['products'][$entry['type_id']][$entry['subtype']] = $entry['text'];
            }
            if (isset($params['products'][$entry['type_id']]['sku_type']) && ($params['products'][$entry['type_id']]['sku_type'] == shopProductModel::SKU_TYPE_SELECTABLE) && !empty($params['products'][$entry['type_id']]['features_selectable'])) {
                $params['products'][$entry['type_id']]['features_selectable'] = $translate->features($params['products'][$entry['type_id']]['features_selectable'], mylangLocale::currentLocale());
            }
        }
    }
}
