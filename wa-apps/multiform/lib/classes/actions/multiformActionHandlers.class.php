<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionHandlers
{
    protected $action;
    
    public function __construct($action_id, $form_id)
    {
        if ($action_id && $form_id) {
            $maf = new multiformActionFormModel();
            $this->action = $maf->getFormActions($form_id, $action_id);
        }
    }
}
