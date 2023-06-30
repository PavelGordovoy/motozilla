<?php 
class contactsSendpulsePluginBackendSaveController extends waJsonController{
	private $plugin_id = "sendpulse";

	public function execute(){
		$this->plugin = waSystem::getInstance()->getPlugin($this->plugin_id, true);
		$this->_namespace = wa()->getApp().'_'.$this->plugin_id;
		$this->plugin->saveSettings(waRequest::post("sendpulse"));
	}
	
	
}
