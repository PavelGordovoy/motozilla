<?php

/**
 * @author wa-apps.ru <info@wa-apps.ru>
 * @copyright 2013-2016 wa-apps.ru
 * @license Webasyst License http://www.webasyst.ru/terms/#eula
 * @link http://www.webasyst.ru/store/plugin/shop/productmotos/
 */
class shopProductmotosPluginFrontendMotoAction extends shopFrontendCategoryAction
{

    /**
     * @param $moto
     * @param shopProductsCollection $collection
     * @return array
     */
    protected function getFilters($moto, $collection)
    {
        $filters = array();
        if ($moto['filter']) {
            $filter_ids = explode(',', $moto['filter']);
            $feature_model = new shopFeatureModel();
            $features = $feature_model->getById(array_filter($filter_ids, 'is_numeric'));
            if ($features) {
                $features = $feature_model->getValues($features);
            }
            $moto_value_ids = $collection->getFeatureValueIds();

            foreach ($filter_ids as $fid) {
                if ($fid == 'price') {
                    $range = $collection->getPriceRange();
                    if ($range['min'] != $range['max']) {
                        $filters['price'] = array(
                            'min' => shop_currency($range['min'], null, null, false),
                            'max' => shop_currency($range['max'], null, null, false),
                        );
                        $this->view->assign('price_min', $filters['price']['min']);
                        $this->view->assign('price_max', $filters['price']['max']);
                    }
                } elseif (isset($features[$fid]) && isset($moto_value_ids[$fid])) {
                    $filters[$fid] = $features[$fid];
                    $min = $max = $unit = null;
                    foreach ($filters[$fid]['values'] as $v_id => $v) {
                        if (!in_array($v_id, $moto_value_ids[$fid])) {
                            unset($filters[$fid]['values'][$v_id]);
                        } else {
                            if ($v instanceof shopRangeValue) {
                                $begin = $this->getFeatureValue($v->begin);
                                if ($min === null || $begin < $min) {
                                    $min = $begin;
                                }
                                $end = $this->getFeatureValue($v->end);
                                if ($max === null || $end > $max) {
                                    $max = $end;
                                    if ($v->end instanceof shopDimensionValue) {
                                        $unit = $v->end->unit;
                                    }
                                }
                            } else {
                                $tmp_v = $this->getFeatureValue($v);
                                if ($min === null || $tmp_v < $min) {
                                    $min = $tmp_v;
                                }
                                if ($max === null || $tmp_v > $max) {
                                    $max = $tmp_v;
                                    if ($v instanceof shopDimensionValue) {
                                        $unit = $v->unit;
                                    }
                                }
                            }
                        }
                    }
                    if (!$filters[$fid]['selectable'] && ($filters[$fid]['type'] == 'double' ||
                            substr($filters[$fid]['type'], 0, 6) == 'range.' ||
                            substr($filters[$fid]['type'], 0, 10) == 'dimension.')
                    ) {
                        if ($min == $max) {
                            unset($filters[$fid]);
                        } else {
                            $type = preg_replace('/^[^\.]*\./', '', $filters[$fid]['type']);
                            if ($type != 'double') {
                                $filters[$fid]['base_unit'] = shopDimension::getBaseUnit($type);
                                $filters[$fid]['unit'] = shopDimension::getUnit($type, $unit);
                                if ($filters[$fid]['base_unit']['value'] != $filters[$fid]['unit']['value']) {
                                    $dimension = shopDimension::getInstance();
                                    $min = $dimension->convert($min, $type, $filters[$fid]['unit']['value']);
                                    $max = $dimension->convert($max, $type, $filters[$fid]['unit']['value']);
                                }
                            }
                            $filters[$fid]['min'] = $min;
                            $filters[$fid]['max'] = $max;
                        }
                    }
                }
            }

            if ($filters && class_exists('shopFiltersDescriptionsModel')) {
                $desc_model = new shopFiltersDescriptionsModel();
                $desc_ids = $desc_model->getFeatureIds(array_keys($filters));
                foreach ($desc_ids as $f_id) {
                    $filters[$f_id]['description'] = true;
                }
            }

            $this->view->assign('filters', $filters);
            $this->view->assign('filters_hash', 'moto/'.$moto['id']);
        }

        return $filters;
    }

