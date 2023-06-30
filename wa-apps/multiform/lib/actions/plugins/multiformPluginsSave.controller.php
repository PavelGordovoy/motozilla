<?php

class multiformPluginsSaveController extends waController
{

    public function execute()
    {
        $plugin_id = waRequest::get('id');
        if (!$plugin_id) {
            throw new waException(_w("Cannot save settings: unknown plugin"));
        }
        $namespace = 'multiform_' . $plugin_id;
        $plugin = waSystem::getInstance()->getPlugin($plugin_id);
        $settings = (array) $this->getRequest()->post($namespace);
        $files = waRequest::file($namespace);
        $settings_defenitions = $plugin->getSettings();
        foreach ($files as $name => $file) {
            if (isset($settings_defenitions[$name])) {
                $settings[$name] = $file;
            }
        }
        try {
            $plugin->saveSettings($settings);
            wa()->getStorage()->set("multiform_plugin_status", "ok");
        } catch (Exception $e) {
            wa()->getStorage()->set("multiform_plugin_status", "fail");
        }
        $referer = waRequest::server('HTTP_REFERER');
        $this->redirect(array("url" => $referer));
    }

}
