<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginSettingsSaveController extends waJsonController
{

    public function execute()
    {
        $hps = new helpdeskProPluginSettingsModel();
        $settings_config = helpdeskProPluginHelper::getSettingsConfig();
        $post = waRequest::post();
        foreach ($settings_config as $k => $s) {
            if ($s['control_type'] == waHtmlControl::CHECKBOX && !isset($post[$k])) {
                $post[$k] = 0;
            }
        }
        $hps->saveSettings(array_merge($hps->getSettings(), $post));
        $this->response = 1;
    }

}
