<?php 
class contactsSendpulsePluginBackendPreimportAction extends waViewAction{
	private $plugin_id = "sendpulse";

	public function execute(){
		$this->plugin = waSystem::getInstance()->getPlugin($this->plugin_id, true);

		$book_model = new waContactCategoryModel();
		$contact_emails_model = new waContactEmailsModel();
		$sendpulse_category_model = new contactsSendpulseCategoryModel();
		$sendpulse_contact_model = new contactsSendpulseContactModel();
		
		$api =  $this->plugin->getAPI();
		
		$page = 0;
		$per_page = 100;
		$book_array = array();
		while(true){
			if($page==0){
				$offset=0;
			}else{
				$offset = $page*$per_page;
			}

			$temp_array = (array)$api->listAddressBooks(100, $offset);

			if(count($temp_array)>0){
				$book_array = array_merge($temp_array, $book_array);
			}else{
				break;
			}
			if($page>25){
				break;
			}
			$page++;	
		}
		

		$sbooks = json_decode(json_encode($book_array), true);
		
		
        $view = wa()->getView(); 
        $view->assign("categories", $sbooks);		

	}
	
	
}
