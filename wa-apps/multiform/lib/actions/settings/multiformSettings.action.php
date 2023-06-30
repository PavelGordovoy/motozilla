<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformSettingsAction extends waViewAction
{

    public function execute()
    {
        if (!wa()->getUser()->isAdmin('multiform')) {
            throw new waRightsException('Access denied');
        }
        $this->view->assign('settings', (new waAppSettingsModel())->get('multiform'));

        $this->setLayout(new multiformBackendLayout());
    }

}
