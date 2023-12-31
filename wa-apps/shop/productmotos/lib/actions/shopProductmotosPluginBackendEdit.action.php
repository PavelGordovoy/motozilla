<?php

class shopProductmotosPluginBackendEditAction extends waViewAction
{
    public function execute()
    {
        $motos_model = new shopProductmotosModel();
        $moto = $motos_model->getMoto(waRequest::get('id'));
        $this->view->assign('moto', $moto);

        $filter = $moto['filter'] !== null ? explode(',', $moto['filter']) : null;
        $feature_filter = array();
        $feature_model = new shopFeatureModel();
        $features['price'] = array(
            'id' => 'price',
            'name' => _w('Price')
        );
        $features += $feature_model
            ->select('*')
            ->where("(selectable=1 OR type='boolean' OR type='double' OR type LIKE 'dimension\.%' OR type LIKE 'range\.%') AND parent_id IS NULL")
            ->fetchAll('id');

        if (!empty($filter)) {
            foreach ($filter as $feature_id) {
                $feature_id = trim($feature_id);
                if (isset($features[$feature_id])) {
                    $feature_filter[$feature_id] = $features[$feature_id];
                    $feature_filter[$feature_id]['checked'] = true;
                    unset($features[$feature_id]);
                }
            }
        }
        $this->view->assign('filter_features', $feature_filter + $features);
    }
}