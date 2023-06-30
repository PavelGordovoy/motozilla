<?php 
class contactsSendpulsePluginBackendImport2Controller extends waJsonController{
	private $plugin_id = "sendpulse";

	public function execute(){
		$this->plugin = waSystem::getInstance()->getPlugin($this->plugin_id, true);
		$category_id = waRequest::post("category_id");
		$offset = waRequest::post("offset");
		
		$book_model = new waContactCategoryModel();
		$contact_model = new waContactModel();
		
		$contact_emails_model = new waContactEmailsModel();
		$sendpulse_category_model = new contactsSendpulseCategoryModel();
		$sendpulse_contact_model = new contactsSendpulseContactModel();
		$api =  $this->plugin->getAPI();
		
		$sbook = $api->getBookInfo($category_id);
		if(@$sbook->is_error or @$sbook->error_code){
			$this->setError(_wp("Book not found"));
			//exit(json_encode(array("status"=>"error", "message"=>"Book not found")));		
		}

		$sbook = isset($sbook[0])?$sbook[0]:false;
		$book_data = false;
		if($sbook and !isset($sbook->error_code) and !isset($sbook->is_error)){
			$book_data = $sendpulse_category_model->getByField("sendpulse_id", $sbook->id); //таблица связей локальной и сендпульса
			if($book_data and $this->plugin->checkLocalCategory($book_data['ss_id'])){ //если книга уже есть - обновляем название
				$book_model->updateByID($book_data['ss_id'], array("name"=>$sbook->name));
			}else{
				$data = array("name"=>$sbook->name, "app_id"=>"contacts", "icon"=>"sendpulse", "cnt"=>0);
				$id = $book_model->insert($data);
				
				if($id){
					$sendpulse_category_model->insert(array("sendpulse_id"=>$sbook->id, "sendpulse_creationdate"=>$sbook->creationdate, 
											"sendpulse_status"=>$sbook->status, "sendpulse_status_explain"=>$sbook->status_explain, 
											"ss_id"=>$id));
				}

				$book_data = $sendpulse_category_model->getByField("sendpulse_id", $sbook->id);
			}		
		
		}else{
			$this->setError(_wp("Book not found"));
			//exit(json_encode(array("status"=>"error", "message"=>_wp("Book not found"))));
		}
		$temp = false;

		if($sbook->all_email_qty>0 and $book_data){		
			$semails = $api->getEmailsFromBook($book_data['sendpulse_id'], $offset, 100);
			foreach($semails as $semail){
				$email = $contact_emails_model->getByField("email", $semail->email);
				if(!$email){ 
					$contact = new waContact();
					$contact->add('email', $semail->email);
					if($semail->phone ){
						$contact->add("phone", array("value"=>$semail->phone, "ext"=>"work"));
					}
				}else{
					$contact = new waContact($email['contact_id']);
					if($semail->phone and !$this->phoneExist($email['contact_id'], $semail->phone, "work")){
						$contact->add("phone", array("value"=>$semail->phone, "ext"=>"work"));
					}
				}
				$name = "";
				if(isset($semail->variables)){
					$names = array("Имя", "имя", "Name", "name", "ім'я", "Ім'я", "Nome", "nome");
					foreach($semail->variables as $k=>$v){
						if(in_array($k, $names)){
							$contact->set("name", $v);
							break;
						}
					}
				}
				
				$contact->save();						

				$this->plugin->addContactToCategory($contact->getID(), $book_data['ss_id']);
				$contact_model->updateByID($contact->getID(), array("in_sendpulse"=>1));
			}
			$book_model->updateByID($book_data['ss_id'], array("cnt"=>$sbook->all_email_qty));
		}
		$this->response = array("book_id"=>$book_data['sendpulse_id'], 
			"local_book_id"=>$book_data['ss_id'], 
			"count"=>$sbook->all_email_qty,
			"temp"=>$temp
		);
	}
	
	
	private function phoneExist($id=0, $phone="", $ext=""){
		if(!$id or !$phone){
			return false;
		}
		$contact_data_model = new waContactDataModel();
		$data = $contact_data_model->getByField(array("contact_id"=>$id, "value"=>$phone, "ext"=>$ext));
		if($data){
			return true;
		}
		return false;
	}
	
	
	
	
	
}
