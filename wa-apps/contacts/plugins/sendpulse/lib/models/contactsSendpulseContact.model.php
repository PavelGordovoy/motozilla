<?php
class contactsSendpulseContactModel extends waModel{
    protected $table = 'contacts_sendpulse_contact_data';

    public function get($ids = array()){
        return $this->select('*')->order('sort')->fetchAll('id');
    }

	public function getImage($id = 0){
	}
}
