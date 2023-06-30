<?php
/**
 * Created by PhpStorm.
 * User: kurd
 * Date: 10/12/18
 * Time: 18:41
 */

class mylangShopViewHandler
{
    /**
     * @param $params
     * @param null $action
     */
    public function execute(&$params, $action = null): void
    {
        $action = str_ireplace('.', '', $action);
        $method = $action.'Action';
        if (method_exists($this, $method)) {
            $this->$method($params);
        }
    }

    /**
     * @param $promocards
     * @return array
     * @throws waException
     */
    public function view_promosAction(&$promocards)
    {
        if (empty($promocards) || (!is_array($promocards))) {
            return $promocards;
        }
        $translate = new mylangTranslate();
        $loc_info = $translate->promos(array_keys($promocards), mylangLocale::currentLocale());
        foreach ($loc_info as $loc) {
            $promocards[$loc['type_id']][$loc['subtype']] = $loc['text'];
        }
        return $promocards;
    }

    /**
     * @param shopProduct $product
     * @return mixed
     * @throws waException
     */
    public function view_productAction($product)
    {
        if (!mylangHelper::checkSite()) {
            return $product;
        }
        $translate = new mylangTranslate();
        return $translate->products([$product['id']=>$product]);
    }

    /**
     * @param array $products
     * @return mixed
     * @throws waException
     */
    public function view_productsafterAction($products)
    {
        if (!mylangHelper::checkSite()) {
            return $products;
        }
        $translate = new mylangTranslate();
        return $translate->products($products);
    }

    /**
     * @param array $categories
     * @param null $locale
     * @return mixed
     * @throws waDbException
     * @throws waException
     */
    public function view_categoriesAction(&$categories, $locale = null)
    {
        if (empty($categories)||!mylangHelper::checkSite()) {
            return $categories;
        }

        $ids = [];
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($categories));
        foreach ($it as $k => $v) {
            if ($k ==='id') {
                $ids[] = $v;
            }
        }

        $model = new mylangModel();
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }

        $cache = new mylangCache();
        $cache_key = $locale.'_'.md5(implode('_', $ids));
        $translated = $cache->get($cache_key, 'mylang_view_categories');
        if ($translated === null) {
            $translated = $model->getValues('category', $ids, $locale);
            $cache->set($cache_key, $translated, 3600, 'mylang_view_categories');
        }

        mylangHelper::iterate_recursive($categories, $ids, $translated);
        return $categories;
    }

    public function view_categoryAction(&$category, $locale = null) {
        return $this->view_categoriesAction($category, $locale);
    }

    /**
     * $wa->shop->tags()
     * @param array $tags
     * @param null $locale
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function view_tagsAction(&$tags, $locale = null)
    {
        if (empty($tags)||!mylangHelper::checkSite()) {
            return $tags;
        }
        //TODO make with class
        $ids = [];
        foreach ($tags as $tag) {
            $ids[] = $tag['id'];
        }
        $model = new mylangModel();
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }
        $cache = new mylangCache();
        $cache_key = $locale.'_'.md5(implode('_', (array)$ids));
        $translated = $cache->get($cache_key, 'mylang_view_tags');
        if ($translated === null) {
            $translated = $model->getValues('tag', $ids, $locale);
            $cache->set($cache_key, $translated, 3600, 'mylang_view_tags');
        }

        foreach ($tags as &$tag) {
            if (!empty($translated[$tag['id']])) {
                $tag['name']=$translated[$tag['id']]['text'];
            }
        }
        return $tags;
    }

    public function view_featuresAction(&$features, &$products = array()): array
    {
        if (!mylangHelper::checkSite()) {
            return $features;
        }
        waConfig::set('mylang_features', true);
        $translate = new mylangTranslate();
        $features = $translate->features($features);
        new mylangPrefilter();
        wa()->getView()->smarty->registerFilter('pre', 'mylang_smarty_prefilter_productsafterfeatures');
        return $features;
    }

    public function view_reviewsAction(&$reviews)
    {

        if (!mylangHelper::checkSite()) {
            return $reviews;
        }
        $disable_filter = mylangHelper::getSettings('disablereviewfilter');
        if (!$disable_filter) {
            $reviews = mylangHelper::getPagesByLocale($reviews);
        }
        return $reviews;
    }

    public function frontend_review_addbeforeAction($review)
    {
        //Add locale only to first review
        if ($review['data']['parent_id'] == 0) {
            $locale = waRequest::param('locale', mylangLocale::currentLocale(), 'string_trim');
            $review['data']['mylang_locale'] = $locale;
        }
    }

    public function products_reviewsAction($params)
    {
        //backend
        $locales = waLocale::getAll('iso3');
        $static = wa()->getRootUrl();
        foreach ($params['reviews'] as &$review) {
            $review['product_url_crop_small'] = ifset($review, 'product_url_crop_small', $static.'/wa-apps/shop/img/image-dummy-small.png');
            $review['product_url_crop_small'] .= '"><img src="'.$static.'wa-content/img/country/'.ifset($locales, $review['mylang_locale'], 'iso3', '--').'.gif';
        }
    }
}