    /**
     * @param $feature
     * @param $moto_url
     * @return array
     * @throws waException
     */
    protected function getMoto($feature, $moto_url)
    {
        $feature_model = new shopFeatureModel();
        $values_model = $feature_model->getValuesModel($feature['type']);

        $motos_model = new shopProductmotosModel();
        $moto = $motos_model->getByField('url', $moto_url);

        if (!$moto) {
            $value_id = $values_model->getValueId($feature['id'], $moto_url);
            if (!$value_id) {
                throw new waException(_wp('Moto not found'), 404);
            }
            $moto = $motos_model->getMoto($value_id);
        } else {
            $moto['params'] = shopProductmotosModel::getParams($moto['params']);
            // check feature value exists
            if (!$values_model->getById($moto['id'])) {
                $motos_model->updateById($moto['id'], array('url' => ''));
                throw new waException(_wp('Moto not found'), 404);
            }
        }

        $b_url = !empty($moto['url']) ? $moto['url'] : $moto['name'];
        if ($b_url != urldecode($moto_url)) {
            $url = wa()->getRouteUrl('/frontend/moto', array('moto' => $b_url));
            if ($q = waRequest::server('QUERY_STRING')) {
                $url .= '?'.$q;
            }
            $this->redirect($url, 301);
        }

        return $moto;
    }

    /**
     * @param $moto
     * @param $feature
     * @return array
     */
    protected function getCategories($moto, $feature)
    {
        $category_model = new shopCategoryModel();

        $b_url = !empty($moto['url']) ? $moto['url'] : $moto['name'];

        // get categories
        $sql = "SELECT cp.category_id, COUNT(DISTINCT cp.product_id) FROM shop_category_products cp
                JOIN shop_product_features pf ON cp.product_id = pf.product_id
                WHERE pf.feature_id = ".(int)$feature['id']." AND pf.feature_value_id = ".(int)$moto['id']."
                GROUP BY cp.category_id";
        $categories_count = $category_model->query($sql)->fetchAll('category_id', true);

        if ($categories_count) {
            $route = wa()->getRouting()->getDomain(null, true) . '/' . wa()->getRouting()->getRoute('url');
            $sql = 'SELECT * FROM shop_category c
                    LEFT JOIN shop_category_routes cr ON c.id = cr.category_id
                    WHERE c.id IN (i:ids) AND c.status = 1 AND
                    (cr.route IS NULL OR cr.route = s:route)
                    ORDER BY c.left_key';
            $categories = $category_model->query($sql, array('ids' => array_keys($categories_count),
                'route' => $route))->fetchAll('id', true);
            foreach ($categories as $c_id => $c) {
                $categories[$c_id]['count'] = $categories_count[$c_id];
            }
        } else {
            $categories = array();
        }
        $c_setting = wa()->getSetting('categories_filter', '', array('shop', 'productmotos'));
        if ($c_setting == -1) {
            $this->view->assign('categories', array());
        } else {
            if (!$c_setting) {
                $category_url = wa()->getRouteUrl('shop/frontend/category', array('category_url' => '%CATEGORY_URL%')) . '?' . $feature['code'] . '=' . $moto['id'];
            } elseif ($c_setting == 1) {
                $category_url = wa()->getRouteUrl('shop/frontend/moto', array('moto' => urlencode($b_url) . '/%CATEGORY_URL%'));
            } else {
                $category_url = wa()->getRouteUrl('shop/frontend/category', array('category_url' => '%CATEGORY_URL%'));
            }
            foreach ($categories as &$c) {
                $c['url'] = str_replace('%CATEGORY_URL%', waRequest::param('url_type') == 1 ? $c['url'] : $c['full_url'], $category_url);
            }
            unset($c);
            $this->view->assign('categories', $categories);
        }

        return $categories;
    }

