<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginConstructorAction extends helpdeskViewAction
{

    public function execute()
    {
        if (!wa()->getUser()->getRights('contacts', 'backend')) {
            throw new waRightsException(_w('Access denied'));
        }

        $fields = $this->getFields();
        if ($fields) {
            uasort($fields, array(__CLASS__, 'sortFunction'));
        }
        $this->view->assign('fields', $fields);
        $this->view->assign('plugin_url', wa('helpdesk')->getPlugin('pro')->getPluginStaticUrl());
    }

    /**
     * Get all available contact fields
     * 
     * @return array
     */
    private function getFields()
    {
        wa('contacts');
        $all_fields = waContactFields::getInfo('all', true);
        if (isset($all_fields['company_contact_id'])) {
            unset($all_fields['company_contact_id']);
        }
        $fields = array();
        $constructor = new helpdeskProPluginConstructorModel();
        $contact_fields = $constructor->getFields();

        $k = 0;
        foreach ($all_fields as $id => $data) {
            if (in_array($id, array('name', 'title', 'firstname', 'middlename', 'lastname'))) {
                continue;
            }
            $fields[] = array(
                'id' => $id,
                'name' => $data['name'],
                'params' => isset($contact_fields[$id]) ? $contact_fields[$id]['params'] : array(),
                'enable' => isset($contact_fields[$id]) ? $contact_fields[$id]['enable'] : 0,
                'sort' => isset($contact_fields[$id]) && $contact_fields[$id]['sort'] !== -1 ? $contact_fields[$id]['sort'] : $k,
            );
            $k++;
        }
        return $fields;
    }

    private function sortFunction($a, $b)
    {
        $a_val = (int) $a['sort'];
        $b_val = (int) $b['sort'];

        if ($a_val > $b_val) {
            return 1;
        }
        if ($a_val < $b_val) {
            return -1;
        }
        return 0;
    }

}
