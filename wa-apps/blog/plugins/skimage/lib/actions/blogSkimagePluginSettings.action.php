<?php

/**
 * Класс экшен для управления настройками плагина
 */
class blogSkimagePluginSettingsAction extends waViewAction{

    /**
     * Запрос данных для страницы настроек
     */
    public function execute(){

        $plugin_id = "skimage";

        $vars = array();

        $plugin = waSystem::getInstance()->getPlugin($plugin_id, true);
        $namespace = wa()->getApp() . '_' . $plugin_id;

        $params = array();
        $params['id'] = $plugin_id;
        $params['namespace'] = $namespace;
        $params['title_wrapper'] = '%s';
        $params['description_wrapper'] = '<br><span class="hint">%s</span>';
        $params['control_wrapper'] = '<div class="name">%s</div><div class="value">%s %s</div>';

        $settings_controls = $plugin->getControls($params);
        $this->getResponse()->setTitle(_w(sprintf('Plugin %s settings', $plugin->getName())));

        $vars['plugin_info'] = array(
            'name' => $plugin->getName()
        );
        $vars['plugin_id'] = $plugin_id;
        $vars['settings_controls'] = $settings_controls;
        $vars['settings'] = $plugin->getSettings();

        $groupsModel = new blogSkimageGroupsModel();
        $vars["groups"] = $groupsModel->getAll();

        $dataMax = $groupsModel->query("SELECT max(id) as max_id FROM blog_skimage_groups")->fetchAssoc();
        $vars["blog_skimage_config"] = array(
            "pathToApp" => wa()->getAppUrl("blog"),
            "max_id" => $dataMax["max_id"],
        );

        $this->view->assign($vars);

        $this->view->assign('blog_plugin_url', wa("blog")->getPlugin($plugin_id)->getPluginStaticUrl());

    }

}
