<?php

class mylangShopActions extends waViewActions
{
    /**
     *
     */
    public function defaultAction()
    {
        $this->setLayout(new mylangBackendLayout());
        $this->checkTables();
    }

    /**
     * @throws waException
     */
    public function featuresAction()
    {
        wa('shop');
        $feature_model = new shopFeatureModel();
        $lang_model = new mylangModel();
        $sortorder = waRequest::get('order') == 'desc' ? 'DESC' : 'ASC';
        $feature_id = waRequest::get('feature', null, 'int');
        if ($feature_id) {
            $features = $feature_model->getFeatures('id', $feature_id, 'id', true);
            if (count($features)) {
                $features = reset($features);
            }
            if ($features['type'] == 'varchar' || $features['type'] == 'text' || $features['type'] == 'color') {
                if ($features['selectable']) {
                    $values = shopFeatureModel::getFeatureValues($features);
                    if ($values) {
                        $loc_values = $lang_model->query(
                            'SELECT * FROM '
                            . $lang_model->getTableName()
                            . " WHERE type = 'feature_value' AND subtype='"
                            . $features['type'] . "' AND type_id IN (" . implode(',',array_keys($values)) . ')'
                        )->fetchAll();
                    }
                } else {
                    if (!empty($features['values'])) {
                        $f = $features['values'];
                    } else {
                        $sql = "SELECT feature_value_id, value FROM shop_product_features spf
                    INNER JOIN shop_feature sf ON spf.feature_id = sf.id LEFT JOIN shop_feature_values_{$features['type']} sfvt
                    ON spf.feature_value_id = sfvt.id WHERE code = ?";
                        $f = $lang_model->query($sql, $features['code'])->fetchAll('feature_value_id', true);
                    }
                    if (!empty($f)) {
                        $loc_values = $lang_model->query(
                            'SELECT * FROM ' . $lang_model->getTableName(
                            ) . " WHERE `type` = 'feature_value' AND `subtype`=? AND `type_id` IN (" . implode(
                                ',',
                                array_keys(
                                    $f
                                )
                            ) . ')',
                            $features['type']
                        )->fetchAll();
                    }
                }
                $result = [];
                if (isset($loc_values)) {
                    foreach ($loc_values as $key => $value) {
                        $result[$value['locale']][$value['type_id']] = $value;
                    }
                    $this->view->assign('loc_values', $result);
                }
                $this->view->assign('type', $features['type']);
                $this->view->assign('values', ifset($values, 'no'));
            }
        } else {
            $features = $feature_model->query(
                'SELECT * FROM ' . $feature_model->getTableName(
                ) . ' WHERE parent_id IS NULL ORDER BY name ' . $sortorder
            )->fetchAll('id');
            if (!empty($features)) {
                $loc_feature = $lang_model->query(
                    'SELECT * FROM ' . $lang_model->getTableName(
                    ) . " WHERE type = 'feature' AND type_id IN (" . implode(',', array_keys($features)) . ')'
                )->fetchAll();
                foreach ($loc_feature as $locf) {
                    if (isset($features[$locf['type_id']])) {
                        $features[$locf['type_id']]['locale'][$locf['locale']] = $locf;
                    }
                }
            }
        }
        $this->view->assign(compact('sortorder', 'features'));
        $this->assignLocales();
    }

