<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormPageRecordAction extends waViewAction
{

    public function execute()
    {
        $mf = new multiformFormModel();
        $mr = new multiformRecordModel();

        // ID формы
        $id = waRequest::get("id", 0, waRequest::TYPE_INT);
        $record_id = waRequest::post("record_id", 0, waRequest::TYPE_INT);

        $record = $mr->getRecord($record_id);
        $form = $mf->getFullForm($id);

        if (!multiformHelper::recordAccess($form)) {
            throw new waRightsException('Access denied');
        }

        if ($record) {
            $collection = new waContactsCollection('/id/' . $record['create_contact_id'] . ',' . $record['update_contact_id'] . '/', array('check_rights' => true));
            $contacts = $collection->getContacts('id,name');
            $this->view->assign("contacts", $contacts);

            // Дополнительные параметры, переданные в форму
            $custom_params = $mr->getCustomParams($id);
            $this->view->assign('custom_params', $custom_params);
        }

        $this->view->assign("domain", wa('multiform')->getConfig()->getDomain());
        $this->view->assign("record", $record);
        $this->view->assign("form", $form);
        $this->view->assign('show_hidden', 1);
        $this->view->assign('show_section_details', 1);
    }

}
