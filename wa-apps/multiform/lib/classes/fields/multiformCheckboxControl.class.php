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
class multiformCheckboxControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['field_id'] = $field['id'];
        $params['option_fields'] = isset($field['option_fields']) ? $field['option_fields'] : array();
        $params['customize_keys'] = isset($field['options']['customize_keys']) ? $field['options']['customize_keys'] : $params['options']['customize_keys']['value'];
        $params['options_layout'] = isset($field['display']['options_layout']) ? $field['display']['options_layout'] : $params['display']['options_layout']['value'];
        $params['option_image_size'] = (!empty($field['options']['option_width']) ? $field['options']['option_width'] : 50) . "x" . (!empty($field['options']['option_height']) ? $field['options']['option_height'] : 50);
        $params['randomize'] = isset($field['display']['randomize']) ? $field['display']['randomize'] : $params['display']['randomize']['value'];

        // Добавляем атрибуты к полю, если имеются ограничения по кол-ву чекбоксов
        $params['field_attr'] = array();
        if (!empty($field['validation']['min'])) {
            $params['field_attr']['data-min'] = (int) $field['validation']['min'];
        }
        if (!empty($field['validation']['max'])) {
            $params['field_attr']['data-max'] = (int) $field['validation']['max'];
        }
    }

    public static function execute($name, $params)
    {
        $control_name = multiformHelper::secureString($name);
        $control = "";
        if ($params['option_fields']) {
            // Если необходимо перемешать варианты в случайном порядке
            if (!empty($params['randomize'])) {
                $new = array();
                $keys = array_keys($params['option_fields']);
                shuffle($keys);
                foreach ($keys as $key) {
                    $new[$key] = $params['option_fields'][$key];
                }
                $params['option_fields'] = $new;
            }
            $control .= "<div" . ($params['class'] ? " " . self::addCustomParams(array('class'), $params) : "") . " id=\"{$params['id']}\">";
            $params['class'] = array("multiform-gap-option");
            $params['class'][] = $params['options_layout'] . "-layout";
            $image_size = (strpos($params['option_image_size'], 'x') !== false) ? explode("x", $params['option_image_size']) : array();

            foreach ($params['option_fields'] as $option_id => $option) {
                // Если переданы значения записи
                if (isset($params['record_value'])) {
                    $option['checked'] = isset($params['record_value'][$option_id]) ? 1 : 0;
                }
                $control .= "<div " . self::addCustomParams(array('class'), $params) . ">";
                $control .= "<label for=\"{$control_name}-{$option_id}\">";
                $control .= "<input type=\"" . $params['type'] . "\" data-formula=\"" . multiformHelper::secureString($option['formula']) . "\" id=\"{$control_name}-{$option_id}\" name=\"{$control_name}[{$option_id}]\" value=\"" . $option_id . "\"" . ($option['checked'] ? " checked" : "");
                if (!empty($params['disabled']) && empty($params['readonly'])) {
                    $control .= " disabled>";
                } else if (!empty($params['readonly'])) {
                    $control .= " disabled>";
                    if ($option['checked']) {
                        $control .= "<input type=\"hidden\"" . (!empty($params['disabled']) ? " disabled" : "") . " data-formula=\"" . multiformHelper::secureString($option['formula']) . "\" name=\"{$control_name}[{$option_id}]\" value=\"" . $option_id . "\">";
                    }
                } else {
                    $control .= ">";
                }
                if ($option['image']) {
                    $control .= "<img alt=\"" . multiformHelper::secureString(strip_tags($option['value'])) . "\"" . ($image_size ? " style=\"max-width: " . $image_size[0] . "px; max-height: " . $image_size[1] . "px\"" : "") . " src=\"" . multiformImage::getOptionImage($option_id, $params['field_id'], $option['image'], $params['option_image_size']) . "\">";
                }
                $control .= "<span>" . strip_tags($option['value'], '<a>') . "</span>";
                $control .= "</label>";
                // Вывод дополнительного текстового поля
                if ($option['sort'] == 999999) {
                    $control .= "<br><input type=\"text\" name=\"{$control_name}[{$option_id}]\" class=\"multiform-other-field\" onfocus=\"$(this).closest('.multiform-gap-option').find(':radio').prop('checked', true)\">";
                }
                $control .= "</div>";
            }
            $control .= "</div>";
        }

        return $control;
    }

    public function prepareOption_image_sizeSettingsParams(&$params, $field, $tab_id, $property_id)
    {
        $params['option_width'] = !empty($field['options']['option_width']) ? $field['options']['option_width'] : 50;
        $params['option_height'] = !empty($field['options']['option_height']) ? $field['options']['option_height'] : 50;
    }

    public function prepareOption_valuesSettingsParams(&$params, $field, $tab_id, $property_id)
    {
        $params['field'] = $field;
    }

    public function GetOptionCheckboxSettingsControl($name, $params)
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
            $control .= "<tr class=\"" . ((!empty($field['options']['allow_other']) && $o['sort'] == '999999') ? "other-field" : "sortable") . "\" data-id=\"" . $o['id'] . "\">
                            <td>" . ((!empty($field['options']['allow_other']) && $o['sort'] == '999999') ? "" : "<i class=\"icon16 sort\"></i>") . "</td>
                            <td><input type=\"" . $field['type'] . "\" title=\"" . _w("Pre-selected option") . "\" name=\"options[default][" . $id . "]\" value=\"" . $o['id'] . "\"" . (!empty($o['checked']) ? " checked" : "") . "></td>
                            <td><input name=\"image\" type=\"file\" class=\"fileupload-option-images hidden\"><a href=\"#/add/optionImage/\" class=\"js-action image-cell\" title=\"" . _w("Add image") . "\">" . ($o['image'] ? "<span class=\"delete-block\">" . _w("delete") . "</span><img src=\"" . multiformImage::getOptionImage($o['id'], $field['id'], $o['image'], '50x50') . "\">" . "<input type=\"hidden\" name=\"options[image][" . $id . "]\" value=\"" . $o['image'] . "\">" : "<i class=\"icon16 image\"></i>") . "</a></td>
                            <td class=\"customize\">" . ((!empty($field['options']['allow_other']) && $o['sort'] == '999999') ? "" : "<input type=\"text\" name=\"options[key][" . $id . "]\" value=\"" . multiformHelper::secureString($o['key']) . "\">") . "</td>
                            <td class=\"customize\">" . ((!empty($field['options']['allow_other']) && $o['sort'] == '999999') ? "" : "<input type=\"text\" style=\"width: 50px !important\" name=\"options[formula][" . $id . "]\" value=\"" . multiformHelper::secureString($o['formula']) . "\">") . "</td>
                            <td class=\"full-width option-value\"><input type=\"text\" name=\"options[value][" . $id . "]\" value=\"" . multiformHelper::secureString($o['value']) . "\"" . ((!empty($field['options']['allow_other']) && $o['sort'] == '999999') ? " placeholder=\"" . _w("Other") . "\"" : "") . "></td>
                            <td class=\"nowrap\">" . ((!empty($field['options']['allow_other']) && $o['sort'] == '999999') ?
                    "<input type=\"hidden\" name=\"options[sort][" . $id . "]\" value=\"999999\">" :
                    "<a href=\"#/add/optionField/\" class=\"js-action add-option\" title=\"" . _w("Add option") . "\"><i class=\"icon16 add\"></i></a>
                             <a href=\"#/delete/optionField/\" class=\"js-action delete-option\" title=\"" . _w("Delete option") . "\"><i class=\"icon16 delete\"></i></a>"
                ) . "
                            </td>
                        </tr>
                        <tr data-image-status-id=\"" . $o['id'] . "\" class=\"hidden\">
                            <td></td>
                            <td></td>
                            <td colspan=\"5\" class=\"image-info\"></td>
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

    public function GetOptionImageSizeCheckboxSettingsControl($name, $params)
    {
        $control = "<input type\"text\" name=\"options[option_width]\" value=\"{$params['option_width']}\" style=\"width: 50px\">";
        $control .= " x ";
        $control .= "<input type\"text\" name=\"options[option_height]\" value=\"{$params['option_height']}\" style=\"width: 50px\">";
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

                // Добавляем новые варианты
                if (strpos($option_id, "new") !== false) {
                    $checked = (!empty($options['default'][$option_id]) ? 1 : 0);
                    $insert[] = array("field_id" => $field_id, "value" => $option, "sort" => $sort_value, "image" => "", "checked" => $checked, "key" => $key, "formula" => $options['formula'][$option_id]);
                } // Обновляем существующие варианты
                else {
                    $id = str_replace("update", "", $option_id);
                    // Сохраняем ID варианта (необходимо для получания вариантов, которые нужно удалить)
                    $update[] = $id;
                    $checked = (!empty($options['default'][$option_id]) ? 1 : 0);
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

    public function beforeOption_image_sizeSettingsSave($tab_id, $f_id, $field, &$data, $post_data)
    {
        $data[$tab_id]['option_width'] = (int) $post_data[$tab_id]['option_width'];
        $data[$tab_id]['option_width'] = $data[$tab_id]['option_width'] <= 0 ? 50 : $data[$tab_id]['option_width'];
        $data[$tab_id]['option_height'] = (int) $post_data[$tab_id]['option_height'];
        $data[$tab_id]['option_height'] = $data[$tab_id]['option_height'] <= 0 ? 50 : $data[$tab_id]['option_height'];
    }

    public function validate_field($field, $value, $form_fields, $post_fields)
    {
        $result = array('errors' => array());
        if (!empty($field['validation']['min']) && count($value) < (int) $field['validation']['min']) {
            $result['errors']['messages']['checkbox_error1_' . $field['id']] = sprintf(_wd(self::$app, 'You have to select at least %d checkboxes'), (int) $field['validation']['min']) . ' ' . _wd(self::$app, 'for the field') . ' ' . multiformHelper::secureString($field['title']);
        }
        if (!empty($field['validation']['max']) && count($value) > (int) $field['validation']['max']) {
            $result['errors']['messages']['checkbox_error1_' . $field['id']] = sprintf(_wd(self::$app, 'Checkboxes cannot exceed %d selected values'), (int) $field['validation']['max']) . ' ' . _wd(self::$app, 'for the field') . ' ' . multiformHelper::secureString($field['title']);
        }

        return $result;
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetCheckboxFieldControl',
            'title' => _w('New checkbox field'),
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
                    'value' => _w('Options'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'options_layout' => array(
                    'title' => _w('Options layout'),
                    'value' => 'one',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'class' => 'f-update-select data-options-layout',
                    'options' => array(
                        array('title' => _w('One column'), 'value' => 'one'),
                        array('title' => _w('Two columns'), 'value' => 'two'),
                        array('title' => _w('Three columns'), 'value' => 'three'),
                        array('title' => _w('One by one'), 'value' => 'line'),
                    ),
                ),
                'randomize' => array(
                    'label' => _w('Randomize'),
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w('The options will be shuffled each time'),
                    'control_type' => waHtmlControl::CHECKBOX,
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
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetOptionCheckboxSettingsControl',
                ),
                'customize_keys' => array(
                    'label' => _w('Customize keys'),
                    'class' => 'f-update-checkbox data-option-customize',
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w('Show to user one value, but save in DB another'),
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'option_image_size' => array(
                    'title' => _w('Option image size'),
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetOptionImageSizeCheckboxSettingsControl',
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
                'info' => array(
                    'value' => _w('Number of checked boxes'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'min' => array(
                    'title' => _w('Minimal number of checked boxes'),
                    'value' => '',
                    'class' => 'f-only-number small width100px',
                    'description' => _w("Minimal number of boxes, which user have to select to submit the form"),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'max' => array(
                    'title' => _w('Maximum number of checked boxes'),
                    'value' => '',
                    'class' => 'f-only-number small width100px',
                    'description' => _w("Maximal number of checked boxes, which user cannot exceed"),
                    'control_type' => waHtmlControl::INPUT,
                ),
            ),
        );
    }

}
