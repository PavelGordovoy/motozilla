<?php

class multiformHTMLControlHelper
{

    /**
     * Register additional html-controls
     *
     */
    protected static function registerAdditionalControls()
    {
        static $inited = 0;
        if (!$inited) {
            waHtmlControl::registerControl('GetInfotitleControl', array(__CLASS__, "getInfotitleControl"));
            waHtmlControl::registerControl('GetCustomHTMLControl', array(__CLASS__, "getCustomHTMLControl"));
            waHtmlControl::registerControl('GetCodeSettingsControl', array(__CLASS__, "getCodeSettingsControl"));
            waHtmlControl::registerControl('GetAttributeSettingsControl', array(__CLASS__, "getAttributeSettingsControl"));
            waHtmlControl::registerControl('GetMaskSettingsControl', array(__CLASS__, "getMaskSettingsControl"));
            waHtmlControl::registerControl('GetRadiogroupControl', array(__CLASS__, "getRadiogroupControl"));
            waHtmlControl::registerControl('GetColorActionsControl', array(__CLASS__, "getColorActionsControl"));
            $inited = 1;
        }
    }

    /**
     * Register certain control
     *
     * @param string $control
     */
    public static function registerControl($control)
    {
        try {
            if (method_exists(__CLASS__, lcfirst($control))) {
                waHtmlControl::registerControl(ucfirst($control), array(__CLASS__, lcfirst($control)));
            }
        } catch (waException $ex) {

        }
    }

