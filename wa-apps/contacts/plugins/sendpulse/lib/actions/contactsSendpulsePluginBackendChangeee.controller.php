<?php 
class contactsSendpulsePluginBackendChangeeeController extends waJsonController{
	private $plugin_id = "sendpulse";

	public function execute(){
		//экспортировать контакт или нет?
		if(waRequest::post("id")){
			$model = new waContactModel();
			$model->updateByID(waRequest::post("id"), array(waRequest::post("field")=>waRequest::post("value")));
			if(waRequest::post("field")=='sync_with_sendpulse' and waRequest::post("value")==0){
				$model->updateByID(waRequest::post("id"), array("in_sendpulse"=>0));
				$this->plugin = waSystem::getInstance()->getPlugin($this->plugin_id, true);
				$api = $this->plugin->getAPI();
				$m = new waContactEmailsModel();
				$emails = $m->getByField(array("contact_id"=>waRequest::post("id")),1);
				foreach($emails as $email){
					$sbook = $api->removeEmailFromAllBooks($email['email']);				
				}
			}
		}
	}
}
