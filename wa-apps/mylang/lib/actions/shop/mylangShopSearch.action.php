<?php


//TODO: Maybe deprecated after waEvent.
class mylangShopSearchAction extends waViewAction
{
    
    public function __construct($params = null)
    {
        wa('shop', 1); //magic
        parent::__construct($params);
    }
    
    public function execute()
    {
        if (!waRequest::isXMLHttpRequest()) {
            $this->setLayout(new shopFrontendLayout());
        }
        $this->view->assign('frontend_nav', wa()->event('frontend_nav'));

        /**
         * @event frontend_nav_aux
         * @return array[string]string $return[%plugin_id%] html output for navigation section
         */
        $this->view->assign('frontend_nav_aux', wa()->event('frontend_nav_aux'));

//        $themes = wa()->getThemes('shop');
//        $theme = waRequest::param('theme');
        $query = waRequest::get('query');
        
        $model = new mylangModel;
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
        
        $this->view->assign('products', $collection->getProducts('*', $offset, $limit));
        $count = $collection->count();
        $pages_count = ceil((float)$count / $limit);
        $this->view->assign('pages_count', $pages_count);
        $this->view->assign('products_count', $count);
        
        //TODO $limit $offset
        
        $query = htmlspecialchars($query);
        $this->view->assign('title', $query);

        if ($this->layout) {
            $this->layout->assign('query', $query);
        }
        if (!$query) {
            $this->view->assign('sorting', true);
        }

        $title = waRequest::param('title');
        if (!$title) {
            $title = wa('shop')->getConfig()->getGeneralSettings('name');
        }
        if (!$title) {
            $app = wa('shop')->getAppInfo();
            $title = $app['name'];
        }
        $title = htmlspecialchars($title);
        wa()->getResponse()->setTitle($query.' — '.$title);
        
        $this->setThemeTemplate('search.html');
        
        /**
         * @event frontend_search
         * @return array[string]string $return[%plugin_id%] html output for search
         */
        $this->view->assign('frontend_search', wa()->event('frontend_search'));
    }
}