    public function servicesAction()
    {
        wa('shop');
        $service_id = waRequest::get('service', null, 'int');
        $model = new mylangModel();
        if (!$service_id) {
            $service_model = new shopServiceModel();
            $services = $service_model->getAll('id');
            if ($services) {
                $loc_service = $model->query(
                    'SELECT * FROM ' . $model->getTableName() . " WHERE type = 'service' AND type_id IN (" . implode(
                        ',',
                        array_keys($services)
                    ) . ")"
                )->fetchAll();
                foreach ($loc_service as $locs) {
                    if (isset($services[$locs['type_id']])) {
                        $services[$locs['type_id']]['locale'][$locs['locale']] = $locs;
                    }
                }
            }
            $this->view->assign('services', $services);
            $this->view->assign('list', true);
            return;
        }
        $sql = "SELECT ss.name as sname, ssv.name as vname, service_id, ssv.id as variant_id FROM shop_service_variants ssv INNER JOIN shop_service ss ON ssv.service_id = ss.id WHERE ssv.service_id = ?";
        $services = $model->query($sql, $service_id)->fetchAll();
        $this->view->assign('services', $services);
        $this->view->assign('list', false);
        if (empty($services)) {
            return;
        }
        foreach ($services as $s) {
            $vars[] = $s['variant_id'];
        }
        $loc_name = $model->query(
            "SELECT * FROM " . $model->getTableName() . " WHERE type = 'service' AND type_id = ?",
            $service_id
        )->fetchAll("locale");
        $result = $model->query(
            "SELECT * FROM " . $model->getTableName() . " WHERE type = 'service_value' AND type_id IN (" . implode(
                ",",
                $vars
            ) . ")"
        )->fetchAll();
        $loc_values = [];
        foreach ($result as $r) {
            $loc_values[$r['locale']][$r['type_id']] = $r['text'];
        }
        $this->view->assign('loc_name', $loc_name);
        $this->view->assign('loc_values', $loc_values);
        $this->assignLocales();
    }

    public function promosAction()
    {
        wa('shop');
        $promo_id = waRequest::get('promo', null, 'int');
        $model = new mylangModel();
        $promo_model = new shopPromoModel();
        if (!$promo_id) {
            $promos = $promo_model->getAll('id');
            if ($promos) {
                $loc_promo = $model->query(
                    "SELECT * FROM " . $model->getTableName() . " WHERE type = 'promos' AND type_id IN (" . implode(
                        ',',
                        array_keys($promos)
                    ) . ")"
                )->fetchAll();
                foreach ($loc_promo as $locs) {
                    if (isset($promos[$locs['type_id']])) {
                        $promos[$locs['type_id']]['locale'][$locs['locale']] = $locs;
                    }
                }
            }
            $this->view->assign('promos', $promos);
            $this->view->assign('list', true);
            return;
        }
        $promos = $promo_model->getById($promo_id);
        $result = $model->query(
            "SELECT * FROM " . $model->getTableName() . " WHERE type = 'promos' AND type_id = ?",
            $promo_id
        )->fetchAll();
        foreach ($result as $r) {
            $promos['locale'][$r['locale']][$r['subtype']] = $r['text'];
        }
        $this->view->assign('promos', $promos);
        $this->view->assign('list', false);
        $this->assignLocales();
    }

    public function stocksAction()
    {
        wa('shop');
        $model = new mylangModel();
        $s_model = new shopStockModel();
        $loc_stocks = $model->getByField('type', 'stock', true);
        $stocks = $s_model->getAll('id');
        foreach ($loc_stocks as $locs) {
            if (isset($stocks[$locs['type_id']])) {
                $stocks[$locs['type_id']]['locale'][$locs['locale']] = $locs;
            }
        }
        if (class_exists('shopVirtualstockModel')) { //ss7
            $v_model = new shopVirtualstockModel();
            $stocks_v = $v_model->getAll('id');
            foreach ($loc_stocks as $locs) {
                if (strpos($locs['type_id'], 'v') === 0) {
                    $locs['type_id'] = ltrim($locs['type_id'], 'v');
                    if (isset($stocks_v[$locs['type_id']])) {
                        $stocks_v[$locs['type_id']]['locale'][$locs['locale']] = $locs;
                    }
                }
            }
            $this->view->assign('stocks_v', $stocks_v);
        }
        $this->view->assign('stocks', $stocks);
        $this->assignLocales();
    }

