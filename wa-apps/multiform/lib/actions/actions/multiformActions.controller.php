<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionsController extends waViewController
{

    private $id = 0;

    public function preExecute()
    {
        // ID формы
        $this->id = waRequest::post("form_id", 0, waRequest::TYPE_INT);

        // Настройки формы
        $form_model = new multiformFormModel();
        $form_settings = $form_model->getFormSettings($this->id);

        if (!multiformHelper::hasFullFormAccess($form_settings)) {
            throw new waRightsException('Access denied');
        }
    }

    public function execute()
    {
        $action_id = waRequest::post("action_id");
        $parent_id = waRequest::post("parent_id", 0, waRequest::TYPE_INT);
        $create = waRequest::post('create');
        // Если редактируем действие
        if ($action_id) {
            if (!$create) {
                $maf = new multiformActionFormModel();
                $action = $maf->getFormActions($this->id, $action_id, $parent_id);
                $action_id = $action ? $action['action_code'] : 0;
            } else {
                $action = new multiformAction($action_id);
            }
        } else {
            $action = null;
        }

        // Если действие существует, вызывает его настройки
        if (!empty($action)) {
            // Получаем информцию о действии
            $this->setLayout(new multiformActionsLayout());
            $this->layout->assign('action', $action);
            $this->layout->assign('action_id', $action_id);
            $this->layout->assign('parent_id', $parent_id);
            $params = array("action" => $action, "form_id" => $this->id);
            // Если у действия используется собственный шаблон для вывода настроек
            if (empty($action['settings'])) {
                $camelcase_id = str_replace('_', '', ucwords($action_id, '_'));
                $action_view = 'multiform' . $camelcase_id . 'ActionAction';
                if (class_exists($action_view)) {
                    $this->executeAction(new $action_view($params));
                }
            }
            // Используем шаблоны вывода по умолчанию
            else {
                $this->executeAction(new multiformActionsSettingsAction($params));
            }
        }
    }

}
