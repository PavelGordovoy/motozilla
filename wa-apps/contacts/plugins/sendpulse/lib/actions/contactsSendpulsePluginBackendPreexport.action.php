<?php 
class contactsSendpulsePluginBackendPreexportAction extends waViewAction{
	private $plugin_id = "sendpulse";
	
	public function execute(){
		$this->plugin = waSystem::getInstance()->getPlugin($this->plugin_id, true);
		$this->_namespace = wa()->getApp().'_'.$this->plugin_id;
		
		$model = new waContactCategoryModel();
		$categories = $model->getAll();

        $view = wa()->getView(); 
        //$view->assign("title", _wp("Export to SendPulse"));
        //$view->assign("title2", _wp("Categories for export"));

        $view->assign("categories", $categories);
	}
}
