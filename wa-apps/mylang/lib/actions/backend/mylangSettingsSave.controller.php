<?php

class mylangSettingsSaveController extends waJsonController
{
    public function execute()
    {
        if (!$this->getUser()->isAdmin('mylang')) {
            throw new waException(_w('Access denied'));
        }
        $request = waRequest::post();
        if (!empty($request)) {
            switch ($request['action']) {
                case 'settings':
                    $data = [
                        'locales' => waRequest::post('locales', null, waRequest::TYPE_ARRAY),
                        'domains' => waRequest::post('domains', null, waRequest::TYPE_ARRAY),
                        'main_language' => waRequest::post('main_language', null, waRequest::TYPE_STRING),
                        'main_provider' => waRequest::post('main_provider', null, 'string_trim'),
                        'app_menu' => waRequest::post('app_menu', 0, 'int'),
                    ];
                    $app_settings = new waAppSettingsModel();
                    foreach ($data as $name => $value) {
                        $app_settings->set('mylang', $name, is_array($value)?json_encode($value):$value);
                    }
                    break;
                case 'fixlocale':
                    $this->fixlocale();
                    break;
                case 'addlocale':
                case 'editlocale':
                case 'deletelocale':
                    $this->localeAction($request['action']);
                    break;
                case 'addrule':
                    $this->addRule();
                    break;
                case 'delrule':
                    $this->delRule();
                    break;
                case 'shopsettings':
                    $data = [
                        'fullsearch' => waRequest::post('fullsearch', 0, 'int'),
                        'disablereviewfilter' => waRequest::post('disablereviewfilter', 0, 'int')
                    ];
                    $app_settings = new waAppSettingsModel();
                    foreach ($data as $name => $value) {
                        $app_settings->set('mylang', $name, is_array($value)?json_encode($value):$value);
                    }
                    break;
                case 'sitesettings':
                    $data = [
                        'filterpages' => waRequest::post('filterpages', 0, 'int'),
                    ];
                    $app_settings = new waAppSettingsModel();
                    foreach ($data as $name => $value) {
                        $app_settings->set('mylang', $name, is_array($value)?json_encode($value):$value);
                    }
                    break;
            }
        }
    }

    private function localeAction($action)
    {
        $id = waRequest::post('locale', null, waRequest::TYPE_STRING_TRIM);
        if (!$id) {
            $this->setError(_w('Empty value'));
            return;
        }

        $locales = new mylangLocale();
        $id = substr($id, 0, 5);
        if ($action === 'editlocale') {
            $locales->delete($id, true);
        }
        if ($action === 'addlocale' || $action === 'editlocale') {
            $localedata = waRequest::post('localedata', []);
            $localedata = array_filter($localedata); //remove empty
            $this->response = $locales->add($id, $localedata);
        }
        if ($action === 'deletelocale') {
            $this->response = $locales->delete($id);
        }
    }

    private function addRule()
    {
        $data = [
            'locale' => waRequest::post('locale', null, waRequest::TYPE_STRING),
            'domain' => waRequest::post('domain', null, waRequest::TYPE_STRING),
            'url' => waRequest::post('url', null, waRequest::TYPE_STRING),
        ];
        $rules = new mylangRules;
        $this->response = $rules->save($data);
    }

    private function delRule()
    {
        $data = [
            'locale' => waRequest::post('locale', null, waRequest::TYPE_STRING),
            'domain' => waRequest::post('domain', null, waRequest::TYPE_STRING),
        ];
        $rules = new mylangRules;
        $this->response = $rules->delete($data);
    }
    
    private function fixlocale()
    {
        $locales = waLocale::getAll();
        mylangLocale::fixIso3($locales);
    }
}