    public function execute()
    {
        $key = array('shop', 'productmotos');
        $moto_url = waRequest::param('moto');

        $this->addCanonical();

        $category_model = new shopCategoryModel();
        if (strpos($moto_url, '/')) {
            list($moto_url, $category_url) = explode('/', $moto_url, 2);
            $category = $category_model->getByField(waRequest::param('url_type') == 1 ? 'url' : 'full_url', $category_url);
            if (!$category) {
                throw new waException('Category not found', 404);
            }
            $this->view->assign('category', $category);
        }

        $feature_model = new shopFeatureModel();
        $feature_id = wa()->getSetting('feature_id', '', $key);
        $feature = $feature_model->getById($feature_id);

        $moto = $this->getMoto($feature, $moto_url);
        $b_url = !empty($moto['url']) ? $moto['url'] : $moto['name'];

        $categories = $this->getCategories($moto, $feature);

        if (!empty($category)) {
            $c = new shopProductmotosPluginCollection('category/'.$category['id']);

            $frontend_url = wa()->getRouteUrl('shop/frontend/moto', array('moto' => urlencode($b_url)));
            $breadcrumbs = array(
                array(
                    'url' => $frontend_url,
                    'name' => $moto['name'],
                )
            );
            $this->view->assign('breadcrumbs', $breadcrumbs);
            if ($this->layout) {
                $this->layout->assign('breadcrumbs', $breadcrumbs);
            }
            $this->view->assign('canonical', $frontend_url);
        } else {
            if (!empty($moto['params']['canonical'])) {
                $this->view->assign('canonical', $moto['params']['canonical']);
            }
            $c = new shopProductmotosPluginCollection();
        }

        $c->addJoin('shop_product_features',
            'p.id = :table.product_id AND :table.feature_id = '.(int)$feature['id'],
            ':table.feature_value_id = '.(int)$moto['id']);

        // sorting
        $this->view->assign('sorting', $moto['enable_sorting']);
        if ($moto['sort_products'] && !waRequest::get('sort')) {
            $sort = explode(' ', $moto['sort_products']);
            $this->view->assign('active_sort', $sort[0] == 'count' ? 'stock' : $sort[0]);
            $c->setMotoSortProducts($moto['sort_products']);
        } elseif (!$moto['sort_products'] && !waRequest::get('sort')) {
            $this->view->assign('active_sort', '');
        }

        $filters = $this->getFilters($moto, $c);

        $c->filters(waRequest::get(), true);
        $this->setCollection($c);

        if (!$c->count()) {
            $motos = shopProductmotosPlugin::getMotos();
            if (empty($motos[$moto['id']])) {
                throw new waException(_wp('Moto not found'), 404);
            }
        }

        if ($moto['filter'] && !empty($filters)) {
            $this->fixPrices($filters);
        }

        if ($this->getConfig()->getOption('can_use_smarty') && $moto['description']) {
            $moto['description'] = wa()->getView()->fetch('string:'.$moto['description']);
        }

        $this->view->assign('moto', $moto);

        $h1 = $moto['h1'] ? $moto['h1'] : $moto['name'];
        if (wa()->getSetting('title_h1', '', $key) && $moto['title'] && empty($moto['h1'])) {
            $h1 = $moto['title'];
        }
        $this->view->assign(
            'title',
            htmlspecialchars(!empty($category) ? $category['name'] : $h1)
        );


        $title = $moto['title'] ? $moto['title'] : $moto['name'];
        if (!empty($category)) {
            $title .= ' - '.$category['name'];
            waRequest::setParam('moto', $title);
        }
        $this->getResponse()->setTitle($title);
        $og = array(
            'type' => 'website',
            'title' => $title,
            'url' => wa()->getConfig()->getHostUrl().wa()->getConfig()->getRequestUrl(false, true)
        );
        if ($moto['meta_keywords']) {
            $this->getResponse()->setMeta('keywords', $moto['meta_keywords']);
        }
        if ($moto['meta_description']) {
            $og['description'] = $moto['meta_description'];
            $this->getResponse()->setMeta('description', $moto['meta_description']);
        }
        if (!empty($moto['image'])) {
            $og['image'] = wa()->getDataUrl('motos/'.$moto['id'].'/'.$moto['id'].$moto['image'], true, 'shop', true);
        }

        if ($t_search = wa()->getSetting('template_search', '', $key)) {
            $html = $this->view->fetch('string:'.$t_search);
        } else {
            $html = $this->view->fetch(wa()->getAppPath('plugins/productmotos/templates/', 'shop').'frontendSearch.html');
        }

        if (!empty($og)) {
            $response = wa()->getResponse();
            if (method_exists($response, 'setOGMeta')) {
                foreach ($og as $property => $value) {
                    $response->setOGMeta('og:' . $property, $value);
                }
            }
        }

        /**
         * @var shopProductmotosPlugin $plugin
         */
        $plugin = wa('shop')->getPlugin('productmotos');

        /**
         * @event frontend_search
         * @return array[string]string $return[%plugin_id%] html output for search
         */
        $frontend_search = (array)wa()->event('frontend_search');
        if (!($template = $plugin->getSettings('moto_theme_template')) || !$this->setThemeTemplate($template)) {
            $this->view->assign('frontend_search', array('productmotos' => $html) + $frontend_search);
            $this->setThemeTemplate('search.html');
        }

        waSystem::popActivePlugin();
    }


