<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginDialogAdditionalFieldAction extends waViewAction
{

    public function execute()
    {
        $constructor = new helpdeskProPluginConstructorModel();
        $field_id = waRequest::get("field_id", 0);
        $field = helpdeskRequestFields::getField(str_replace('a_', '', $field_id));
        $contact_fields = $constructor->getFields($field_id, null, 'additional');
        $contact_fields['name'] = $field->getName();
        $this->view->assign('field', $contact_fields);
        $this->view->assign('plugin_url', wa('helpdesk')->getPlugin('pro')->getPluginStaticUrl());

        $this->setTemplate(wa()->getAppPath('plugins/pro/templates/actions/dialog/DialogContactField.html', 'helpdesk'));
    }

}
