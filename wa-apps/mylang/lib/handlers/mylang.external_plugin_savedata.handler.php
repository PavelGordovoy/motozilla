<?php

class mylangMylangExternal_plugin_savedataHandler extends waEventHandler
{

    /**
     * @param array{mylang: array}: $params
     * $params[mylang][app_id.plugin_id]
     * @throws waDbException
     * @throws waException
     */
    public function execute(&$params)
    {
        $plugin_id = key($params['mylang']);
        $data['mylang'] = $params['mylang'][$plugin_id];
        $model = new mylangModel();
        $model->saveLocale($data, $plugin_id);
    }
}
