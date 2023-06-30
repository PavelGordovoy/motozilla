<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformConfig extends waAppConfig
{

    public function getClasses()
    {
        $cache_file = waConfig::get('wa_path_cache') . '/apps/' . $this->application . '/config/autoload.php';
        if (self::isDebug() || !file_exists($cache_file)) {
            waFiles::create(waConfig::get('wa_path_cache') . '/apps/' . $this->application . '/config');
            $paths = array($this->getAppPath() . '/lib/');
            // plugins
            $all_plugins = waFiles::listdir($this->getAppPath() . '/plugins');
            foreach ($all_plugins as $plugin_id) {
                $path = $this->getPluginPath($plugin_id) . '/lib/';
                if (file_exists($path)) {
                    $paths[] = $path;
                }
            }
            // widgets
            $all_widgets = waFiles::listdir($this->getAppPath() . '/widgets');

            foreach ($all_widgets as $w_id) {
                $path = $this->getWidgetPath($w_id) . '/lib/';
                if (file_exists($path)) {
                    $paths[] = $path;
                }
            }
            // api
            if (file_exists($this->getAppPath() . '/api')) {
                $v = waRequest::request('v', 1, 'int');
                if (file_exists($this->getAppPath() . '/api/v' . $v)) {
                    $paths[] = $this->getAppPath() . '/api/v' . $v . '/';
                }
            }
            // actions
            $all_actions = waFiles::listdir($this->getAppPath() . '/app_actions');
            foreach ($all_actions as $action_id) {
                $path = $this->getActionPath($action_id) . 'lib/';
                if (file_exists($path)) {
                    $paths[] = $path;
                }
            }

            $result = array();
            $length = strlen($this->getRootPath());
            foreach ($paths as $path) {
                $files = $this->getPHPFiles($path);
                foreach ($files as $file) {
                    $class = $this->getClassByFilename(basename($file));
                    if ($class) {
                        // Classes in dir named /custom/ have priority.
                        // This allows to override code without modifications to the original.
                        if (isset($result[$class]) && false !== stripos(str_replace('\\', '/', $result[$class]), '/custom/')) {
                            continue;
                        }
                        $result[$class] = substr($file, $length + 1);
                    }
                }
            }

            if (!self::isDebug()) {
                waUtils::varExportToFile($result, $cache_file);
            } else {
                waFiles::delete($cache_file);
            }
            return $result;
        }
        return include($cache_file);
    }

    private function getActionPath($action_code)
    {
        return wa()->getAppPath('app_actions/' . $action_code . '/', 'multiform');
    }

    public function onCount()
    {
        // Последнее время просмотра записей
        $contact_model = new waContactSettingsModel();
        $contact_settings = $contact_model->get(wa()->getUser()->getId(), 'multiform');

        $mf = new multiformFormModel();
        $mr = new multiformRecordModel();
        // Вычисляем количество непросмотренных записей        
        $sql = "SELECT COUNT(r.id) as count FROM {$mf->getTableName()} f"
                . " LEFT JOIN {$mr->getTableName()} r ON f.id = r.form_id ";
        // Узнаем, какие формы доступны для текущего пользователя
        $user = wa()->getUser();
        if ($user->getRights('multiform', 'backend') == 1) {
            $sql .= "WHERE 1";
            $ids = array();
            $record_rights = $user->getRights('multiform', 'records.%');

            if (isset($record_rights['all'])) {
                unset($record_rights['all']);
                $ids = array_keys($record_rights);
            } elseif (!empty($record_rights)) {
                $ids = array_keys($record_rights);
            }

            if ($ids) {
                $sql .= " AND f.id IN('" . implode("','", $ids) . "')";
            }

            $create_rights = $user->getRights('multiform', 'create');
            if ($create_rights) {
                $sql .= " AND f.create_contact_id = '" . (int) $user->getId() . "'";
            }

            if (!$create_rights && !$ids) {
                return 0;
            }
        }

        if ($contact_settings) {
            $values = array();
            foreach ($contact_settings as $cs_ext => $cs) {
                if (strpos($cs_ext, "multiform_last_datetime_") !== false) {
                    $form_id = substr($cs_ext, strlen("multiform_last_datetime_"));
                    if ((isset($ids) && in_array($form_id, $ids)) || !isset($ids) || !empty($create_rights)) {
                        $values[] = "(f.id = '" . (int) $form_id . "' AND r.create_datetime > '" . $mf->escape($cs) . "')";
                    }
                }
            }
            if ($values) {
                $sql .= (!isset($ids) ? "WHERE " : " AND ") . implode(" OR ", $values);
            }
        }
        $sql .= " GROUP BY f.id";
        $count = 0;
        foreach ($mf->query($sql) as $c) {
            $count += (int) $c['count'];
        }

        return $count;
    }

    /**
     * Convert shortcuts of upload_max_filesize. Availible shortcuts: K (kilo), M (mega) and G (giga)
     * @param string $value
     * @return int
     */
    public function convertShortcuts($value)
    {
        $digits = preg_replace("/[^\d]/", "", $value);
        if (strpos($value, "M")) {
            $digits *= 1000000;
        } elseif (strpos($value, "K")) {
            $digits *= 1000;
        } elseif (strpos($value, "G")) {
            $digits *= 1000000000;
        }
        return $digits;
    }

    public function getSaveQuality($for2x = false)
    {
        // TODO
//        $quality = $this->getOption('image_save_quality'.($for2x ? '_2x' : ''));
        $quality = "";
        if (!$quality) {
            $quality = $for2x ? 70 : 90;
        }
        return $quality;
    }

    /**
     * Remove port from domain
     * 
     * @return string
     */
    public function getDomain()
    {
        $domain = parent::getDomain();
        if (strpos($domain, ":") !== false) {
            $domain = substr($domain, 0, strpos($domain, ":"));
        }
        if (strpos($domain, "/index.php") !== false) {
            $domain = substr($domain, 0, strpos($domain, "/index.php"));
        }

        // Проверяем существует ли поселение
        $route_url = wa()->getRouteUrl("multiform/frontend", array("domain" => $domain), true);
        if (!$route_url) {
            if (strpos($domain, 'www.') === false && wa()->getRouteUrl("multiform/frontend", array("domain" => 'www.' . $domain), true)) {
                $domain = 'www.' . $domain;
            } elseif (strpos($domain, 'www.') !== false && wa()->getRouteUrl("multiform/frontend", array("domain" => str_replace('www.', '', $domain)), true)) {
                $domain = str_replace('www.', '', $domain);
            }
        }

        return $domain;
    }

    /**
     * Get all field types
     * 
     * @staticvar array $types
     * @return array
     */
    public function getFieldTypes()
    {
        static $types = array();
        if (!$types) {
            $types = include wa()->getAppPath('lib/config/fields.php', 'multiform');
            // Получаем поля плагинов
            $plugins = $this->getPlugins();
            if ($plugins) {
                foreach ($plugins as $p_id => $p) {
                    if (!empty($p['fields'])) {
                        $plugin_path = $this->getPluginPath($p_id);
                        if (file_exists($plugin_path . '/lib/config/fields.php')) {
                            $plugin_types = include($plugin_path . '/lib/config/fields.php');
                            if (is_array($plugin_types)) {
                                $types += $plugin_types;
                            }
                        }
                    }
                }
            }
        }
        return $types;
    }

    public function callActionEvent($name, $event_params)
    {
        $ma = new multiformActions();
        $actions = $ma->getActions();
        foreach ($actions as $action) {
            $path = $ma->_getPath($action['id']);
            $file_path = $path . 'lib/handlers/' . $name . ".handler.php";
            if (!file_exists($file_path)) {
                continue;
            }
            include_once($file_path);
            $class_name = 'multiform' . str_replace('_', '', ucwords($action['id'], '_')) . str_replace('_', '', ucwords($name, '_')) . 'Handler';
            if (!class_exists($class_name)) {
                if (waSystemConfig::isDebug()) {
                    waLog::log('Event handler class does not exist: ' . $class_name);
                }
                continue;
            }

            try {
                $handler = new $class_name();
                $handler->execute($event_params);
            } catch (Exception $e) {
                waLog::log('Event handling error in ' . $file_path . ': ' . $e->getMessage());
            }
        }

        wa()->event($name, $event_params);
    }

}