    /**
     * Get info title HTML
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public static function getInfotitleControl($name, $params)
    {
        $control = "<div class='infotext'>" . $params['value'] . "</div>";
        return $control;
    }

    /**
     * Get custom HTML
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public static function getCustomHTMLControl($name, $params)
    {
        $control = $params['value'];
        return $control;
    }

    /**
     * Get code setting field HTML
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public static function getCodeSettingsControl($name, $params)
    {
        $control_name = multiformHelper::secureString($name);
        $control = "<div class='small' style='margin-top: 5px;'>" . _w('Symbolic code') . ": " . ($params['value'] ? multiformHelper::secureString($params['value']) : "") . " ";
        $control .= "<a href='#/edit/symbolicCode/' class='js-action' title='" . _w('edit') . "'><i class='icon16 edit'></i>" . _w('edit') . "</a></div>";
        $control .= "<div style='display: none;'><label>" . _w('Symbolic code') . "</label>";
        $control .= "<input type=\"text\" onkeypress=\"$.multiform.isValidInput(event, /[0-9a-zA-Z\-_]/);\" name=\"{$control_name}\" value=\"{$params['value']}\">";
        $control .= "<div class='multiform-gap-description'>{$params['desc']}</div>";
        $control .= "</div>";
        return $control;
    }

    /**
     * Get attribute setting field HTML
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public static function getAttributeSettingsControl($name, $params = array())
    {
        $control = "<label>" . (!empty($params['custom_name']) ? $params['custom_name'] : _w("Custom attributes")) . "</label>";
        if (!empty($params['button_name'])) {
            $control .= '<div class="margin-block top">';
        }
        $control .= " <a href=\"#/add/attribute/\" title=\"" . _w("add attribute") . "\" class=\"inline-block js-action\"><i class=\"icon16 add\"></i>" . (!empty($params['button_name']) ? $params['button_name'] : '') . "</a>";
        if (!empty($params['button_name'])) {
            $control .= '</div>';
        }
        if (!empty($params['value']) || !empty($params['default'])) {
            $attributes = !empty($params['value']) ? unserialize($params['value']) : $params['default'];
            $control .= "<table class='attribute-table'>"
                . "<tr><th>" . _w("Name") . "</th><th>" . _w("Value") . "</th></tr>";
            foreach ($attributes as $attr) {
                $control .= "<tr><td style='width: 150px;'><input type='text' name='{$params['tab_id']}[attribute][name][]' value='{$attr['name']}'></td><td><input type='text' name='{$params['tab_id']}[attribute][value][]' value='{$attr['value']}'></td><td><a href='javascript:void(0)' onclick='$(this).closest(\"tr\").remove();'><i class='icon16 no'></i></a></td></tr>";
            }
            $control .= "</table>";
        }
        return $control;
    }

    /**
     * Get mask setting field HTML
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public static function getMaskSettingsControl($name, $params)
    {
        $control_name = multiformHelper::secureString($name);
        $value = isset($params['value']) ? $params['value'] : false;
        if ($value) {
            if (!isset($params['checked'])) {
                $params['checked'] = 'checked';
            }
        } elseif (isset($params['checked'])) {
            unset($params['checked']);
        }
        if (empty($params['value'])) {
            $params['value'] = 1;
        }
        $control = "<label for='{$params['id']}'>" . _w('Use regular expression') . "</label> <input id=\"{$params['id']}\" type='checkbox' name=\"{$control_name}\" ";
        $control .= self::addCustomParams(array('class', 'value', 'checked'), $params);
        $control .= ">";
        $control .= "<div class='fields'" . (!empty($params['checked']) ? " style='display: block'" : " style='display: none'") . ">";
        $control .= "<div class='field'>"
            . "<div class='name'><span>" . _w('Regular expression (pattern)') . "</span></div>"
            . "<div class='value'>"
            . "<textarea name='validation[regexp]'>{$params['regexp']}</textarea>"
            . "<div class=\"multiform-gap-description\">" . _w("Use only construction /pattern/") . "</div>"
            . "</div>"
            . "</div>";
        $control .= "<div class='field'>"
            . "<div class='name'><span>" . _w('Case insensitive') . "</span></div>"
            . "<div class='value'>"
            . "<input type='checkbox' value='i' name='validation[regexp_casein]'" . (!empty($params['regexp_casein']) ? " checked" : "") . ">"
            . "</div>"
            . "</div>";
        $mm = new multiformMaskModel();
        $masks = $mm->getAll("id");
        $control .= "<div class='field'>";
        $control .= "<div class='name'><span>" . _w('Mask') . "</span></div>";
        if ($masks) {
            $control .= "<div class='value'><select class='f-update-select data-mask-id'>";
            $control .= "<option value='0'>--- " . _w("Select the mask") . " ---</option>";
            foreach ($masks as $mask_id => $mask) {
                $control .= "<option value='" . $mask_id . "'>" . multiformHelper::secureString($mask['name']) . "</option>";
            }
            $control .= "</select></div>";
            $control .= "<script>$.form_edit.masks = " . json_encode($masks) . "</script>";
        } else {
            $control .= "<div class='value'>" . _w("You don't have any masks.") . "<a href='?module=masks' target='_blank'>" . _w("Read more") . "<i class='icon16 new-window'></i></a></div>";
        }
        $control .= "</div>";
        $control .= "<div class='field'><div class='name'><span>" . _w('Error message') . "</span></div><div class='value'><textarea name='validation[regexp_error]'>{$params['regexp_error']}</textarea></div></div>";
        $control .= "</div>";
        return $control;
    }

    /**
     * Radiogroup control
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public static function getRadiogroupControl($name, $params)
    {
        $control = '';
        $id = 0;
        $value = htmlentities((string) $params['value'], ENT_QUOTES, waHtmlControl::$default_charset);
        $options = isset($params['options']) ? (is_array($params['options']) ? $params['options'] : array($params['options'])) : array();
        foreach ($options as $option) {
            ++$id;
            $option_value = $option['value'];
            if ($option_value == $value) {
                $params['checked'] = 'checked';
            } elseif (isset($params['checked'])) {
                unset($params['checked']);
            }
            waHtmlControl::makeId($params, $name, md5($option_value));
            $params['id'] = $params['field_id'] . '_' . $params['id'];
            $option_value = multiformHelper::secureString((string) $option_value);
            $control_name = multiformHelper::secureString($name);
            $control .= "<input type=\"radio\" name=\"{$control_name}\" value=\"{$option_value}\"";
            $control .= self::addCustomParams(array('class', 'style', 'id', 'checked', 'readonly',), $params);
            if (!empty($option['title'])) {
                $option_title = multiformHelper::secureString($option['title']);
                $control .= ">&nbsp;<label";
                $control .= self::addCustomParams(array('id' => 'for',), $params);
                $control .= self::addCustomParams(array('description' => 'title', 'class', 'style',), $option);
                $control .= ">{$option_title}</label>\n";
            } else {
                $control .= ">\n";
            }

            $new_params = array_merge($params, array('description' => null), $option);
            if (!empty($new_params['description_wrapper']) && !empty($new_params['description'])) {
                $control .= sprintf($new_params['description_wrapper'], $new_params['description']);
            }
            if ($id < count($options)) {
                $control .= $params['control_separator'];
            }
        }
        return $control;
    }

    /**
     * Adding custom params from array to field
     *
     * @param array $list
     * @param array $params
     * @return string
     */
    protected static function addCustomParams($list, $params = array())
    {
        $params_string = '';
        foreach ($list as $param => $target) {
            if (is_int($param)) {
                $param = $target;
            }
            if (isset($params[$param])) {
                if ($param == 'class') {
                    if (isset($params['control_type']) && in_array($params['control_type'], $params['class'])) {
                        unset($params[$param][array_search($params['control_type'], $params['class'])]);
                    }
                    if ((is_array($params['class']) && count($params['class']) == 1 && !reset($params['class'])) || (!is_array($params['class']) && !$params['class'])) {
                        $params['class'] = false;
                    }
                }
                $param_value = $params[$param];
                if (is_array($param_value)) {
                    if (array_filter($param_value, 'is_array')) {
                        $param_value = json_encode($param_value);
                    } else {
                        $param_value = implode(' ', $param_value);
                    }
                }
                if ($param_value !== false) {
                    if (in_array($param, array('disabled', 'readonly'))) {
                        $param_value = $param;
                    } else {
                        $param_value = multiformHelper::secureString((string) $param_value);
                    }
                    if (in_array($param, array('autofocus'))) {
                        $params_string .= " {$target}";
                    } else {
                        $params_string .= " {$target}=\"{$param_value}\"";
                    }
                }
            }
        }
        if (!empty($params['attribute'])) {
            $attributes = unserialize($params['attribute']);
            foreach ($attributes as $attr) {
                $params_string .= " " . multiformHelper::secureString($attr['name']) . "=\"" . multiformHelper::secureString($attr['value']) . "\"";
            }
        }
        return $params_string;
    }

