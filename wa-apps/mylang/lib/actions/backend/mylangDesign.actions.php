<?php

class mylangDesignActions extends waViewActions
{
    public function defaultAction()
    {
        $app = waRequest::get('app_id', null, 'string_trim');
        $themes = wa($app)->getThemes();
        $selected = waRequest::get('theme', null, 'string_trim');
        $data = [];
        foreach ($themes as $id => $theme) {
            $data[$id]['name'] = $theme->getName();
            $data[$id]['id'] = $id;
            if ($selected) {
                $localesStrings = wao(new mylangTheme($selected, $app))->getLocales(1);
                $localesStrings = mylangHelper::hashKey($localesStrings);
                $model = new mylangModel();
                $translated = $model->getTheme($id);
                $settings = $theme->getSettings('full');
                foreach ($settings as $key => $set) {
                    if ($set['control_type'] == 'text') {
                        if (isset($translated[$key])) {
                            $set['value'] = $translated[$key];
                        }
                        $data[$id]['settings'][$key] = $set;
                    }
                }
                $data[$id]['disabled'] = !ifset($data[$id]['settings']);
            }
        }
        if ($selected) {
            $this->view->assign('theme', $data[$selected]);
            $this->view->assign('localeStrings', $localesStrings);
        }
        $this->view->assign('app', $app);
        $this->view->assign('themes', $data);
        $this->view->assign('locales', waLocale::getAll());
    }
    
    public function saveAction()
    {
        $data = waRequest::post();
        $theme = new mylangTheme($data['app_id'].':'.$data['theme_id']);
        $settings = $theme->getSettings(true); //onlyvalues
        $locales = $theme->getLocales(1);
        if (empty($data['settings']) && empty($data['locales'])) {
            return;
        }
        foreach ($data['settings'] as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $l => $val) {
                    $val = trim($val);
                    $params['mylang']['theme'][$l][$key][$data['theme_id']] = $val;
                }
            }
            $settings[$key] = $value;
        }
        $locales = mylangHelper::hashKey($locales);
        if (!empty($data['locales'])) {
            foreach ($locales as $key => $value)
            {
                if (isset($data['locales'][$value['md5']])) {
                    $locales[$key] = $data['locales'][$value['md5']];
                    $locales[$key] = array_filter($locales[$key]);
                }
            }
        }
        $locales = mylangHelper::unhashKey($locales);
        $theme['settings'] = $settings;
        $theme['locales'] = $locales;
        $this->getResponse()->addHeader('Content-type', 'application/json');
        $this->view->assign('message', ['status' => 'ok', 'data' => $theme->save()]);
        $model = new mylangModel();
        $model->saveLocale($params, $data['app_id']);
    }
}
