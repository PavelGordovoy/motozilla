<?php

class mylangBackendLayout extends waLayout
{
    public function execute()
    {
        $this->loc();
        
        $supported = [
            'contacts',
            'shop',
            'site',
            'blog',
            'photos',
        ];
        $apps = wa()->getApps();
        foreach ($apps as $key => $app) {
            if (!in_array($key, $supported, true)) {
                unset($apps[$key]);
            }
        }
        wa('mylang', 1);
        $apps['editor'] = ['name' => _w('Editor')];
        //$apps['tutorial'] = ['name' => _w('Tutorial')]; TODO 3.0
        $this->view->assign('apps', $apps);
        $this->view->assign('v', wa()->getVersion());
        wa()->getResponse()->addJs('wa-content/js/jquery-wa/wa.dialog.js', null);

        /*
        // Welcome-screen-related stuff: redirect on first login unless skipped
        $app_settings_model = new waAppSettingsModel();
        if (waRequest::get('skipwelcome')) {
            $app_settings_model->del('mylang', 'welcome');
            $app_settings_model->del('mylang', 'show_tutorial');
            $app_settings_model->get('mylang', 'setup_demo_time');
        }

        // Tutorial tab status
        $tutorial_progress = 0;
        $tutorial_visible = $app_settings_model->get('mylang', 'show_tutorial') || waRequest::request('module') === 'tutorial';
        if ($tutorial_visible) {
            $tutorial_progress = mylangTutorialActions::getTutorialProgress();
        }*/
        $this->view->assign(array(
            'page'              => $this->getPage(),
            //'tutorial_progress' => $tutorial_progress,
            //'tutorial_visible'  => $tutorial_visible,
        ));
    }
    
    protected function getPage()
    {
        // Default page
        $default_page = 'contacts';
        $module = waRequest::get('module', 'backend');
        $page = waRequest::get('module', ($module === 'backend') ? $default_page : 'default');
        if ($module !== 'backend') {
            $page = $module;
        }
        $plugin = waRequest::get('plugin');
        if ($plugin) {
            if ($module === 'backend') {
                $page = ':'.$page;
            }
            $page = $plugin.':'.$page;
        }
        return $page;
    }
    
    private function loc(): void
    {
         $strings = [];
        // Application locale strings
        foreach ([
            'Cancel',
            'File is not selected.',
            'Locale is not selected.',
            'Api error. Check the token key and quota.',
            'System keys',
            'Plural form',
            'Delete string?'
                 ] as $s) {
            $strings[$s] = _w($s);
        }
        $this->view->assign('strings', $strings ? json_encode($strings) : new stdClass()); // stdClass is used to show {} instead of [] when there's no strings
    }
}
