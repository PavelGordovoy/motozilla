<?php

class multiformConditionsMultiformActionFormDeleteHandler
{

    /**
     * @param int $params['action_id'] 
     * @param multiformAction $params['action'] 
     * @return void
     */
    public function execute(&$params)
    {
        // Если удаляется действие для создания условий, очищаем таблицы multiform_condition 
        if (!empty($params['action']) && $params['action']['action_code'] == 'conditions') {
            $condition_model = new multiformConditionModel();
            $condition_model->delete($params['action']['form_id'], 'action_conditions', $params['action_id']);
        }
    }

}
