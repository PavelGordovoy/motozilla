<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginSettingsModel extends waModel
{

    protected $table = 'helpdesk_pro_settings';

    /**
     * Get settings for contact
     * 
     * @param null|int $contact_id
     * @return array
     */
    public function getSettings($contact_id = null)
    {
        if ($contact_id === null) {
            $contact_id = wa()->getUser()->getId();
        }
        $settings_config = helpdeskProPluginHelper::getSettingsConfig();
        $settings = $this->getByField(array('contact_id' => $contact_id), 'name');

        // Для админа добавляем настройки
        if (wa()->getUser()->isAdmin()) {
            $settings = array_merge($settings, $this->getByField(array('contact_id' => 0), 'name'));
        }

        foreach ($settings as $k => $sc) {
            // Удаляем несуществующие настройки
            if (!$settings_config[$k]) {
                unset($settings[$k]);
            }
            // Обрабатываем существующие
            $settings[$k] = is_array($sc) ? (isset($sc['value']) ? $sc['value'] : null) : $sc;
        }

        // Настройки по умолчанию
        if ($settings_config) {
            foreach ($settings_config as $key => $row) {
                if (!isset($settings[$key])) {
                    $settings[$key] = is_array($row) ? (isset($row['value']) ? $row['value'] : null) : $row;
                }
            }
        }

        return $settings;
    }

    /**
     * Save plugin settings
     * 
     * @param mixed [string] $settings 
     * @return void|array
     */
    public function saveSettings($settings = array())
    {
        $contact_id = wa()->getUser()->getId();
        $is_admin = wa()->getUser()->isAdmin();

        $settings_config = helpdeskProPluginHelper::getSettingsConfig();

        $this->deleteByField(array('contact_id' => $contact_id));
        if ($is_admin) {
            $this->deleteByField(array('contact_id' => 0));
        }

        foreach ($settings_config as $name => $row) {
            if (!isset($settings[$name])) {
                if ((ifset($row['control_type']) == waHtmlControl::CHECKBOX) && !empty($row['value'])) {
                    $settings[$name] = false;
                } elseif ((ifset($row['control_type']) == waHtmlControl::GROUPBOX) && !empty($row['value'])) {
                    $settings[$name] = array();
                }
            }
        }
        $data = array();
        foreach ($settings as $name => $value) {
            if (!isset($settings_config[$name]) || (in_array($settings_config[$name]['app'], array('admin', 'developer')) && !$is_admin) || (!empty($settings_config[$name]['skip']))) {
                continue;
            }
            $data[] = array('contact_id' => in_array($settings_config[$name]['app'], array('admin', 'developer')) && $is_admin ? 0 : $contact_id, 'name' => $name, 'value' => is_array($value) ? json_encode($value) : $value);
        }
        if ($data) {
            $this->multipleInsertIgnore($data);
        }
        helpdeskProPluginHelper::clearSettings($contact_id);
    }

    /**
     * Multiple INSERT IGNORE
     *
     * @param array $data
     * @return boolean
     * @throws waException
     */
    private function multipleInsertIgnore($data)
    {
        if (!$data) {
            return true;
        }
        $values = $row_values = array();
        $fields = array('contact_id', 'name', 'value');

        foreach ($data as $row) {
            $row_values = array();
            foreach ($row as $field => $value) {
                if (in_array($field, $fields)) {
                    $row_values[$this->escape($field)] = $this->getFieldValue($field, $value);
                }
            }
            $values[] = implode(',', $row_values);
        }
        if ($values) {
            $sql = "INSERT IGNORE INTO " . $this->table . " (" . implode(',', $fields) . ") VALUES (" . implode('), (', $values) . ")";
            return $this->query($sql);
        }
        return true;
    }

}
