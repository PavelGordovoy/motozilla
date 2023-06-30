<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginConstructorModel extends waModel
{

    protected $table = 'helpdesk_pro_constructor';

    /**
     * Get contact fields
     *
     * @param string $field_id
     * @param null|int $enabled
     * @param string $type
     * @return array
     */
    public function getFields($field_id = null, $enabled = null, $type = '')
    {
        $fields = array();
        $contact_id = wa()->getUser()->getId();

        $sql = "SELECT * FROM {$this->table} 
                WHERE `contact_id` = '" . (int) $contact_id . "' AND `type` = '" . $this->escape($type) . "' ";
        if ($field_id) {
            $sql .= "AND `field_id` = '" . $this->escape($field_id) . "' ";
        }
        if ($enabled !== null) {
            $sql .= "AND `enable` = '" . (int) $enabled . "' ";
        }
        $sql .= "ORDER BY `sort` ASC";

        foreach ($this->query($sql) as $r) {
            $fields[$r['field_id']] = array('sort' => $r['sort'], 'params' => @unserialize($r['params']), 'enable' => $r['enable'], 'type' => $r['type']);
            if (!is_array($fields[$r['field_id']]['params'])) {
                $fields[$r['field_id']]['params'] = array();
            }
        }
        return $field_id && isset($fields[$field_id]) ? $fields[$field_id] : $fields;
    }

    /**
     * Save contact field information
     *
     * @param int $field_id
     * @param array $fields
     * @param string $type
     */
    public function save($field_id, $fields, $type = '')
    {
        $contact_id = wa()->getUser()->getId();

        $params = @serialize($fields);

        // Проверяем наличие сохраненных данных об этом поле у пользователя
        $contact_field = $this->getFields($field_id, null, $type);
        if ($contact_field) {
            $this->updateByField(array("contact_id" => $contact_id, 'field_id' => $field_id, 'type' => $type), array('params' => $params));
        } else {
            $this->insert(array("contact_id" => $contact_id, 'field_id' => $field_id, 'sort' => -1, 'enable' => 0, 'params' => $params, 'type' => $type));
        }
    }

}
