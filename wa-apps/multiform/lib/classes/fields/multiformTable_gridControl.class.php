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
class multiformTable_gridControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['statements'] = !empty($field['option_fields']['statement']) ? $field['option_fields']['statement'] : $params['options']['statement_values']['options'];
        $params['columns'] = !empty($field['option_fields']['column']) ? $field['option_fields']['column'] : $params['options']['column_values']['options'];
        $params['customize_statement_keys'] = isset($field['options']['customize_statement_keys']) ? $field['options']['customize_statement_keys'] : $params['options']['customize_statement_keys']['value'];
        $params['customize_column_keys'] = isset($field['options']['customize_column_keys']) ? $field['options']['customize_column_keys'] : $params['options']['customize_column_keys']['value'];
        $params['randomize_statements'] = isset($field['display']['randomize_statements']) ? $field['display']['randomize_statements'] : $params['display']['randomize_statements']['value'];
        $params['randomize_columns'] = isset($field['display']['randomize_columns']) ? $field['display']['randomize_columns'] : $params['display']['randomize_columns']['value'];
    }

    public static function execute($name, $params)
    {
        static $js;

        $control_name = multiformHelper::secureString($name);
        $control = "<div" . ($params['class'] ? " " . self::addCustomParams(array('class'), $params) : "") . " id=\"{$params['id']}\">";
        $control .= "<table class=\"multiform-grid\">";

        if (!$js) {
            $js = <<<HTML
                $(document).off('click', '.multiform-grid .circle-slash').on('click', '.multiform-grid .circle-slash', function() {
                   $(this).closest('td').removeClass('is-checked').find('input').prop('checked', false).trigger('change'); 
                });
                $(document).on('change', '.multiform-grid :radio', function() {
                   var elem = $(this);
                   elem.closest('tr').find('.is-checked').removeClass('is-checked');
                   if (elem.prop('checked')) {
                       elem.closest('td').addClass('is-checked');
                   }
                });
HTML;
            self::addToJs($js);
        }

        // Если необходимо перемешать варианты в случайном порядке
        if (!empty($params['randomize_statements'])) {
            $new = array();
            $keys = array_keys($params['statements']);
            shuffle($keys);
            foreach ($keys as $key) {
                $new[$key] = $params['statements'][$key];
            }
            $params['statements'] = $new;
        }
        if (!empty($params['randomize_columns'])) {
            $new = array();
            $keys = array_keys($params['columns']);
            shuffle($keys);
            foreach ($keys as $key) {
                $new[$key] = $params['columns'][$key];
            }
            $params['columns'] = $new;
        }

        $i = 0;
        $thead = "<thead><tr><th></th>";
        $tbody = "<tbody>";

        $frontend = wa()->getEnv() == 'frontend';
        foreach ($params['statements'] as $st_key => $st) {
            $tbody .= "<tr class=\"" . ($i !== 0 && (($i - 1) % 2 == 0) ? "even" : "odd") . "\">";
            $tbody .= "<th>" . multiformHelper::secureString($st['value']) . "</th>";
            $j = 0;
            foreach ($params['columns'] as $col_key => $col) {
                // Если переданы значения записи
                if (isset($params['record_value'])) {
                    if ($frontend) {
                        $col['checked'] = isset($params['record_value'][$st_key]) ? $params['record_value'][$st_key] == $col_key : 0;
                    } else {
                        $col['checked'] = isset($params['record_value'][$st_key]) ? ($params['customize_column_keys'] ? $params['record_value'][$st_key] == multiformHelper::secureString($col['key']) : $params['record_value'][$st_key] == multiformHelper::secureString($col['value'])) : 0;
                    }
                }
                if ($i == 0) {
                    $thead .= "<td>" . multiformHelper::secureString($col['value']) . "</td>";
                }
                $tbody .= "<td>"
                        . "<label for=\"{$control_name}[{$st_key}]-{$j}\">"
                        . "<input data-formula=\"" . multiformHelper::secureString((float) $col['formula'] + (float) $st['formula']) . "\" data-statement=\"{$st_key}\" data-column=\"{$col_key}\"" . (!empty($params['disabled']) ? " disabled" : "") . " id=\"{$control_name}[{$st_key}]-{$j}\" type=\"radio\"" . ($col['checked'] ? " checked" : "") . " name=\"{$control_name}[{$st_key}]\" value=\"" . ($params['customize_column_keys'] ? multiformHelper::secureString($col['key']) : multiformHelper::secureString($col['value'])) . "\">"
                        . "</label>"
                        . "<i class='mf circle-slash'></i>"
                        . "</td>";
                $j++;
            }
            $i++;
            $tbody .= "</tr>";
        }
        $tbody .= "</tbody>";
        $thead .= "</tr></thead>";

        $control .= $thead . $tbody;
        $control .= "</table>";
        $control .= "</div>";

        return $control;
    }

    public function prepareStatement_valuesSettingsParams(&$params, $field, $tab_id, $property_id)
    {
        $params['table_type'] = 'statement';
        $params['options'] = !empty($field['option_fields']['statement']) ? $field['option_fields']['statement'] : array();
        $params['customize_keys'] = isset($field['options']['customize_statement_keys']) ? $field['options']['customize_statement_keys'] : 0;
    }

    public function prepareColumn_valuesSettingsParams(&$params, $field, $tab_id, $property_id)
    {
        $params['table_type'] = 'column';
        $params['options'] = !empty($field['option_fields']['column']) ? $field['option_fields']['column'] : array();
        $params['customize_keys'] = isset($field['options']['customize_column_keys']) ? $field['options']['customize_column_keys'] : 0;
    }

    public function GetOptionTableGridSettingsControl($name, $params)
    {
        $control_name = multiformHelper::secureString($name);
        $control = "";
        $control = "
                <table class=\"zebra" . (!empty($params['customize_keys']) ? " show-customize" : "") . " s-" . $params['table_type'] . "\">
                    <thead>
                        <tr class=\"white\">
                            <td class=\"min-width\"></td>
                            " . ($params['table_type'] == 'column' ? "<td class=\"min-width\"></td>" : "") . "
                            <td class=\"customize min-width\">" . _w("Key") . "</td>
                            <td class=\"customize min-width\">" . _w("Formula") . "</td>
                            <td>" . _w("Value") . "</td>
                            <td width=\"50\"></td>
                        </tr>
                    </thead>
                    <tbody>";
        $k = 0;
        foreach ($params['options'] as $o) {
            $id = $o['id'] ? "update" . $o['id'] : 0;
            $control .= "<tr class=\"" . ($k !== 0 ? "sortable" : "") . "\" data-id=\"" . $o['id'] . "\">
                            <td>" . ($k !== 0 ? "<i class=\"icon16 sort\"></i>" : "") . "</td>
                            " . ($params['table_type'] == 'column' ? "<td><input type=\"radio\" title=\"" . _w("Pre-selected column") . "\" value=\"{$id}\"" . (!empty($o['checked']) ? " checked" : "") . " name=\"options_{$params['table_type']}[default]\"></td>" : "") . "
                            <td class=\"customize\"><input type=\"text\" name=\"options_{$params['table_type']}[key][" . $id . "]\" value=\"" . multiformHelper::secureString($o['key']) . "\"></td>
                            <td class=\"customize\"><input type=\"text\" style=\"width: 50px !important\" name=\"options_{$params['table_type']}[formula][" . $id . "]\" value=\"" . multiformHelper::secureString($o['formula']) . "\"></td>
                            <td class=\"full-width option-value\"><input type=\"text\" name=\"options_{$params['table_type']}[value][" . $id . "]\" value=\"" . multiformHelper::secureString($o['value']) . "\"></td>
                            <td class=\"nowrap\">
                                <a href=\"#/add/optionField/\" class=\"js-action add-option\" title=\"" . _w("Add option") . "\"><i class=\"icon16 add\"></i></a>
                                <a href=\"#/delete/optionField/\"" . ($k == 0 ? " style=\"display: none;\"" : "") . " class=\"js-action delete-option\" title=\"" . _w("Delete option") . "\"><i class=\"icon16 delete\"></i></a>
                            </td>
                        </tr>";
            $k++;
        }
        $control .= "
                    </tbody>
                </table>
                <div class=\"align-center\" style=\"margin: 10px 0; position: relative\">
                    " . ($params['table_type'] == 'column' ? "<a class=\"js-action\" style=\"position: absolute; left: 10px; top: 0\" href=\"cancel/optionDefaultValue\">" . _w("No default") . "</a>" : "") . "
                    <a href=\"#/open/optionImportDialog/\" class=\"js-action import-options\">" . _w("Import options") . "</a>
                    <div class=\"dialog-option-values\" style=\"display: none;\">
                        <p>- " . _w("Use new line to specify new option. <br>- If you want to customize keys, you should use symbol '<b>|</b>' as divider.") . "<br>" . _w("E.g") . ",</p>
                        <div class=\"align-center\"><p class=\"info-text\">" . _w("key|First option<br>another key|Second option<br>new key|Third option") . "</p></div>
                        <textarea style=\"width: 100%; height: 150px;\"></textarea>
                    </div>
                </div>
                ";
        return $control;
    }

    private function beforeOption_valuesSettingsSave($tab_id, $f_id, $field, &$data, $post_data, &$response, &$errors, $ext)
    {
        $option_model = new multiformFieldOptionsModel();
        $insert = $update = $delete = $keys = array();

        $options = ($ext == 'statement' ? waRequest::post('options_statement', array()) : waRequest::post('options_column', array()));
        $field_id = $field['id'];

        // Получаем ID всех вариантов для поля
        $option_ids = $option_model->select('id')->where("field_id = i:f_id AND ext = s:ext", array("f_id" => $field_id, "ext" => $ext))->fetchAll(null, true);

        if (isset($options['value'])) {

            // Если необходимо указать свои ключи для вариантов, проверяем их на уникальность
            $customize_keys = ($ext == 'statement' ? (!empty($post_data['options']['customize_statement_keys']) ? 1 : 0) : (!empty($post_data['options']['customize_column_keys'])) ? 1 : 0);
            if ($customize_keys) {
                foreach ($options['value'] as $option_id => $option) {
                    $key = ifempty($options['key'][$option_id], "");
                    if (in_array($key, $keys)) {
                        $errors['tabs'][] = 'options';
                        throw new waException(_w("Option keys should be unique"));
                    }
                    $keys[] = $key;
                }
            }
            $sort = 0;
            foreach ($options['value'] as $option_id => $option) {
                $key = ifempty($options['key'][$option_id], "");
                $sort_value = ifempty($options['sort'][$option_id], $sort);
                $checked = (!empty($options['default']) && $options['default'] == $option_id) ? 1 : 0;

                // Добавляем новые варианты
                if (strpos($option_id, "new") !== false) {
                    $insert[] = array("field_id" => $field_id, "value" => $option, "sort" => $sort_value, "ext" => $ext, "checked" => $checked, "key" => $key, "formula" => $options['formula'][$option_id]);
                }
                // Обновляем существующие варианты
                else {
                    $id = str_replace("update", "", $option_id);
                    // Сохраняем ID варианта (необходимо для получания вариантов, которые нужно удалить)
                    $update[] = $id;
                    $option_model->updateById($id, array("value" => $option, "sort" => $sort_value, "ext" => $ext, "checked" => $checked, "key" => $key, "formula" => $options['formula'][$option_id]));
                }
                $sort++;
            }
        }
        if ($insert) {
            $option_model->multipleInsert($insert);
            // Флаг, указывающий на необходимость обновления полей настроек
            $response['updateSettings'] = 1;
            $response['uploadOptions'] = 1;
        }
        // ID, которые необходимо удалить
        $delete = array_diff($option_ids, $update);
        if ($delete) {
            $option_model->delete($delete, $field_id);
        }
    }

    public function beforeStatement_valuesSettingsSave($tab_id, $f_id, $field, &$data, $post_data, &$response, &$errors)
    {
        $this->beforeOption_valuesSettingsSave($tab_id, $f_id, $field, $data, $post_data, $response, $errors, 'statement');
    }

    public function beforeColumn_valuesSettingsSave($tab_id, $f_id, $field, &$data, $post_data, &$response, &$errors)
    {
        $this->beforeOption_valuesSettingsSave($tab_id, $f_id, $field, $data, $post_data, $response, $errors, 'column');
    }

    public function beforeTable_gridCreate(&$data)
    {
        $field_options = $this->fieldOptions();
        $statements = $field_options['options']['statement_values']['options'];
        $columns = $field_options['options']['column_values']['options'];

        $save = array();
        foreach ($statements as $k => $st) {
            $save[] = array(
                'ext' => 'statement',
                'sort' => $k,
                'value' => $st['value'],
                'key' => $st['key']
            );
        }
        foreach ($columns as $kk => $col) {
            $save[] = array(
                'ext' => 'column',
                'sort' => $kk,
                'value' => $col['value'],
                'key' => $col['key']
            );
        }

        $option_model = new multiformFieldOptionsModel();
        $option_model->add($data['id'], $save);
    }

    public function validate_required($post_fields, $form_field)
    {
        if (empty($post_fields[$form_field['id']]['value']) || (!empty($post_fields[$form_field['id']]['value']) && count($post_fields[$form_field['id']]['value']) !== count($form_field['option_fields']['statement']))) {
            return false;
        }
        return true;
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetTable_gridFieldControl',
            'title' => _w('New table grid'),
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
                'info2' => array(
                    'value' => _w('Randomize output'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'randomize_statements' => array(
                    'label' => _w('Randomize statements'),
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w('Statement options will be shuffled each time'),
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'randomize_columns' => array(
                    'label' => _w('Randomize columns'),
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w('Column options will be shuffled each time'),
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'info3' => array(
                    'value' => _w('Field properties'),
                    'control_type' => 'GetInfotitleControl',
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
            'options' => array(
                'statement_values' => array(
                    'title' => _w("Statements"),
                    'after_title' => "(<a href='#/show/optionIds/' class='js-action' title='" . _w('Show option IDs') . "'>#</a>)",
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetOptionTableGridSettingsControl',
                    'options' => array(
                        array('value' => _w('Statement') . '1', 'key' => '1'),
                        array('value' => _w('Statement') . '2', 'key' => '2'),
                        array('value' => _w('Statement') . '3', 'key' => '3'),
                    ),
                ),
                'customize_statement_keys' => array(
                    'label' => _w('Customize statement keys'),
                    'class' => 'f-update-checkbox data-option-customize f-statement',
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w('Show to user one value, but save in DB another'),
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'column_values' => array(
                    'title' => _w("Columns"),
                    'after_title' => "(<a href='#/show/optionIds/' class='js-action' title='" . _w('Show option IDs') . "'>#</a>)",
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetOptionTableGridSettingsControl',
                    'options' => array(
                        array('value' => _w('Column') . '1', 'key' => '1'),
                        array('value' => _w('Column') . '2', 'key' => '2'),
                        array('value' => _w('Column') . '3', 'key' => '3'),
                    ),
                ),
                'customize_column_keys' => array(
                    'label' => _w('Customize column keys'),
                    'class' => 'f-update-checkbox data-option-customize f-column',
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w('Show to user one value, but save in DB another'),
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
