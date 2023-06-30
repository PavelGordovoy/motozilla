<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionsSave
{

    protected $form_id;
    protected $result;
    protected $action;
    protected $action_id;
    protected $parent_id = 0;
    protected $is_create = 0;
    
    public function run($params = null)
    {
        $this->preExecute();
        $this->execute();
    }
    
    protected function preExecute()
    {
        
    }

    public function __construct()
    {
        $this->result = array("errors" => array("messages" => array()), "status" => 'ok', "data" => array());
        // ID формы
        $this->form_id = waRequest::post("form_id", 0, waRequest::TYPE_INT);
        $this->action_id = waRequest::post("action_id", 0, waRequest::TYPE_INT);
        $this->parent_id = waRequest::post("parent_id", 0, waRequest::TYPE_INT);

        // Настройки формы
        $form_model = new multiformFormModel();
        $form_settings = $form_model->getFormSettings($this->form_id);
        // Проверка доступа
        if (!multiformHelper::hasFullFormAccess($form_settings)) {
            throw new waRightsException('Access denied');
        }

        $this->preExecute();
    }

    public function postExecute()
    {
        // Устанавливаем ключ, чтобы JS скрипт понял, что загрузка файлов закончена
        $this->result['postExecute'] = 1;

        $this->generateResponse();
    }

    /**
     * If we have XMLHttpRequest, then generate json data, else redirect to referer page
     * 
     * @param array[mixed] $result
     */
    protected function generateResponse()
    {
        $this->result['status'] = $this->hasErrors() ? 'fail' : 'ok';
        // Если был сделан ajax-запрос, то возвращаем json-ответ
        if (waRequest::isXMLHttpRequest()) {
            header("Content-type: application/json; charset=UTF-8");
            header("Cache-Control: must-revalidate");
            header("Pragma: no-cache");
            header("Expires: -1");
            exit(json_encode($this->result));
        }
    }

    /**
     * Add error message
     * 
     * @param string $message
     * @param int $field_id
     */
    protected function addError($message, $field_id = 0)
    {
        if (is_array($message)) {
            $this->result['errors']['messages'][key($message)] = current($message);
        } else {
            $this->result['errors']['messages'][] = $message;
        }
        if ($field_id) {
            $this->result['errors']['fields'][$field_id] = $field_id;
        }
    }

    /**
     * Check, if we have any errors
     * 
     * @return bool
     */
    protected function hasErrors()
    {
        return !empty($this->result['errors']['messages']);
    }

    /**
     * Create action, if ID is not passed
     */
    protected function getActionId()
    {
        $ma = new multiformActions();
        $maf = new multiformActionFormModel();
        // Если не передан ID действия, то необходимо создать новое действие и присвоить его форме
        if (!$this->action_id) {
            $action_code = waRequest::post('action_code');
            if ($ma->actionExists($action_code)) {
                $this->action_id = $maf->createAction($action_code, $this->form_id, $this->parent_id);
                $this->is_create = 1;
            } else {
                $this->addError(_w('Action does not exist'));
                $this->generateResponse();
            }
        }
    }

}
