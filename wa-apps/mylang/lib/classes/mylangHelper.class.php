<?php

class mylangHelper
{
    public static $enabled;

    public static function getSettings($name = null, $js = false)
    {
        $model = new waAppSettingsModel();
        $settings = $model->get('mylang');
        foreach ($settings as $key => $value) {
            #decode non string values
            if (!is_numeric($value)) {
                $json = json_decode($value, true);
                if (is_array($json)) {
                    $settings[$key] = $json;
                }
            }
        }
        if ($name === null) {
            $result = $settings;
        } else {
            $result = ifset($settings[$name]);
        }
        return $js ? json_encode($result) : $result;
    }

    public static function setSettings($data)
    {
        $app_settings = new waAppSettingsModel();
        foreach ($data as $name => $value) {
            $app_settings->set('mylang', $name, is_array($value) ? json_encode($value) : $value);
        }
    }

    /**
     * @param $pages
     * @param null $locale
     * @return mixed
     * @throws waException
     */
    public static function getPagesByLocale($pages, $locale = null)
    {
        $locale = ifset($locale, mylangLocale::currentLocale());
        foreach ($pages as $key => $page) {
            if (!empty($page['mylang_locale']) && stripos($page['mylang_locale'], $locale) === false) {
                unset($pages[$key]);
            }
        }
        return $pages;
    }

    /**
     * @param $product_id
     * @param null $locale
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public static function featureValuesByProduct($product_id, $locale = null)
    {
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }
        $model = new mylangModel();
        $features = $model->getValues('feature_value', $product_id, $locale);
        foreach ($features as $key => &$value) {
            if (count($features[$key]) == 1) {
                $features[$key] = [$value[0]['id'] => $value[0]['text']];
                continue;
            }
            $values = [];
            foreach ($value as $v) {
                $values[$v['id']] = $v['text'];
            }
            $features[$key] = $values;
        }
        return $features;
    }

    // Не используется?
    public static function tags($tags = [], $locale = null)
    {
        if (empty($tags)) {
            return $tags;
        }
        $ids = [];
        foreach ($tags as $tag) {
            $ids[] = $tag['id'];
        }
        $model = new mylangModel();
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }
        $translated = $model->getValues('tag', $ids, $locale);
        foreach ($tags as &$tag) {
            if (!empty($translated[$tag['id']])) {
                $tag['name'] = $translated[$tag['id']]['text'];
            }
        }
        return $tags;
    }

    /**
     * @param bool $checkLocale
     * @return bool
     * @throws waException
     */
    public static function checkSite($checkLocale = true)
    {
        if (self::$enabled !== null) {
            return self::$enabled;
        }
        //Hack the auth url
        if (!waConfig::get('is_template')) {
            $route_url = waConfig::get('mylang_auth_url');
            if (!$route_url) {
                $routing = wa()->getRouting();
                $domain = $routing->getDomain(null, true, false);
                waConfig::set('mylang_this_domain', $domain);
                $authConfig = wa()->getAuthConfig($domain);
                $auth_app = ifset($authConfig, 'app', false);
                $route = $routing->getRoute();
                $currentApp = ifset($route, 'app', false);
                $route_url = ifset($route, 'url', false);
                if ($auth_app !== $currentApp) {
                    $routes = $routing->getRoutes($domain);
                    $searchAuthApp = false;
                    foreach ($routes as $rt) {
                        if ($route_url === ifset($rt, 'url', false)) {
                            $searchAuthApp = true;
                            continue;
                        }
                        if ($searchAuthApp && $auth_app === ifset($rt, 'app', false)) {
                            $route_url = ifset($rt, 'url', false);
                            break;
                        }
                    }
                }
                if ($route_url) {
                    $config = waDomainAuthConfig::factory($domain);
                    waConfig::set('mylang_auth_url', $route_url);
                    $config->setRouteUrl($route_url);
                }
            }
        }
        $site = wa()->getRouting()->getDomain();
        $current_loc = mylangLocale::currentLocale();
        $asm = new waAppSettingsModel();
        $data = $asm->get('mylang');
        if ($current_loc == ifset($data['main_language'])) {
            return false;
        }
        $domains = json_decode(ifset($data['domains']), true);
        if (!is_array($domains)) {
            $domains = [$domains];
        }
        if (!$checkLocale) {
            return in_array($site, $domains);
        }
        $locales = json_decode(ifset($data['locales']), true);
        if (!is_array($locales)) {
            $locales = [$locales];
        }
        self::$enabled = in_array($site, $domains) && in_array($current_loc, $locales);
        return self::$enabled;
    }

    /**
     * @param $params
     * @param string $name
     * @return string
     * @throws waException
     */
    public static function pageLocaleSelect($params, $name = 'info')
    {
        $page_locale = ifset($params['mylang_locale']);
        $select = '<select name="' . $name . '[mylang_locale]"><option value=""';
        $select .= empty($page_locale) ? 'selected' : '';
        $select .= '>' . _w('Everyone') . '</option>';
        $locales = waLocale::getAll('name');
        foreach ($locales as $key => $locale) {
            $selected = $key == $page_locale ? 'selected' : null;
            $select .= '<option value="' . $key . '" ' . $selected . '>' . $locale . '</option>';
        }
        return $select . '</select>';
    }

