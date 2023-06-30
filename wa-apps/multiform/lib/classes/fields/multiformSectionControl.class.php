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
class multiformSectionControl extends multiformFormBuilder
{

    // Кол-во вызовов секции
    private static $count = -1;

    public function preExecute(&$params, $field)
    {
        $params['form_uid'] = self::getFormId();
        $params['edit_record_page'] = !empty($field['edit_record_page']) ? 1 : 0;

        $field_attr = array(
            "class" => multiformHelper::getFieldCssClasses($field) . ' multiform-section sort-list ' . $field['class'],
            "data-id" => $field['unique_id'],
            "data-type" => $field['type'],
            "data-field-id" => $field['id'],
            "id" => "multiformField" . $field['id'] . ($params['form_uid'] ? '_' . $params['form_uid'] : '')
        );

        if (wa()->getEnv() == 'backend') {
            $field_attr['class'] .= ((empty($field['fields']) && !$params['edit_record_page']) ? ' empty' : '') . (self::$count % 2 === 0 ? ' even' : '');
            $params['control_wrapper'] = "<div " . self::addCustomParams(array_keys($field_attr), $field_attr) . ">"
                . (!$params['edit_record_page'] ? "<div class='row-wrapper'>" : "")
                . "%s"
                . (!$params['edit_record_page'] ?
                    '<div class="multiform-gap-name">' . ($field['title'] ? multiformHelper::secureString($field['title']) : _w('Section')) . ' #' . $field['unique_id'] . '</div>'
                    : '')
                . "%s"
                . "%s"
//                    . "</div>" Данный закрывающийся тег будет проставлен в multiformFormBuilder
//                    . "</div>"
            ;
        } else {
            $field_attr['class'] .= (!empty($field['display']['layout']) && $field['display']['layout'] == 'horizontal' ? ' l-horiz' : '');
            $params['control_wrapper'] = "<div " . self::addCustomParams(array_keys($field_attr), $field_attr) . ">"//                    . "</div>" Данный закрывающийся тег будет проставлен в multiformFormBuilder
            ;
        }
    }

    public static function execute($name, $params)
    {
        $control = '';
        if (!$params['edit_record_page']) {
            $control_name = multiformHelper::secureString($name);
            $control .= "<input id=\"{$params['id']}\" type=\"hidden\" name=\"{$control_name}\" ";
            $control .= self::addCustomParams(array('class', 'value'), $params);
            $control .= ">";
        }

        return $control;
    }

    public function openSection($section)
    {
        self::$count++;
        return self::getFullControl($section);
    }

    public function closeSection()
    {
        self::$count--;
        return '</div></div>';
    }

