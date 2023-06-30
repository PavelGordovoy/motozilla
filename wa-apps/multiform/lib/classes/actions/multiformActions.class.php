<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActions
{

    private static $actions = array();

    public function __construct()
    {
        if (!self::$actions) {
            // Осуществляем перебор системных действий
            $system_actions = wa('multiform')->getConfig()->getInfo('actions');
            if ($system_actions) {
                foreach ($system_actions as $action_code => $enabled) {
                    // Проверяем активность действий
                    if ($enabled) {
                        // Получаем основную информацию из файла action.php
                        $action_info = $this->_getInfo($action_code);
                        if ($action_info) {
                            self::$actions[$action_code] = $action_info;
                            self::$actions[$action_code]['id'] = $action_code;
                            // Если действие использует настройки, получаем их. Файл settings.php
                            if (!empty($action_info['settings'])) {
                                self::$actions[$action_code]['settings'] = $this->_getSettings($action_code);
                            }
                        }
                    }
                }
            }
        }
    }

    public function getActions()
    {
        return self::$actions;
    }

    public function getAction($action_code)
    {
        if ($this->actionExists($action_code)) {
            return self::$actions[$action_code];
        }
        return array();
    }

    public function actionExists($action_code)
    {
        return isset(self::$actions[$action_code]);
    }

    public function _getPath($action_code)
    {
        $plugin_root = $this->getPluginRootPath();
        return wa()->getAppPath($plugin_root . 'app_actions/' . $action_code . '/', 'multiform');
    }

    public function _getUrl($action_code)
    {
        $plugin_root = $this->getPluginRootUrl();
        $c = wa()->getAppStaticUrl('multiform', true) . $plugin_root . 'app_actions/' . $action_code . '/';
        return wa()->getAppStaticUrl('multiform', true) . $plugin_root . 'app_actions/' . $action_code . '/';
    }

    private function getPluginRootPath()
    {
        return '';
    }

    private function getPluginRootUrl()
    {
        return '';
    }

    public function _getConfigPath($action_code)
    {
        return $this->_getPath($action_code) . 'lib/config/';
    }

    public function _getConfig($action_code)
    {
        static $config = array();
        if (!isset($config[$action_code])) {
            $path = $this->_getConfigPath($action_code) . 'config.php';
            if (file_exists($path)) {
                $config[$action_code] = include $path;
            } else {
                $config[$action_code] = array();
            }
        }
        return $config[$action_code];
    }

    public function _getInfo($action_code)
    {
        static $info = array();
        if (!isset($info[$action_code])) {
            $path = $this->_getConfigPath($action_code) . 'action.php';
            if (file_exists($path)) {
                $info[$action_code] = include $path;
            } else {
                $info[$action_code] = array();
            }
        }
        return $info[$action_code];
    }

    public function _getSettings($action_code)
    {
        static $settings = array();
        if (!isset($settings[$action_code])) {
            $path = $this->_getConfigPath($action_code) . 'settings.php';
            if (file_exists($path)) {
                $settings[$action_code] = include $path;
            } else {
                $settings[$action_code] = array();
            }
        }
        return $settings[$action_code];
    }

}
