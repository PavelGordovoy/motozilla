<?php

/**
 * @author wa-apps.ru <info@wa-apps.ru>
 * @copyright 2013-2016 wa-apps.ru
 * @license Webasyst License http://www.webasyst.ru/terms/#eula
 * @link http://www.webasyst.ru/store/plugin/shop/productmotos/
 */
class shopProductmotosPlugin extends shopPlugin
{
    /**
     * @var array
     */
    protected static $feature;

    /**
     * @var array
     */
    protected static $motos;

    /**
     * @return string
     */
    public function frontendNav()
    {
        if ($this->getSettings('hook') == 'frontend_nav') {
            return $this->nav();
        }
    }

    /**
     * @return string
     */
    public function frontendNavAux()
    {
        if ($this->getSettings('hook') == 'frontend_nav_aux') {
            return $this->nav();
        }
    }

    protected function nav()
    {
        $motos = self::getMotos();
        if (!$motos) {
            return;
        }
        $view = wa()->getView();
        $view->assign('motos', $motos);
        if ($t_nav = $this->getSettings('template_nav')) {
            return $view->fetch('string:'.$t_nav);
        } else {
            return $view->fetch($this->path.'/templates/frontendNav.html');
        }
    }


    /**
     * @param $params
     * @return array
     */
    public function backendProducts($params)
    {
        if (!$params) {
            $view = wa()->getView();
            return array(
                'sidebar_top_li' => '<li id="s-productmotos">
                    <a href="#/motos/"><i class="icon16" style="background-image: url('.$this->getPluginStaticUrl().'img/motos.png);"></i>'._wp('Motos').'</a>
                    <script src="'.$this->getPluginStaticUrl().'js/productmotos.js"></script>
                    </li>',
                'sidebar_section' => $view->fetch($this->path.'/templates/backendProducts.html')
            );
        } elseif (!empty($params['type']) && $params['type'] == 'moto') {
            return array(
                'title_suffix' => '<span class="s-product-list-manage"><a href="#/'.waRequest::get('hash').'" class="gray"><i class="icon16 settings"></i>'._w('Settings').'</a></span>'
            );
        }
    }

    /**
     * @param int $category_id
     * @return array
     */
    public static function getCategoryMotos($category_id)
    {
        $collection = new shopProductmotosPluginCollection('category/'.$category_id);
        return $collection->getMotos();
    }

    /**
     * Returns moto feature
     * @return array
     */
    protected static function getFeature()
    {
        if (self::$feature === null) {
            self::$feature = array();
            $feature_id = wa()->getSetting('feature_id', null, array('shop', 'productmotos'));
            if ($feature_id) {
                $feature_model = new shopFeatureModel();
                if ($feature = $feature_model->getById($feature_id)) {
                    self::$feature = $feature;
                }
            }
        }
        return self::$feature;
    }

    /**
     * Returns motos of the product
     *
     * @param int $product_id
     * @param bool $all
     * @return array
     */
    public static function productMoto($product_id, $all = false)
    {
        $feature = self::getFeature();
        if ($feature) {
            $product_features_model = new shopProductFeaturesModel();
            $row = $product_features_model->getByField(array(
                'product_id' => $product_id, 'feature_id' => $feature['id'], 'sku_id' => null
            ), $all);
            $moto_model = new shopProductmotosModel();
            if ($row) {
                if ($all) {
                    $motos = array();
                    foreach ($row as $r) {
                        $moto = $moto_model->getMoto($r['feature_value_id']);
                        $moto_url = $moto['url'] ? $moto['url'] : urlencode($moto['name']);
                        $moto['url'] = wa()->getRouteUrl('shop/frontend/moto', array('moto' => $moto_url));
                        $motos[] = $moto;
                    }
                    return $motos;
                } else {
                    $moto = $moto_model->getMoto($row['feature_value_id']);
                    $moto_url = $moto['url'] ? $moto['url'] : urlencode($moto['name']);
                    $moto['url'] = wa()->getRouteUrl('shop/frontend/moto', array('moto' => $moto_url));
                    return $moto;
                }
            }
        }
        return array();
    }

    /**
     * @param array $products
     * @return array
     */
    public static function prepareProducts($products)
    {
        $feature = self::getFeature();
        if (!$products || !$feature) {
            return $products;
        }
        $motos = self::getMotos();
        $product_features_model = new shopProductFeaturesModel();
        $rows = $product_features_model->getByField(array('product_id' => array_keys($products), 'feature_id' => $feature['id'], 'sku_id' => null), true);
        foreach ($rows as $row) {
            $moto_id = $row['feature_value_id'];
            if (isset($motos[$moto_id])) {
                $products[$row['product_id']]['moto'] = $motos[$moto_id];
            }
        }
        return $products;
    }

