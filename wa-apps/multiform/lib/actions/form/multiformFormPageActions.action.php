<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormPageActionsAction extends waViewAction
{

    public function execute()
    {
        // ID формы
        $id = waRequest::get("id", 0, waRequest::TYPE_INT);

        // Настройки формы
        $form_model = new multiformFormModel();
        $form_settings = $form_model->getFormSettings($id);

        if (!multiformHelper::hasFullFormAccess($form_settings)) {
            throw new waRightsException('Access denied');
        }

        // Список всех действий
        $ma = new multiformActions();
        $actions = $ma->getActions();

        // Список действий для формы
        $maf = new multiformActionFormModel();
        $form_actions = $maf->getFormActions($id, 0, 0);
        
        // Список групп
        $mag = new multiformActionGroupModel();
        $groups = $mag->getByField(array('form_id' => $id), 'id');
        $groups_sort = array();
        if ($groups) {
            foreach ($groups as $k => $g) {
                if ((int)$g['sort'] !== -1) {
                    $groups_sort[$g['sort']] = $k;
                }
            }
        }
        $form_actions_new = array();
        if ($form_actions) {
            foreach ($form_actions as $k => $fa) {
                // Добавляем группы в общую выборку действий
                if (isset($groups_sort[$fa['sort']-1])) {
                    $form_actions_new[] = $groups[$groups_sort[$fa['sort']-1]]; 
                    unset($groups[$groups_sort[$fa['sort']-1]]);
                } 
                $form_actions_new[] = $fa;
            }
        }

        $this->view->assign('form', $form_settings);
        $this->view->assign('lang', substr(wa()->getLocale(), 0, 2));
        $this->view->assign('actions', $actions);
        $this->view->assign('groups', $groups);
        $this->view->assign('form_actions', $form_actions_new);
    }

}
