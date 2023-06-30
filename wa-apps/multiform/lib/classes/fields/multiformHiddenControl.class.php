<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

/**
 * Class is responsible for field functionality.
 * 
 * It has 3 required methods:
 * 
 * - preExecute(&$params, $field) Prepare field data, set default values, add params to field. 
 * - execute($name, $params) Output field HTML. Return string.
 * - fieldOptions() Returns field params array
 * 
 * Also this class can prepare setting fields. Use method:
 * - prepare%Setting_id%SettingsParams(&$params, $field, $tab_id, $property_id)
 * 
 * To output setting fields use control_type as method name. E.g,
 * - get%Setting_id%SettingsControl($name, $params) Return string.
 * 
 * To process field data before save use:
 * - before%Field_id%Save(&$data) 
 * 
 * To process field settings before save use:
 * - before%Setting_id%SettingsSave($tab_id, $f_id, $field, &$data, $post_data)
 */
class multiformHiddenControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['value'] = "";
        if (isset($field['properties']['default']) && wa()->getEnv() !== 'backend') {
            $type = $field['properties']['default'];
            switch ($type) {
                case 'current_link':
                     $url = wa()->getConfig()->getCurrentUrl();
                    if (($i = strpos($url, '?')) !== false) {
                        $url = substr($url, 0, $i);
                    }
                    $params['value'] = wa()->getConfig()->getHostUrl().$url;
                    break;
                case 'referer':
                    $params['value'] = waRequest::server("HTTP_REFERER");
                    break;
                case 'text':
                default:
                    $params['value'] = isset($field['properties']['default_text']) ? $field['properties']['default_text'] : "";
                    break;
            }
        }
        $params['attribute'] = (!empty($field['properties']['attribute']) ? $field['properties']['attribute'] : "");
    }

    public static function execute($name, $params)
    {
        $control = '';
        $control_name = multiformHelper::secureString($name);
        
        // Если переданы значения записи
        if (isset($params['record_value'])) {
            $params['value'] = (isset($params['record_value']) && $params['record_value'] !== '') ? $params['record_value'] : '';
        }
        
        $control .= "<input id=\"{$params['id']}\" type=\"".(isset($params['record_value']) && wa()->getEnv() == 'backend' ? "text" : "hidden")."\" name=\"{$control_name}\" ";
        $control .= self::addCustomParams(array('class', 'value', 'disabled'), $params);
        $control .= ">";
        
        if (wa()->getEnv() == 'backend') {
            $control .= "<i class=\"icon-custom spy\" title=\""._w("Hidden field")."\"></i>";
        }
        return $control;
    }
    
    public function prepareDefaultSettingsParams(&$params, $field, $tab_id, $property_id) {
        $params['default_text'] = isset($field['properties']['default_text']) ? $field['properties']['default_text'] : "";
    }
    
    public function GetDefaultHiddenSettingsControl($name, $params) {
        $control_name = multiformHelper::secureString($name);
        $control = "<select name=\"{$control_name}\" class=\"f-update-select data-hidden-default\">";
        $control .= "<option value=\"text\"".($params['value'] == 'text' || !$params['value'] ? " selected" : "").">"._w("Text")."</option>";
        foreach (array("current_link" => _w("Current page link"), "referer" => _w("Referer")) as $key => $value) {
                $control .= "<option value=\"{$key}\"".($key == $params['value'] ? " selected" : "").">{$value}</option>";
            }
        $control .= "</select>";
        $control .= "<div style=\"margin-top: 5px;".($params['value'] !== 'text'  && $params['value'] ? "display: none" : "")."\"><input type=\"text\"".($params['value'] !== 'text'  && $params['value'] ? " disabled" : "")." class=\"width150px\" name=\"properties[default_text]\" value=\"{$params['default_text']}\"></div>";
        
        return $control;
    }
    
    public function beforeDefaultSettingsSave($tab_id, $f_id, $field, &$data, $post_data) {
        $data[$tab_id][$f_id] = $post_data[$tab_id][$f_id];
        $data[$tab_id]['default_text'] = isset($post_data[$tab_id]['default_text']) ? $post_data[$tab_id]['default_text'] : '';
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetHiddenFieldControl',
            'title' => _w('New hidden field'),
            'properties' => array(
                'title' => array(
                    'title' => _w('Title'),
                    'value' => '',
                    'class' => 'f-update-text data-title',
                    'basic' => 1,
                    'description' => _w('Visible only in admin area'),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'code' => array(
                    'basic' => 1,
                    'desc' => _w('Valid characters') . ': <b>a-z</b>, <b>A-Z</b>, <b>0-9</b>, <b>-</b>, <b>_</b>',
                    'control_type' => 'GetCodeSettingsControl',
                ),
                'default' => array(
                    'value' => '',
                    'title' => _w('Value'),
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetDefaultHiddenSettingsControl',
                ),
                'class' => array(
                    'title' => _w('CSS class'),
                    'value' => '',
                    'basic' => 1,
                    'control_type' => waHtmlControl::INPUT,
                ),
                'save_data' => array(
                    'label' => _w('Save users data'),
                    'value' => 1,
                    'filter' => 'int',
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'attribute' => array(
                    'control_type' => 'GetAttributeSettingsControl',
                ),
            ),
            'display' => array(
                'private' => array(
                    'label' => _w('Private'),
                    'value' => 0,
                    'basic' => 1,
                    'filter' => 'int',
                    'description' => _w('Private fields are shown to users with special access'),
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
            ),
        );
    }

}
