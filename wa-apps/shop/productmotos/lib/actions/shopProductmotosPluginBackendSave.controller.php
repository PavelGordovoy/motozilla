<?php

class shopProductmotosPluginBackendSaveController extends waJsonController
{
    public function execute()
    {
        $id = waRequest::get('id');
        $data = waRequest::post();

        $motos_model = new shopProductmotosModel();
        $moto = $motos_model->getById($id);

        $feature_model = new shopFeatureValuesVarcharModel();
        $f = $feature_model->getById($id);

        // change name of the moto, save new value for feature
        if ($data['name'] != $f['value']) {
            $feature_model->updateById($id, array('value' => $data['name']));
        }

        if (empty($data['image']) && $moto && $moto['image']) {
            waFiles::delete(wa()->getDataPath('motos/'.$id, true, 'shop', false));
        }

        $data['hidden'] = empty($data['hidden']) ? 0 : 1;
        if (waRequest::post('allow_filter')) {
            $data['filter'] = implode(',', ifset($data['filter'], array()));
        } else {
            $data['filter'] = null;
        }

        $data['enable_sorting'] = waRequest::post('enable_sorting') ? 1 : 0;

        $image = waRequest::file('image_file');
        if ($image->uploaded() && in_array(strtolower($image->extension), array('jpg', 'jpeg', 'png', 'gif'))) {
            $this->response['image'] = $data['image'] = '.'.$image->extension;
            $path = wa()->getDataPath('motos/'.$id.'/', true, 'shop');
            $image->moveTo($path, $id.$data['image']);
            $this->response['image_url'] = wa()->getDataUrl('motos/'.$id.'/'.$id.$data['image'], true, 'shop').'?v'.time();

            $sizes = trim(wa()->getSetting('sizes', '', array('shop', 'productmotos')));
            if ($sizes) {
                $sizes = explode(';', $sizes);
                foreach ($sizes as $size) {
                    if (!$size) {
                        continue;
                    }
                    if ($thumb_img = shopImage::generateThumb($path.$id.$data['image'], $size)) {
                        $thumb_img->save($path.$id.'.'.$size.$data['image']);
                    }
                }
            }
        }

        if ($moto) {
            $motos_model->updateById($id, $data);
        } else {
            $data['id'] = $id;
            $motos_model->insert($data);
        }
    }

    public function display()
    {
        $this->getResponse()->sendHeaders();
        if (!$this->errors) {
            $data = array('status' => 'ok', 'data' => $this->response);
            echo json_encode($data);
        } else {
            echo json_encode(array('status' => 'fail', 'errors' => $this->errors));
        }
    }
}