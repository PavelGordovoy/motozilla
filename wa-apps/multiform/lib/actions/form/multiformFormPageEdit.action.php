<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormPageEditAction extends waViewAction
{

    public function execute()
    {
        $mf = new multiformFieldModel();
        // ID формы
        $id = waRequest::get("id", 0, waRequest::TYPE_INT);

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);

        if (!multiformHelper::hasFullFormAccess($form)) {
            throw new waRightsException('Access denied');
        }

        // Получаем поля для формы
        $fields = $mf->getDataById($id, 'form');
        $fields = $mf->getTree($fields);

        $this->view->assign('id', $id);
        $this->view->assign('fields', $fields);
        $this->view->assign("field_types", wa('multiform')->getConfig()->getFieldTypes());
    }

}
