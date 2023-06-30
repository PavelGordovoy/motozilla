<?php

class mylangShopFrontend_navHandler extends mylangShopEventHandler
{
    public $domain;
    private $fullurl = false;
    
    public function execute(&$params) //null
    {
        if ($this->checkVersion()) {
            return false;
        }
        $query = waRequest::get('locale', false);
        if ($query && wa()->getEnv() === 'frontend') {
            $this->domain = wa()->getConfig()->getDomain();
            $rules = new mylangRules();
            $rules_user = $rules->get($this->domain);
            $redirect_url = ifset($rules_user[$query]);
            if ($query && $redirect_url) {
                unset($_GET['locale']);
                if (strpos(waRequest::server('HTTP_REFERER'), '/shop/?action') > 0) {
                    wa()->getStorage()->remove('locale');
                    return false;
                }
                if (strpos($redirect_url, '//') === false) {
                    $this->checkCurrency($redirect_url);
                    $action = waRequest::param('action');
                    if ($action === 'product') {
                        $this->productRedirect($redirect_url);
                    }
                    if ($action === 'category') {
                        $redirect_url = $this->categoryRedirect($redirect_url);
                    }
                    if ($action === 'search') {
                        $redirect_url .= 'search/?query='.waRequest::get('query', null, 'string');
                    }
                    wa()->getStorage()->remove('locale'); //убираем.

                    if ($this->fullurl && $redirect_url) {
                        wa()->getResponse()->redirect($redirect_url);
                        return false;
                    }
                    wa()->getResponse()->redirect('//'.$this->domain.$redirect_url);
                } else {
                    wa()->getStorage()->remove('locale'); //убираем.
                    wa()->getResponse()->redirect($redirect_url);
                }
            }
            wa()->getResponse()->sendHeaders();
        }
        mylangHelper::addSmartyFilter();
        wa()->getView()->smarty->registerFilter('pre', 'mylang_smarty_prefilter_productsafterfeatures');
    }

    private function productRedirect($redirect_url)
    {
        $redirect_url = ltrim($redirect_url, '/').'*';
        $routing = wa()->getRouting();
        $data = $routing->getRoutes();
        $url_type = '1';
        foreach ($data as $route)
        {
            if($route['url'] === $redirect_url) {
                $url_type = $route['url_type'];
                break;
            }
        }
        $url_params = [
            'product_url' => waRequest::param('product_url')
        ];

        if ($url_type === '2') {
            $product_model = new shopProductModel();
            $product = $product_model->getByUrl($url_params['product_url']);
            $c_id = $product['category_id'];
            if ($c_id) {
                $category_model = new shopCategoryModel();
                $c = $category_model->getById($c_id);
                if (isset($c['full_url'])) {
                    $url_params['category_url'] = $c['full_url'];
                }
            }
            $url = $routing->getUrl('shop/frontend/product', $url_params, true, rtrim($this->domain, '/'), $redirect_url);
        } else {
            $url = wa()->getRouteUrl('shop/frontend/product', $url_params, false, $this->domain, $redirect_url);
        }
        if ($url) {
            wa()->getResponse()->redirect($url);
            return $url;
        }
    }
    
    private function categoryRedirect($redirect_url)
    {
        $redirect_url = ltrim($redirect_url, '/').'*';
        $data = wa()->getRouting()->getRoutes();
        $url_type = '1';
        foreach ($data as $route)
        {
            if($route['url'] === $redirect_url) {
                $url_type = $route['url_type'];
                break;
            }
        }
        $url_field = $url_type === '1' ? 'url' : 'full_url';
        $category_model = new shopCategoryModel();
        if (waRequest::param('category_id')) {
            $category = $category_model->getById(waRequest::param('category_id'));
        } else {
            $category = $category_model->getByField($url_field, waRequest::param('category_url'));
        }
        if ($category) {
            $this->fullurl = true;
            return wa()->getRouteUrl('shop/frontend/category',
                array('category_url' => $category[$url_field], 'url_type' => $url_type), false, $this->domain, $redirect_url
            );
        }
    }

    private function checkCurrency($url = null)
    {
        $routing = wa()->getRouting()->getRoutes();
        $url = ltrim($url, '/');
        $url .= '*';
        $storage = wa()->getStorage();
        foreach ($routing as $route) {
            if (!empty($route['currency']) && (stripos($route['url'], $url) !== false)) {
                $storage->set('shop/currency', $route['currency']);
                $cart = $storage->get('shop/cart');
                unset($cart['total']);
                $storage->set('shop/cart', $cart);
            }
        }
    }
}
