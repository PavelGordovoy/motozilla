<?php
class contactsSendpulsePluginSettingsAction extends waViewAction{
	public function execute(){
    	$plugin = wa('contacts')->getPlugin('sendpulse');
        $namespace = 'sendpulse';

        $params = array();
        $params['id'] = 'sendpulse';
        $params['namespace'] = $namespace;
        $params['title_wrapper'] = '%s';
        $params['description_wrapper'] = '<br><span class="hint">%s</span>';
        $params['control_wrapper'] = '<div class="name">%s</div><div class="value">%s %s</div>';
        $settings_controls = $plugin->getControls($params);
	
		$this->view->assign('plugin_id', "sendpulse");
		$this->view->assign('plugin_info', array("name"=>_wp("SendPulse settings")));		
        $this->view->assign('settings_controls', $settings_controls);
	}
}