    /**
     * Get multiply fields: add or remove
     *
     * @param array $section
     * @param string $action - add|remove
     * @param bool $hide
     * @return string
     */
    public function getMultiplyField($section, $action = 'add', $hide = false)
    {
        $text = !empty($section['properties']['multiply_link_add']) ? $section['properties']['multiply_link_add'] : _w('add additional field');
        $remove_text = !empty($section['properties']['multiply_link_remove']) ? $section['properties']['multiply_link_remove'] : _w('remove field');
        $data_attr = "data-limit='" . (!empty($section['properties']['multiply_max']) ? (int) $section['properties']['multiply_max'] : '') . "'";
        $data_attr .= " data-action='{$action}'";
        $html = "<div class='multiform-section-multiply' {$data_attr}" . ($hide ? ' style="display: none"' : '') . ">";

        $add_link = '<svg viewBox="0 0 438.533 438.533" class="multiform-section-multiply-icon"><use xlink:href="' . wa()->getAppStaticUrl('multiform', true) . '/img/svg/plus.svg#plus"></use></svg>';
        $add_link .= '<span>' . $text . '</span>';

        $remove_link = '<svg viewBox="0 0 438.533 438.533" class="multiform-section-multiply-icon"><use xlink:href="' . wa()->getAppStaticUrl('multiform', true) . '/img/svg/minus.svg#minus"></use></svg>';
        $remove_link .= '<span>' . $remove_text . '</span>';

        $link_data_attr = "data-action='{$action}'";
        $link_data_attr .= " data-remove-link='";
        $link_data_attr .= waString::escapeAll($remove_link);
        $link_data_attr .= "'";
        $link_data_attr .= " data-add-link='" . waString::escapeAll($add_link) . "'";

        $html .= "<div class='multiform-section-multiply-inner' {$link_data_attr}>";
        $html .= $action === 'add' ? $add_link : $remove_link;
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    public function afterSectionDelete($field)
    {
        $mf = new multiformFieldModel();
        $mfp = new multiformFieldParamsModel();
        $delete_all = waRequest::post('delete_all', 0);

        $field_params = $mfp->getByField(array('param' => 'additional', 'ext' => 'section', 'value' => $field['id']), true);
        if ($field_params) {
            foreach ($field_params as $fp) {
                if ($delete_all) {
                    $mf->delete($fp['field_id']);
                }
                $mfp->deleteByField(array('field_id' => $fp['field_id'], 'param' => 'additional', 'ext' => 'section', 'value' => $field['id']));
            }
        }
    }

    /**
     * Find all section children ids
     *
     * @param array $fields
     * @param int $id
     * @return array
     */
    public static function findAllChildrenIds($fields, $id)
    {
        $children = array();
        $collect_ids = 0;
        foreach ($fields as $f) {
            $parent_id = !empty($f['additional']['section']) ? $f['additional']['section'] : 0;
            if ($parent_id == $id) {
                $collect_ids = 1;
            }
            if (!$parent_id) {
                $collect_ids = 0;
            }
            if ($collect_ids) {
                $children[] = $f['id'];
            }
        }
        return $children;
    }

    /**
     * Get section child fields
     *
     * @param array $form_fields
     * @param string $action
     * @param array $child
     * @return array
     */
    public function addSectionChildsForFields(&$form_fields, $action = 'collect', $child = array())
    {
        if ($action == 'collect') {
            // Собираем информацию о детях секций
            foreach ($form_fields as $key => &$field) {
                if ($field['type'] == 'section') {
                    $field['childs'] = $field['all_childs'] = [];
                }
                if (!empty($field['additional']['section']) && isset($form_fields[$field['additional']['section']])) {
                    $form_fields[$field['additional']['section']]['childs'][$key] = $key;
                }
                unset($field);
            }
            foreach ($form_fields as $key => &$field) {
                if (!empty($field['childs'])) {
                    $field['all_childs'] = $field['childs'];
                    foreach ($field['childs'] as $child) {
                        if ($form_fields[$child]['type'] == 'section') {
                            $form_fields[$key]['all_childs'] += $this->addSectionChildsForFields($form_fields, 'get_childs', $form_fields[$child]['childs']);
                        }
                    }
                }
            }
        } else {
            $childs = array();
            foreach ($child as $key) {
                $childs[$key] = $key;
                if ($form_fields[$key]['type'] == 'section') {
                    $childs += $this->addSectionChildsForFields($form_fields, 'get_childs', $form_fields[$key]['childs']);
                }
            }
            return $childs;
        }
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetSectionFieldControl',
            'title' => _w('Section'),
            'properties' => array(
                'title' => array(
                    'title' => _w('Title'),
                    'value' => '',
                    'class' => 'f-update-text data-title data-section',
                    'basic' => 1,
                    'description' => _w('Visible only in admin area'),
                    'control_type' => waHtmlControl::INPUT,
                ),
                'code' => array(
                    'basic' => 1,
                    'desc' => _w('Valid characters') . ': <b>a-z</b>, <b>A-Z</b>, <b>0-9</b>, <b>-</b>, <b>_</b>',
                    'control_type' => 'GetCodeSettingsControl',
                ),
                'class' => array(
                    'title' => _w('CSS class'),
                    'value' => '',
                    'basic' => 1,
                    'control_type' => waHtmlControl::INPUT,
                ),
                'multiply_fields' => [
                    'label' => _w('Allow users to add extra fields'),
                    'value' => 0,
                    'filter' => 'int',
                    'childs' => [
                        'onopen_value' => 1,
                        'fields' => [
                            'multiply_link_add' => array(
                                'title' => _w('"Add button" text'),
                                'title_type' => 'inline',
                                'value' => _w('add additional field'),
                                'control_type' => waHtmlControl::INPUT,
                            ),
                            'multiply_link_remove' => array(
                                'title' => _w('"Remove button" text'),
                                'title_type' => 'inline',
                                'value' => _w('remove field'),
                                'control_type' => waHtmlControl::INPUT,
                            ),
                            'multiply_max' => array(
                                'title' => _w('Maximum times'),
                                'value' => '',
                                'title_type' => 'inline',
                                'placeholder' => '∞',
                                'class' => 'f-only-number small width100px',
                                'description' => _w("Maximum value of additional fields"),
                                'control_type' => waHtmlControl::INPUT,
                            ),
                        ]
                    ],
                    'control_type' => waHtmlControl::CHECKBOX,
                ],
                'color' => array(
                    'control_type' => 'GetColorActionsControl',
                    'title' => _w('Select the color of the section'),
                    'description' => _w('Useful for Records tab'),
                    'value' => '#ccffcc',
                    'title_type' => 'inline',
                    'position' => 'bottom-top'
                ),
                'attribute' => array(
                    'control_type' => 'GetAttributeSettingsControl',
                ),
            ),
            'display' => array(
                'info' => array(
                    'value' => _w('Layout'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'layout' => array(
                    'title' => _w('Fields layout'),
                    'value' => 'vertical',
                    'title_type' => 'inline',
                    'class' => 'f-update-select data-section',
                    'control_type' => waHtmlControl::SELECT,
                    'description' => _w('Select how fields inside section will be positioned.'),
                    'options' => array(
                        array('title' => _w('Horizontal'), 'value' => 'horizontal'),
                        array('title' => _w('Vertical'), 'value' => 'vertical'),
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
                'info' => array(
                    'value' => _w('Automatic mode for horizontal layout'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'autowidth' => array(
                    'label' => _w('Automatically change sizes of the elements in the section'),
                    'value' => 1,
                    'filter' => 'int',
                    'description' => _w('Disable this option, if you want to control the sizes (title width, field width, etc) of each element'),
                    'control_type' => waHtmlControl::CHECKBOX,
                    'childs' => [
                        'onopen_value' => 1,
                        'fields' => [
                            'all_block_width' => array(
                                'title' => _w('Width of each block'),
                                'value' => 'auto',
                                'title_type' => 'inline',
                                'control_type' => waHtmlControl::SELECT,
                                'options' => multiformHelper::getWidthOptionValues(true),
                            ),
                            'field_width' => array(
                                'title' => _w('Width of all fields'),
                                'value' => 'auto',
                                'title_type' => 'inline',
                                'control_type' => waHtmlControl::SELECT,
                                'description' => _w('Titles will be calculated depending on fields width'),
                                'options' => multiformHelper::getWidthOptionValues(true),
                            ),
                        ]
                    ]
                ),
                'info2' => array(
                    'value' => _w('Size settings'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'block_width' => array(
                    'title' => _w('Section width'),
                    'value' => 'colm12',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => multiformHelper::getWidthOptionValues(),
                ),
            ),
        );
    }

}