    /**
     * @return array
     */
    public static function getMotos()
    {
        if (self::$motos === null) {
            $feature = self::getFeature();
            if ($feature) {
                $feature_model = new shopFeatureModel();
                $motos = $feature_model->getFeatureValues($feature);
                $product_features_model = new shopProductFeaturesModel();
                $types = array();
                if (wa()->getEnv() == 'frontend' && waRequest::param('type_id') && is_array(waRequest::param('type_id'))) {
                    $types = waRequest::param('type_id');
                }
                $sql = "SELECT feature_value_id, COUNT(*) FROM " . $product_features_model->getTableName() . " pf
                        JOIN shop_product p ON pf.product_id = p.id
                        WHERE pf.feature_id = i:0 AND pf.sku_id IS NULL " . (wa()->getEnv() == 'frontend' ? "AND p.status = 1 " : '') .
                    ($types ? 'AND p.type_id IN (i:1) ' : '') .
                    "GROUP BY pf.feature_value_id";
                $counts = $product_features_model->query($sql, $feature['id'], $types)->fetchAll('feature_value_id', true);
            } else {
                $motos = array();
                $counts = array();
            }

            if ($motos) {
                $motos_model = new shopProductmotosModel();
                $rows = $motos_model->getById(array_keys($motos));
                if (wa()->getEnv() == 'frontend') {
                    $path = wa()->getAppPath('plugins/productmotos/lib/config/routing.php', 'shop');
                    $routing = include($path);
                    $url = wa()->getRouteUrl('shop/frontend') . 'moto/%MOTO%/';
                    foreach ($routing as $k => $v) {
                        if ($v == 'frontend/moto') {
                            $url = wa()->getRouteUrl('shop/frontend') . str_replace('<moto>', '%MOTO%', $k);
                            break;
                        }
                    }
                }
                foreach ($motos as $id => $name) {
                    if (wa()->getEnv() == 'frontend' && !isset($counts[$id])) {
                        unset($motos[$id]);
                        continue;
                    }
                    if (isset($rows[$id])) {
                        $motos[$id] = $rows[$id];
                        $motos[$id]['name'] = $name;
                        $motos[$id]['params'] = shopProductmotosModel::getParams($motos[$id]['params']);
                    } else {
                        $motos[$id] = array(
                            'id' => $id,
                            'name' => $name,
                            'summary' => '',
                            'description' => '',
                            'image' => null,
                            'url' => null,
                            'filter' => '',
                            'hidden' => 0,
                            'params' => array()
                        );
                    }
                    if (wa()->getEnv() == 'frontend') {
                        if ($motos[$id]['hidden']) {
                            unset($motos[$id]);
                            continue;
                        }
                        $moto_url = $motos[$id]['url'] ? $motos[$id]['url'] : urlencode($name);
                        $motos[$id]['url'] = str_replace('%MOTO%', $moto_url, $url);
                    }
                    $motos[$id]['count'] = isset($counts[$id]) ? $counts[$id] : 0;
                }
            }
            if ($motos && wa()->getSetting('sort', null, array('shop', 'productmotos'))) {
                uasort($motos, array('shopProductmotosPlugin', 'sortMotos'));
            }
            self::$motos = $motos;
        }
        return self::$motos;
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    protected static function sortMotos($a, $b)
    {
        if ($a['name'] == $b['name']) {
            return 0;
        }
        return ($a['name'] < $b['name']) ? -1 : 1;
    }

    public function productsCollection($params)
    {
        /**
         * @var shopProductsCollection $collection
         */
        $collection = $params['collection'];
        $hash = $collection->getHash();
        if ($hash[0] !== 'moto') {
            return null;
        }
        $feature_id = (int)wa()->getSetting('feature_id', null, array('shop', 'productmotos'));
        if ($feature_id) {
            $varchar_model = new shopFeatureValuesVarcharModel();
            $v = $varchar_model->getById($hash[1]);
            $collection->addTitle($v['value']);
            $collection->addJoin('shop_product_features', null, ':table.feature_id = '.$feature_id.' AND :table.feature_value_id = '.(int)$hash[1]);
            return true;
        }
    }


    /**
     * @param array $route
     * @return array
     */
    public function sitemap($route)
    {
        $feature_id = $this->getSettings('feature_id');
        $feature_model = new shopFeatureModel();
        $feature = $feature_model->getById($feature_id);
        if (!$feature) {
            return;
        }
        $values = $feature_model->getFeatureValues($feature);

        if (!empty($route['type_id']) && is_array($route['type_id'])) {
            $types = $route['type_id'];
        } else {
            $types = array();
        }

        $motos_model = new shopProductmotosModel();
        $motos = $motos_model->getAll('id');

        $existed = $this->getByTypes($feature['id'], $types);

        $urls = array();
        $moto_url = wa()->getRouteUrl('shop/frontend/moto', array('moto' => '%MOTO%'), true);
        foreach ($values as $v_id => $v) {
            if (in_array($v_id, $existed)) {
                if (isset($motos[$v_id])) {
                    if ($motos[$v_id]['hidden']) {
                        continue;
                    }
                    if (!empty($motos[$v_id]['url'])) {
                        $v = $motos[$v_id]['url'];
                    }
                }
                $urls[] = array(
                    'loc' => str_replace('%MOTO%', str_replace('%2F', '/', urlencode($v)), $moto_url),
                    'changefreq' => waSitemapConfig::CHANGE_MONTHLY,
                    'priority' => 0.2
                );
            }
        }
        if ($urls) {
            return $urls;
        }
    }

    /**
     * @param $feature_id
     * @param $types
     * @return array
     */
    protected function getByTypes($feature_id, $types)
    {
        $product_features_model = new shopProductFeaturesModel();
        $sql = "SELECT DISTINCT pf.feature_value_id FROM ".$product_features_model->getTableName()." pf
                JOIN shop_product p ON pf.product_id = p.id
                WHERE pf.feature_id = i:0 AND pf.sku_id IS NULL AND p.status = 1";
        if ($types) {
            $sql .= " AND p.type_id IN (i:1)";
        }
        return $product_features_model->query($sql, $feature_id, $types)->fetchAll(null, true);
    }
}

