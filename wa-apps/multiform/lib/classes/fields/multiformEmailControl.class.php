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
class multiformEmailControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['value'] = (isset($field['properties']['default']) ? $field['properties']['default'] : (isset($params['value']) ? $params['value'] : ""));
        $required = (!empty($field['required']) ? 1 : $params['validation']['required']['value']);
        $title_pos = (isset($field['title_pos']) ? $field['title_pos'] : $params['display']['title_pos']['value']);
        $params['placeholder'] = (isset($field['properties']['placeholder']) ? $field['properties']['placeholder'] : "");
        if ($params['placeholder'] && $title_pos == 'hide' && $required) {
            $params['placeholder'] .= " *";
        }
        $params['attribute'] = (!empty($field['properties']['attribute']) ? $field['properties']['attribute'] : "");
        $validator = new waEmailValidator();
        $params['mask'] = $validator->getPattern();
        $params['mask'] = substr($params['mask'], 1, strrpos($params['mask'], '/') - 1);
        $params['mask_error'] = _wd(self::$app, "Incorrect email");
    }

    public static function execute($name, $params)
    {
        $control = '';
        $control_name = multiformHelper::secureString($name);

        // Если переданы значения записи
        if (isset($params['record_value'])) {
            $params['value'] = !empty($params['record_value']) ? $params['record_value'] : '';
        }
        if (wa()->getEnv() == 'frontend' || !isset($params['record_value'])) {
            $params['class'][] = multiformHelper::getFieldSizeWidth($params['field']);
        }

        $control .= "<input id=\"{$params['id']}\" type=\"email\" name=\"{$control_name}\" ";
        $control .= "data-field-mask = \"{$params['mask']}\" data-field-mask-casein=\"i\" data-field-mask-error=\"{$params['mask_error']}\" ";
        $params['class'][] = "f-mask";
        $control .= self::addCustomParams(array('class', 'value', 'placeholder', 'readonly', 'disabled'), $params);
        $control .= ">";
        return $control;
    }

    public function validate_mask($field, $value)
    {
        // Проверка email-адреса по маске
        $validator_email = new waEmailValidator();
        $regexp = $validator_email->getPattern();
        if (!preg_match($regexp, $value)) {
            return array(
                'error_message' => _wd(self::$app, "Incorrect email") . ": " . _wd(self::$app, 'for the field') . ' ' . multiformHelper::secureString($field['title']),
            );
        }
        return true;
    }

    public function validate_field($field, $value, $form_fields, $post_fields)
    {
        $result = array('errors' => array());
        // Проверка уникальности
        if (!empty($field['validation']['unique'])) {
            $mrv = new multiformRecordValuesModel();
            $sql = "SELECT COUNT(*) FROM {$mrv->getTableName()} WHERE field_id = i:field_id AND value = s:value" . (wa()->getEnv() == 'backend' ? " AND record_id <> " . waRequest::post("record_id", 0, waRequest::TYPE_INT) : "");
            if ($mrv->query($sql, array("field_id" => $field['id'], "value" => $value))->fetchField()) {
                $result['errors']['messages']['email_unique_' . $field['id']] = sprintf(_wd(self::$app, "Value of the field \"%s\" is already exist. Please, specify unique value"), multiformHelper::secureString($field['title']));
            }
        }
        return $result;
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetEmailFieldControl',
            'title' => _w('New email'),
            'properties' => array(
                'title' => array(
                    'title' => _w('Title'),
                    'value' => '',
                    'class' => 'f-update-text data-title',
                    'basic' => 1,
                    'control_type' => waHtmlControl::INPUT,
                ),
                'code' => array(
                    'basic' => 1,
                    'desc' => _w('Valid characters') . ': <b>a-z</b>, <b>A-Z</b>, <b>0-9</b>, <b>-</b>, <b>_</b>',
                    'control_type' => 'GetCodeSettingsControl',
                ),
                'placeholder' => array(
                    'title' => _w('Placeholder'),
                    'value' => '',
                    'class' => 'f-update-text data-placeholder',
                    'control_type' => waHtmlControl::INPUT,
                ),
                'default' => array(
                    'title' => _w('Predefined value'),
                    'value' => '',
                    'class' => 'f-update-text data-default',
                    'control_type' => waHtmlControl::INPUT,
                ),
                'description' => array(
                    'title' => _w('Description'),
                    'value' => '',
                    'basic' => 1,
                    'class' => 'f-update-text data-description',
                    'description' => _w("You can use HTML"),
                    'control_type' => waHtmlControl::TEXTAREA,
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
                'info' => array(
                    'value' => _w('Basic'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'title_pos' => array(
                    'title' => _w('Title position'),
                    'value' => 'left',
                    'basic' => 1,
                    'title_type' => 'inline',
                    'class' => 'f-update-select data-pos',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => array(
                        array('title' => _w('Left'), 'value' => 'left'),
                        array('title' => _w('Top'), 'value' => 'top'),
                        array('title' => _w('Hide'), 'value' => 'hide'),
                    ),
                ),
                'prefix' => array(
                    'title' => _w('Prefix'),
                    'value' => '',
                    'class' => 'f-update-text data-prefix',
                    'description' => _w("You can use HTML"),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'suffix' => array(
                    'title' => _w('Suffix'),
                    'value' => '',
                    'class' => 'f-update-text data-suffix',
                    'description' => _w("You can use HTML"),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'info2' => array(
                    'value' => _w('Field properties'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'disabled' => array(
                    'label' => _w('Read only'),
                    'class' => 'f-update-checkbox data-disabled',
                    'value' => 0,
                    'basic' => 1,
                    'filter' => 'int',
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'private' => array(
                    'label' => _w('Private'),
                    'value' => 0,
                    'basic' => 1,
                    'filter' => 'int',
                    'description' => _w('Private fields are shown to users with special access'),
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'hidden' => array(
                    'label' => _w('Hidden'),
                    'value' => 0,
                    'basic' => 1,
                    'filter' => 'int',
                    'description' => _w('Hidden fields not shown to users. They can be edited only in admin page. But you can use the values of hidden fields in frontend'),
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
            ),
            'sizes' => array(
                'block_width' => array(
                    'title' => _w('Block width'),
                    'value' => 'colm12',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => multiformHelper::getWidthOptionValues(),
                ),
                'title_width' => array(
                    'title' => _w('Title width'),
                    'value' => 'colm4',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => multiformHelper::getWidthOptionValues(),
                ),
                'field_width' => array(
                    'title' => _w('Field width'),
                    'value' => 'colm8',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => multiformHelper::getWidthOptionValues(),
                ),
                'width' => array(
                    'title' => _w('Field size'),
                    'value' => 'colm6',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => multiformHelper::getWidthOptionValues(),
                ),
            ),
            'validation' => array(
                'required' => array(
                    'label' => _w('Required'),
                    'value' => 0,
                    'basic' => 1,
                    'filter' => 'int',
                    'class' => 'f-update-checkbox data-required',
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'unique' => array(
                    'label' => _w('Unique (no duplicates)'),
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w("The information entered into this field is unique and has not been submitted previously"),
                    'control_type' => waHtmlControl::CHECKBOX,
                )
            ),
        );
    }

}
