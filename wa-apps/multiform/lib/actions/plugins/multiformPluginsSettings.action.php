<?php

class multiformPluginsSettingsAction extends waViewAction
{

    public function execute()
    {
        $plugin_id = waRequest::get('slug', null);
        $plugins_count = 0;
        if ($plugin_id) {
            $plugins = $this->getConfig()->getPlugins();
            $plugins_count = count($plugins);
            if (isset($plugins[$plugin_id])) {
                $plugin = waSystem::getInstance()->getPlugin($plugin_id);
                $namespace = 'multiform_' . $plugin_id;
                waSystem::pushActivePlugin($plugin_id, wa()->getApp());
                $params = array();
                $params['id'] = $plugin_id;
                $params['namespace'] = $namespace;
                $params['title_wrapper'] = '%s';
                $params['description_wrapper'] = '<br><span class="hint">%s</span>';
                $params['control_wrapper'] = '<div class="name">%s</div><div class="value">%s %s</div>';

                $settings_controls = $plugin->getControls($params);
                $this->getResponse()->setTitle(sprintf(_w("Plugin settings %s"), $plugin->getName()));

                $save_status = wa()->getStorage()->get("multiform_plugin_status");
                wa()->getStorage()->remove("multiform_plugin_status");

                $this->view->assign('plugin_info', $plugins[$plugin_id]);
                $this->view->assign('multiform_plugin_status', $save_status);

                $this->view->assign('plugin_id', $plugin_id);
                $this->view->assign('settings_controls', $settings_controls);
                waSystem::popActivePlugin();
            }
        }
        $this->view->assign('plugins_count', $plugins_count);
    }

}
