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
class multiformFileControl extends multiformFormBuilder
{

    public function preExecute(&$params, $field)
    {
        $params['attach_file_checkbox'] = isset($field['display']['attach_file_checkbox']) ? $field['display']['attach_file_checkbox'] : $params['display']['attach_file_checkbox']['value'];
        // Настройки для прикрепленного файла
        if ($params['attach_file_checkbox']) {
            if (!empty($field['display']['attach_file'])) {
                $file_info = unserialize($field['display']['attach_file']);
                $params['attach_file'] = $file_info['hash'];
            }
            $params['attach_file_name'] = !empty($field['display']['attach_file_name']) ? $field['display']['attach_file_name'] : (!empty($file_info['name']) ? $file_info['name'] : $params['display']['attach_file_name']['value']);
            $params['attach_file_before_text'] = !empty($field['display']['attach_file_before_text']) ? $field['display']['attach_file_before_text'] : "";
            $params['attach_file_after_text'] = !empty($field['display']['attach_file_after_text']) ? $field['display']['attach_file_after_text'] : "";
            $params['attach_file_pos'] = isset($field['display']['attach_file_pos']) ? $field['display']['attach_file_pos'] : $params['display']['attach_file_pos']['value'];
            $params['hide_file'] = isset($field['display']['hide_file']) ? $field['display']['hide_file'] : $params['display']['hide_file']['value'];
        }
        $params['file_max_count'] = !empty($field['validation']['file_max_count']) ? $field['validation']['file_max_count'] : 0;
        $params['file_max_size'] = !empty($field['validation']['file_max_size']) ? $field['validation']['file_max_size'] : 0;
        $params['button_name'] = isset($field['properties']['button_name']) ? $field['properties']['button_name'] : $params['properties']['button_name']['value'];
        $params['file_extension'] = array();
        $params['file_extension'] = !empty($field['validation']['file_extension']) ? unserialize($field['validation']['file_extension']) : array();
        $params['hide_file_requirements'] = isset($field['validation']['hide_file_requirements']) ? $field['validation']['hide_file_requirements'] : $params['validation']['hide_file_requirements']['value'];
        $params['field_id'] = $field['id'];
        $params['unique_id'] = $field['unique_id'];
        $params['edit_record_page'] = !empty($field['edit_record_page']) ? 1 : 0;
    }

