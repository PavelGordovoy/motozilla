<?php

class mylangShopSaveController extends waJsonController
{
    public function execute()
    {
        $params = waRequest::post();
        if (!empty($params)) {
            if (isset($params['workflowAction'])) {
                $this->saveWorkflow($params);
            } else {
                $model = new mylangModel();
                $model->saveLocale($params, 'shop');
            }
        }
        $this->response = 0;
    }
    
    public function saveWorkflow($params)
    {
        wa('shop');
        $workflow = new shopWorkflow();
        $config = $workflow::getConfig();
        $states = $params['mylang']['state'];
        foreach ($states as $state => $name) {
            $name = array_filter($name);
            $config['states'][$state]['name'] = $name;
        }
        $workflow::setConfig($config);
    }
}