    public static function getFormFieldsControl($name, $params = array())
    {
        $control_name = multiformHelper::secureString($name);
        $control = '';
        $params['value'] = isset($params['value']) ? $params['value'] : '';
        if (empty($params['form_fields'])) {
            $control .= '<i class="icon16 exclamation-red"></i> ' . _w("Ooops, you don't have any fields. <a href='#/page/edit' title='Create field'><i class='icon16 add'></i> Create it</a>");
        } else {
            // Фильтруем поля
            if (!empty($params['filter'])) {
                $params['form_fields'] = array_filter($params['form_fields'], function ($v) use ($params) {
                    return in_array($v['type'], $params['filter']);
                });
            }
            // Удаляем ненужные поля
            if (!empty($params['exclude'])) {
                $params['form_fields'] = array_filter($params['form_fields'], function ($v) use ($params) {
                    return !in_array($v['type'], $params['exclude']);
                });
            }
            if (empty($params['form_fields'])) {
                $control .= '<i class="icon16 exclamation-red"></i> ' . _w("Ooops, you don't have any fields. <a href='#/page/edit' title='Create field'><i class='icon16 add'></i> Create it</a>");
            } else {
                $control = "<select name=\"{$control_name}\" " . self::addCustomParams(array('id', 'class', 'disabled', 'style'), $params) . ">";
                foreach ($params['form_fields'] as $ff) {
                    $control .= '<option value="' . $ff['id'] . '"' . ($ff['id'] == $params['value'] ? ' selected' : '') . '>' . multiformHelper::secureString($ff['title']) . ' (' . $ff['type'] . ')</option>';
                }
                $control .= "<select>";
            }
        }
        return $control;
    }

    public static function getColorActionsControl($name, $params = array())
    {
        $params['class'][] = 'colorpicker';
        $control_name = multiformHelper::secureString($name);
        $control = "<input type=\"text\" name=\"{$control_name}\" ";
        $control .= self::addCustomParams(array('id', 'class', 'value', 'disabled', 'position'), $params);
        $control .= ">";
        $control .= <<<HTML
            <script>
                $(function() {
                   $.form_edit.initColorpicker();     
                });
            </script>
HTML;
        return $control;
    }

