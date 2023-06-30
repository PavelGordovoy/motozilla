<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginDialogDeveloperProductsAction extends waViewAction
{

    public function execute()
    {
        if (!wa()->getUser()->isAdmin()) {
            throw new waRightsException(_w('Access denied'));
        }
        $developer_id = helpdeskProPluginHelper::getSettings('developer_id', 0);
        $products = wao(new helpdeskProPluginHelper())->getProducts();

        $this->view->assign('products', $products);
        $this->view->assign('developer_id', $developer_id);
    }

}
