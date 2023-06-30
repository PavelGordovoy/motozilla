<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginConstructorSaveController extends waJsonController
{

    public function execute()
    {
        if (!wa()->getUser()->getRights('contacts', 'backend')) {
            throw new waRightsException(_w('Access denied'));
        }

        $constructor = new helpdeskProPluginConstructorModel();
        $field_id = waRequest::get('field_id');
        $fields = waRequest::post('fields');
        $type = waRequest::post('type', '');

        if (!empty($fields['name']) && empty($fields['name']['show'])) {
            $fields['name']['show'] = 0;
        }

        // Получаем информацию о переданном поле
        if (!$type) {
            $field = waContactFields::get($field_id);
            $person_disabled = waContactFields::getInfo('person_disabled', true);
            // Если оно существует
            if ($field instanceof waContactField || isset($person_disabled[$field_id])) {
                $constructor->save($field_id, $fields);
            }
        } else {
            $constructor->save($field_id, $fields, 'additional');
        }

        $this->response = 1;
    }

}
