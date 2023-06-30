<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginDialogDomainCheckAction extends waViewAction
{

    public function execute()
    {
        $this->view->assign('developer_id', helpdeskProPluginHelper::getSettings('developer_id', 0));
        $this->view->assign('products', wao(new helpdeskProPluginHelper())->getProducts());
    }

}
