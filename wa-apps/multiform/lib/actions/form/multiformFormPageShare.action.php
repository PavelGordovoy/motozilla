<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormPageShareAction extends waViewAction
{

    public function execute()
    {
        // ID формы
        $id = waRequest::get("id", 0, waRequest::TYPE_INT);

        // Настройки формы
        $form_model = new multiformFormModel();
        $form_settings = $form_model->getFormSettings($id);

        if (!multiformHelper::hasFullFormAccess($form_settings)) {
            throw new waRightsException('Access denied');
        }

        $domain = wa('multiform')->getConfig()->getDomain();
        $server_host = waRequest::server('HTTP_HOST');
        $this->view->assign('id', $id);
        $this->view->assign("domain", $domain);
        $this->view->assign('host', strpos($domain, 'www.') !== false && strpos($server_host, 'www.') === false ? 'www.' . $server_host : $server_host);
        $this->view->assign('form', $form_settings);
    }

}
