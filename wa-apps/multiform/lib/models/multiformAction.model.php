<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionModel extends waModel
{

    protected $table = 'multiform_action';

    public function actionExists($action_id) 
    {
        return $this->getById($action_id);
    }
}
