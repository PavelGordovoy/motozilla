<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformCustomSettingsExampleActionAction extends multiformActionsViewAction
{

    public function execute()
    {
        $this->view->assign('test', 'test');
    }

}