    public static function execute($name, $params)
    {
        static $initJs = '';

        $control = $js = $js_inline = '';
        if (wa()->getEnv() == 'frontend' || (wa()->getEnv() == 'backend' && $params['edit_record_page'])) {
            $js .= '<script src="' . wa()->getAppStaticUrl('multiform', true) . 'js/jquery-fileupload/js/vendor/jquery.ui.widget.js"></script>
                        <script src="' . wa()->getAppStaticUrl('multiform', true) . 'js/jquery-fileupload/js/jquery.iframe-transport.js"></script>
                        <script src="' . wa()->getAppStaticUrl('multiform', true) . 'js/jquery-fileupload/js/jquery.fileupload.js"></script>
                        <script src="' . wa()->getAppStaticUrl('multiform', true) . 'js/jquery-fileupload/js/jquery.fileupload-process.js"></script>
                        <script src="' . wa()->getAppStaticUrl('multiform', true) . 'js/jquery-fileupload/js/jquery.fileupload-validate.js"></script>';
            self::addToJs($js, 'file');
        }

        $control_name = multiformHelper::secureString($name);
        $attachment_block = "";
        if ($params['attach_file_checkbox'] && !empty($params['attach_file'])) {
            $attachment_block = "<div class=\"multiform-attachment\">"
                . ($params['attach_file_before_text'] ? "<div class=\"multiform-attachment-text before\">" . $params['attach_file_before_text'] . "</div>" : "")
                . "<a href=\"" . (wa()->getEnv() == 'frontend' ? wa()->getRouteUrl('multiform/frontend/downloadAttachment', array("hash" => $params['attach_file'], "domain" => wa('multiform')->getConfig()->getDomain()), true) : "?module=frontend&action=downloadAttachment&hash=" . $params['attach_file']) . "\" title=\"" . multiformHelper::secureString(strip_tags($params['attach_file_name'])) . "\">{$params['attach_file_name']}</a>"
                . ($params['attach_file_after_text'] ? "<div class=\"multiform-attachment-text after\">" . $params['attach_file_after_text'] . "</div>" : "")
                . "</div>";
        }
        $control .= "<div" . ($params['class'] ? " " . self::addCustomParams(array('class'), $params) : "") . ">";
        if (!empty($params['attach_file_pos']) && $params['attach_file_pos'] == 'top') {
            $control .= $attachment_block;
        }
        if (empty($params['hide_file']) || empty($attachment_block)) {
            $control .= "<span class=\"multiform-fileinput-button\">";
            $control .= "<span>{$params['button_name']}</span>";
            $control .= "<input name=\"{$control_name}\" 
                            data-field-id=\"{$params['field_id']}\" 
                            data-id=\"{$params['unique_id']}\" 
                            " . (!empty($params['field']['data_index']) ? 'data-index="' . $params['field']['data_index'] . '"' : '') . "
                            " . (!empty($params['field']['data_index']) ? 'data-name="files_' . $params['field_id'] . '_' . str_replace('.', '_', $params['field']['data_index']) . '"' : '') . "
                            type=\"file\" 
                            id=\"{$params['id']}\" 
                            class=\"multiform-fileupload\"" . (!$params['file_max_count'] || $params['file_max_count'] > 1 ? " multiple" : "") . ">";
            $control .= "</span>";

            if (!empty($params['record_value'])) {
                $control .= "<input type=\"hidden\" name=\"has_files[]\" value=\"{$params['field_id']}|".(!empty($params['field']['data_index']) ? $params['field']['data_index'] : 0)."\">";
            }

            // Определяем максимальный размер загружаемого файла согласно системным настройкам
            $ini_max_filesize = ini_get('upload_max_filesize');
            $ini_post_max_size = ini_get('post_max_size');
            $ini_max_filesize_converted = wa('multiform')->getConfig()->convertShortcuts($ini_max_filesize);
            $ini_post_max_size_converted = wa('multiform')->getConfig()->convertShortcuts($ini_post_max_size);
            $system_max_size = $ini_max_filesize_converted <= $ini_post_max_size_converted ? $ini_max_filesize_converted : $ini_post_max_size_converted;
            $params['file_max_size'] = ($params['file_max_size'] > $system_max_size || !$params['file_max_size']) ? $system_max_size : $params['file_max_size'];

            if (!$params['hide_file_requirements']) {
                if ($params['file_extension']) {
                    $control .= "<div class='multiform-file-info extensions'>" . _wd(self::$app, "Valid extensions") . ": " . implode(", ", $params['file_extension']) . "</div>";
                }
                if ($params['file_max_size']) {
                    $max_size = $params['file_max_size'] / 1000;
                    $control .= "<div class='multiform-file-info filesize'>" . _wd(self::$app, "Maximum file size") . ": " . ($max_size < 1000 ? round($max_size, 2) . " KB" : round($max_size / 1000, 2) . " MB") . "</div>";
                }
                if ($params['file_max_count'] && $params['file_max_count'] > 1) {
                    $control .= "<div class='multiform-file-info maxcount'>" . _wd(self::$app, "Maximum files count") . ": {$params['file_max_count']}</div>";
                }
            }

            // Если у формы есть файлы, имитируем их наличие, чтобы не требовать их загрузки в случае обязательного поля
            if (wa()->getEnv() == 'backend' && !empty($params['record_value'])) {
                $control .= '<div class="multiform-files"><div class="multiform-temp-file"></div></div>';
            }

            if (wa()->getEnv() == 'frontend' || (wa()->getEnv() == 'backend' && $params['edit_record_page'])) {
                if (!$initJs) {
                    $initJs = self::addJs();
                    $js_inline .= $initJs;
                }
                $js_inline .= "MultiformScripts.initFileupload($('#{$params['id']}'), { maxCount: {$params['file_max_count']}, maxSize: {$params['file_max_size']}, extensions: " . json_encode($params['file_extension']) . " });";
                self::addToJs($js_inline);
            }
        }
        if (!empty($params['attach_file_pos']) && $params['attach_file_pos'] == 'bottom') {
            $control .= $attachment_block;
        }
        $control .= "</div>";

        return $control;
    }

