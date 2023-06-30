<?php

class mylangEditorActions extends waViewActions
{
    private $apps;
    private $locales;
    private $shipping;
    private $payment;
    private $widgets;
    
    public function preExecute()
    {
        $ajax = waRequest::isXMLHttpRequest();
        if (!$ajax) {
            $this->setLayout(new mylangBackendLayout());
        }
        $this->apps = wa()->getApps(true);
        $this->locales = waLocale::getAll('name');
        $this->getSidebarHtml();
    }
    
    private function getSidebarHtml()
    {
        $rootpath = waConfig::get('wa_path_apps');
        ksort($this->apps);
        foreach ($this->apps as $key => &$app) {
            if (!is_dir($rootpath.'/'.$key.'/locale')) {
                if ($key !== 'webasyst') {
                    unset($this->apps[$key]);
                }
                continue;
            }
            if (array_key_exists('plugins', $app)) {
                $app['plugins'] = wa($key)->getConfig()->getPlugins();
            }
            //$app['widgets'] = wa($key)->getConfig()->getWidgets();
        }
        $this->apps = ['mylang_custom' => ['name' => _w('Custom strings'), 'id' => 'mylang_custom']] +$this->apps;
        wa('mylang', 1);
        $locales = waLocale::getAll('name');
        $this->view->assign('locales', $locales);
        $this->view->assign('apps', $this->apps);
        //System plugins
        $rootpath = waConfig::get('wa_path_plugins');
        $shipping = class_exists('waShipping') ? waShipping::enumerate() : [];
        if (!empty($shipping)) {
            foreach ($shipping as $key => $s) {
                if (!is_dir($rootpath.'/shipping/'.$key.'/locale')) {
                    unset($shipping[$key]);
                    continue;
                }
            }
        }
        $payment = class_exists('waPayment') ? waPayment::enumerate() : [];
        if (!empty($payment)) {
            foreach ($payment as $key => $p) {
                if (!is_dir($rootpath.'/payment/'.$key.'/locale')) {
                    unset($payment[$key]);
                    continue;
                }
            }
        }
        
        $widgets = wa('webasyst')->getConfig()->getWidgets();
        wa('mylang', 1);
        $this->payment = $payment;
        $this->shipping = $shipping;
        $this->widgets = $widgets;
        $this->view->assign('shipping', $shipping);
        $this->view->assign('payment', $payment);
        $this->view->assign('widgets', $widgets);
        $sidebar = $this->view->fetch(wa()->getAppPath($this->template_folder.'editor/EditorSidebar.html', 'mylang'));
        $this->view->assign('sidebar', $sidebar);
        $settings = mylangHelper::getSettings();
        $this->view->assign('settings', $settings);
        $this->view->assign('v', wa()->getVersion('mylang'));
    }
    
    public function defaultAction(): void
    {
        $slug = waRequest::get('slug', null, 'string_trim');
        if (!$slug) {
            $slug = key($this->apps);
        }
        $locale = waRequest::get('locale', null, 'string_trim');
        //System plugins
        wa('mylang', 1);
        if (strpos($slug, 'wa-plugins/') === 0) {
            $slug_path = explode('/', $slug);
            if ($slug_path['1'] === 'shipping') {
                $title = ifempty($this->shipping[$slug_path['2']]['name']);
                $title = _w('Shipping').' > '.$title;
            }
            if ($slug_path['1'] === 'payment') {
                $title = ifempty($this->payment[$slug_path['2']]['name']);
                $title = _w('Payment').' > '.$title;
            }
            $path = '';
        } elseif (strpos($slug, 'wa-widgets/') === 0) {
            $slug_path = explode('/', $slug);
                $title = ifempty($this->widgets[$slug_path['1']]['name']);
                $title = _ws('Widgets').' > '.$title;
            $path = '';
        } elseif (strpos($slug, 'webasyst') === 0) {
            $path = 'wa-system/';
            $title = _ws('Webasyst');
        } else {
            $path = 'wa-apps/';
            $slug_path = explode('/plugins/', $slug);
            $title = ifempty($this->apps[$slug_path[0]]['name']);
            if (isset($slug_path[1])) {
                $title .= ' > '._w('Plugins').' > '.$this->apps[$slug_path[0]]['plugins'][$slug_path[1]]['name'];
            }
        }
        $current_locales = waFiles::listdir($path.$slug.'/locale');
        $available_locales = array_intersect($current_locales, array_keys($this->locales));
        //saved settings;
        $user_locale = $this->getUser()->getSettings('mylang', 'editor_locale');
        $this->view->assign(compact('slug', 'locale', 'available_locales', 'title', 'user_locale'));
    }

    public static function localeDownloadAction(): void
    {
        $slug = waRequest::get('slug', '', 'string_trim');
        $locale = waRequest::get('locale', null, 'string_trim');

        if (strpos($slug, 'wa-plugins/') === 0) {
            $path = waConfig::get('wa_path_root');
            $target_file = str_replace(['wa-plugins/', '/'], ['', '_'], $slug);
            $path .= '/'.$slug.'/locale/'.$locale.'/LC_MESSAGES/'.$target_file.'.po';
        } elseif (strpos($slug, 'wa-widgets/') === 0) {
            $path = waConfig::get('wa_path_root');
            $target_file = str_replace(['wa-widgets/', '/'], ['', '_'], $slug);
            $path .= '/'.$slug.'/locale/'.$locale.'/LC_MESSAGES/widget_'.$target_file.'.po';
        } else {
            $path = wa()->getAppPath('locale/', $slug);
            $target_file = $slug;
            if (strpos($slug, '/plugins/')) {
                $target_file = str_replace('/plugins/', '_', $slug);
            }
            $path .= $locale . '/LC_MESSAGES/' . $target_file . '.po';
        }
        waFiles::readFile($path, $target_file.'_'.$locale.'.po', true);
    }
}
