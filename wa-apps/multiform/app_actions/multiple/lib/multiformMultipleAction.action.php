<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformMultipleActionAction extends multiformActionsViewAction
{

    public function execute()
    {
        // Список действий
        $maf = new multiformActionFormModel();
        $form_actions = $maf->getFormActions($this->form_id, 0, $this->action['id'] == 'multiple' ? -1 : $this->action['id']);
        
        // Список всех действий
        $ma = new multiformActions();
        $actions = $ma->getActions();
        foreach ($actions as $k => $v) {
            // Убираем возможность создания "Составных условий"
            if ($k == 'multiple') {
                unset($actions[$k]);
            }
        }

        $this->view->assign('id', $this->form_id);
        $this->view->assign('actions', $actions);
        $this->view->assign('form_actions', $form_actions);
        
        
    }

}