    public function tagsAction()
    {
        wa('shop');
        $model = new mylangModel();
        $t_model = new shopTagModel();
        $tags = $t_model->getAll('id');
        $loc_tag = $model->getByField(['type' => 'tag', 'app_id' => 'shop'], true);
        foreach ($loc_tag as $locf) {
            if (isset($tags[$locf['type_id']])) {
                $tags[$locf['type_id']]['locale'][$locf['locale']] = $locf;
            }
        }
        $this->view->assign('tags', $tags);
        $this->assignLocales();
    }

    public function settingsAction()
    {
        $this->view->assign('settings', mylangHelper::getSettings());
    }

    public function checkoutAction()
    {
        //can't translate step's names and terms
        $this->view->assign('locales', waLocale::getAll());
        $checkout = wa()->getConfig()->getConfigPath('checkout.php', true, 'shop');
        $modified = false;
        $this->view->assign('modified', $modified);
        if (!file_exists($checkout)) {
            return;
        }
        $checkout = include($checkout);
        if (is_array($checkout['contactinfo'])) {
            foreach ($checkout['contactinfo']['fields'] as $id => $field) {
                if (isset($field['localized_names'])) {
                    $modified = true;
                    $checkout['mylang_custom_fields'][] = $id;
                }
            }
        }

        foreach ($checkout as $key => $step) {
            if (is_array($step) && isset($step['name'])) {
                $checkout['mylang_custom_steps'][] = $key;
                $modified = true;
            }
        }
        $this->view->assign('checkout', $checkout);
        $this->view->assign('modified', $modified);
    }

    public function autotranslateAction()
    {
        $model = new waAppSettingsModel();
        $cache = new mylangJsonCache();
        $cacheFiles = $cache->getFiles();
        $providers = wa('mylang')->getConfig()->getProviders();
        $locales = waLocale::getAll('name');
        $this->view->assign('locales', $locales);
        $this->view->assign('main_locale', $model->get('mylang', 'main_language'));
        $this->view->assign('providers', $providers);
        $this->view->assign('cacheFiles', $cacheFiles);
    }

    public function exportAction()
    {
        $locales = waLocale::getAll('name');
        $this->view->assign('locales', $locales);
    }

    public function workflowAction()
    {
        wa('shop');
        $wf = new shopWorkflow();
        $states = $wf->getAvailableStates();

        $orig_states = [
            'new' => ['ru_RU' => 'Новый', 'en_US' => 'New'],
            'processing' => ['ru_RU' => 'В обработке', 'en_US' => 'Confirmed'],
            'paid' => ['ru_RU' => 'Оплачен', 'en_US' => 'Paid'],
            'shipped' => ['ru_RU' => 'Отправлен', 'en_US' => 'Shipped'],
            'completed' => ['ru_RU' => 'Выполнен', 'en_US' => 'Completed'],
            'refunded' => ['ru_RU' => 'Возврат', 'en_US' => 'Refunded'],
            'deleted' => ['ru_RU' => 'Удалён', 'en_US' => 'Deleted'],
        ];

        foreach ($states as $id => &$state) {
            if (is_string($state['name'])) {
                if (isset($orig_states[$id])) {
                    $state['name'] = $orig_states[$id];
                } else {
                    //custom state
                    $state['name'] = [wa()->getLocale() => $state['name']];
                }
            }
        }
        unset($state);

        $this->view->assign('states', $states);
        $this->assignLocales();
        $this->view->assign('default_states', $orig_states);
    }

    public function checkTables()
    {
        //reviews
        $model = new waModel();
        try {
            $model->query('SELECT mylang_locale from `shop_product_reviews`');
        } catch (waDbException $e) {
            $model->exec('Alter table `shop_product_reviews` add mylang_locale varchar(10) DEFAULT NULL');
            waLog::log('Reviews table updated', 'mylang/mylang_update.log');
        }
    }

    private function assignLocales()
    {
        $this->view->assign('locales', mylangLocale::getActive());
    }
}
