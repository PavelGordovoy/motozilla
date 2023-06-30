<?php

class shopProductmotosPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        /**
         * @var shopProductmotosPlugin $plugin
         */
        $plugin = wa()->getPlugin('productmotos');

        $feature_model = new shopFeatureModel();
        $features = $feature_model->select('*')->where('selectable = 1')->order('id DESC')->fetchAll();
        $this->view->assign('features', $features);

        $key = array('shop', 'productmotos');

        $app_settings_model = new waAppSettingsModel();
        $feature_id = $app_settings_model->get($key, 'feature_id');
        if (!$feature_id) {
            $ids = array('moto', 'brend', 'manufacturer', 'make');
            foreach ($features as $f) {
                if (in_array($f['code'], $ids)) {
                    $feature_id = $f['id'];
                    break;
                }
            }
        }
        $this->view->assign('feature_id', $feature_id);

        $this->view->assign('settings', $plugin->getSettings());

        $this->view->assign('sizes', $app_settings_model->get($key, 'sizes'));
        $this->view->assign('categories_filter', $app_settings_model->get($key, 'categories_filter'));
        $this->view->assign('title_h1', $app_settings_model->get($key, 'title_h1'));
        $this->view->assign('sort', $app_settings_model->get($key, 'sort'));

        $path = wa()->getAppPath('plugins/productmotos/templates/', 'shop');

        if ($t_nav = $app_settings_model->get($key, 'template_nav')) {
            $this->view->assign('template_nav', $t_nav);
        } else {
            $this->view->assign('template_nav', file_get_contents($path.'frontendNav.html'));
        }

        if ($t_search = $app_settings_model->get($key, 'template_search')) {
            $this->view->assign('template_search', $t_search);
        } else {
            $this->view->assign('template_search', file_get_contents($path.'frontendSearch.html'));
        }

        if ($t_motos = $app_settings_model->get($key, 'template_motos')) {
            $this->view->assign('template_motos', $t_motos);
        } else {
            $this->view->assign('template_motos', file_get_contents($path.'frontendMotos.html'));
        }
    }
}