    public function GetFileAttachSettingsControl($name, $params)
    {
        $control_name = multiformHelper::secureString($name);
        $value = $params['value'] ? unserialize($params['value']) : array();
        $ini_max_filesize = ini_get('upload_max_filesize');
        $ini_post_max_size = ini_get('post_max_size');
        $ini_max_filesize_converted = wa('multiform')->getConfig()->convertShortcuts($ini_max_filesize);
        $ini_post_max_size_converted = wa('multiform')->getConfig()->convertShortcuts($ini_post_max_size);
        $limit = $ini_max_filesize_converted <= $ini_post_max_size_converted ? array("ini_value" => $ini_max_filesize, "converted" => $ini_max_filesize_converted) : array("ini_value" => $ini_post_max_size, "converted" => $ini_post_max_size_converted);
        $control = "<input type=\"file\" name=\"attachment\" class=\"fileupload-attachment\" data-name=\"{$control_name}\">";
        $control .= "<div class=\"multiform-gap-description\">" . sprintf(_w("Value cannot be more than %sbyte (or %s byte) in your server php.ini"), $limit['ini_value'], $limit['converted']) . "</div>";
        if ($value) {
            $control .= "<div class=\"block attachment-block\">"
                . "<a href=\"?module=frontend&action=downloadAttachment&hash={$value['hash']}\" class=\"f-attachment\"><i class=\"icon-custom attachment\"></i> <span>{$value["name"]}</span></a>"
                . " - <a href=\"#/delete/fileAttachment/\" class=\"js-action\" title=\"" . _w("delete") . "\"><i class=\"icon10 delete\"></i> " . _w("delete") . "</a>"
                . "<input type=\"hidden\" name=\"{$control_name}[hash]\" value=\"{$value['hash']}\">"
                . "<input type=\"hidden\" name=\"{$control_name}[name]\" value=\"{$value['name']}\">"
                . "</div>";
        }
        $control .= "<div class=\"progressfield-block hidden block\"></div>";

        return $control;
    }

    public function GetFileMaxCountSettingsControl($name, $params)
    {
        $control_name = multiformHelper::secureString($name);
        $control = "<input type=\"text\" name=\"{$control_name}\" class=\"width50px\" " . ($params['value'] ? "value=\"" . (int) $params['value'] . "\"" : "placeholder=\"∞\"") . ">";
        return $control;
    }

    public function GetFileMaxSizeSettingsControl($name, $params)
    {
        $control_name = multiformHelper::secureString($name);
        $control = "<input type=\"text\" name=\"{$control_name}\" class=\"width100px\" value=\"" . ($params['value'] ? (int) $params['value'] : "") . "\">";
        // Получаем настройки php.ini. Максимальный размер загружаемых файлов
        $ini_max_filesize = ini_get('upload_max_filesize');
        $ini_post_max_size = ini_get('post_max_size');
        $ini_max_filesize_converted = wa('multiform')->getConfig()->convertShortcuts($ini_max_filesize);
        $ini_post_max_size_converted = wa('multiform')->getConfig()->convertShortcuts($ini_post_max_size);
        $limit = $ini_max_filesize_converted <= $ini_post_max_size_converted ? array("ini_value" => $ini_max_filesize, "converted" => $ini_max_filesize_converted) : array("ini_value" => $ini_post_max_size, "converted" => $ini_post_max_size_converted);
        $control .= "<div class=\"multiform-gap-description\">"
            . _w("Specify value in bytes.") . "<br>"
            . sprintf(_w("Value cannot be more than %sB (or %s byte) in your server php.ini"), $limit['ini_value'], $limit['converted'])
            . "</div>";

        return $control;
    }

