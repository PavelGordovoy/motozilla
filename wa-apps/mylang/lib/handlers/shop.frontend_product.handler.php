<?php

class mylangShopFrontend_productHandler extends mylangShopEventHandler
{
    /**
     * @var $view waView
     * @var $breadcrumbs array
     */
    public $view;
    private $breadcrumbs;

    public function execute(&$params)
    {
        //Показать только нужные страницы
        $params['pages'] = mylangHelper::getPagesByLocale($params['pages']);
        if (!mylangHelper::checkSite() || self::checkVersion()) {
            return;
        }
        $this->view = wa()->getView();
        $this->reviews();
        $this->breadcrumbs($params);

        //Если мы на странице товара
        $page = $this->view->getVars('page');
        if (!empty($page)) {
            $model = new mylangModel();
            $products = $model->getPlainValues('product', $params['id'], mylangLocale::currentLocale());
            foreach ($products as $entry) {
                if ($entry['subtype'] == 'name') {
                    $params['name'] = $entry['text'];
                    wa()->getResponse()->setTitle($params['name'] . ' - ' . $page['title']);
                    $this->breadcrumbs[0]['name'] = $params['name'];
                    break;
                }
            }
        } else {
            //На странице товара
            $original_features = $params['features']; //save for sku_features
            $is_cart = waRequest::get('cart');
            if ($is_cart) {
                $feature_codes = array_keys($params->features);
                $feature_model = new shopFeatureModel();
                $features = $feature_model->getByCode($feature_codes);
                $this->view->assign('features', $features);
            }
            $translate = new mylangTranslate();
            $feature_values = mylangHelper::featureValuesByProduct($params['id']);
            //check for hidden
            foreach ($feature_values as $key => $param) {
                if (!isset($params['features'][$key])) {
                    unset($feature_values[$key]);
                }
            }
            $selectable = $params->sku_type == shopProductModel::SKU_TYPE_SELECTABLE;
            if ($selectable) {
                /** @var array $original */
                $original = $this->view->getVars('features_selectable');
                $features_selectable = array_replace_recursive($original, $feature_values);
            }
            foreach ($params['features'] as $key => $feature) {
                //<colors>
                if (is_array($feature) && reset($feature) instanceof \shopColorValue) {
                    if (array_key_exists($key, $feature_values)) {
                        foreach ($feature_values[$key] as $id => $color) {
                            if (isset($params['features'][$key][$id])) {
                                $params['features'][$key][$id]->__set('value', $color);// = $color;
                                if ($selectable) {
                                    $features_selectable[$params['features'][$key][$id]['feature_id']]['values'][$id] = $params['features'][$key][$id];
                                }
                            }
                            unset($feature_values[$key]);
                        }
                    }
                }
                //</colors>
                if (is_string($feature)) {
                    if (array_key_exists($key, $feature_values)) {
                        if (is_array($feature_values[$key]) && count($feature_values[$key]) === 1) {
                            $feature_values[$key] = reset($feature_values[$key]);
                        }
                    }
                }
            }
            $params['features'] = array_replace_recursive($params['features'], $feature_values);

            /*
             * Categories
             * Services
             * Stocks
             * Tags
             * */
            $this->prepareData($params);

            $features = $translate->features($this->view->getVars('features'));
            $this->view->assign('features', $features); //Характеристики вместо хелпера

            if ($selectable && $features) {
                foreach ($features_selectable as $f => $v) {
                    if (!array_key_exists($f, $original)) {
                        unset($features_selectable[$f]);
                    }
                    if (isset($v['code'])) {
                        if (array_key_exists($v['code'], $features_selectable)) {
                            if (!empty($features[$v['code']]['name'])) {
                                $features_selectable[$f]['name'] = $features[$v['code']]['name'];
                            }
                            if ($v['type'] == 'color') {
                                foreach ($features_selectable[$v['code']] as $objKey => $objValue) {
                                    $features_selectable[$f]['values'][$objKey]['value'] = $objValue;
                                }
                            } else {
                                $features_selectable[$f]['values'] = array_replace_recursive(
                                    $features_selectable[$f]['values'],
                                    $features_selectable[$v['code']]
                                );
                            }
                            unset($features_selectable[$v['code']]);
                        }
                    }
                }
                $this->view->assign('features_selectable', $features_selectable);
            }

            //sku_features
            $sku_features = $this->view->getVars('sku_features');
            if (!empty($sku_features)) {
                //Sku feature names
                foreach ($sku_features as $code => &$feature) {
                    $sf = ifset($features, $code, false);
                    if ($sf) {
                        $feature['name'] = $sf['name'];
                    }
                }
                unset($feature);
                $this->view->assign('sku_features', $sku_features);
                //Sku feature values
                $skus = ifset($params, 'skus', false);
                if ($skus) {
                    $this->prepareOriginalFeatures($original_features);
                    //$original_features = array_map('array_flip', $original_features);
                    foreach ($skus as &$sku) {
                        if (isset($sku['features'])) {
                            $this->skuFeatures(['original'=>$original_features, 'features'=>$params['features']], $sku['features']);
                        }
                    }
                    unset($sku);
                    $params['skus'] = $skus;
                }
            }
        }
        $this->view->assign('breadcrumbs', $this->breadcrumbs);
    }

