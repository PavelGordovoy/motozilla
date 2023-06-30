<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformMultipleActionSaveController extends multiformActionsSave
{

    public function preExecute()
    {
        /*
         * Если не передан ID действия, то необходимо создать новое действие и присвоить его форме 
         * If we don't have action ID, create new action and save ID to $this->action_id
         */
        $this->getActionId();
    }

    public function execute()
    {
        $maf = new multiformActionFormModel();

        /*
         * Проверяем принадлежность действия к форме 
         * Check, if action belongs to the form
         */
        if ($action = $maf->getFormActions($this->form_id, $this->action_id, $this->parent_id)) {
            $values = array();

            /*
             * Поля "Название действия", "Описание действия", "Цвет иконки действия", "Автовыполнение"
             * Fields "Action name", "Action description", "Action color" and "Auto execute"
             */
            $default_values = array('custom_name' => waRequest::post('custom_name'), 'custom_description' => waRequest::post('custom_description'), 'custom_color' => waRequest::post('custom_color'), 'auto_execute' => waRequest::post('auto_execute'));

            $data = waRequest::post('data', array());
            $data = array_merge($data, $default_values);

            foreach ($data as $k => $v) {
                if (!empty($v)) {
                    if (is_array($v)) {
                        foreach ($v as $key => $value) {
                            $values[] = array("form_id" => $this->form_id, "action_id" => $this->action_id, "param" => $k, "ext" => $key, "value" => $value);
                        }
                    } else {
                        $values[] = array("form_id" => $this->form_id, "action_id" => $this->action_id, "param" => $k, "ext" => '', "value" => $v);
                    }
                }
            }

            /*
             * Сохранение полей "Название действия", "Описание действия", "Цвет иконки действия", "Автовыполнение" 
             * Saving fields "Action name", "Action description", "Action color" and "Auto execute"
             */
            foreach ($default_values as $df_k => $df) {
                $values[] = array("form_id" => $this->form_id, "action_id" => $this->action_id, "param" => $df_k, "ext" => '', "value" => $df);
            }
            
            /* 
             * Сохраняем статус действия 
             * Save action status
             */
            $maf->updateById($this->action_id, array('status' => waRequest::post('custom_status', 0, waRequest::TYPE_INT)));

            /*
             * Если имеются ошибки, то прерываем обработку
             * If we have errors, abort the process
             */
            if ($this->hasErrors()) {
                /*
                 * Если только что создали действие, то удаляем его
                 * If action has been created just now, then delete it 
                 */
                if ($this->is_create) {
                    $maf->delete($this->form_id, $this->action_id);
                }
                $this->generateResponse();
            }

            /*
             * Сохраняем основные данные в БД
             * Save data to DB
             */
            $this->save($values);

            /*
             * Отправляем данные в javascript обработчик
             * Pass data to javascript
             */
            $this->result['data']['id'] = $this->action_id;
            $this->result['data']['form_id'] = $this->form_id;

            /*
             * Если действие только что создано, перезагружаем страницу 
             * Reload page, if action has been saved just now 
             */
            if ($this->is_create) {
                $this->result['data']['reload'] = 1;
            }
        } else {
            $this->addError(_w('Action does not exist'));
        }
        $this->generateResponse();
    }

    /**
     * Insert or update data
     *
     * @param array $data
     * @return int 
     * 
     */
    private function save($data)
    {
        $mafs = new multiformActionFormSettingsModel();
        $mafs->deleteByField(array("form_id" => $this->form_id, "action_id" => $this->action_id));
        if ($data) {
            $mafs->multipleInsert($data);
        }
        return true;
    }

}
