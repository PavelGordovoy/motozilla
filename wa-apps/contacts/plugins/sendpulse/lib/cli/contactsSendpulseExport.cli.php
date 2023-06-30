<?php 
class contactsSendpulseExportCli extends waCliController{
	private $plugin_id = "sendpulse";
	public function execute(){
		$this->plugin = waSystem::getInstance()->getPlugin($this->plugin_id, true);
		$categories = $this->plugin->getSettings("cron_categories");
		
		if(!$categories){
			exit("no categories");
		}
		$book_model = new waContactCategoryModel();
		$contact_model = new waContactModel();
				
		$contact_emails_model = new waContactEmailsModel();
		$sendpulse_category_model = new contactsSendpulseCategoryModel();
		$sendpulse_contact_model = new contactsSendpulseContactModel();
		$api = $this->plugin->getAPI();
		
		foreach($categories as $category_id){
			$sbooks = json_decode(json_encode($api->listAddressBooks()), true);
			$book = $book_model->getById($category_id);
			
			$all_contacts = $this->plugin->getContactsInCategory($book['id'], 0, 9999999);
			if(!$all_contacts){
				echo "no contacts in {$book['id']}\r\n";
			}else{
			}
		
			$per_page = 100;
			$total_pages = ceil(count($all_contacts)/100);
			for($page=0; $page!=$total_pages; $page++){
				$offset = $page*$per_page;
				$contacts = $this->plugin->getContactsInCategory($book['id'], $offset, 100);
				echo "category: {$book['id']}; offset: $offset\r\n";
				
				$book_data = $sendpulse_category_model->getByField("ss_id", $book['id']);
				if($book_data){
					$book_on_server = (array)$api->getBookInfo($book_data['sendpulse_id']);
				}
		
				if($book_data and !isset($book_on_server['error_code'])){
					$sbook_id = $book_data['sendpulse_id'];
				}else{ //создаем книгу на сервере
					$temp = (array)$api->createAddressBook($book['name']);
					if(isset($temp['error_code']) and $temp['error_code']==203){ 
						foreach($sbooks as $sbook){
							if($sbook['name']==$book['name']){
								$sbook_id = $sbook['id'];
								break;
							}
						}
					}else{ 
						$sbook_id = $temp['id'];
					}

					if($sbook_id){
						$sendpulse_category_model->insert(array("sendpulse_id"=>$sbook_id, 
												"sendpulse_status"=>0, 
												"sendpulse_status_explain"=>"Active", "ss_id"=>$book['id']));
					}		
					$book_data =  $sendpulse_category_model->getByField("ss_id", $book['id']);		
				}
		
				$to_book = array();
				foreach($contacts as $contact){
					$cont = new waContact($contact['contact_id']);
			
			 		$c = array();
			 		$c[_wp("Name")] = $cont->get("firstname"); 
			 		$c[_wp("Full name")] = $cont->get('name');
			 		$fields = $this->plugin->getSettings("fields_for_export_rand");
					foreach($fields as $f){
						$val = $cont->get($f);
						if(!$val or empty($val) or in_array($f, array("name", "email"))){
							continue;
						}
						switch ($f){
							case 'sex':
								$c[_wp($f)] = "Female";
								if($val=='m'){
									$c[_wp($f)] = "Male";
								}
							break;
							case 'phone':
								if(count($val)>1){
									foreach($val as $p){
										if($p and isset($p['value'])){
											if(!isset($c[_wp('Phones')])){
												$c[_wp('Phones')] = "";
											}
											$c[_wp('Phones')] .= "{$p['ext']} : {$p['value']}; ";
										}
									}
									$c['Phone'] = preg_replace("/[^0-9]/","", $val[0]['value']);						
								}else{
									$c['Phone'] = preg_replace("/[^0-9]/","", $val[0]['value']);		
								}
							break;
							default:
								if(is_array($val)){
									if($this->plugin->isAssoc($val)){
										$c[_wp($f)] = $val['value'];
									}else{
										foreach($val as $v){
											$c[_wp($f)." ".$v['ext']] = $v['value'];
										}
									}
								}else{
									$c[_wp($f)] = $val;
								}
							break;
						}
					}

			
					$c = array_filter($c);

					if($c){
						$to_book[] = array(
							"email"=>$contact['email'], 
							"variables"=>$c
						);			
					}else{
						$to_book[] = array(
							"email"=>$contact['email'], 
						);
					}
			
					$contact_model->updateByID($contact['contact_id'], array("in_sendpulse"=>1));


				}
				if(!empty($to_book)){
					$api->addEmails($sbook_id, $to_book);
				}
			}
		}
		
	}
}