    protected function fixPrices($filters)
    {
        // fix prices
        $products = $this->view->getVars('products');
        $product_ids = array();
        foreach ($products as $p_id => $p) {
            if ($p['sku_count'] > 1) {
                $product_ids[] = $p_id;
            }
        }
        if ($product_ids) {
            $min_price = $max_price = null;
            $tmp = array();
            foreach ($filters as $fid => $f) {
                if ($fid == 'price') {
                    $min_price = waRequest::get('price_min');
                    if (!empty($min_price)) {
                        $min_price = (double)$min_price;
                    } else {
                        $min_price = null;
                    }
                    $max_price = waRequest::get('price_max');
                    if (!empty($max_price)) {
                        $max_price = (double)$max_price;
                    } else {
                        $max_price = null;
                    }
                } else {
                    $fvalues = waRequest::get($f['code']);
                    if ($fvalues && !isset($fvalues['min']) && !isset($fvalues['max'])) {
                        $tmp[$fid] = $fvalues;
                    }
                }
            }

            $rows = array();
            if ($tmp) {
                $pf_model = new shopProductFeaturesModel();
                $rows = $pf_model->getSkusByFeatures($product_ids, $tmp);
            } elseif ($min_price || $max_price) {
                $ps_model = new shopProductSkusModel();
                $rows = $ps_model->getByField('product_id', $product_ids, true);
            }
            $product_skus = array();
            shopRounding::roundSkus($rows, $products);
            foreach ($rows as $row) {
                $product_skus[$row['product_id']][] = $row;
            }

            $default_currency = $this->getConfig()->getCurrency(true);
            if ($product_skus) {
                foreach ($product_skus as $product_id => $skus) {
                    $currency = $products[$product_id]['currency'];
                    usort($skus, array($this, 'sortSkus'));
                    $k = 0;
                    if ($min_price || $max_price) {
                        foreach ($skus as $i => $sku) {
                            if ($min_price) {
                                $tmp_price = shop_currency($min_price, true, $currency, false);
                                if ($sku['price'] < $tmp_price) {
                                    continue;
                                }
                            }
                            if ($max_price) {
                                $tmp_price = shop_currency($max_price, true, $currency, false);
                                if ($sku['price'] > $tmp_price) {
                                    continue;
                                }
                            }
                            $k = $i;
                            break;
                        }
                    }
                    $sku = $skus[$k];
                    if ($products[$product_id]['sku_id'] != $sku['id']) {
                        $products[$product_id]['sku_id'] = $sku['id'];
                        $products[$product_id]['frontend_url'] .= '?sku='.$sku['id'];
                        $products[$product_id]['price'] =
                            shop_currency($sku['price'], $currency, $default_currency, false);
                        $products[$product_id]['compare_price'] =
                            shop_currency($sku['compare_price'], $currency, $default_currency, false);
                    }
                }
                $this->view->assign('products', $products);
            }
        }
    }
}
