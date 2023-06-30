<?php
class contactsSendpulsePlugin extends waPlugin{
	private $plugin_id = "sendpulse";
	private $sendpulse_api_id = "";
	private $sendpulse_api_secret = "";
	private $sendpulse_access_token = "";
	private $net = false;
	
	public function backendMenu(){
		$view = wa()->getView();
		$sids = $this->getSendpulseContacts();
		$settings_exist = false;
		if($this->getApiID() and $this->getApiSecretKey()){
			$settings_exist = true;
		}
		$strings = array();
		$po_strings = array("Completed", "Error", "Cancel");
		foreach($po_strings as $s){
            $strings[$s] = _wp($s);
        }
		
		$content = $view->fetch($this->path.'/templates/Menu.html', 
			array(
				"sids"=>$sids, 
				"settings_exist"=>$settings_exist,
				"strings"=>$strings
			));
		return $content;
	}
	
	public function getSendpulseContacts(){
	
		$model = new waContactModel();
		$sql = "select id from wa_contact where in_sendpulse = 1 and sync_with_sendpulse = 1";
		$data =$model->query($sql)->fetchAll();
		
		$return = array();
		if($data){
			foreach($data as $el){
				$return[$el['id']] = $el['id'];
			}
		}
		return $return;
	}
	
	public function getApiID(){
		return $this->getSettings('sendpulse_api_id');
	}	
	
	public function getApiSecretKey(){
		return $this->getSettings('sendpulse_api_secret');
	}		
	public function getAPI(){
		return new contactsSendpulseApi($this->getApiID(), $this->getApiSecretKey(), "session");
	}

	
	public function backendContactInfo($contact){
		$contact_id = $contact['contact_id'];
		$model = new waContactModel();
		$contact = $model->getByID($contact_id);	
		if($contact){
			//$contact = array_pop($contact);
		}
		if(isset($contact['sync_with_sendpulse']) and $contact['sync_with_sendpulse']){
			return array(
				'after_top' => '<label><input id="sendpulse_sync_contact_checkbox" type="checkbox" checked value="'.$contact_id.'">'._wp("Sync with SendPulse").'</label>',
			);
		}else{
			return array(
				'after_top' => '<label><input id="sendpulse_sync_contact_checkbox" type="checkbox" value="'.$contact_id.'">'._wp("Sync with SendPulse").'</label>',
			);			
		}
	}	

	public function saveSettings($settings = array()){
		new contactsSendpulseApi($settings['sendpulse_api_id'], $settings['sendpulse_api_secret'], "session");
		parent::saveSettings($settings);
	}
	
	
	public function includeScripts(){
		$scripts = "<script src='/wa-apps/contacts/plugins/sendpulse/js/sendpulse.main.js'></script>";
		$scripts .="<link href='/wa-apps/contacts/plugins/sendpulse/css/sendpulse.main.css' rel='stylesheet' type='text/css' >";
		return $scripts;
	}

	public function checkLocalCategory($id=0){
		$book_model = new waContactCategoryModel();
		$book = $book_model->getByID($id);
		if($book){
			return true;// $book['id'];
		}
		$c = new contactsSendpulseCategoryModel();
		$c->deleteByField("ss_id", $id);
		return false;
	}

	public function checkLocalCategoryByName($name=""){
		$book_model = new waContactCategoryModel();
		$book = $book_model->getByField("name", $name);
		if($book){
			return true;// $book['id'];
		}
	}
	
	
	public function addContactToCategory($contact_id=0, $category_id=0){
		if(!$contact_id or !$category_id){
			return false;
		}
		$model = new waContactCategoriesModel();
		$exist = $model->getByField(array("category_id"=>$category_id, "contact_id"=>$contact_id));
		if(!$exist){
			return $model->insert(array("contact_id"=>$contact_id, "category_id"=>$category_id));
		}
		return true;
	}

	public function getContactsInCategory($category=0, $offset=0, $limit=100){
		$model = new waContactCategoriesModel();
		
		$category = (int)$category;
		$offset = (int)$offset;
		$limit = (int)$limit;
		
		$sql = "select wce.* from wa_contact_categories as wcc 
			join wa_contact_emails as wce on (wcc.contact_id = wce.contact_id)
			join wa_contact as wc on (wce.contact_id = wc.id)
			where wcc.category_id = '$category' and email != '' and sync_with_sendpulse= 1 LIMIT $limit OFFSET $offset";
		return $model->query($sql)->fetchAll();
	}
	
	
	public function saveContactSetting($id=0, $field="", $value=""){
		$model = new contactsSendpulseContactModel();
		$current = $model->getByField(array("contact_id"=>$id, "field"=>$field));
		if($current){
			return $model->updateByID($current['id'], array("field_value"=>$value));
		}
		return $model->insert(array("contact_id"=>$id, "field"=>$field, "field_value"=>$value));
	}

	public function getContactSetting($id=0, $field=""){
		$model = new contactsSendpulseContactModel();
		return $model->getByField(array("contact_id"=>$id, "field"=>$field));
	}


    public static function settingsTemplates($description = true){
		$model = new waContactCategoryModel();
		$categories = $model->getAll();    
    	foreach($categories as $cat){
    		$templates[$cat['name']] = array(
		            'value' => $cat['id'],
		            'title' => $cat['name'],
				);
    	}
        return $templates;
    }
    
    public static function allContactSettingsFields($description=true){
		$templates = array(
			"name"=>array("title"=>_wp("Name"), "value"=>"name", "description"=>_wp("required"), "disabled"=>true, "checked"=>true),
			"email"=>array("title"=>_wp("Email"), "value"=>"email", "description"=>_wp("required"), "disabled"=>true, "checked"=>true),
			"jobtitle"=>array("title"=>_wp("Job title"), "value"=>"jobtitle"),
			"company"=>array("title"=>_wp("Company"), "value"=>"company"),
			"sex"=>array("title"=>_wp("Gender"), "value"=>"sex"),
			"phone"=>array("title"=>_wp("Phone"), "value"=>"phone"),
			"im"=>array("title"=>_wp("Im clients"), "value"=>"im"),
			"address"=>array("title"=>_wp("Address"), "value"=>"address"),
			"url"=>array("title"=>_wp("Site"), "value"=>"url"),	
			"birthday"=>array("title"=>_wp("Birthday"), "value"=>"birthday"),
			"locale"=>array("title"=>_wp("Locale"), "value"=>"locale"),
			"timezone"=>array("title"=>_wp("Timezone"), "value"=>"timezone"),
			"socialnetwork"=>array("title"=>_wp("Social networks"), "value"=>"socialnetwork"),
			"about"=>array("title"=>_wp("Description"), "value"=>"about"),						
		);
        return $templates;
    }
    
    

	public function isAssoc(array $arr){
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	public static function settingCustomControl(){
		return "<div style='padding: 2px 0 2px 10px;border-left: 3px solid #ddd;margin-top: 10px;'><code>0 1 * * * /usr/bin/php -q ".wa()->getConfig()->getPath('root').DIRECTORY_SEPARATOR."cli.php contacts sendpulseExport </code></div>";
	}


}
