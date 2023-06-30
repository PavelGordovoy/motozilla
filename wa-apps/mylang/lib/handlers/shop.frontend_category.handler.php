<?php

class mylangShopFrontend_categoryHandler extends mylangShopEventHandler
{
    public function execute(&$params)
    {
        if (!mylangHelper::checkSite() || $this->checkVersion()) {
            return;
        }
        $ids = [$params['id']];
        if (!empty($params['subcategories'])) {
            $ids = array_merge($ids, array_keys($params['subcategories']));
        }
        $translate = new mylangTranslate();
        $category = $translate->category($ids, mylangLocale::currentLocale());
        foreach ($category as $cat) {
            if ($cat['type_id'] == $params['id']) { //Основная категория
                $params[$cat['subtype']] = $cat['text'];
            } else { //Подкатегории
                $params['subcategories'][$cat['type_id']][$cat['subtype']] = $cat['text'];
            }
        }
        $view = wa()->getView();
        $breadcrumbs = $view->getVars('breadcrumbs');
        $params['og']['title'] = $params['meta_title'];
        $this->breadcrumbs($breadcrumbs);
        $view->assign('breadcrumbs', $breadcrumbs);
        $view->assign('category', $params);
        $filters = $view->getVars('filters');
        if ($filters) {
            $this->filters($filters);
        }
        $view->assign('filters', $filters);
        
        // set meta
        wa()->getResponse()->setTitle($params['meta_title']);
        wa()->getResponse()->setMeta('keywords', $params['meta_keywords']);
        wa()->getResponse()->setMeta('description', $params['meta_description']);
        foreach (ifset($params['og'], []) as $property => $content) {
            $content && wa()->getResponse()->setOGMeta('og:'.$property, $content);
        }
    }

    private function breadcrumbs(&$params)
    {
        $url = waRequest::param('category_url');
        $model = new shopCategoryModel();
        if ($url) {
            $url_field = waRequest::param('url_type') == 1 ? 'url' : 'full_url';
            $sql = 'SELECT id FROM '.$model->getTableName().' where '.$url_field.'=?';
            $id = $model->query($sql, $url)->fetchField('id');
        } else {
            $id = waRequest::param('category_id');
        }
        $path = array_reverse($model->getPath($id));
        $path = mylangViewHelper::categories($path);
        foreach ($path as $key => $value) {
            $params[$key]['name'] = $value['name'];
        }
    }

    private function filters(&$filters)
    {
        $v_ids = [];
        foreach ($filters as $k => $f) {
            $f_ids[] = ifset($f['id']);
            if (!(isset($f['type']) && ($f['type'] == 'varchar' || $f['type'] == 'text' || $f['type'] == 'color'))) {
                continue;
            }
            if (!empty($f['values']) && is_array($f['values'])) {
                $v_ids[$f['type']] = array_merge(array_keys($f['values']), ifset($v_ids[$f['type']], []));
            }
        }
        $f_ids = array_filter($f_ids);
        if (empty($f_ids)) {
            return;
        }

        $model = new mylangModel();
        $names = $model->getValues('feature', $f_ids, mylangLocale::currentLocale());
        foreach ($names as $k => $f) {
            $filters[$k]['name'] = $f['text'];
        }
        $keys = array_keys($v_ids);
        if (empty($keys)) {
            return $filters;
        }
        $values = $model->getPlainValuesSubtype('feature_value', $keys, $v_ids, mylangLocale::currentLocale());
        if (empty($values)) {
            return $filters;
        }
        foreach ($filters as $key => $f) {
            if (!isset($f['values'])) {
                continue;
            }
            foreach ($f['values'] as $k => $v) {
                if (isset($values[$f['type']][$k])) {
                    if ($f['type'] === 'color') {
                        $filters[$key]['values'][$k]->__set('value', $values[$f['type']][$k]);
                    } else {
                        $filters[$key]['values'][$k] = $values[$f['type']][$k];
                    }
                }
            }
        }
        return $filters;
    }
}
