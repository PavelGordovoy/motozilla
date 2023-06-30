<?php

/**
 * Class mylangShopCategory_saveHandler
 */
class mylangShopCategory_saveHandler extends mylangShopEventHandler
{
    /**
     * @param array $params
     * @return bool
     * @throws waDbException
     * @throws waException
     */
    public function execute(&$params = null)
    {
        if (!$params||$this->checkVersion()) {
            return false;
        }
        //Additional inputs are not passed by default in params
        //category[mylang][category][locale][category.id][category.field]
        $data = waRequest::post('category', [], 'array');
        $data['id'] = ifset($params, 'id', waRequest::get('category_id', null, 'int'));
        if (isset($data['mylang']) && ($data['id']>0)) {
            $model = new mylangModel();
            $model->saveLocale($data, 'shop');
        }
    }
}
