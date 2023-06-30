<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginDialogDeveloperSettingsAction extends waViewAction
{

    public function execute()
    {
        if (!wa()->getUser()->isAdmin()) {
            throw new waRightsException(_w('Access denied'));
        }

        $this->view->assign('developer_id', helpdeskProPluginHelper::getSettings('developer_id', 0));
        $this->view->assign('settings', helpdeskProPluginHelper::getControls());
    }

}
