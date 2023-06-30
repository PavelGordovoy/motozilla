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
class multiformButtonControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['value'] = (!empty($field['properties']['name']) ? $field['properties']['name'] : $params['properties']['name']['value']);
        $params['action'] = (!empty($field['properties']['action']) ? $field['properties']['action'] : $params['properties']['action']['value']);
        $params['page_link'] = (!empty($field['properties']['page_link']) ? $field['properties']['page_link'] : $params['properties']['page_link']['value']);
        $params['onblank'] = (!empty($field['properties']['onblank']) ? $field['properties']['onblank'] : $params['properties']['onblank']['value']);
        $params['default_button_css'] = (!empty($field['display']['default_button_css']) ? $field['display']['default_button_css'] : $params['display']['default_button_css']['value']);
        $params['default_button_block_css'] = (!empty($field['display']['default_button_block_css']) ? $field['display']['default_button_block_css'] : $params['display']['default_button_block_css']['value']);
        $params['attribute'] = (!empty($field['properties']['attribute']) ? $field['properties']['attribute'] : "");
    }

    public static function execute($name, $params)
    {
        $control = '';

        // Если переданы значения записи
        if (isset($params['record_value'])) {
            $params['value'] = (isset($params['record_value']) && $params['record_value'] !== '') ? $params['record_value'] : '';
        }

        if (wa()->getEnv() == 'frontend' || !isset($params['record_value'])) {
            $params['class'][] = multiformHelper::getFieldSizeWidth($params['field']);
        }

        if (!empty($params['default_button_block_css'])) {
            $control .= "<div class='multiform-submit'>";
        }
        // Используем стандартные стили для кнопок
        if (!empty($params['default_button_css'])) {
            $params['class'][] = 'mf-button';
        }
        $control .= "<button data-{$params['action']} ";
        $control .= self::addCustomParams(array('class'), $params);
        if ($params['action'] == 'redirect') {
            $control .= " data-link='" . multiformHelper::secureString($params['page_link']) . "'";
            $control .= " data-onblank='" . (int) $params['onblank'] . "'";
        }
        $control .= ">";
        $control .= $params['value'];
        $control .= "</button>";
        if (!empty($params['default_button_block_css'])) {
            $control .= "</div>";
        }
        return $control;
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetButtonFieldControl',
            'title' => _w('New button'),
            'properties' => array(
                'title' => array(
                    'title' => _w('Title'),
                    'value' => '',
                    'class' => 'f-update-text data-title',
                    'basic' => 1,
                    'hidden' => 1,
                    'control_type' => waHtmlControl::INPUT,
                ),
                'name' => array(
                    'title' => _w('Name'),
                    'value' => _w('Send'),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'action' => array(
                    'title' => _w('Onclick action'),
                    'value' => 'submit',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => array(
                        array('title' => _w('Form submit'), 'value' => 'submit'),
                        array('title' => _w('Redirect'), 'value' => 'redirect'),
                    ),
                    'childs' => [
                        'onopen_value' => 'redirect',
                        'fields' => [
                            'page_link' => array(
                                'title' => _w('Page link'),
                                'title_type' => 'inline',
                                'value' => '',
                                'control_type' => waHtmlControl::INPUT,
                            ),
                            'onblank' => array(
                                'title' => _w('Open in new tab'),
                                'title_type' => 'inline',
                                'value' => 0,
                                'control_type' => waHtmlControl::CHECKBOX,
                            ),
                        ]
                    ],
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
                'attribute' => array(
                    'control_type' => 'GetAttributeSettingsControl',
                ),
            ),
            'display' => array(
                'info' => array(
                    'value' => _w('Appearance'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'default_button_css' => array(
                    'label' => _w('Use default button styles'),
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w('Button will have CSS styles as on the tab Appearance'),
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'default_button_block_css' => array(
                    'label' => _w('Use default button block styles'),
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w('Button block will have CSS styles as on the tab Appearance'),
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
                'title_pos' => array(
                    'hidden' => 1,
                    'title' => _w('Title position'),
                    'value' => 'hide',
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
                'info2' => array(
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
            ),
            'sizes' => array(
                'block_width' => array(
                    'title' => _w('Block width'),
                    'value' => 'colm12',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => multiformHelper::getWidthOptionValues(),
                ),
                'field_width' => array(
                    'title' => _w('Field width'),
                    'value' => 'colm12',
                    'title_type' => 'inline',
                    'hidden' => 1,
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
        );
    }

}
