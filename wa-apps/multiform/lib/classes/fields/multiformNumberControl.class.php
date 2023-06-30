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
 *
 * STOREFRONT PARAMS:
 * - min
 * - max
 * - step
 */
class multiformNumberControl extends multiformFormBuilder
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
        $params['min'] = (isset($field['validation']['min']) ? $field['validation']['min'] : $params['validation']['min']['value']);
        $params['max'] = (isset($field['validation']['max']) ? $field['validation']['max'] : $params['validation']['max']['value']);
        $params['step'] = (isset($field['validation']['step']) ? $field['validation']['step'] : $params['validation']['step']['value']);
        $params['slider'] = (isset($field['display']['slider']) ? $field['display']['slider'] : $params['display']['slider']['value']);
        $params['slider_attributes'] = (isset($field['display']['attribute']) ? unserialize($field['display']['attribute']) : $params['display']['attribute']['default']);
        $params['integer'] = !empty($field['validation']['integer']) ? 1 : 0;
    }

    public static function execute($name, $params)
    {
        $control = '';

        $field_size_width = multiformHelper::getFieldSizeWidth($params['field']);
        // Если переданы значения записи
        if (isset($params['record_value'])) {
            $params['value'] = (isset($params['record_value']) && $params['record_value'] !== '') ? $params['record_value'] : '';
        }

        if (wa()->getEnv() == 'frontend' || !isset($params['record_value'])) {
            $params['class'][] = $field_size_width;
        }

        // Принимаем параметры с витрины
        self::getStorefrontParams($params, array('min', 'max', 'step'));

        $control_name = multiformHelper::secureString($name);
        if (!empty($params['slider'])) {
            $control .= "<div id=\"{$params['id']}\" style=\"display: inline;\">";
        }
        if ($params['min'] === '') {
            unset($params['min']);
        } else {
            $params['data-min'] = $params['min'];
        }
        if (!$params['max']) {
            unset($params['max']);
        } else {
            $params['data-max'] = $params['max'];
            if ($params['max'] < $params['value']) {
                $params['value'] = $params['max'];
            }
        }
        if (!$params['step']) {
            unset($params['step']);
        } else {
            $params['data-step'] = $params['step'];
        }

        $control .= "<input " . (empty($params['slider']) ? "id=\"{$params['id']}\"" : "") . ($params['integer'] ? " onkeypress=\"$.multiformFrontend.validate.regexp(event, /[0-9]/);\"" : " onkeypress=\"$.multiformFrontend.validate.regexp(event, /[0-9\.\-]/);\"") . " type=\"text\" name=\"{$control_name}\" data-integer=\"{$params['integer']}\" ";
        $custom_params = array('class', 'value', 'readonly', 'disabled');
        if (!empty($params['slider'])) {
            $params['class'][] = "range-slider";
            $params['data-value'] = $params['value'];
            $custom_params[] = 'min';
            $custom_params[] = 'max';
            $custom_params[] = 'step';
            $custom_params[] = 'data-value';
            $custom_params[] = 'data-min';
            $custom_params[] = 'data-max';
            $custom_params[] = 'data-step';
        } else {
            $custom_params[] = 'placeholder';
            $custom_params[] = 'data-value';
            $custom_params[] = 'data-min';
            $custom_params[] = 'data-max';
            $custom_params[] = 'data-step';
        }
        if ($params['value'] == '') {
            unset($params['value']);
        }
        $control .= self::addCustomParams($custom_params, $params);
        $control .= ">";
        if (!empty($params['slider'])) {
            $control .= "</div>";
        }
        if (!empty($params['slider'])) {
            self::addToJs('<script src="' . wa()->getAppStaticUrl('multiform', true) . 'js/rangeslider/ion.rangeSlider.js"></script>', 'number');
            self::addToCss('<link rel="stylesheet" href="' . wa()->getAppStaticUrl('multiform', true) . 'js/rangeslider/ion.rangeSlider.min.css">', 'number');
            $attrs = array();
            if ($params['slider_attributes']) {
                foreach ($params['slider_attributes'] as $slider_attribute) {
                    $slider_attribute['value'] = strtolower($slider_attribute['value']);
                    $attrs[$slider_attribute['name']] = $slider_attribute['value'];
                }
            }
            $js_inline = "MultiformScripts.initRangeSlider($('#{$params['id']} input'), " . json_encode($attrs) . ");";

            self::addToJs($js_inline);
        }
        return $control;
    }

    public function beforeNumberSave(&$data)
    {
        // Только целочисленные
        if (!empty($data['validation']['integer'])) {
            if (isset($data['validation']['min']) && trim($data['validation']['min']) !== '') {
                $data['validation']['min'] = (int) $data['validation']['min'];
            }
            if (isset($data['validation']['max']) && trim($data['validation']['max']) !== '') {
                $data['validation']['max'] = (int) $data['validation']['max'];
            }
            if (isset($data['validation']['step']) && trim($data['validation']['step']) !== '') {
                $data['validation']['step'] = (int) $data['validation']['step'];
                if ($data['validation']['step'] == 0) {
                    $data['validation']['step'] = 1;
                }
            }
        }
    }

    public function validate_required($post_fields, $form_field)
    {
        if (!isset($post_fields[$form_field['id']]) || $post_fields[$form_field['id']] == '') {
            return false;
        }
        return true;
    }

    public function validate_field($field, $value, $form_fields, $post_fields)
    {
        $result = array('errors' => array());

        // Проверяем числовое значение
        $max = !empty($field['validation']['max']) ? (float) $field['validation']['max'] : '';
        $min = !empty($field['validation']['min']) ? (float) $field['validation']['min'] : '';
        $step = !empty($field['validation']['step']) ? (float) $field['validation']['step'] : '';
        $only_int = !empty($field['validation']['integer']) ? 1 : 0;
        $number = $only_int ? (int) $value : (float) $value;
        if ($max !== '' || $min !== '' || $step !== '') {
            if ($min !== '' && $number < $min) {
                $result['errors']['messages']['number_error1_' . $field['id']] = _wd(self::$app, 'Value must be more than') . ' ' . $min . ' ' . _wd(self::$app, 'for the field') . ' ' . multiformHelper::secureString($field['title']);
            }
            if ($max !== '' && $number > $max) {
                $result['errors']['messages']['number_error2_' . $field['id']] = _wd(self::$app, 'Value must be less than') . ' ' . $max . ' ' . _wd(self::$app, 'for the field') . ' ' . multiformHelper::secureString($field['title']);
            }
            if ($step !== '' && $step !== 0 && $this->fmod($number,$step) !== 0.0) {
                $result['errors']['messages']['number_error3_' . $field['id']] = _wd(self::$app, 'Value must be a multiple of') . ' ' . $step . ' ' . _wd(self::$app, 'for the field') . ' ' . multiformHelper::secureString($field['title']) . ". " . _wd(self::$app, "For example,") . ' ' . $step . ", " . ($step * 2) . ", " . ($step * 3);
            }
        }

        if (empty($result['errors']) && !empty($field['validation']['unique'])) {
            // Проверка уникальности
            $mrv = new multiformRecordValuesModel();
            if ($mrv->countByField(array("field_id" => $field['id'], "value" => $value))) {
                $result['errors']['messages']['number_unique_' . $field['id']] = sprintf(_wd(self::$app, "Value of the field \"%s\" is already exist. Please, specify unique value"), multiformHelper::secureString($field['title']));
            }
        }
        return $result;
    }

    private function fmod($x, $y) {
        $i = round($x / $y);
        return $x - $i * $y;
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetNumberFieldControl',
            'title' => _w('New number field'),
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
                    'class' => 'f-update-text data-default f-only-number',
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
                'info3' => array(
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
                'info' => array(
                    'value' => _w('Range'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'slider' => array(
                    'label' => _w('Range slider'),
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w("Use slider for the field"),
                    'control_type' => waHtmlControl::CHECKBOX,
                    'childs' => [
                        'onopen_value' => 1,
                        'fields' => [
                            'attribute' => array(
                                'button_name' => _w('Add parameter'),
                                'custom_name' => _w('Slider parameters. More info in <a target="_blank" href="http://ionden.com/a/plugins/ion.rangeSlider/api.html">docs</a>'),
                                'default' => array(
                                    array('name' => 'type', 'value' => 'single'),
                                    array('name' => 'grid', 'value' => 'true'),
                                    array('name' => 'grid_num', 'value' => 4),

                                ),
                                'control_type' => 'GetAttributeSettingsControl',
                            ),
                        ]
                    ]
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
                ),
                'integer' => array(
                    'label' => _w('Only integers'),
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w("Only integers would be valid"),
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'info' => array(
                    'value' => _w('Range'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'min' => array(
                    'title' => _w('Min'),
                    'value' => '',
                    'class' => 'f-only-number small width100px',
                    'description' => _w("Minimum value"),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'max' => array(
                    'title' => _w('Max'),
                    'value' => '',
                    'class' => 'f-only-number small width100px',
                    'description' => _w("Maximum value"),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'step' => array(
                    'title' => _w('Step'),
                    'value' => '',
                    'class' => 'f-only-number small width100px',
                    'description' => _w("Step increment/decrement value"),
                    'control_type' => waHtmlControl::INPUT,
                ),
            ),
        );
    }

}
