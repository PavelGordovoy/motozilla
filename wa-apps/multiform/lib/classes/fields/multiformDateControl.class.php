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
 * - Get%Setting_id%SettingsControl($name, $params) Return string.
 *
 * To process field data before save use:
 * - before%Field_id%Save(&$data)
 *
 * To process field settings before save use:
 * - before%Setting_id%SettingsSave($tab_id, $f_id, $field, &$data, $post_data)
 */
class multiformDateControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['value'] = isset($field['value']) ? $field['value'] : "";
        $params['date_type'] = isset($field['display']['date_type']) ? $field['display']['date_type'] : $params['display']['date_type']['value'];
        $params['date_separator'] = isset($field['display']['date_separator']) ? $field['display']['date_separator'] : $params['display']['date_separator']['value'];
        $params['day_field'] = isset($field['display']['day_field']) ? $field['display']['day_field'] : $params['display']['day_field']['value'];
        $params['day_width'] = isset($field['display']['day_width']) ? $field['display']['day_width'] : '';
        $params['month_field'] = isset($field['display']['month_field']) ? $field['display']['month_field'] : $params['display']['month_field']['value'];
        $params['month_width'] = isset($field['display']['month_width']) ? $field['display']['month_width'] : '';
        $params['year_field'] = isset($field['display']['year_field']) ? $field['display']['year_field'] : $params['display']['year_field']['value'];
        $params['year_width'] = isset($field['display']['year_width']) ? $field['display']['year_width'] : '';
        $params['year_start'] = !empty($field['display']['year_start']) ? $field['display']['year_start'] : $params['display']['year_interval']['year_start'];
        $params['year_end'] = !empty($field['display']['year_end']) ? $field['display']['year_end'] : date("Y") + 10;
        $params['date_popup'] = isset($field['display']['date_popup']) ? $field['display']['date_popup'] : $params['display']['date_popup']['value'];
        $params['default'] = isset($field['properties']['default']) ? $field['properties']['default'] : $params['properties']['default']['value'];
        $params['default_value'] = isset($field['properties']['default_value']) ? $field['properties']['default_value'] : "";
        $params['date_format'] = isset($field['display']['date_format']) ? $field['display']['date_format'] : $params['display']['date_format']['value'];
    }

    public static function execute($name, $params)
    {
        $control = '';

        self::addToJs('<script src="' . wa()->getAppStaticUrl('multiform', true) . 'js/datepicker/javascript/zebra_datepicker.src.js"></script>', 'date');
        self::addToCss('<link rel="stylesheet" href="' . wa()->getAppStaticUrl('multiform', true) . 'js/datepicker/css/metallic.css">', 'date');

        $control_name = multiformHelper::secureString($name);
        $control .= "<div id=\"{$params['id']}\" style=\"display: inline;\"" . ($params['date_type'] == 'fields' ? " data-separator = '" . multiformHelper::secureString(strip_tags($params['date_separator'])) . "'" : "") . ">";

        $readonly = !empty($params['readonly']) ? 1 : 0;
        $disabled = !empty($params['disabled']) ? 1 : 0;

        // Если переданы значения записи
        if (isset($params['record_value'])) {
            if (is_array($params['record_value']) && key($params['record_value'])) {
                $params['default_value'] = key($params['record_value']);
            } elseif (!is_array($params['record_value'])) {
                $params['default_value'] = $params['record_value'];
            }
            $params['default'] = 0;
        }

        $default_value = (strpos($params['default_value'], "-") !== false ? explode("-", $params['default_value']) : array());

        $day = !empty($params['default']) ? date("d") : (!empty($default_value[2]) ? $default_value[2] : "");
        $month = !empty($params['default']) ? date("m") : (!empty($default_value[1]) ? $default_value[1] : "");
        $year = !empty($params['default']) ? date("Y") : (!empty($default_value[0]) ? $default_value[0] : "");

        $has_popup = !empty($params['date_popup']) && !$readonly && ($params['date_type'] == 'textfield' || ($params['date_type'] == 'fields' && ($params['day_field'] !== 'hide' || $params['month_field'] !== 'hide' || $params['year_field'] !== 'hide')));
        if ($has_popup) {
            $default_date_params = '';
            $popup_params = [
                'inside' => ($params['date_type'] == 'fields' ? 0 : 1),
            ];
            if (wa()->getLocale() == 'ru_RU') {
                $popup_params['months'] = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
                $popup_params['days_abbr'] = ["Вск", "Пон", "Вт", "Ср", "Чтв", "Пят", "Суб"];
                $popup_params['days'] = ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"];
                $popup_params['show_select_today'] = "Сегодня";
                $popup_params['lang_clear_date'] = "Очистить";
            }
            if ($params['date_type'] == 'fields') {
                $popup_params['show_clear_date'] = 0;
            }
            if ($params['date_type'] == 'textfield') {
                $popup_params['format'] = $params['date_format'];
            }

            if ($params['year_start'] && $params['year_end']) {
                $popup_params['disabled_dates'] = "['* * * *']";
                $popup_params['enabled_dates'] = ["* * " . $params['year_start'] . "-" . $params['year_end']];
            }

            if (wa()->getLocale() == 'ru_RU' && $params['date_type'] == 'textfield' && $year && $month && $day) {
                $default_date_params = "{$year}-{$month}-{$day}";
            }
        }

        // Формируем вывод для 3 полей даты
        if ($params['date_type'] == 'fields') {
            $control .= "<div style=\"display: inline;\"";
            $control .= " " . self::addCustomParams(array('class'), $params);
            $control .= ">";

            // Строим поле для дней
            if ($params['day_field'] !== 'hide' && $params['day_field'] !== 'hidden') {
                if ($params['day_field'] == 'dropdown') {
                    // Вычисляем количество дней
                    $days = multiformHelper::days_in_month($month, $year);
                    $control .= "<select name=\"{$control_name}[day]\" class=\"multiform-day\"" . (!empty($params['day_width']) ? " style=\"width: {$params['day_width']}px;\"" : "") . ($readonly || $disabled ? " disabled" : "") . ">";
                    $control .= "<option value=\"\">" . _wd(self::$app, "Day") . "</option>";
                    for ($i = 1; $i <= $days; $i++) {
                        $control .= "<option value=\"{$i}\"" . ($i == $day ? " selected" : "") . ">{$i}</option>";
                    }
                    $control .= "</select>";
                    if ($readonly) {
                        $control .= "<input type=\"hidden\" value=\"{$day}\" name=\"{$control_name}[day]\"" . ($disabled ? " disabled" : "") . ">";
                    }
                } else {
                    $control .= "<input type=\"text\" onkeypress=\"$.multiformFrontend.validate.regexp(event, /[0-9]/);\" maxlength=\"2\" value=\"{$day}\" name=\"{$control_name}[day]\" class=\"multiform-day\"" . (!empty($params['day_width']) ? " style=\"width: {$params['day_width']}px;\"" : "") . ($readonly ? " readonly" : "") . ($disabled ? " disabled" : "") . ">";
                }
                $control .= $params['date_separator'];
            } elseif ($params['day_field'] == 'hidden') {
                $control .= "<input type=\"hidden\" value=\"{$day}\" name=\"{$control_name}[day]\"" . ($disabled ? " disabled" : "") . ">";
            }
            // Строим поле для месяцев
            if ($params['month_field'] !== 'hide' && $params['month_field'] !== 'hidden') {
                if ($params['month_field'] == 'dropdown') {
                    $control .= "<select name=\"{$control_name}[month]\" class=\"multiform-month\"" . (!empty($params['month_width']) ? " style=\"width: {$params['month_width']}px;\"" : "") . ($readonly || $disabled ? " disabled" : "") . ">";
                    $control .= "<option value=\"\">" . _wd(self::$app, "Month") . "</option>";
                    for ($i = 1; $i <= 12; $i++) {
                        $control .= "<option value=\"{$i}\"" . ($i == $month ? " selected" : "") . ">" . _wd(self::$app, date("F", mktime(0, 0, 0, $i))) . "</option>";
                    }
                    $control .= "</select>";
                    if ($readonly) {
                        $control .= "<input type=\"hidden\" value=\"{$month}\" name=\"{$control_name}[month]\"" . ($disabled ? " disabled" : "") . ">";
                    }
                } else {
                    $control .= "<input type=\"text\" onkeypress=\"$.multiformFrontend.validate.regexp(event, /[0-9]/);\" maxlength=\"2\" value=\"{$month}\" name=\"{$control_name}[month]\" class=\"multiform-month\"" . (!empty($params['month_width']) ? " style=\"width: {$params['month_width']}px;\"" : "") . ($readonly ? " readonly" : "") . ($disabled ? " disabled" : "") . ">";
                }
                $control .= ($params['year_field'] !== 'hide' && $params['year_field'] !== 'hidden' ? $params['date_separator'] : '');
            } elseif ($params['month_field'] == 'hidden') {
                $control .= "<input type=\"hidden\" value=\"{$month}\" name=\"{$control_name}[month]\"" . ($disabled ? " disabled" : "") . ">";
            }
            // Строим поле для годов
            if ($params['year_field'] !== 'hide' && $params['year_field'] !== 'hidden') {
                if ($params['year_field'] == 'dropdown') {
                    $control .= "<select name=\"{$control_name}[year]\" class=\"multiform-year\"" . (!empty($params['year_width']) ? " style=\"width: {$params['year_width']}px;\"" : "") . ($readonly || $disabled ? " disabled" : "") . ">";
                    $control .= "<option value=\"\">" . _wd(self::$app, "Year") . "</option>";
                    for ($i = $params['year_start']; $i <= $params['year_end']; $i++) {
                        $control .= "<option value=\"{$i}\"" . ($i == $year ? " selected" : "") . ">{$i}</option>";
                    }
                    $control .= "</select>";
                    if ($readonly) {
                        $control .= "<input type=\"hidden\" value=\"{$year}\" name=\"{$control_name}[year]\"" . ($disabled ? " disabled" : "") . ">";
                    }
                } else {
                    $control .= "<input type=\"text\" onkeypress=\"$.multiformFrontend.validate.regexp(event, /[0-9]/);\" maxlength=\"4\" value=\"{$year}\" name=\"{$control_name}[year]\" class=\"multiform-year\"" . (!empty($params['year_width']) ? " style=\"width: {$params['year_width']}px;\"" : "") . ($readonly ? " readonly" : "") . ($disabled ? " disabled" : "") . ">";
                }
            } elseif ($params['year_field'] == 'hidden') {
                $control .= "<input type=\"hidden\" value=\"{$year}\" name=\"{$control_name}[year]\"" . ($disabled ? " disabled" : "") . ">";
            }
            $control .= "</div>";
        } // Формируем вывод для одного поля
        else {
            if (!empty($params['date_popup']) && !$readonly) {
                $control .= "<input type=\"hidden\" value=\"{$year}-{$month}-{$day}\" class=\"multiform-formatted-date\" name=\"{$control_name}[formatted]\"" . ($disabled ? " disabled" : "") . ">";
                $params['class'][] = "multiform-datepicker";
            }
            $params['class'][] = "multiform-full-date";
            $format_date = date($params['date_format'], mktime(0, 0, 0, $month ? $month : 0, $day ? $day : 0, $year ? $year : 0));
            $control .= "<input type=\"text\" value=\"{$format_date}\" data-value=\"{$year}-{$month}-{$day}\" name=\"{$control_name}[value]\" ";
            if ($has_popup) {
                $control .= self::addCustomParams(array('data-params', 'data-defaultDate'), [
                    'data-params' => json_encode($popup_params, JSON_HEX_APOS | JSON_HEX_QUOT),
                    'data-defaultDate' => $default_date_params,
                ]);
            }
            $control .= self::addCustomParams(array('class', 'readonly', 'disabled'), $params);
            $control .= ">";
        }

        if ($has_popup && $params['date_type'] == 'fields') {
            $control .= "<input type=\"text\" style=\"width: 0\" value=\"" . date("Y-m-d", mktime(0, 0, 0, $month ? $month : 0, $day ? $day : 0, $year ? $year : 0)) . "\" class=\"multiform-datepicker outside\"";
            $control .= self::addCustomParams(array('data-params', 'data-defaultDate'), [
                'data-params' => json_encode($popup_params, JSON_HEX_APOS | JSON_HEX_QUOT),
                'data-defaultDate' => $default_date_params,
            ]);
            $control .= ">";
        }
        $control .= "</div>";

        if (wa()->getEnv() == 'backend' && $has_popup) {
            $js = "MultiformScripts.initDatepicker($('#{$params['id']}'));";
            self::addToJs($js);
        }

        return $control;
    }

    public function GetDateDefaultValueSettingsControl($name, $params)
    {
        $control_name = multiformHelper::secureString($name);
        $control = "<div id=\"{$params['id']}\"><label>" . _w('Default value') . ":</label> ";
        $control .= "<input type=\"text\" name=\"{$control_name}\" value=\"{$params['value']}\" class=\"multiform-datepicker multiform-full-date\">";
        $control .= "<div class='multiform-gap-description'>{$params['desc']}</div>";
        $control .= "</div>";
        $control .= "
    <script>
        $(function() { 
            $(\"#{$params['id']}\").closest(\".tab-properties\").find(\".data-date-default\").bind(\"init-default-datepicker\", function() {
                if (!$(this).prop(\"checked\")) {
                    var params = {";
        if (wa()->getLocale() == 'ru_RU') {
            $control .= "months: [\"Январь\", \"Февраль\", \"Март\", \"Апрель\", \"Май\", \"Июнь\", \"Июль\", \"Август\", \"Сентябрь\", \"Октябрь\", \"Ноябрь\", \"Декабрь\"],"
                . "days_abbr: [\"Вск\", \"Пон\", \"Вт\", \"Ср\", \"Чтв\", \"Пят\", \"Суб\"],"
                . "show_select_today: \"Сегодня\","
                . "lang_clear_date: \"Очистить\",";
        }
        $control .= <<<HTML
                            inside: true,
                            onSelect: function(formatDate, origDate, obj) {
                                $("#{$params['id']} .multiform-full-date").val(formatDate);
                            }
                    };
                    if (!$("#{$params['id']}").find(".Zebra_DatePicker_Icon").length) {
                        $("#{$params['id']} .multiform-datepicker").Zebra_DatePicker(params);
                    } else {
                        $("#{$params['id']} .multiform-datepicker").data('Zebra_DatePicker').update();
                    }
                } 
            }); 
            $("#{$params['id']}").closest(".settings-block").on('field-is-editing', function() {
                $("#{$params['id']} .multiform-datepicker").data('Zebra_DatePicker') !== undefined && $("#{$params['id']} .multiform-datepicker").data('Zebra_DatePicker').update(); 
            });
        });
    </script>                 
HTML;
        return $control;
    }

    public function prepareYear_intervalSettingsParams(&$params, $field, $tab_id, $property_id)
    {
        $params['year_start'] = !empty($field['display']['year_start']) ? $field['display']['year_start'] : $params['year_start'];
        $params['year_end'] = !empty($field['display']['year_end']) ? $field['display']['year_end'] : date("Y") + 10;
    }

    public function GetYearIntervalSettingsControl($name, $params)
    {
        $control = "<input type\"text\" name=\"display[year_start]\" value=\"{$params['year_start']}\" ";
        $control .= self::addCustomParams(array('class'), $params);
        $control .= "> - ";
        $control .= "<input type\"text\" name=\"display[year_end]\" value=\"{$params['year_end']}\">";
        return $control;
    }

    public static function GetDateCallbackSettingsControl($name, $params)
    {
        $control = <<<HTML
<span id="{$params['id']}"></span>
<script>
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

    public function beforeYear_intervalSettingsSave($tab_id, $f_id, $field, &$data, $post_data)
    {
        $data[$tab_id]['year_start'] = (int) $post_data[$tab_id]['year_start'];
        $data[$tab_id]['year_end'] = (int) $post_data[$tab_id]['year_end'];
        if ($data[$tab_id]['year_end'] < $data[$tab_id]['year_start']) {
            $data[$tab_id]['year_end'] = date("Y") + 10;
        }
    }

    public function validate_required($post_fields, $form_field)
    {
        // Проверяем поле на пустоту
        if (!empty($post_fields[$form_field['id']]['value'])) {
            // Если отображаются несколько полей для даты
            if ((!empty($form_field['display']['date_type']) && $form_field['display']['date_type'] == 'fields') || empty($form_field['display']['date_type'])) {
                $chunks = explode("-", $post_fields[$form_field['id']]['formatted']);
                if (
                    ($form_field['display']['year_field'] !== 'hide' && $form_field['display']['year_field'] !== 'hidden' && !$chunks[0]) ||
                    ($form_field['display']['month_field'] !== 'hide' && $form_field['display']['month_field'] !== 'hidden' && !$chunks[1]) ||
                    ($form_field['display']['day_field'] !== 'hide' && $form_field['display']['day_field'] !== 'hidden' && !$chunks[2])
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
            'control_type' => 'GetDateFieldControl',
            'title' => _w('New date'),
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
                    'label' => _w('Use current date as default'),
                    'value' => 0,
                    'control_type' => waHtmlControl::CHECKBOX,
                    'class' => 'f-update-checkbox data-date-default',
                    'childs' => [
                        'onopen_value' => 0,
                        'fields' => [
                            'default_value' => array(
                                'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetDateDefaultValueSettingsControl',
                                'desc' => _w('In frontend users will see formatted value'),
                            ),

                        ]
                    ]
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
                    'value' => _w('Date field type'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'date_type' => array(
                    'value' => 'fields',
                    'control_type' => 'GetRadiogroupControl',
                    'class' => 'f-update-select data-date-type',
                    'options' => array(
                        array('title' => _w('3 fields'), 'value' => 'fields'),
                        array('title' => _w('Textfield'), 'value' => 'textfield'),
                    ),
                ),
                'date_format' => array(
                    'title' => _w('Date format'),
                    'value' => 'd/m/Y',
                    'title_type' => 'inline',
                    'class' => 'f-update-text data-date-format f-date-textfield width100px',
                    'description' => _w("Available characters: d, D, j, l, N, w, S, F, m, M, n, Y, y"),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'date_separator' => array(
                    'title' => _w('Fields separator'),
                    'value' => '&nbsp;/&nbsp;',
                    'title_type' => 'inline',
                    'class' => 'f-update-text data-date-separator f-date-fields width150px',
                    'description' => _w("You can use HTML"),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'hr3' => array(
                    'value' => '<div class="infotext f-date-fields">' . _w('Day') . '</div>',
                    'control_type' => 'GetCustomHTMLControl',
                ),
                'day_field' => array(
                    'title' => _w('Day field'),
                    'value' => 'dropdown',
                    'control_type' => 'GetRadiogroupControl',
                    'control_separator' => '&nbsp;',
                    'title_type' => 'inline',
                    'class' => 'f-date-fields data-day',
                    'options' => array(
                        array('title' => _w('Dropdown'), 'value' => 'dropdown'),
                        array('title' => _w('Textfield'), 'value' => 'textfield'),
                        array('title' => _w('Hide'), 'value' => 'hide'),
                        array('title' => _w('Hidden'), 'value' => 'hidden'),
                    ),
                ),
                'day_width' => array(
                    'title' => _w('Day field width'),
                    'title_type' => 'inline',
                    'class' => 'f-update-text data-date-width f-only-digits f-date-fields width150px',
                    'control_type' => waHtmlControl::INPUT,
                ),
                'hr' => array(
                    'value' => '<div class="infotext f-date-fields">' . _w('Month') . '</div>',
                    'control_type' => 'GetCustomHTMLControl',
                ),
                'month_field' => array(
                    'title' => _w('Month field'),
                    'value' => 'dropdown',
                    'control_separator' => '&nbsp;',
                    'title_type' => 'inline',
                    'control_type' => 'GetRadiogroupControl',
                    'class' => 'f-date-fields data-month',
                    'options' => array(
                        array('title' => _w('Dropdown'), 'value' => 'dropdown'),
                        array('title' => _w('Textfield'), 'value' => 'textfield'),
                        array('title' => _w('Hide'), 'value' => 'hide'),
                        array('title' => _w('Hidden'), 'value' => 'hidden'),
                    ),
                ),
                'month_width' => array(
                    'title' => _w('Month field width'),
                    'title_type' => 'inline',
                    'class' => 'f-update-text data-date-width f-only-digits f-date-fields width150px',
                    'control_type' => waHtmlControl::INPUT,
                ),
                'hr2' => array(
                    'value' => '<div class="infotext f-date-fields">' . _w('Year') . '</div>',
                    'control_type' => 'GetCustomHTMLControl',
                ),
                'year_field' => array(
                    'title' => _w('Year field'),
                    'value' => 'dropdown',
                    'control_separator' => '&nbsp;',
                    'title_type' => 'inline',
                    'control_type' => 'GetRadiogroupControl',
                    'class' => 'f-date-fields data-year',
                    'options' => array(
                        array('title' => _w('Dropdown'), 'value' => 'dropdown'),
                        array('title' => _w('Textfield'), 'value' => 'textfield'),
                        array('title' => _w('Hide'), 'value' => 'hide'),
                        array('title' => _w('Hidden'), 'value' => 'hidden'),
                    ),
                ),
                'year_width' => array(
                    'title' => _w('Year field width'),
                    'title_type' => 'inline',
                    'class' => 'f-update-text data-date-width f-only-digits f-date-fields width150px',
                    'control_type' => waHtmlControl::INPUT,
                ),
                'year_interval' => array(
                    'title' => _w('Year interval'),
                    'year_start' => 1920,
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetYearIntervalSettingsControl',
                    'class' => 'f-date-fields',
                ),
                'info3' => array(
                    'value' => _w('Popup calendar'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'date_popup' => array(
                    'label' => _w('Enable popup calendar'),
                    'value' => 1,
                    'filter' => 'int',
                    'control_type' => waHtmlControl::CHECKBOX,
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
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetDateCallbackSettingsControl'
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