    public function GetFileExtensionSettingsControl($name, $params)
    {
        $extensions = wa('multiform')->getConfig()->getOption('extension');
        $params['value'] = !empty($params['value']) ? unserialize($params['value']) : array();
        $control = "<div class=\"extensions\">";
        foreach ($extensions as $ext_group => $ext) {
            $control .= "<div class=\"extension-group\">";
            $control .= "<div class=\"extension-gr-name\"><span class=\"bold\">" . _w(ucfirst($ext_group)) . "</span>";
            if ($ext_group !== 'custom') {
                $control .= " (<a href=\"#/extension/selectAll/\" class=\"js-action\">" . _w("select all") . "</a>)";
            }
            $control .= "</div>";
            if ($ext_group !== 'custom') {
                foreach ($ext as $e) {
                    $control .= "<div class=\"extension-item" . (isset($params['value'][$e]) ? " selected" : "") . "\"><input type=\"checkbox\" id=\"extension-{$ext_group}-" . multiformHelper::secureString($e) . "\" name=\"extension[" . multiformHelper::secureString($e) . "]\" value=\"" . multiformHelper::secureString($e) . "\"" . (isset($params['value'][$e]) ? " checked" : "") . "> <label for=\"extension-{$ext_group}-" . multiformHelper::secureString($e) . "\">" . multiformHelper::secureString($e) . "</label></div>";
                    if (isset($params['value'][$e])) {
                        unset($params['value'][$e]);
                    }
                }
            } else {
                $control .= "<input type=\"text\" name=\"custom_extension\" value=\"" . ($params['value'] ? implode(", ", $params['value']) : "") . "\">";
                $control .= "<div class=\"multiform-gap-description\">" . _w("Specify your extensions. Use commas as separators") . "</div>";
            }
            $control .= "</div>";
        }
        $control .= "<div style=\"font-style: italic; color: #ff0000\">" . _w("If you want to allow upload files of any extension - do not select anything") . "</div>";
        $control .= "</div>";
        return $control;
    }

    public function beforeFile_extensionSettingsSave($tab_id, $f_id, $field, &$data, $post_data)
    {
        $extensions = waRequest::post("extension", array());
        $custom_extension = waRequest::post("custom_extension", "");
        if ($custom_extension) {
            if (strpos($custom_extension, ",") !== false) {
                foreach (explode(",", $custom_extension) as $ext) {
                    if (trim($ext)) {
                        $extensions[] = trim($ext);
                    }
                }
            } elseif (trim($custom_extension)) {
                $extensions[] = trim($custom_extension);
            }
        }
        $data[$tab_id][$f_id] = serialize($extensions);
    }

    public function beforeAttach_fileSettingsSave($tab_id, $f_id, $field, &$data, $post_data)
    {
        if (!empty($post_data[$tab_id][$f_id])) {
            $data[$tab_id][$f_id] = serialize($post_data[$tab_id][$f_id]);
        } else {
            $data[$tab_id][$f_id] = '';
            // Удаляем старый файл
            waFiles::delete(wa()->getDataPath('fields/' . $field['id'] . '/attachment/', false, 'multiform'), true);
        }
    }

