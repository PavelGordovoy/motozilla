<?php

class mylangShopFrontend_searchHandler extends mylangShopEventHandler
{
    public function execute(&$params) //$params = всегда NULL
    {
        if (!mylangHelper::checkSite() || $this->checkVersion()) {
            return false;
        }
        $param = waRequest::param();
        if (!empty($param['tag'])) {
            return $this->searchTag($param);
        }
        return $this->searchProduct();
    }
    
    private function searchTag($param)
    {
        $tag = $param['tag'];
        $model = new shopTagModel();
        $tag = $model->getByName($tag);
        $translate = new mylangTranslate();
        $tr_tag = $translate->tags([$tag['id'] => $tag]);
        if (empty($tr_tag)) {
            return;
        }
        $tr_tag = $tr_tag[$tag['id']];
        if (is_array($tr_tag)||$tr_tag === $tag['name']) {
            return;
        }
        $view = wa()->getView();
        $view->assign('title', $tr_tag, true);
        //getStoreName
        $title = waRequest::param('title');
        if (!$title) {
            $title = wa()->getConfig()->getGeneralSettings('name');
        }
        if (!$title) {
            $app = wa('shop')->getAppInfo();
            $title = $app['name'];
        }
        $title = htmlspecialchars($title);
        wa()->getResponse()->setTitle(htmlspecialchars($tr_tag).' — '.$title);
    }

    //TODO duplicate mylangShopSearch
    private function searchProduct()
    {
        $query = waRequest::get('query', null, 'string');
        if (!$query) {
            return;
        }
        $view = wa()->getView();
        $model = new mylangModel();

        $query = urldecode($query);
        $query = $model->escape($query, 'like');
        $sql = "SELECT type_id as id from ".$model->getTableName()." WHERE type = 'product' AND text LIKE '%".$query."%'";
        if (mylangHelper::getSettings('fullsearch')) { //Add simple search
            $sql .= " UNION (SELECT p.id from shop_product p WHERE p.name LIKE '%".$query."%') 
             UNION (SELECT sps.product_id from shop_product_skus sps WHERE sps.sku LIKE '%".$query."%' OR sps.name LIKE '%".$query."%')";
        }
        $result = $model->query($sql)->fetchAll('id', 1);
        $result = array_keys($result);

        $collection = new shopProductsCollection($result);
        $collection->filters(waRequest::get());

        $limit = (int)waRequest::cookie('products_per_page');
        if (!$limit || $limit < 0 || $limit > 500) {
            $limit = wa('shop')->getConfig()->getOption('products_per_page');
        }
        $page = waRequest::get('page', 1, 'int');
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;

        $view->assign('products', $collection->getProducts('*', $offset, $limit));
        $count = $collection->count();
        $pages_count = ceil((float)$count / $limit);
        $view->assign('pages_count', $pages_count);
        $view->assign('products_count', $count);
    }
}
