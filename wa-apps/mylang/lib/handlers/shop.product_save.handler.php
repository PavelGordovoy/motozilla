<?php

class mylangShopProduct_saveHandler extends mylangShopEventHandler
{
    public function execute(&$params)
    {
        if ($this->checkVersion()) {
            return;
        }
        if (array_key_exists('mylang', $params['data'])) {
            if (isset($params['data']['mylang']['product'])) {
                $model = new mylangModel();
                $model->saveLocale($params, 'shop');
            } elseif (count($params['data']['mylang'])) {   //it's import
                $id = $params['data']['id'];
                foreach ($params['data']['mylang'] as $k => $v) {
                    $parts = explode('/', $k);
                    $params['data']['mylang']['product'][$parts[1]][$id][$parts[0]] = $v;
                    unset($params['data']['mylang'][$k]);
                }
                $model = new mylangModel();
                $model->saveLocale($params, 'shop');
            }
        } elseif (isset($params['data']['skus'])) {   // it might be import too =(
            $mylang = false;
            foreach ($params['data']['skus'] as $sku_id => $sku) {
                $id = $params['data']['id'];
                if (isset($sku['mylang'])) {
                    $mylang = true;
                    foreach ($sku['mylang'] as $k => $v) {
                        $parts = explode('/', $k);
                        $params['data']['mylang']['product'][$parts[1]][$id]['skuname_' . $sku_id] = $v;
                    }
                }
            }
            if ($mylang) {
                $model = new mylangModel();
                $model->saveLocale($params, 'shop');
            }
        }
    }
}