    private static function addJs()
    {
        return '
         $.multiformFrontend.initFileuploadFrontend =  function(field, params) {
            var block = field.closest(".multiform-gap-field"),
                form = block.closest(".multiform-wrap");
            field.val("");
            /* Исключаем двойную инициализацию */
            if (field.data("blueimpFileupload") !== undefined) { return false; }
            field.fileupload({
                autoUpload: false,
                dataType: "json",
                url: "' . (wa()->getEnv() == 'frontend' ? wa()->getRouteUrl("multiform/frontend/handler", array("domain" => wa('multiform')->getConfig()->getDomain()), true) : "?module=form&action=recordEdit") . '"
            }).on("fileuploadadd", function (e, data) {
                var fieldBlock = $(this);
                var file = data.files[0];
                var error = false;
                var errormsg = "";

                /* Проверка на допустимое количество */
                if (params.maxCount == 1) {
                    block.find(".multiform-temp-file").remove();
                } else if (params.maxCount && params.maxCount <= block.find(".multiform-temp-file").length) {
                    block.find(".multiform-temp-file:first").remove();
                }

                if (!block.find(".multiform-files").length) {
                    block.find(".multiform-fileupload").closest("div").append("<div class=\"multiform-files\"></div>");
                } 
                var filesBlock = block.find(".multiform-files");
                var size = parseFloat(file.size / 1000);
                var html = $("<div class=\"multiform-temp-file\">" +
                        "<div class=\"multiform-cell multiform-temp-file-name\">" + file.name + "</div>" +
                        "<div class=\"multiform-cell multiform-temp-file-size\">" + (size < 1000 ? size.toFixed(2) + " KB" : (size / 1000).toFixed(2) + " MB") + "</div>" +
                        "<div class=\"multiform-cell multiform-temp-file-icons\"></div>" +
                        "<div class=\"multiform-cell multiform-progress-field\" style=\"display: none;\"></div>" +
                        "</div>");
                $("<a href=\"javascript:void(0)\">' . _wd(self::$app, "Delete") . '</a>").click(function () {
                    $(this).closest(".multiform-temp-file").remove();
                    if (block.hasClass("has-condition")) {
                        $.multiformFrontend.condition.checkCondition(block.data("condition"), form);
                    }
                }).appendTo(html.find(".multiform-temp-file-icons"));

                /* Проверка на размер файла */
                if (params.maxSize && params.maxSize < file.size) {
                    html.addClass("error").find(".multiform-temp-file-size").addClass("error");
                    error = 1;
                    errormsg = "' . _wd(self::$app, "Check file size") . '";
                }

                /* Проверка расширения */
                var exts = $.map(params.extensions, function(key) { return params.extensions[key]; });
                if (exts.length) {
                    var ext = file.name.substr(file.name.lastIndexOf(".") + 1).toLowerCase();
                    if ($.inArray(ext, exts) == -1) {
                        var lastIndex = file.name.lastIndexOf(".") + 1;
                        var name = file.name.substr(0, lastIndex) + "<span style=\"color: #ff0000\">" + file.name.substr(lastIndex) + "</span>";
                        html.addClass("error").find(".multiform-temp-file-name").html(name);
                        error = 1;
                        errormsg = (errormsg !== "" ? errormsg + ", " : "") + "' . _wd(self::$app, "Check file extension") . '";
                    }
                }

                if (error) {
                    ' . (wa()->getEnv() == 'frontend' ? '$.multiformFrontend.validate.addError(fieldBlock.closest("div"), errormsg, true);' : '') . '
                } else {
                    /* Сохраняем данные о файлах, если нет ошибок */
                    data.formData = { "data": "files", "unique_id": $(this).data("id"), "field_id": $(this).data("field-id") };
                    data.fieldProcess = $(html);
                    /* Для клонированных полей меняем название и добавляем индекс */
                    if ($(this).attr("data-name")) {
                        data.formData["cloned_index"] = $(this).attr("data-index");
                        data.paramName = $(this).attr("data-name");
                    }
                    html.data("file", data);
                }

                filesBlock.append(html);
                
                /* Меняем размер фрейма */
                $.multiformFrontend.changeFrameSize(form);
                
                $.multiformFrontend.dialog.updatePopupSize(form);

                /* Если имеется условие, вызываем проверку */
                if (block.hasClass("has-condition")) {
                    $.multiformFrontend.condition.checkCondition(block.data("condition"), form);
                }
            }).on("fileuploadsubmit", function (e, data) {
                var form = $(this).closest(".multiform-wrap");
                data.formData["_csrf"] = form.find("input[name=\"_csrf\"]").val();
                if ((parseInt(form.attr("data-files-done")) + 1) === parseInt(form.attr("data-files"))) {
                    data.formData["isLast"] = 1;
                }
                data.fieldProcess.removeClass("error").find(".multiform-progress-field").html("<div class=\"progressbar green small\"><div class=\"progressbar-outer\"><div class=\"progressbar-inner\" style=\"width: 0%;\"></div></div></div><img src=\"" + $.multiformFrontend.appUrl + "css/loading16.gif\" alt=\"" + __("Loading") + "...\" />").show().siblings().hide();
            }).on("fileuploadprogress", function (e, data) {
                var progress = parseInt(data.loaded / data.total * 90, 10);
                data.fieldProcess.find(".progressbar-inner").css("width", progress + "%");
            })
            .on("fileuploaddone", function (e, data) { 
                var response = data._response.result;
                if (response && response.status == "ok") {
                    var form = $(this).closest(".multiform-wrap");
                    form.attr("data-files-done", parseInt(form.attr("data-files-done")) + 1);
                    if (parseInt(form.attr("data-files-done")) === parseInt(form.attr("data-files")) || (typeof response.postExecute !== "undefined")) {
                        $.multiformFrontend.postExecute(form, response);
                    }
                    data.fieldProcess.addClass("done").find(".progressbar-inner").css("width", "100%");
                    setTimeout(function() {
                        data.fieldProcess.find(".multiform-progress-field").html("' . _wd(self::$app, "File uploaded") . '");
                    }, 500);
                    
                    /* Следующий файл для загрузки */
                    var next = form.find(".multiform-temp-file").not(":empty").not(".done");
                    if (next.length) {
                        var file = next.first().data("file");
                        if (file !== undefined) {
                            file.formData["record_id"] = response.data;
                            file.submit();
                        }
                    }
                } else {
                    $(this).data("blueimpFileupload")._trigger("fail", null, data);
                }
            }).on("fileuploadfail", function (e, data) {
                var form = $(this).closest(".multiform-wrap");
                form.attr("data-files-done", 0);
                form.find(".multiform-temp-file").removeClass("done").find(".multiform-progress-field").hide().siblings().show();
                data.fieldProcess.addClass("error");
                form.find(".multiform-submit input").show().siblings(".multiform-temp-loading").remove();
                var response = data._response.result;
                var errors = "";
                if (typeof response === "undefined") {
                    errors = __("Data saved with errors");
                    return false;
                } else if (typeof response.errors !== "undefined" && typeof response.errors.messages !== "undefined") {
                    $.each(response.errors.messages, function (i, v) {
                        errors += v + "<br>";
                    });
                } else {
                    errors += "' . _wd(self::$app, "Cannot save files") . '";
                }
                form.find(".multiform-errorfld .errormsg").html(errors).css("display", "inline-block");
                /* Меняем размер фрейма */
                $.multiformFrontend.changeFrameSize(form);
                
                $.multiformFrontend.dialog.updatePopupSize(form);
            });
        };';
    }