    /**
     *
     * Compress locale into archive file
     * @param string $path target archive path
     * @param string $name archive filename
     * @return string arcive path
     * @throws waException
     */
    public static function compress($path, $name = null)
    {
        if (waConfig::get('is_template')) {
            return false;
        }
        $name .= '_locale.tar.gz';
        $tar_path = wa()->getTempPath('mylang');

        $autoload = waAutoload::getInstance();
        $autoload->add('Archive_Tar', 'wa-installer/lib/vendors/PEAR/Tar.php');
        $autoload->add('PEAR', 'wa-installer/lib/vendors/PEAR/PEAR.php');

        $target_file = "{$tar_path}{$name}";
        if (file_exists($path) && class_exists('Archive_Tar')) {
            waFiles::create($target_file);
            $tar_object = new Archive_Tar($target_file, true);
            $tar_object->setIgnoreRegexp('@(\.(?|_|\.db|mo$|htaccess|files\.md5$))@'); //filter *.mo *.htaccess
            $path_tar = getcwd();
            chdir(dirname($path));
            if (!$tar_object->create('./' . basename($path))) { // TODO
                waFiles::delete($target_file);
            }
            chdir($path_tar);
        }
        return $target_file;
    }

    public static function hashKey($data = [], $keyname = 'md5')
    {
        foreach ($data as $key => $val) {
            $data[$key][$keyname] = md5($key);
        }
        return $data;
    }

    public static function unhashKey($data = [], $keyname = 'md5')
    {
        foreach ($data as $key => $val) {
            unset($data[$key][$keyname]);
        }
        return $data;
    }

    public static function getSupported()
    {
        $cache = new waVarExportCache('supported_apps', 3600, 'mylang');
        $result = $cache->get();
        if (!empty($result)) {
            return $result;
        }
        //All apps and plugins
        $apps = wa()->getApps(true);
        $rootpath = waConfig::get('wa_path_apps');
        foreach ($apps as $key => &$app) {
            if (array_key_exists('plugins', $app)) {
                $plugins = wa($key)->getConfig()->getPlugins();
                foreach ($plugins as $plugin_id => &$plugin) {
                    $plugin['locale_path'] = $rootpath . '/' . $key . '/plugins/' . $plugin_id . '/locale/';
                    if (!is_dir($plugin['locale_path'])) {
                        unset($plugins[$plugin_id]);
                        continue;
                    }
                }
                $app['plugins'] = $plugins;
            }
            $widgets = wa($key)->getConfig()->getWidgets();
            if ($key === 'webasyst') {
                $widgets_path = waConfig::get('wa_path_widgets');
                foreach ($widgets as $widget_id => &$widget) {
                    $widget['locale_path'] = $widgets_path . '/' . $key . '/widgets/' . $widget_id . '/locale/';
                    if (!is_dir($widget['locale_path'])) {
                        unset($widgets[$widget_id]);
                        continue;
                    }
                }
            } else {
                foreach ($widgets as $widget_id => &$widget) {
                    $widget['locale_path'] = $rootpath . '/' . $key . '/widgets/' . $widget_id . '/locale/';
                    if (!is_dir($widget['locale_path'])) {
                        unset($widgets[$widget_id]);
                        continue;
                    }
                }
            }
            $app['widgets'] = $widgets;
            if ($key !== 'webasyst' && empty($app['plugins']) && !is_dir($rootpath . '/' . $key . '/locale/')) {
                unset($apps[$key]);
            }
            $app['locale_path'] = $rootpath . '/' . $key . '/locale/';
            if ($key === 'webasyst') {
                $app['locale_path'] = waConfig::get('wa_path_system') . '/' . $key . '/locale/';
            }
        }
        //System plugins
        $rootpath = waConfig::get('wa_path_plugins');
        $shipping = waShipping::enumerate();
        if (!empty($shipping)) {
            foreach ($shipping as $key => &$s) {
                $s['locale_path'] = $rootpath . '/shipping/' . $key . '/locale/';
                if (!is_dir($s['locale_path'])) {
                    unset($shipping[$key]);
                    continue;
                }
            }
        }
        $payment = waPayment::enumerate();
        if (!empty($payment)) {
            foreach ($payment as $key => &$p) {
                $p['locale_path'] = $rootpath . '/payment/' . $key . '/locale/';
                if (!is_dir($p['locale_path'])) {
                    unset($payment[$key]);
                    continue;
                }
            }
        }
        $result = [
            'apps' => $apps,
            'payment' => $payment,
            'shipping' => $shipping,
        ];
        $cache->set($result);
        return $result;
    }

    /**
     * @param $target_arr
     * @param $ids
     * @param null $translated
     * @return array|void
     */
    public static function iterate_recursive(&$target_arr, &$ids = null, $translated = null)
    {
        if (!is_array($target_arr)) {
            return;
        }
        if (isset($target_arr['id'])) {
            $ids[] = $last = $target_arr['id'];
            if (!empty($translated[$last])) {
                foreach ($translated[$last] as $r) {
                    $target_arr[$r['subtype']] = $r['text'];
                }
            }
            if (array_key_exists('childs', $target_arr) || isset($target_arr['childs'])) {
                self::iterate_recursive($target_arr['childs'], $ids, $translated);
            }
        } else {
            foreach ($target_arr as &$target) {
                self::iterate_recursive($target, $ids, $translated);
            }
        }
        return array_unique($ids);
    }

    public static function addSmartyFilter()
    {
        wa()->getView()->smarty->unregisterFilter('pre', 'smarty_prefilter_translate');
        new mylangPrefilter();
        wa()->getView()->smarty->registerFilter('pre', 'mylang_smarty_prefilter_translate');
    }
}
