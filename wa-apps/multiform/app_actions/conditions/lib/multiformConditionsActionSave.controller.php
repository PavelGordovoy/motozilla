<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformConditionsActionSaveController extends multiformActionsSave
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
             * Поля "Название действия", "Описание действия", "Цвет иконки действия" 
             * Fields "Action name", "Action description" and "Action color"
             */
            $default_values = array('custom_name' => waRequest::post('custom_name'), 'custom_description' => waRequest::post('custom_description'), 'custom_color' => waRequest::post('custom_color'));
            foreach ($default_values as $df_k => $df) {
                $values[] = array("form_id" => $this->form_id, "action_id" => $this->action_id, "param" => $df_k, "ext" => '', "value" => $df);
            }
            
            /* Сохраняем статус действия */
            $maf->updateById($this->action_id, array('status' => waRequest::post('custom_status', 0, waRequest::TYPE_INT)));

            /* Сохраняем условия */
            $conditions = waRequest::post("condition", array());
            $tab = 'action_conditions';

            $condition_model = new multiformConditionModel();
            $params_model = new multiformConditionParamsModel();
            $params = $data = array();
            $c = 0;
            foreach ($conditions as $sort_id => $condition) {
                // Основные данные условия
                $data[$sort_id] = array(
                    'form_id' => $this->form_id,
                    'action' => '',
                    'target' => (int) $condition['target'],
                    'andor' => (int) $condition['andor'],
                    'sort' => $c,
                    'tab' => $tab,
                    'extra_tab' => $this->action_id
                );
                $c++;
            }
            // Очищаем условия, чтобы занести их занаво
            $condition_model->delete($this->form_id, $tab, $this->action_id);

            foreach ($data as $s_id => $d) {
                $condition_id = $condition_model->insert($d);
                if ($condition_id) {
                    // Обрабатываем параметры условия
                    foreach ($conditions[$s_id]['params'] as $param) {
                        $params[] = array(
                            'condition_id' => (int) $condition_id,
                            'source' => (int) $param['source'],
                            'source_ext' => !empty($param['source_ext']) ? (int) $param['source_ext'] : 0,
                            'operator' => $param['operator'],
                            'value' => (mb_strlen($param['value'], "UTF-8") > 255 ? mb_substr($param['value'], 0, 255, "UTF-8") : $param['value']),
                        );
                    }
                }
            }
            if ($params) {
                $params_model->multipleInsert($params);
            }


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
                    $condition_model->delete($this->form_id, $tab, $this->action_id);
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
