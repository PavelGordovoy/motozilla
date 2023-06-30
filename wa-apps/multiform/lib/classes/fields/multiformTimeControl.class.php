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
class multiformTimeControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['value'] = (isset($field['properties']['default']) ? $field['properties']['default'] : (isset($params['value']) ? $params['value'] : ""));
        $params['time_type'] = isset($field['display']['time_type']) ? $field['display']['time_type'] : $params['display']['time_type']['value'];
        $params['separator'] = isset($field['display']['separator']) ? $field['display']['separator'] : $params['display']['separator']['value'];
        $params['hour_field'] = isset($field['display']['hour_field']) ? $field['display']['hour_field'] : $params['display']['hour_field']['value'];
        $params['minute_field'] = isset($field['display']['minute_field']) ? $field['display']['minute_field'] : $params['display']['minute_field']['value'];
        $params['second_field'] = isset($field['display']['second_field']) ? $field['display']['second_field'] : $params['display']['second_field']['value'];
        $params['time_format'] = isset($field['display']['time_format']) ? $field['display']['time_format'] : $params['display']['time_format']['value'];
    }

    public static function execute($name, $params)
    {
        $control = '';
        $control_name = multiformHelper::secureString($name);
        $control .= "<div id=\"{$params['id']}\" style=\"display: inline-block; vertical-align: middle;\"" . ($params['time_type'] == 'fields' ? " data-separator = '" . multiformHelper::secureString(strip_tags($params['separator'])) . "'" : "") . ">";

        $readonly = !empty($params['readonly']) ? 1 : 0;
        $disabled = !empty($params['disabled']) ? 1 : 0;

        // Если переданы значения записи
        if (isset($params['record_value'])) {
            if (is_array($params['record_value']) && key($params['record_value'])) {
                $params['value'] = key($params['record_value']);
            } elseif (!is_array($params['record_value'])) {
                $params['value'] = $params['record_value'];
            }
        }

        $default_value = (strpos($params['value'], ":") !== false ? explode(":", $params['value']) : array());
        $second = (!empty($default_value[2]) ? $default_value[2] : 0);
        $second = $second > 60 ? 60 : $second;
        $minute = (!empty($default_value[1]) ? $default_value[1] : 0);
        $minute = $minute > 60 ? 60 : $minute;
        $hour = (!empty($default_value[0]) ? $default_value[0] : 0);
        $hour = $hour > 24 ? 24 : $hour;
        $time_format = 'am';
        if ($params['time_format'] == 12 && $hour > 12) {
            $time_format = 'pm';
            $hour -= 12;
        }
        // Формируем вывод для 3 полей времени
        if ($params['time_type'] == 'fields') {
            $params['class'][] = "multiform-time-field";
            $control .= "<div style=\"display: inline;\" " . self::addCustomParams(array('class'), $params) . ">";

            // Строим поле для часов
            if ($params['hour_field'] !== 'hide' && $params['hour_field'] !== 'hidden') {
                $control .= "<span class=\"hours\">";
                if ($params['hour_field'] == 'dropdown') {
                    $control .= "<select name=\"{$control_name}[hour]\" class=\"multiform-hour width50px\"" . ($readonly || $disabled ? " disabled" : "") . ">";
                    $control .= "<option value=\"\">" . _wd(self::$app, "Hour") . "</option>";
                    for ($i = 0; $i <= $params['time_format']; $i++) {
                        $control .= "<option value=\"{$i}\"" . ($i == $hour ? " selected" : "") . ">" . str_pad($i, 2, 0, STR_PAD_LEFT) . "</option>";
                    }
                    $control .= "</select>";
                    if ($readonly) {
                        $control .= "<input type=\"hidden\" value=\"{$hour}\" name=\"{$control_name}[hour]\"" . ($disabled ? " disabled" : "") . ">";
                    }
                } else {
                    $control .= "<input type=\"text\" onkeypress=\"$.multiformFrontend.validate.regexp(event, /[0-9]/);\" maxlength=\"2\" value=\"" . str_pad($hour, 2, 0, STR_PAD_LEFT) . "\" id=\"{$control_name}-hour\" name=\"{$control_name}[hour]\" class=\"multiform-hour width30px\"" . ($readonly ? " readonly" : "") . ($disabled ? " disabled" : "") . ">";
                    $control .= "<label for=\"{$control_name}-hour\">" . _wd(self::$app, "HH") . "</label>";
                }
                $control .= "</span>";
                $control .= "<span class=\"separator\">" . $params['separator'] . "</span>";
            } elseif ($params['hour_field'] == 'hidden') {
                $control .= "<input type=\"hidden\" value=\"{$hour}\" name=\"{$control_name}[hour]\"" . ($disabled ? " disabled" : "") . ">";
            }
            // Строим поле для минут
            if ($params['minute_field'] !== 'hide' && $params['minute_field'] !== 'hidden') {
                $control .= "<span class=\"minutes\">";
                if ($params['minute_field'] == 'dropdown') {
                    $control .= "<select name=\"{$control_name}[minute]\" class=\"multiform-minute width50px\"" . ($readonly || $disabled ? " disabled" : "") . ">";
                    $control .= "<option value=\"\">" . _wd(self::$app, "Minute") . "</option>";
                    for ($i = 0; $i <= 60; $i++) {
                        $control .= "<option value=\"{$i}\"" . ($i == $minute ? " selected" : "") . ">" . str_pad($i, 2, 0, STR_PAD_LEFT) . "</option>";
                    }
                    $control .= "</select>";
                    if ($readonly) {
                        $control .= "<input type=\"hidden\" value=\"{$minute}\" name=\"{$control_name}[minute]\"" . ($disabled ? " disabled" : "") . ">";
                    }
                } else {
                    $control .= "<input type=\"text\" onkeypress=\"$.multiformFrontend.validate.regexp(event, /[0-9]/);\" maxlength=\"2\" value=\"" . str_pad($minute, 2, 0, STR_PAD_LEFT) . "\" id=\"{$control_name}-minute\" name=\"{$control_name}[minute]\" class=\"multiform-minute width30px\"" . ($readonly ? " readonly" : "") . ($disabled ? " disabled" : "") . ">";
                    $control .= "<label for=\"{$control_name}-minute\">" . _wd(self::$app, "MM") . "</label>";
                }
                $control .= "</span>";
                $control .= ($params['second_field'] !== 'hide' && $params['second_field'] !== 'hidden' ? "<span class=\"separator\">" . $params['separator'] . "</span>" : "");
            } elseif ($params['minute_field'] == 'hidden') {
                $control .= "<input type=\"hidden\" value=\"{$minute}\" name=\"{$control_name}[minute]\"" . ($disabled ? " disabled" : "") . ">";
            }
            // Строим поле для секунд
            if ($params['second_field'] !== 'hide' && $params['second_field'] !== 'hidden') {
                $control .= "<span class=\"seconds\">";
                if ($params['second_field'] == 'dropdown') {
                    $control .= "<select name=\"{$control_name}[second]\" class=\"multiform-second width50px\"" . ($readonly || $disabled ? " disabled" : "") . ">";
                    $control .= "<option value=\"\">" . _wd(self::$app, "Second") . "</option>";
                    for ($i = 0; $i <= 60; $i++) {
                        $control .= "<option value=\"{$i}\"" . ($i == $second ? " selected" : "") . ">" . str_pad($i, 2, 0, STR_PAD_LEFT) . "</option>";
                    }
                    $control .= "</select>";
                    if ($readonly) {
                        $control .= "<input type=\"hidden\" value=\"{$second}\" name=\"{$control_name}[second]\"" . ($disabled ? " disabled" : "") . ">";
                    }
                } else {
                    $control .= "<input type=\"text\" onkeypress=\"$.multiformFrontend.validate.regexp(event, /[0-9]/);\" maxlength=\"2\" value=\"" . str_pad($second, 2, 0, STR_PAD_LEFT) . "\" id=\"{$control_name}-second\" name=\"{$control_name}[second]\" class=\"multiform-second width30px\"" . ($readonly ? " readonly" : "") . ($disabled ? " disabled" : "") . ">";
                    $control .= "<label for=\"{$control_name}-second\">" . _wd(self::$app, "SS") . "</label>";
                }
                $control .= "</span>";
            } elseif ($params['second_field'] == 'hidden') {
                $control .= "<input type=\"hidden\" value=\"{$second}\" name=\"{$control_name}[second]\"" . ($disabled ? " disabled" : "") . ">";
            }
            if ($params['time_format'] == '12' && $params['hour_field'] !== 'hide') {
                $control .= "<span class=\"time-formats\">";
                $control .= "<select name=\"{$control_name}[time_format]\" id=\"{$control_name}-time_format\" class=\"width50px\">";
                $control .= "<option value=\"am\"" . ($time_format == 'am' ? " selected" : "") . ">AM</option>";
                $control .= "<option value=\"pm\"" . ($time_format == 'pm' ? " selected" : "") . ">PM</option>";
                $control .= "</select>";
                $control .= "<label for=\"{$control_name}-time_format\">" . _wd(self::$app, "AM/PM") . "</label>";
                $control .= "</span>";
            }
            $control .= "</div>";
        }
        // Формируем вывод для одного поля
        else {
            $params['class'][] = "multiform-full-time";
            $control .= "<input type=\"text\" value=\"" . str_pad($hour, 2, 0, STR_PAD_LEFT) . ":" . str_pad($minute, 2, 0, STR_PAD_LEFT) . ":" . str_pad($second, 2, 0, STR_PAD_LEFT) . "\" name=\"{$control_name}\" ";
            $control .= self::addCustomParams(array('class', 'readonly', 'disabled'), $params);
            $control .= ">";
        }
        if (!multiformFormBuilder::isRegister("phone")) {
            self::addToJs("<script src=\"" . wa()->getAppStaticUrl('multiform', true) . "js/jquery.mask.js\"></script>", 'time');
        }
        if (!$readonly && $params['time_type'] == 'textfield') {
            $js = "MultiformScripts.initTime($('#{$params['id']}'));";
            self::addToJs($js);
        }
        $control .= "</div>";
        return $control;
    }

    public function GetTimeCallbackSettingsControl($name, $params)
    {
        $control = <<<HTML
        <span id="{$params['id']}"></span>
        <script>
            $("#{$params['id']}").closest(".form-builder-row").find(".f-time-default").mfMask("00:00:00");
            var tab = $("#{$params['id']}").closest(".tab-display");
            if (tab.find(".data-date-type:checked").val() == 'fields') {
                tab.find(".f-date-textfield").closest(".settings-field").hide();
                tab.find(".f-date-fields").closest(".settings-field").show();
            } else {
                tab.find(".f-date-textfield").closest(".settings-field").show();
                tab.find(".f-date-fields").closest(".settings-field").hide();
            }
        </script>                 
HTML;
        return $control;
    }

    public function validate_required($post_fields, $form_field)
    {
        // Проверяем поле на пустоту
        if (!empty($post_fields[$form_field['id']]['value'])) {
            // Если отображаются несколько полей для даты
            if ((!empty($form_field['display']['time_type']) && $form_field['display']['time_type'] == 'fields') || empty($form_field['display']['time_type'])) {
                $chunks = explode(":", $post_fields[$form_field['id']]['formatted']);
                if (
                        ($form_field['display']['hour_field'] !== 'hide' && $form_field['display']['hour_field'] !== 'hidden' && $chunks[0] == '') ||
                        ($form_field['display']['minute_field'] !== 'hide' && $form_field['display']['minute_field'] !== 'hidden' && $chunks[1] == '') ||
                        ($form_field['display']['second_field'] !== 'hide' && $form_field['display']['second_field'] !== 'hidden' && $chunks[2] == '')
                ) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function validate_field($field, $value, $form_fields, $post_fields)
    {
        return array('field' => array($value['formatted'] => $value['value']));
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetTimeFieldControl',
            'title' => _w('New time'),
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
                'default' => array(
                    'title' => _w('Predefined value'),
                    'value' => '',
                    'class' => 'f-time-default',
                    'description' => _w("HH") . ":" . _w("MM") . ":" . _w("SS"),
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
                'info4' => array(
                    'value' => _w('Time field type'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'time_type' => array(
                    'value' => 'fields',
                    'control_type' => 'GetRadiogroupControl',
                    'class' => 'f-update-select data-date-type',
                    'options' => array(
                        array('title' => _w('3 fields'), 'value' => 'fields'),
                        array('title' => _w('Textfield'), 'value' => 'textfield'),
                    ),
                ),
                'separator' => array(
                    'title' => _w('Fields separator'),
                    'value' => '&nbsp;:&nbsp;',
                    'title_type' => 'inline',
                    'class' => 'f-update-text data-date-separator f-date-fields width150px',
                    'description' => _w("You can use HTML"),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'hour_field' => array(
                    'title' => _w('Hour field'),
                    'value' => 'textfield',
                    'control_type' => 'GetRadiogroupControl',
                    'control_separator' => '&nbsp;',
                    'title_type' => 'inline',
                    'class' => 'f-date-fields',
                    'options' => array(
                        array('title' => _w('Dropdown'), 'value' => 'dropdown'),
                        array('title' => _w('Textfield'), 'value' => 'textfield'),
                        array('title' => _w('Hide'), 'value' => 'hide'),
                        array('title' => _w('Hidden'), 'value' => 'hidden'),
                    ),
                ),
                'minute_field' => array(
                    'title' => _w('Minute field'),
                    'value' => 'textfield',
                    'control_separator' => '&nbsp;',
                    'title_type' => 'inline',
                    'control_type' => 'GetRadiogroupControl',
                    'class' => 'f-date-fields',
                    'options' => array(
                        array('title' => _w('Dropdown'), 'value' => 'dropdown'),
                        array('title' => _w('Textfield'), 'value' => 'textfield'),
                        array('title' => _w('Hide'), 'value' => 'hide'),
                        array('title' => _w('Hidden'), 'value' => 'hidden'),
                    ),
                ),
                'second_field' => array(
                    'title' => _w('Second field'),
                    'value' => 'textfield',
                    'control_separator' => '&nbsp;',
                    'title_type' => 'inline',
                    'control_type' => 'GetRadiogroupControl',
                    'class' => 'f-date-fields',
                    'options' => array(
                        array('title' => _w('Dropdown'), 'value' => 'dropdown'),
                        array('title' => _w('Textfield'), 'value' => 'textfield'),
                        array('title' => _w('Hide'), 'value' => 'hide'),
                        array('title' => _w('Hidden'), 'value' => 'hidden'),
                    ),
                ),
                'time_format' => array(
                    'title' => _w('Time format'),
                    'value' => '24',
                    'control_separator' => '&nbsp;',
                    'title_type' => 'inline',
                    'control_type' => 'GetRadiogroupControl',
                    'class' => 'f-date-fields',
                    'options' => array(
                        array('title' => _w('12 hours (am, pm)'), 'value' => '12'),
                        array('title' => _w('24 hours'), 'value' => '24'),
                    ),
                    'description' => _w('Use for dropdown fields')
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
                'callback' => array(
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetTimeCallbackSettingsControl'
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
            ),
        );
    }

}
