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
 * - before%Setting_id%SettingsSave($tab_id, $f_id, $field, &$data, $post_data, &$response, &$errors)
 */
class multiformSelectControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['attribute'] = (!empty($field['properties']['attribute']) ? $field['properties']['attribute'] : "");
        $params['field_id'] = $field['id'];
        $params['option_fields'] = isset($field['option_fields']) ? $field['option_fields'] : array();
        $params['customize_keys'] = isset($field['options']['customize_keys']) ? $field['options']['customize_keys'] : $params['options']['customize_keys']['value'];
    }

    public static function execute($name, $params)
    {
        $control_name = multiformHelper::secureString($name);
        $control = "";
        if ($params['option_fields']) {
            $checked = "";
            if (wa()->getEnv() == 'frontend' || !isset($params['record_value'])) {
                $params['class'][] = multiformHelper::getFieldSizeWidth($params['field']);
            }
            $control .= "<select" . (!empty($params['readonly']) || !empty($params['disabled']) ? " disabled" : "") . " id=\"{$params['id']}\" name=\"{$control_name}\"" . ($params['class'] ? " " . self::addCustomParams(array('class'), $params) : "") . ">";
            $c = 0;
            foreach ($params['option_fields'] as $option_id => $option) {
                // Если переданы значения записи
                if (isset($params['record_value'])) {
                    $option['checked'] = isset($params['record_value'][$option_id]) ? 1 : 0;
                }
                $control .= "<option data-formula=\"" . multiformHelper::secureString($option['formula']) . "\" data-id=\"{$option_id}\" value=\"" . ($c == 0 ? "" : $option_id) . "\"" . ($option['checked'] ? " selected" : "") . ">" . multiformHelper::secureString($option['value']) . "</option>";
                if (!empty($params['readonly']) && $option['checked']) {
                    $checked = "<input type=\"hidden\"" . (!empty($params['disabled']) ? " disabled" : "") . " name=\"{$control_name}\" value=\"" . $option_id . "\">";
                }
                $c++;
            }
            $control .= "</select>" . $checked;
        }

        return $control;
    }

    public function prepareOption_valuesSettingsParams(&$params, $field, $tab_id, $property_id)
    {
        $params['field'] = $field;
    }

    public function GetOptionSelectSettingsControl($name, $params)
    {
        $control_name = multiformHelper::secureString($name);
        $control = "";
        $field = $params['field'];
        $options = !empty($field['option_fields']) ? $field['option_fields'] : array(array("id" => 0, "key" => "", "formula" => "", "image" => "", "value" => ""));
        $control = "
                <table class=\"zebra" . (!empty($field['options']['customize_keys']) ? " show-customize" : "") . "\">
                    <thead>
                        <tr class=\"white\">
                            <td class=\"min-width\"></td>
                            <td class=\"min-width\"></td>
                            <td class=\"customize min-width\">" . _w("Key") . "</td>
                            <td class=\"customize min-width\">" . _w("Formula") . "</td>
                            <td>" . _w("Value") . "</td>
                            <td width=\"50\"></td>
                        </tr>
                    </thead>
                    <tbody>";
        $k = 0;
        foreach ($options as $o) {
            $id = $o['id'] ? "update" . $o['id'] : 0;
            $control .= "<tr class=\"" . ($k == 0 ? "" : "sortable") . "\" data-id=\"" . $o['id'] . "\">
                            <td><i class=\"icon16 sort\"" . ($k == 0 ? " style=\"display: none\"" : "") . "></i></td>
                            <td><input type=\"radio\" title=\"" . _w("Pre-selected option") . "\" value=\"{$id}\"" . (!empty($o['checked']) ? " checked" : "") . " name=\"options[default]\"></td>
                            <td class=\"customize\"><input type=\"text\" name=\"options[key][" . $id . "]\" value=\"" . multiformHelper::secureString($o['key']) . "\"></td>
                            <td class=\"customize\"><input type=\"text\" style=\"width: 50px !important\" name=\"options[formula][" . $id . "]\" value=\"" . multiformHelper::secureString($o['formula']) . "\"></td>
                            <td class=\"full-width option-value\"><input type=\"text\" name=\"options[value][" . $id . "]\" value=\"" . multiformHelper::secureString($o['value']) . "\"></td>
                            <td class=\"nowrap\">
                                <a href=\"#/add/optionField/\" class=\"js-action add-option\" title=\"" . _w("Add option") . "\"><i class=\"icon16 add\"></i></a>
                                <a href=\"#/delete/optionField/\" class=\"js-action delete-option\" title=\"" . _w("Delete option") . "\"" . ($k == 0 ? " style=\"display: none;\"" : "") . "><i class=\"icon16 delete\"></i></a>
                            </td>
                        </tr>";
            $k++;
        }
        $control .= "
                    </tbody>
                </table>
                <div class=\"align-center\" style=\"margin: 10px 0; position: relative\">
                    <a class=\"js-action\" style=\"position: absolute; left: 10px; top: 0\" href=\"cancel/optionDefaultValue\">" . _w("No default") . "</a>
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

    public function beforeOption_valuesSettingsSave($tab_id, $f_id, $field, &$data, $post_data, &$response, &$errors)
    {
        $option_model = new multiformFieldOptionsModel();
        $insert = $update = $delete = $keys = array();

        $options = ifempty($post_data['options'], array());
        $field_id = $field['id'];

        // Получаем ID всех вариантов для поля
        $option_ids = $option_model->getOptionIDs($field_id);

        if (!empty($options['value'])) {

            // Если необходимо указать свои ключи для вариантов, проверяем их на уникальность
            if (!empty($post_data['options']['customize_keys'])) {
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
                    $insert[] = array("field_id" => $field_id, "value" => $option, "sort" => $sort_value, "image" => "", "checked" => $checked, "key" => $key, "formula" => $options['formula'][$option_id]);
                }
                // Обновляем существующие варианты
                else {
                    $id = str_replace("update", "", $option_id);
                    // Сохраняем ID варианта (необходимо для получания вариантов, которые нужно удалить)
                    $update[] = $id;
                    $image = ifempty($options['image'][$option_id], "");
                    // Удаляем изображение, если ничего не было передано
                    if (!$image) {
                        multiformImage::deleteImage($id, $field_id);
                    }
                    $option_model->updateById($id, array("value" => $option, "sort" => $sort_value, "image" => $image, "checked" => $checked, "key" => $key, "formula" => $options['formula'][$option_id]));
                }
                $sort++;
            }
        }
        if ($insert) {
            $option_model->multipleInsert($insert);
            // Флаг, указывающий на необходимость обновления полей настроек
            $response['updateSettings'] = 1;
            // Флаг, указывающий на необходимость загрузки изображений для полей вариантов
            $response['uploadOptions'] = 1;
        }
        // ID, которые необходимо удалить
        $delete = array_diff($option_ids, $update);
        if ($delete) {
            $option_model->delete($delete, $field_id);
        }
    }

    public function validate_required($post_fields, $form_field)
    {
        // Проверка первого поля select. Если оно выбрано, считаем, что поле не заполнено
        if (empty($post_fields[$form_field['id']]['formatted']) || (!empty($post_fields[$form_field['id']]['formatted']) && !empty($form_field['option_fields'][$post_fields[$form_field['id']]['formatted']]) && $form_field['option_fields'][$post_fields[$form_field['id']]['formatted']]['sort'] == 0)) {
            return false;
        }
        return true;
    }

    public function validate_field($field, $value, $form_fields, $post_fields)
    {
        return array('field' => array($value['formatted'] => $value['value']));
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetSelectFieldControl',
            'title' => _w('New select field'),
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
                'info3' => array(
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
            'options' => array(
                'option_values' => array(
                    'title' => _w("Options"),
                    'after_title' => "(<a href='#/show/optionIds/' class='js-action' title='" . _w('Show option IDs') . "'>#</a>)",
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetOptionSelectSettingsControl',
                ),
                'customize_keys' => array(
                    'label' => _w('Customize keys'),
                    'class' => 'f-update-checkbox data-option-customize',
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
            ),
        );
    }

}
