<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormPageConditionsAction extends waViewAction
{

    public function execute()
    {
        $mf = new multiformFieldModel();
        $mfp = new multiformFormParamsModel();
        $mfc = new multiformConditionModel();
        // ID формы
        $id = waRequest::get("id", 0, waRequest::TYPE_INT);

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if (!multiformHelper::hasFullFormAccess($form)) {
            throw new waRightsException('Access denied');
        }

        // Получаем поля для формы
        $fields = $mf->getDataById($id, 'form');

        if ($fields) {
            $has_first_field = 0;
            foreach ($fields as $f) {
                if ($f['type'] !== 'html' && $f['type'] !== 'file' && !$f['hidden']) {
                    $this->view->assign('first_field', $f);
                    $has_first_field = 1;
                    break;
                }
            }
            // Если среди типов полей только HTML и файлы, то делаем невозможным создать условия
            if (!$has_first_field) {
                $fields = array();
            }
        }
        // Активность условий
        $enable = $mfp->select("value")->where("form_id = i:form_id AND param = 'condition_status'", array("form_id" => $id))->fetchField();

        // Условия 
        $conditions = $mfc->getConditions($id);

        $this->view->assign('id', $id);
        $this->view->assign('enable', $enable);
        $this->view->assign('fields', $fields);
        $this->view->assign('conditions', $conditions);
        $this->view->assign('lang', substr(wa()->getLocale(), 0, 2));
    }

}
