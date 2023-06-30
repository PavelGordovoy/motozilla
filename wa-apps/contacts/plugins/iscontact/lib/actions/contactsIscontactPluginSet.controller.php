<?php

class contactsIscontactPluginSetController extends waJsonController {

    public function execute() {
		
		$model = new waModel();
		$contact_id = waRequest::request('contact_id', 0, waRequest::TYPE_INT);
		$is_company = waRequest::request('is_company', 0, waRequest::TYPE_INT);

		$this->response['result'] = $model->query("UPDATE wa_contact SET is_company = ? WHERE id = ?", $is_company, $contact_id)->affectedRows();
		$this->response['contact_id'] = $contact_id;
		$this->response['is_company'] = $is_company;
	}
}