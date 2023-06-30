<?php

class mylangSettingsMaintenanceAction extends waViewAction
{

    public function execute()
    {
        $cache = new mylangJsonCache();
        $cacheFiles = $cache->getFiles();
        $this->view->assign('cachefiles', $cacheFiles);
        $this->view->assign('localestats', $this->getLocaleStats());
        $this->view->assign('tables', $this->getTables());
        $locales = waLocale::getAll('name');
        $this->view->assign('locales', $locales);
    }

    private function getTables()
    {
        $model = new waModel();
        $tables = $model->query('SHOW TABLE STATUS WHERE Data_free > 51400')->fetchAll('Name');
        if (!empty($tables)) {
            foreach ($tables as &$table) {
                $table['Data_free'] = waFiles::formatSize($table['Data_free'], '%0.2f', _w('B,KB,MB,GB'));
            }
        }
        //return ifset($tables, 'mylang', []);
        return $tables;
    }

    private function getLocaleStats()
    {
        $model = new waModel();
        $sql = 'SELECT locale, count(*) as count from mylang WHERE 1 GROUP by locale';
        return $model->query($sql)->fetchAll('locale', 1);
    }
}