    public function reviews(): void
    {
        $disable_filter = mylangHelper::getSettings('disablereviewfilter');
        if (!$disable_filter) {
            $reviews = $this->view->getVars('reviews');
            if (!empty($reviews)) {
                $reviews = mylangHelper::getPagesByLocale($reviews);
                $this->view->assign('reviews', $reviews);
                $this->view->assign('reviews_count', count($reviews));
            }
        }
    }

    /**
     * Update breadcrumbs
     * @param $params
     * @throws waException
     */
    public function breadcrumbs(&$params)
    {
        $this->breadcrumbs = $this->view->getVars('breadcrumbs');
        if (!empty($this->breadcrumbs)) {
            //Reviews || Page
            $page = waRequest::param('action');
            if ($page === 'productReviews' || $page === 'productPage') {
                if (count($this->breadcrumbs) === 1) {
                    return;
                }
                $product = array_pop($this->breadcrumbs);
            }
            $catmodel = new shopCategoryModel();

            $path = array_reverse($catmodel->getPath($params->category_id));
            $cats = $params->categories;
            if (empty($path)) {
                $this->breadcrumbs[current(array_keys($this->breadcrumbs))]['id'] = $params->category_id;
                $path = mylangViewHelper::categories($this->breadcrumbs);
            } else {
                if (reset($cats)) {
                    $path[] = reset($cats);
                }
                $path = mylangViewHelper::categories($path);
            }
            foreach ($path as $key => $value) {
                $this->breadcrumbs[ifset($value, 'id', null)]['name'] = $value['name'];
            }
            if (isset($product)) {
                $this->breadcrumbs[] = $product;
            }
        }
    }

    private function prepareData(&$params)
    {
        $translate = new mylangTranslate();
        //Categories
        if (!empty($params['categories'])) {
            $params['categories'] = mylangViewHelper::categories($params['categories']);
        }
        //Services
        $this->view->assign(
            'services',
            $translate->services($this->view->getVars('services'))
        ); //Услуги вместо хелпера
        //Stocks
        $this->view->assign('stocks', $translate->stocks($this->view->getVars('stocks')));
        //Tags
        $params['tags'] = $translate->tags($params['tags']);
    }

    /**
     * Translate sku feature values from already translated product f. values
     * @param $data
     * @param $sku_features
     */
    private function skuFeatures($data, &$sku_features)
    {
        foreach ($sku_features as $key=>&$value) {
            if(is_string($value)) {
                $pfv = ifset($data, 'original', $key, $value, false);
                if ($pfv) {
                    $value = ifset($data, 'features', $key, $pfv, $value);
                }
                continue;
            }
            if(is_object($value)) {
                $pfv = ifset($data, 'original', $key, $value->id, false);
                if ($pfv) {
                    $value->value = ifset( $pfv, $value->value);
                }
            }
        }
    }

    private function prepareOriginalFeatures(&$features): void
    {
        foreach ($features as &$feature) {
            if(is_array($feature)) {
                if(is_string(reset($feature))) {
                    $feature = array_flip($feature);
                    continue;
                }
                if (is_object(reset($feature))) {
                    $fcolor = null;
                    foreach ($feature as $color) {
                        $fcolor[$color->id]=$color->value;
                    }
                    if (!empty($fcolor)) {
                        $feature = $fcolor;
                    }
                }
            }
        }
    }
}