    public function validate_required($post_fields, $form_field)
    {
        // Проверка файлов, если отключен javascript
        if (!waRequest::isXMLHttpRequest() && empty($post_fields[$form_field['id']])) {
            return false;
        }
        return true;
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetFileFieldControl',
            'title' => _w('New file field'),
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
                'button_name' => array(
                    'title' => _w('Button name'),
                    'value' => _w('Select files..'),
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
                    'value' => _w('Attachment'),
                    'control_type' => 'GetInfotitleControl',
                ),
                'attach_file_checkbox' => array(
                    'label' => _w('Attach file'),
                    'value' => 0,
                    'filter' => 'int',
                    'description' => _w('You can attach any file, and users will be able to download it'),
                    'control_type' => waHtmlControl::CHECKBOX,
                    'childs' => [
                        'onopen_value' => 1,
                        'fields' => [
                            'attach_file' => array(
                                'title' => _w('Attachment'),
                                'title_type' => 'inline',
                                'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetFileAttachSettingsControl',
                            ),
                            'attach_file_name' => array(
                                'title' => _w('File name'),
                                'value' => _w('Download it!'),
                                'control_type' => waHtmlControl::INPUT,
                            ),
                            'attach_file_before_text' => array(
                                'title' => _w('Text before attachment'),
                                'value' => '',
                                'description' => _w("You can use HTML"),
                                'control_type' => waHtmlControl::INPUT,
                            ),
                            'attach_file_after_text' => array(
                                'title' => _w('Text after attachment'),
                                'value' => '',
                                'description' => _w("You can use HTML"),
                                'control_type' => waHtmlControl::INPUT,
                            ),
                            'attach_file_pos' => array(
                                'title' => _w('Position of attachment block'),
                                'value' => 'bottom',
                                'title_type' => 'inline',
                                'control_type' => waHtmlControl::SELECT,
                                'options' => array(
                                    array('title' => _w('Top'), 'value' => 'top'),
                                    array('title' => _w('Bottom'), 'value' => 'bottom'),
                                ),
                            ),
                            'hide_file' => array(
                                'label' => _w('Hide file field'),
                                'value' => 0,
                                'filter' => 'int',
                                'description' => _w('Users will see only attach field'),
                                'control_type' => waHtmlControl::CHECKBOX,
                            ),
                        ]
                    ]
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
                'file_max_count' => array(
                    'title' => _w('Maximum quantity of files'),
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetFileMaxCountSettingsControl',
                ),
                'file_max_size' => array(
                    'title' => _w('Maximum size of each file'),
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetFileMaxSizeSettingsControl',
                ),
                'file_extension' => array(
                    'title' => _w('Allowed extensions'),
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetFileExtensionSettingsControl',
                ),
                'hide_file_requirements' => array(
                    'label' => _w('Hide file requirements'),
                    'value' => 0,
                    'filter' => 'int',
                    'control_type' => waHtmlControl::CHECKBOX,
                ),
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