    public static function getFileUpgradedActionsControl($name, $params = array())
    {
        $control = '';
        $control_name = multiformHelper::secureString($name);
        $file_params = array_merge(array("file_max_count" => 10, "file_max_size" => 2000000, 'file_extension' => array('jpg', 'jpeg', 'jpe', 'png')), $params);
        $control .= "<input" . ($file_params['file_max_count'] > 1 ? " multiple" : "") . " type=\"file\" data-name=\"{$params['field_id']}\" class=\"multiform-fileupload\" id=\"{$params['id']}\" name=\"files_{$params['field_id']}[]\">";

        // Определяем максимальный размер загружаемого файла согласно системным настройкам
        $ini_max_filesize = ini_get('upload_max_filesize');
        $ini_post_max_size = ini_get('post_max_size');
        $ini_max_filesize_converted = wa('multiform')->getConfig()->convertShortcuts($ini_max_filesize);
        $ini_post_max_size_converted = wa('multiform')->getConfig()->convertShortcuts($ini_post_max_size);
        $system_max_size = $ini_max_filesize_converted <= $ini_post_max_size_converted ? $ini_max_filesize_converted : $ini_post_max_size_converted;
        $file_params['file_max_size'] = ($file_params['file_max_size'] > $system_max_size || !$file_params['file_max_size']) ? $system_max_size : $file_params['file_max_size'];

        if ($file_params['file_extension'] !== '*') {
            $control .= "<div class='multiform-file-info extensions'>" . _wd('multiform', "Valid extensions") . ": " . implode(", ", $file_params['file_extension']) . "</div>";
        }
        if ($file_params['file_max_size']) {
            $max_size = $file_params['file_max_size'] / 1000;
            $control .= "<div class='multiform-file-info filesize'>" . _wd('multiform', "Maximum file size") . ": " . ($max_size < 1000 ? round($max_size, 2) . " KB" : round($max_size / 1000, 2) . " MB") . "</div>";
        }
        if ($file_params['file_max_count'] && $file_params['file_max_count'] > 1) {
            $control .= "<div class='multiform-file-info maxcount'>" . _wd('multiform', "Maximum files count") . ": {$file_params['file_max_count']}</div>";
        }

        $control .= "<script>$(function() {";
        $control .= "$.form_actions.initFileupload($(\"#{$params['id']}\"), { maxCount: {$file_params['file_max_count']}, maxSize: {$file_params['file_max_size']}" . ($file_params['file_extension'] !== '*' ? ', extensions:' . json_encode($file_params['file_extension']) : '') . " });";
        $control .= "});</script>";

        $control .= "<div class='multiform-loaded-files'>";
        if (!empty($params['value'])) {
            // Перебираем все файлы поля
            foreach ($params['value'] as $hash => $filename) {
                $path = multiformFiles::getActionFilePath($params['action_id'], $params['field_id'], $filename, empty($params['private']));
                if (file_exists($path)) {
                    $control .= '<div>';
                    $control .= '<a href="#/delete/file/" title="' . _w('Delete') . '" class="s-delete js-action">' . _w('delete') . ' <i class="icon10 no"></i></a>';
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    // Если перед нами изображение, выведем его
                    if (in_array($ext, array('jpg', 'jpeg', 'jpe', 'png', 'gif'))) {
                        try {
                            new waImage($path);
                            $url = multiformFiles::getActionFileUrl($params['action_id'], $params['field_id'], $filename, empty($params['private']));
                            $control .= "<img src='" . multiformHelper::secureString($url) . "'>";
                        } catch (waException $e) {
                            $control .= "<a title='" . _w('Download') . "' href='?module=actions&action=downloadFile&hash={$hash}'>" . multiformHelper::secureString($filename) . "</a>";
                        }
                    } else {
                        $control .= "<a title='" . _w('Download') . "' href='?module=actions&action=downloadFile&hash={$hash}'>" . multiformHelper::secureString($filename) . "</a>";
                    }
                    $control .= "<input type=\"hidden\"  name=\"data[{$params['field_id']}][$hash]\" value=\"$filename\">";
                    $control .= '</div>';
                }
            }
        }
        $control .= "</div>";
        return $control;
    }

    /**
     * Get field name
     *
     * @param array $field
     * @param string $form_uid
     * @return string
     */
    protected static function getControlName($field, $form_uid = "")
    {
        $name = 'field_' . $field['id'] . '_' . $form_uid;
        $field_name = $field['type'] == 'file' ? "files_{$field['id']}[]" : 'fields[' . $name . ']';
        if (!empty($field['data_name'])) {
            $field_name = $field['data_name'] . '[' . $name . ']';
            if ($field['type'] == 'file') {
                $field_name = $field['data_name'] . "files_{$field['id']}[]";
            }
        }

        return $field_name;
    }

}
