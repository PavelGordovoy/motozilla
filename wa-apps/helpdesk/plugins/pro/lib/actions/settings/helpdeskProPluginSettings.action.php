<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginSettingsAction extends helpdeskViewAction
{

    public function execute()
    {
        waHtmlControl::registerControl('GetColorControl', array(__CLASS__, "getColorControl"));
        waHtmlControl::registerControl('GetCustomHTMLControl', array(__CLASS__, "getCustomHTMLControl"));

        $crm_rights = wa()->getUser()->getRights('crm', 'backend');

        $this->view->assign('settings', helpdeskProPluginHelper::getControls());
        $this->view->assign('crmAccess', wa()->appExists('crm') && !empty($crm_rights));
        $this->view->assign('plugin_url', wa('helpdesk')->getPlugin('pro')->getPluginStaticUrl());
    }

    /**
     * Register new control to waHtmlControl. 
     * Color selecting
     * 
     * @param string $name
     * @param array $params
     * @return string
     */
    public static function getColorControl($name, $params = array())
    {
        static $js = null;
        $control_name = helpdeskProPluginHelper::escape($name);

        $control = "<div class=\"color-icon\" title=\"" . _wp('change color') . "\" data-color=\"" . (!empty($params['value']) ? $params['value'] : '') . "\"></div>
                    <input type=\"hidden\" name=\"{$control_name}\" value=\"" . (!empty($params['value']) ? $params['value'] : '') . "\"";

        if (!empty($params['id'])) {
            $control .= "id = \"{$params['id']}\" ";
        }
        $control .= ">";

        if ($js === null) {
            $plugin_url = wa('helpdesk')->getPlugin('pro')->getPluginStaticUrl();
            $js = '<link rel="stylesheet" type="text/css" href="' . $plugin_url . 'js/colorpicker/css/colorpicker.css">';
            $js .= '<script src="' . $plugin_url . 'js/colorpicker/js/colorpicker.js"></script>';
            $control .= $js;
        }

        $control .= <<<HTML
            <script>
                $(function() {
                   $.helpdesk_pro.initColorpicker();     
                });
            </script>
HTML;
        return $control;
    }

    /**
     * Register new control to waHtmlControl. 
     * Get custom HTML
     * 
     * @param string $name
     * @param array $params
     * @return string
     */
    public static function getCustomHTMLControl($name, $params)
    {
        $control = $params['value'];
        return $control;
    }

}
