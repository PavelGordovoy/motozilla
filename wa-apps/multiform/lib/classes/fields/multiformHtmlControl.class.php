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
class multiformHtmlControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['value'] = isset($field['description']) ? $field['description'] : $params['properties']['default_html']['value'];
        $params['description'] = "";
        $params['form_uid'] = self::getFormId();

        $field_attr = array(
            "class" => multiformHelper::getFieldCssClasses($field) . ' ' . $field['class'],
            "data-id" => $field['unique_id'],
            "data-type" => $field['type'],
            "data-field-id" => $field['id'],
            "id" => "multiformField" . $field['id'] . ($params['form_uid'] ? '_' . $params['form_uid'] : '')
        );
        if (!empty($field['condition_id'])) {
            $field_attr["data-condition"] = $field['condition_id'];
        }
        $params['control_wrapper'] = "<div " . self::addCustomParams(array_keys($field_attr), $field_attr) . ">"
            . "%s"
            . "<div class='multiform-gap-value " . multiformHelper::getFieldWidth($field) . "'>"
            . (!empty($field['display']['prefix']) ? "<div class='prefix'>" . $field['display']['prefix'] . "</div>" : "")
            . "%s"
            . (!empty($field['display']['suffix']) ? "<div class='suffix'>" . $field['display']['suffix'] . "</div>" : "")
            . "%s"
            . "</div>"
            . "</div>";
    }

    public static function execute($name, $params)
    {
        // Если переданы значения записи
        if (isset($params['record_value'])) {
            $params['value'] = (isset($params['record_value']) && $params['record_value'] !== '') ? $params['record_value'] : '';
        }
        return $params['value'];
    }

    public function prepareDefault_htmlSettingsParams(&$params, $field, $tab_id, $property_id)
    {
        $params['value'] = isset($field['description']) ? $field['description'] : $params['value'];
    }

    public function GetHtmlDefaultSettingsControl($name, $params)
    {
        static $js = "";
        static $images_json = array();

        $form_id = waRequest::request('id', 0, waRequest::TYPE_INT);

        $control = "";
        $control_name = multiformHelper::secureString($name);
        // Подключение скриптов редактора
        $lang = substr(wa()->getLocale(), 0, 2);
        if (!$js) {
            $js .= "<link rel=\"stylesheet\" href=\"" . wa()->getRootUrl(true) . "wa-content/js/redactor/2/redactor.css\">
                        <script src=\"" . wa()->getRootUrl(true) . "wa-content/js/redactor/2/redactor.min.js?v=" . wa()->getVersion() . "\"></script>";
            if ($lang != 'en') {
                $js .= "<script src=\"" . wa()->getRootUrl(true) . "wa-content/js/redactor/2/{$lang}.js?v=" . wa()->getVersion() . "\"></script>";
            }
            $js .= "<script src=\"" . wa()->getAppStaticUrl('multiform') . "js/redactor/imagemanager.js?v=" . wa()->getVersion() . "\"></script>";
            $control .= multiformHelper::csrf();
            $control .= $js;

            // Загруженные фото через визуальный редактор
            $image_url = 'multiform/' . $form_id . '/';
            $images_path = wa()->getDataPath($image_url, true, 'site');
            $images = multiformFiles::listdir($images_path, true, 100);
            if ($images) {
                // Каталог с миниатюрами
                $thumbs_url = 'thumbs/' . $form_id . '/';
                $thumbs_dir = wa()->getDataPath($thumbs_url, true, 'multiform');
                foreach ($images as $k => $image) {
                    try {
                        // Проверяем, чтобы нам попадались только изображения
                        $img = new waImage($images_path . $image);
                        $images_json[] = array(
                            "thumb" => file_exists($thumbs_dir . $image) ? wa()->getDataUrl($thumbs_url . $image, true, 'multiform', true) : wa()->getDataUrl($image_url . $image, true, 'site', true),
                            "url" => wa()->getDataUrl($image_url . $image, true, 'site', true),
                            "id" => $k
                        );
                    } catch (Exception $ex) {
                        continue;
                    }
                }
            }
            // Формируем JSON массив загруженных изображений и отправляем его в редактор
            $images_json = json_encode($images_json);
        }

        $control .= "<textarea name=\"{$control_name}\" id=\"{$params['id']}\">{$params['value']}</textarea>";
        $control .= <<<HTML
                    <script>
                        $(function(){
                            $('#{$params['id']}').redactor({
                                source: false,
                                paragraphy: false,
                                replaceDivs: false,
                                toolbarFixed: true,
                                replaceTags: false,
                                removeNewlines: false,
                                removeComments: false,
                                buttons: ['format', 'bold', 'italic', 'underline', 'deleted', 'lists',
                                     'image', 'video', 'table', 'link', 'alignment',
                                    'horizontalrule',  'fontcolor', 'fontsize', 'fontfamily'],
                                plugins: ['fontcolor', 'fontfamily', 'alignment', 'fontsize', 'table', 'video', 'source', 'imagemanager'],
                                imageUpload: '?module=form&action=redactorImageUpload&form_id={$form_id}',
                                imageManagerJson: {$images_json},
                                imageUploadParam: 'image',
                                deniedTags: false,
                                multipleImageUpload: false,
                                imageUploadFields: '[name="_csrf"]:first',
                                lang: '{$lang}',
                                imageUploadErrorCallback: function(json, xhr) {
                                    if (typeof json.error !== 'undefined') {
                                        alert(json.error);
                                    }
                                }
                            });
                        });
                    </script>
HTML;

        return $control;
    }

    public function beforeDefault_htmlSettingsSave($tab_id, $f_id, $field, &$data, $post_data)
    {
        $data['field']['description'] = $post_data[$tab_id][$f_id];
        unset($data[$tab_id][$f_id]);
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetHtmlFieldControl',
            'title' => _w('New html field'),
            'properties' => array(
                'title' => array(
                    'title' => _w('Title'),
                    'value' => '',
                    'class' => 'f-update-text data-title',
                    'basic' => 1,
                    'control_type' => waHtmlControl::INPUT,
                ),
                'class' => array(
                    'title' => _w('CSS class'),
                    'value' => '',
                    'basic' => 1,
                    'control_type' => waHtmlControl::INPUT,
                ),
                'default_html' => array(
                    'title' => _w('HTML code'),
                    'value' => _w('<p>This is <b>HTML</b> field.</p><p>Use clean markup!</p>'),
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetHtmlDefaultSettingsControl',
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
            ),
        );
    }

}
