<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginDialogContactFieldAction extends waViewAction
{

    public function execute()
    {
        if (!wa()->getUser()->getRights('contacts', 'backend')) {
            throw new waRightsException(_w('Access denied'));
        }

        $constructor = new helpdeskProPluginConstructorModel();
        $field_id = waRequest::get("field_id", 0);
        $field = waContactFields::get($field_id);
        $person_disabled = waContactFields::getInfo('person_disabled', true);
        if ($field instanceof waContactField || isset($person_disabled[$field_id])) {
            $contact_fields = $constructor->getFields($field_id);
            $field = $field instanceof waContactField ? $field->getInfo() : $person_disabled[$field_id];
            $contact_fields['name'] = $field['name'];
            $this->view->assign('field', $contact_fields);
        }
        $this->view->assign('plugin_url', wa('helpdesk')->getPlugin('pro')->getPluginStaticUrl());
    }

}
