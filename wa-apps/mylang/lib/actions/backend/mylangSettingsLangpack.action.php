<?php

class mylangSettingsLangpackAction extends waViewAction
{
    public $langpack = '';

    public function execute()
    {
        $packs = mylangLangpack::getInstalled();
        $selected = waRequest::get('plugin_id', '', 'string_trim');
        $selected = ifset($packs, $selected, 'id', false);
        if ($selected) {
            $this->langpack = $selected;
            $this->getdata();
        }
        $this->view->assign('selected', $selected);
        $this->view->assign('packs', $packs);
        $locales = waLocale::getAll('name');
        $this->view->assign('locales', $locales);
    }

    private function getData()
    {
        $pack = new mylangLangpack($this->langpack);
        $diff = $pack->compareVersions();
        $this->view->assign('data', $diff);
    }
}