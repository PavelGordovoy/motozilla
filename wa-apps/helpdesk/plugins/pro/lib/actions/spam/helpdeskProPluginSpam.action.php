<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginSpamAction extends helpdeskViewAction
{

    public function execute()
    {
        if (!wa()->getUser()->isAdmin()) {
            throw new waRightsException(_w('Access denied'));
        }

        $spam_model = new helpdeskProPluginSpamModel();
        $emails = $spam_model->select('email')->order('email ASC')->fetchAll(null, true);

        $this->view->assign('emails', $emails);
        $this->view->assign('plugin_url', wa('helpdesk')->getPlugin('pro')->getPluginStaticUrl());
    }

}
