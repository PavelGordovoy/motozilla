<?php

class mylangContactsSaveController extends waJsonController
{
    public function execute()
    {
        $fields = waRequest::post('mylang', null, waRequest::TYPE_ARRAY);
        $all_fields = waContactFields::getAll('all', true);
        foreach ($fields as $key => $field) {
            if (array_key_exists('fields', $field)) {
                foreach ($field['fields'] as $subkey=>$subfield) {
                    $subcurrent = $all_fields[$key]->getFields()[$subkey]->getParameter('localized_names');
                    $subcurrent = array_filter(array_merge($subcurrent, $subfield));
                    $all_fields[$key]->getFields()[$subkey]->setParameter('localized_names', $subcurrent);
                }
            }
            $current = $all_fields[$key]->getParameter('localized_names');
            $all_fields[$key]->setParameter('localized_names', array_merge($current, $field));
            waContactFields::updateField($all_fields[$key]);
        }
        $this->response = ['mylang' => $fields];
    }
}
