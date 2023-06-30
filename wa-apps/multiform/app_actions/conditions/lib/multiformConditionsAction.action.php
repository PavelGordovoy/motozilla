<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformConditionsActionAction extends multiformActionsViewAction
{

    public function execute()
    {
        $mfc = new multiformConditionModel();

        $fields = array();
        if (!empty($this->form['fields'])) {
            $fields = $this->form['fields'];
            $has_first_field = 0;
            foreach ($this->form['fields'] as $f) {
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

        // Список действий для формы
        $maf = new multiformActionFormModel();
        $form_actions = $maf->getFormActions($this->form_id);
        // Удаляем текущее действие и родительское действие из общего списка
        if (isset($form_actions[$this->action['id']])) {
            unset($form_actions[$this->action['id']]);
        }
        $parent_id = waRequest::post('parent_id', 0, waRequest::TYPE_INT);
        if ($parent_id && isset($form_actions[$parent_id])) {
            unset($form_actions[$parent_id]);
        }

        // Условия 
        $conditions = $mfc->getConditions($this->form_id, 'action_conditions', $this->action['id']);
        if (!empty($form_actions)) {
            $target_default = reset($form_actions);
            $this->view->assign('target_default', $target_default);
        }

        $this->view->assign('id', $this->form_id);
        $this->view->assign('form_actions', $form_actions);
        $this->view->assign('fields', $fields);
        $this->view->assign('conditions', $conditions);
    }

}
