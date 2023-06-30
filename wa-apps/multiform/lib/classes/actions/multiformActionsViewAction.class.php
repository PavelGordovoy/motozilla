<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionsViewAction extends waViewAction
{

    protected $action = null;
    protected $form_id = null;
    protected $form = null;

    public function __construct($params = null)
    {
        parent::__construct($params);
        $this->action = !empty($params['action']) ? $params['action'] : array();
        $this->form_id = !empty($params['form_id']) ? $params['form_id'] : 0;
        $mf = new multiformFormModel();
        $this->form = $mf->getFullForm($this->form_id);
        if ($this->action) {
            $template = $this->action['path'] . 'templates/' . str_replace('_', '', ucwords($this->action['action_code'], '_')) . 'Action.html';
            $this->view->assign('action', $this->action);
            $this->view->assign('form', $this->form);
            $this->setTemplate($template);
        }
    }

}
