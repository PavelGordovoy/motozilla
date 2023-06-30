<?php
class contactsSendpulseCategoryModel extends waModel{
    protected $table = 'contacts_sendpulse_category_data';

    public function get($ids = array()){
        return $this->select('*')->order('sort')->fetchAll('id');
    }

	public function getImage($id = 0){
	}
}
