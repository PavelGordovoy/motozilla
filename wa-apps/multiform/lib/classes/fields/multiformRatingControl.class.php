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
class multiformRatingControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['value'] = (isset($field['properties']['default']) ? $field['properties']['default'] : (isset($params['value']) ? $params['value'] : 0));
        $params['size'] = (isset($field['sizes']['width']) ? $field['sizes']['width'] : $params['sizes']['width']['value']);
    }

    public static function execute($name, $params)
    {
        static $js_inline = "";
        $control = "";
        if (!$js_inline) {
            $js_inline = <<<HTML
            $(document).off("click", ".multiform-rating:not('.disabled') label").on("click", ".multiform-rating:not('.disabled') label", function() {
               var that = $(this);
               that.find("i").addClass("star-full").removeClass("star-empty");
               that.prevAll("label").find("i").removeClass("star-full").addClass("star-empty"); 
               that.nextAll("label").find("i").addClass("star-full").removeClass("star-empty"); 
            });        
            $(document).off("click", ".multiform-rating a").on("click", ".multiform-rating a", function() {
               var that = $(this);
               that.siblings("input").prop("checked", false).change();
               that.prevAll("label").add(that.nextAll("label")).find("i").removeClass("star-full").addClass("star-empty");
               that.closest(".multiform-wrap").change();
               that.after("<input type>")
            });        
HTML;
            self::addToJs($js_inline);
        }
        $control_name = multiformHelper::secureString($name);

        $params['class'][] = "multiform-rating";
        if (!empty($params['readonly'])) {
            $params['class'][] = "disabled";
        }
        $control .= "<div id=\"{$params['id']}\" style=\"display: inline-block; vertical-align: middle;\" " . self::addCustomParams(array('class'), $params) . ">";
        if (empty($params['readonly'])) {
            $control .= "<a href=\"javascript:void(0)\" class=\"{$params['size']}\" title=\"" . _wd(self::$app, "reset") . "\"><i class=\"mf circle-slash\"></i></a>";
        }

        // Если переданы значения записи
        if (isset($params['record_value'])) {
            $params['value'] = (isset($params['record_value']) && $params['record_value'] !== '') ? $params['record_value'] : '';
        }

        $control .= "<input type=\"hidden\" name=\"{$control_name}\" value=\"0\">";
        for ($i = 5; $i >= 1; $i--) {
            $control .= "<input id=\"{$control_name}-{$i}\"" . (!empty($params['disabled']) ? " disabled" : "") . " type=\"radio\" name=\"{$control_name}\" value=\"{$i}\"" . ($params['value'] == $i ? " checked" : "") . ">";
            $control .= "<label for=\"{$control_name}-{$i}\" class=\"{$params['size']}" . ($i === 1 ? ' last' : '') . "\"><i class=\"mf " . ($params['value'] >= $i ? "star-full" : "star-empty") . "\"></i></label>";
        }
        $control .= "</div>";

        return $control;
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetRatingFieldControl',
            'title' => _w('New rating field'),
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
                    'value' => '0',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => array(
                        array('title' => _w('No predefined value'), 'value' => '0'),
                        array('title' => _w('1'), 'value' => '1'),
                        array('title' => _w('2'), 'value' => '2'),
                        array('title' => _w('3'), 'value' => '3'),
                        array('title' => _w('4'), 'value' => '4'),
                        array('title' => _w('5'), 'value' => '5'),
                    ),
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
                    'value' => _w('Field properties'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'disabled' => array(
                    'label' => _w('Read only'),
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
                'width' => array(
                    'title' => _w('Star sizes'),
                    'value' => 'small',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => array(
                        array('title' => _w('Small '), 'value' => 'small'),
                        array('title' => _w('Medium '), 'value' => 'medium'),
                        array('title' => _w('Large '), 'value' => 'large'),
                    ),
                ),
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
