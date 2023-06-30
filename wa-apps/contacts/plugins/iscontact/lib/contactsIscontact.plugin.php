<?php

class contactsIscontactPlugin extends waPlugin {
	
    public function backendContactInfo($params){
		$contact_id = $params['contact_id'];
		$is_company = 0;
		
		if($contact_id){			
			$contact = new waContact($contact_id);
			$is_company = $contact->get('is_company');
			return array('after_top' => '<script>
											$(function() {
												var lastSelect = $("#plugin_'.$this->id.'"),
													lastValue = lastSelect.val();
												lastSelect.on("change", function(event) {
													if(!confirm("'._wp('Are you sure you want to change the contact type?').'")){
														$(this).prop("selectedIndex",lastValue);
														return false
													}
													$(this).after("<i class=\"icon16 loading\"></i>");
													this.disabled = true;
													$.post("'.wa_backend_url().'contacts/?plugin='.$this->id.'&module=set",{ contact_id:'.$contact_id.', is_company:this.value }, function (r) {
														if("'.wa()->getApp().'" == "team")
															$.team.content.reload();
														else if("'.wa()->getApp().'" == "contacts")
															$.wa.controller.contactAction(['.$contact_id.']);
														else
															window.location.reload();
													}, "json");
												})
											});
										</script>
										
										<select '.($contact->isAdmin() ? 'disabled' : 'id="plugin_'.$this->id.'"').'>
											<option value="0">'._wp('Person').'</option>
											<option value="1" '.($is_company ? 'selected' : null).'>'._wp('Company').'</option>
										</select>'.($contact->isAdmin() ? '<div class="hint">'._wp('The administrator cannot change the contact type!').'</div>' : null));
		}
    }
}
