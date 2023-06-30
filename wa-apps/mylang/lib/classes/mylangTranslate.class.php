<?php

/**
 * Class mylangTranslate
 */
class mylangTranslate
{
    public static $errors = false;

    /**
     * @param $features
     * @param null $locale
     * @return array|null
     * @throws waException
     */
    public function translateFeaturesSelectable($features, $locale = null)
    {
        //TODO check
        if (empty($features)) {
            return $features;
        }
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }
        $ids = array_column($features, 'id');
        $model = new mylangModel();
        $data['subtype'] = $ids;
        return $model->getByField($data, true);
    }

    /**
     * @param $products
     * @param null $locale
     * @return mixed
     * @throws waException
     */
    public function products($products, $locale = null)
    {
        if (empty($products)) {
            return $products;
        }
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }
        $single = is_object(reset($products));
        $ids = array_keys($products);
        $cache = new mylangCache();
        $cache_key = $locale.'_'.md5(implode('_', $ids));
        $translated = $cache->get($cache_key, 'mylang_view_products');
        if ($translated === null) {
            $translated = $this->translate($ids, $locale);
            $cache->set($cache_key, $translated, 3600, 'mylang_view_products');
        }

        foreach ($translated as $entry) {
            if ($single && stripos($entry['subtype'], 'skuname_')!==false) { //Перевод Sku
                $products['skus'][str_ireplace('skuname_', '', $entry['subtype'])]['name'] = $entry['text'];
            } else {
                $products[$entry['type_id']][$entry['subtype']] = $entry['text'];
            }
        }
        foreach ($products as &$product) {
            if (!empty($product['features'])) {
                $stringFeatures = [];
                $colorFeatures = [];
                foreach ($product['features'] as $key=>$pf) {
                    if (is_string($pf)) {
                        $stringFeatures[] = $key;
                        continue;
                    }
                    if (is_array($pf) && reset($pf) instanceof shopColorValue) {
                        $colorFeatures[] = $key;
                    }
                }
                $feature_values = mylangHelper::featureValuesByProduct($product['id']);
                if (!empty($stringFeatures)) {
                    foreach ($stringFeatures as $sf) {
                        if (empty($feature_values[$sf])) {
                            continue;
                        }
                        $feature_values[$sf] = reset($feature_values[$sf]);
                    }
                }

                foreach ($colorFeatures as $f_id) {
                    foreach ($product['features'][$f_id] as $v_id=>$fv) {
                        $color = ifset($feature_values, $f_id, $v_id, null);
                        if (!empty($color)) {
                            $product['features'][$f_id][$v_id]->__set('value', $color);
                        }
                    }
                    unset($feature_values[$f_id], $fv);
                }
                $product['features'] = array_replace_recursive($product['features'], $feature_values);
            }
        }
        return $products;
    }

    /**
     * @param $ids
     * @param null $locale
     * @return array|null
     * @throws waException
     */
    public function productsIds($ids, $locale = null)
    {
        return $this->translate($ids, $locale);
    }

    /**
     * @param $ids
     * @param null $locale
     * @return array|null
     * @throws waException
     */
    public function category($ids, $locale = null)
    {
        return $this->translate($ids, $locale, 'category');
    }

    /**
     * @param $ids
     * @param null $locale
     * @param string $type
     * @param null $app_id
     * @return array|null
     * @throws waException
     */
    public function translate($ids, $locale = null, $type = 'product', $app_id = null)
    {
        //В случае пустой локали отдавать все данные
        if ($locale) {
            $data['locale'] = $locale;
        }
        $model = new mylangModel();
        $data['type'] = $type;
        $data['type_id'] = $ids;
        if ($app_id) {
            $data['app_id'] = $app_id;
        }
        return $model->getByField($data, true);
    }

    /**
     * @param array $tags
     * @param null $locale
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public static function tags($tags = [], $locale = null)
    {
        if (empty($tags)) {
            return [];
        }
        $ids = array_keys($tags);
        $model = new mylangModel();
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }
        $translated = $model->getValues('tag', $ids, $locale, 'shop');
        foreach ($tags as $key => &$tag) {
            if (!empty($translated[$key])) {
                $tag = $translated[$key]['text'];
            }
        }
        return $tags;
    }

    //{$services = shopMylangPluginHelper::services($services)}

    /**
     * @param array $services
     * @param null $locale
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public static function services($services = [], $locale = null)
    {
        if (empty($services)) {
            return [];
        }
        $variants = $ids = [];
        if (isset($services['id'])) {
            $services = [$services['id'] => $services];
        }
        foreach ($services as $service) {
            $ids[]=$service['id'];
            if (isset($service['variants'])) {
                $variants = array_merge($variants, array_keys($service['variants']));
            }
        }
        $model = new mylangModel();
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }
        if (!empty($variants)) {
            $translated_values = $model->getValues('service_value', $variants, $locale);
            array_walk($translated_values, wa_lambda('&$value,$key', '$value = array("name"=>$value["text"]);'));
        }
        $translated = $model->getValues('service', $ids, $locale);
        foreach ($services as &$service) {
            if (!empty($translated[$service['id']])) {
                $service['name'] = $translated[$service['id']]['text'];
            }
            if (isset($service['variants'], $translated_values)) {
                $service['variants'] = array_replace_recursive($service['variants'], $translated_values);
            }
        }
        return $services;
    }

    /**
     * @param array $features
     * @param null $locale
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public static function features($features = [], $locale = null)
    {
        if (empty($features)) {
            return [];
        }
        $model = new mylangModel();
        $ids = [];
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }
        foreach ($features as $f) {
            $ids[] = $f['id'];
        }
        $ids = $model->getValues('feature', $ids, $locale);
        foreach ($features as &$f) {
            $f['name'] = ifset($ids[$f['id']]['text'], $f['name']);
        }
        return $features;
    }

    /**
     * @param array $stocks
     * @param null $locale
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public static function stocks($stocks = [], $locale = null)
    {
        if (empty($stocks)) {
            return $stocks;
        }
        $ids = [];
        foreach ($stocks as $key => $stock) {
            if (strpos($key, 'v')===false) {
                $ids[] = $stock['id'];
            } else {
                $ids[] = $key;
            }
        }
        unset($key);
        $model = new mylangModel();
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }
        $translated = $model->getValues('stock', $ids, $locale);
        foreach ($stocks as $key => &$stock) {
            if (strpos($key, 'v')===false) {
                if (!empty($translated[$stock['id']])) {
                    $stock['name'] = $translated[$stock['id']]['text'];
                }
            } elseif (!empty($translated[$key])) {
                $stock['name'] = $translated[$key]['text'];
            }
        }
        return $stocks;
    }

    /**
     * @param array $ids
     * @param null $locale
     * @return array|null
     * @throws waException
     */
    public function promos($ids = [], $locale = null)
    {
        return $this->translate($ids, $locale, 'promos');
    }

    /**
     * @param [] $params
     * @param string $locale
     * @return mixed
     * @throws waException
     */
    public function order($params, $locale = null)
    {
        $items = $params['items'];
        if (empty($items)) {
            return $params;
        }
        $products = $services = $skufeatures = $skus = [];
        /** @var array $items*/
        foreach ($items as &$item) {
            if (!isset($products[$item['product_id']])) {
                /* @var $products[$item['product_id']] shopProduct */
                $products[$item['product_id']] = new shopProduct($item['product_id']);
                $products[$item['product_id']]['skufeatures'] = $products[$item['product_id']]->getSkuFeatures();
                $skus[$item['product_id']] = $products[$item['product_id']]->getSkus();
            }
            if ($item['type'] === 'product') {
                $item['sku_type'] = $products[$item['product_id']]->getData('sku_type');
            }
            if ($item['type'] === 'service') {
                $services[$item['service_id']]['variants'][$item['service_variant_id']] = $item['service_variant_id'];
                $services[$item['service_id']]['id'] = $item['service_id'];
                $services[$item['service_id']]['name'] = $item['name'];
            } elseif ($item['sku_type'] === '1') {
                $skufeatures[] = $item['sku_id'];
            }
        }
        unset($item);
        $translated = $this->products($products, $locale);
        /*Skip translation services if none*/
        if (!empty($services)) {
            $services = self::services($services, $locale);
        }
        if ($skufeatures) {
            $skufeatures = implode(',', $skufeatures);
            $model = new waModel();
            $sql = 'SELECT sku_id, code, text FROM `shop_product_features` as spf LEFT JOIN `shop_feature` as sf on feature_id=sf.id LEFT JOIN `mylang` as m on m.subtype = sf.type AND m.type_id = spf.feature_value_id WHERE `sku_id` IN(' .$skufeatures.") and m.type='feature_value'";
            if ($locale) {
                $sql .= ' AND m.locale = ?';
            }
            $data = $model->query($sql, $locale)->fetchAll();
            $skufeatures = [];
            foreach ($data as $sku) {
                /** @noinspection UnsupportedStringOffsetOperationsInspection */
                $skufeatures[$sku['sku_id']][$sku['code']] = $sku['text'];
            }
        }
        foreach ($items as &$item) {
            if ($item['type'] === 'product') {
                $item['name'] = $translated[$item['product_id']]['name'];
                if (!empty($skufeatures) && $item['sku_type'] === '1') {
                    $features[$item['sku_id']] = array_replace_recursive($products[$item['product_id']]['skufeatures'][$item['sku_id']], $skufeatures[$item['sku_id']]);
                    foreach ($features[$item['sku_id']] as &$f) {
                        if (is_object($f)) {
                            $f = $f['value'];
                        }
                    }
                    unset($f);
                    $item['name'] .=' ('.implode(', ', $features[$item['sku_id']]).')';
                }

                if ($item['sku_type'] === '0') {
                    $translated_sku = ifset($translated, 'skus', $item['sku_id'], 'name', null);
                    $orig_sku = ifset($skus, $item['product_id'], $item['sku_id'], 'name', '');
                    $item['name'] .= ' ('.ifset($translated_sku, $orig_sku).')';
                    unset($translated_sku, $orig_sku);
                }
                $item['name'] = str_replace('()','', $item['name']); //Remove ()
                continue;
            }
            if ($item['type'] === 'service') {
                $item['name'] = $services[$item['service_id']]['name'];
                if (isset($services[$item['service_id']]['variants'][$item['service_variant_id']]['name'])) {
                    $item['name'] .= '(' .$services[$item['service_id']]['variants'][$item['service_variant_id']]['name'].')';
                }
            }
        }
        unset($item);
        $params['items'] = $items;
        return $params;
    }

    public function skus($ids = [], $locale = null)
    {
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }
        $model = new mylangModel();
        $newIds = [];
        foreach ($ids as &$id) {
            $newIds[] = 'skuname_'.$id;
        }
        if (empty($newIds)) {
            return [];
        }
        $sql = 'SELECT * FROM '. $model->getTableName() .' WHERE `type` = "product" and  `subtype` IN (s:ids) AND `locale` = s:locale';
        $data = $model->query($sql, ['ids'=>$newIds, 'locale'=>$locale])->fetchAll('subtype', 1);
        if (empty($data)) {
            return [];
        }
        $keys = [];
        foreach (array_keys($data) as $key) {
            $keys[] = str_ireplace('skuname_', '', $key);
        }

        return array_combine($keys, array_values($data));
    }
}
