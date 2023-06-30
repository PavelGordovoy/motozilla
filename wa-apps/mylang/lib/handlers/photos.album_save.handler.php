<?php

class mylangPhotosAlbum_saveHandler extends waEventHandler
{
    public function execute(&$params)
    {
        if (!$params) {
            return;
        }
        //Additional inputs are not passed by default in params
        $data['mylang'] = waRequest::post("mylang", [], 'array');
        $data['id'] = $params['id'];
        if (isset($data['mylang'])) {
            $model = new mylangModel();
            $model->saveLocale($data, 'photos');
        }
    }
}
