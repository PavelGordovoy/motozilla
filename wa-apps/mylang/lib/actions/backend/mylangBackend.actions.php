<?php

class mylangBackendActions extends waJsonActions
{
    public function saveAction()
    {
        //Additional inputs are not passed by default in params
        $data['mylang'] = waRequest::post('mylang', [], 'array');
        $data['id'] = waRequest::post('type_id');
        $app_id = waRequest::post('app_id', null, 'string_trim');
        if (isset($data['mylang'])) {
            $model = new mylangModel();
            $model->saveLocale($data, $app_id);
        }
    }

    public function create_langpackAction()
    {
        $locale = waRequest::post('locale', 'en_US', 'string_trim');
        $pack = new mylangLangpack();
        $langpack = $pack->generate($locale);
        $stat = _w('%d app', '%d apps', $langpack['stat']['apps']).',<br>'.
            _w('%d plugin', '%d plugins', $langpack['stat']['plugins']).',<br>'.
            _w('%d widget', '%d widgets', $langpack['stat']['widgets']).'<br>';
        if (file_exists($langpack['file'])) {
            $stat .= '<br><a href="?module=backend&action=download_langpack&locale='.$locale.'"><i class="icon16 download"></i>'._w('Download').'</a>';
        }
        $this->response = ['stat'=>$stat, 'file'=> basename($langpack['file'])];
    }

    public function download_langpackAction()
    {
        $locale = waRequest::request('locale', 'en_US', 'string_trim');
        $path = wa()->getTempPath('mylang');
        $file = $path.$locale.'_locale.tar.gz';
        waFiles::readFile($file, $locale.'_'.date('Ymd').'.tar.gz');
    }

    public function install_langpackAction()
    {
        $plugin_id = waRequest::post('langpack', null, 'string_trim');
        $missing = waRequest::post('langpack-installmissing', '0', 'int');
        $langpack = new mylangLangpack($plugin_id);
        $files = $langpack->install($missing);
        $this->response = _w('Files added:').' '.count($files); //TODO: check errors
    }

    /**
     * @return mixed|string
     * @throws waDbException
     * @throws waException
     */
    public function optimizeAction()
    {
        $table = waRequest::post('table');
        if ($table === null) {
            return $this->errors[] = _w('No table name');
        }
        $m = new waModel();
        $tables = $m->query('SHOW TABLE STATUS')->fetchAll('Name'); //Check table exists
        try {
            if (array_key_exists($table, $tables)) {
                $m->exec('OPTIMIZE TABLE ' . $table); //Optimize table
            } else {
                return $this->errors[] = _w('No table name');
            }
        } catch (waDbException $e) {
            return $this->errors[] = _w('Database error');
        }
    }

    /**
     * @return bool|mixed|string
     * @throws waException
     */
    public function deleteCachefileAction()
    {
        $file = waRequest::post('file', null, 'string_trim');
        $file = preg_replace('!\.\.[/\\\]!','', $file);
        if ($file === null) {
            return $this->errors[] = _w('No filename');
        }
        $path = wa()->getDataPath('cache/', false, 'mylang');
        $file = $path.$file;
        if (file_exists($file)) {
            return waFiles::delete($file);
        }
    }

    /**
     * @return mixed|string
     * @throws waDbException
     * @throws waException
     */
    public function deleteLocalizedDataAction()
    {
        $locale = waRequest::post('locale', null, 'string_trim');
        $data = waRequest::post('apps');
        if ($locale === null || $data === null) {
            return $this->errors[] = _w('No data');
        }
        $apps = $slug = $custom = null;
        $model = new mylangModel();
        foreach ($data as $key => $app) {
            //all
            if ($app === 'all') {
                $apps[] = $key;
                continue; //delete all app
            }
            if (is_array($app)) {
                foreach ($app as $type => $part) {
                    if ($part === '1') {
                        $slug[] = $type;
                    } else {
                        $custom[] = [
                            'app' => $key,
                            'type' => $type,
                            'subtype' => $part,
                            'locale' => $locale
                        ];
                    }
                }
                if (!empty($slug)) {
                    $sql = 'DELETE FROM ' . $model->getTableName() . ' WHERE app_id in(?) and type in(?) and locale=?';
                    $model->exec($sql, $key, $slug, $locale);
                }
                unset($slug);
            }
        }
        //DELETE complete apps
        if (!empty($apps)) {
            $sql = 'DELETE FROM ' . $model->getTableName() . ' WHERE app_id in(?) and locale=?';
            $model->exec($sql, $apps, $locale);
        }
        //DELETE partial
        foreach ($custom as $entry) {
            $sql = 'DELETE FROM '.$model->getTableName().' WHERE app_id = s:app and type= s:type and subtype=s:subtype and locale=s:locale';
            $model->exec($sql, $entry);
        }
    }

    public function welcomeAction()
    {

    }
}
