<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

abstract class multiformFormBuilder extends multiformHTMLControlHelper
{

    private static $register_fields = array();
    private static $active_form_id = '';
    private static $css = array('inline' => '');
    private static $js = array('inline' => '');
    protected static $app = 'multiform';
    private static $settings_tabs = array('properties', 'display', 'sizes', 'options', 'validation', 'formula');

    protected static function execute($name, $params)
    {

    }

    abstract protected function preExecute(&$params, $field);

    abstract protected function fieldOptions();

    private static function register($field_id)
    {
        if (!isset(self::$register_fields[$field_id])) {
            self::registerAdditionalControls();
            $field_name = 'multiform' . ucfirst($field_id) . 'Control';
            $field_class = wa()->getAppPath('lib/classes/fields/' . $field_name . '.class.php', 'multiform');

            // Проверяем плагины
            if (!file_exists($field_class)) {
                $break = 1;
                $plugins = wa('multiform')->getConfig()->getPlugins();
                if ($plugins) {
                    foreach ($plugins as $p_id => $p) {
                        if (!empty($p['fields'])) {
                            $field_class = wa('multiform')->getConfig()->getPluginPath($p_id) . '/lib/classes/fields/' . $field_name . '.class.php';
                            if (file_exists($field_class)) {
                                $break = 0;
                                break;
                            }
                        }
                    }
                }
                if ($break) {
                    return false;
                }
            }

            require_once $field_class;
            self::$register_fields[$field_id] = new $field_name();
            waHtmlControl::registerControl('Get' . ucfirst($field_id) . 'FieldControl', array($field_name, "execute"));
        }
        return true;
    }

    public static function getFieldInstance($field_id)
    {
        if (self::register($field_id)) {
            return self::$register_fields[$field_id];
        }
        return false;
    }

    public static function isRegister($field_id)
    {
        return isset(self::$register_fields[$field_id]);
    }

    /**
     * Build field HTML
     *
     * @param int|array $field
     * @param int|string|array $record_value
     * @param bool $backend
     * @return string
     */
    public static function getControl($field, $record_value = null, $backend = false)
    {
        $html = "";
        // Получаем информацию о поле
        $field = self::getFieldInfo($field);
        $type = $field['type'];

        // Если тип поля существует среди перечисленных в настройках
        if (self::register($type)) {
            $instance = self::$register_fields[$type];
            $field_options = $instance->getFieldOptions($type);

            // Получаем дефолтные значения полей настроек. В случае скрытых полей - заменяем значения
            multiformHelper::getDefaultSettings($field, $field_options);

            // Если запись редактируется из бекенда, то увеличиваем все поля
            if ($backend) {
                if (!empty($field['sizes']['width'])) {
                    $field['sizes']['width'] = "colm12";
                    $field['title_pos'] = 'left';
                }
            }

            // Производим обработку данных поля
            $params = self::prepareParams($field_options, $field);

            $form_uid = self::getFormId();
            $field_attr = array(
                "class" => multiformHelper::getFieldCssClasses($field),
                "data-id" => $field['unique_id'],
                "data-field-id" => $field['id'],
                "data-type" => $type,
                "id" => "multiformField" . $field['id'] . ($form_uid ? '_' . $form_uid : '')
            );

            // Добавляем дополнительные атрибуты к полю
            if (!empty($params['field_attr'])) {
                $field_attr += $params['field_attr'];
            }

            if (!empty($field['condition_id'])) {
                $field_attr["data-condition"] = implode(",", $field['condition_id']);
                if (!empty($field['hide'])) {
                    $params['disabled'] = 1;
                }
            }

            // Если передано значение записи
            if ($record_value !== null) {

                // Если в переданных значениях массив и имеются доп параметры, отделяем их
                if (is_array($record_value) && isset($record_value['value'])) {
                    // Параметр *default* используется для подстановки стандартных значений из формы. Иными словами record_value не передается
                    if ($record_value['value'] !== '*default*') {
                        $params['record_value'] = $record_value['value'];
                    }
                    unset($record_value['value']);
                    $params['params'] = $record_value;
                } else {
                    $params['record_value'] = $record_value;
                }
                if ($backend) {
                    unset($params['readonly'], $params['disabled']);
                }
            }

            $wrappers = array(
                'title_wrapper' => false,
                'description_wrapper' => '<div class="multiform-gap-description">%s</div>',
                'control_wrapper' => "<div " . self::addCustomParams(array_keys($field_attr), $field_attr) . ">"
                    . "%s"
                    . (!empty($field['title']) ? "<div class='multiform-gap-name " . multiformHelper::getTitleWidth($field) . "'>" . multiformHelper::secureString($field['title']) . ($backend && $field['required'] ? '<br><span class="grey no-bold">(<a href="#/skip/required/" class="js-action" data-title="' . _w('return required') . '" title="' . _w('skip required?') . '">' . _w('skip required?') . '</a>)</span>' : '') . '</div>' : '')
                    . "<div class='multiform-gap-value " . multiformHelper::getFieldWidth($field) . "'>"
                    . (!empty($field['display']['prefix']) ? "<div class='prefix'>" . $field['display']['prefix'] . "</div>" : "")
                    . "%s"
                    . (!empty($field['display']['suffix']) ? "<div class='suffix'>" . $field['display']['suffix'] . "</div>" : "")
                    . "%s"
                    . "</div>"
                    . "</div>",
            );
            $params = array_merge($wrappers, $params);

            // Выполняем построение
            $html = waHtmlControl::getControl($params['control_type'], self::getControlName($field, $form_uid), $params);
        }
        return $html;
    }

    /**
     * Build field settings HTML
     *
     * @param array $field
     * @return array[string]
     */
    public static function getSettingsControl($field)
    {
        $html = array();

        $instance = self::$register_fields[$field['type']];
        $field_options = $instance->getFieldOptions($field['type']);

        foreach (self::$settings_tabs as $tab_id) {
            $html[$tab_id] = "";
            if (isset($field_options[$tab_id])) {
                foreach ($field_options[$tab_id] as $pp_id => $pp) {
                    $html[$tab_id] .= self::buildSettingOption($pp, $field, $tab_id, $pp_id);
                }
            }
        }
        return $html;
    }

    private static function buildSettingOption($setting_option, $field, $tab_id, $option_id)
    {
        static $settings_control = array();

        $instance = self::$register_fields[$field['type']];
        // Дополнителная обработка параметров настроек
        $prepare_type = "prepare" . ucfirst($option_id) . "SettingsParams";
        if (method_exists($instance, $prepare_type)) {
            $instance->$prepare_type($setting_option, $field, $tab_id, $option_id);
        }
        if (preg_match('/^([\w]+)(\s+.*)$/', $setting_option['control_type'], $matches)) {
            $matches[2] = trim($matches[2]);
            if (!isset($settings_control[$matches[2]])) {
                if (!strpos($matches[2], '::')) {
                    waHtmlControl::registerControl($matches[2], array($instance, $matches[2]));
                    $setting_option['control_type'] = $matches[2];
                    $settings_control[$matches[2]] = 1;
                }
            } else {
                $setting_option['control_type'] = $matches[2];
            }
        }
        $setting_option['value'] = !empty($field[$option_id]) ? $field[$option_id] : (isset($field[$tab_id][$option_id]) ? $field[$tab_id][$option_id] : (!empty($setting_option['value']) ? $setting_option['value'] : ""));
        $setting_option['description_wrapper'] = '<div class="multiform-gap-description">%s</div>';
        $option_class = "settings-field type-" . $setting_option['control_type'] . (!empty($setting_option['childs']['fields']) ? " has-dependent-fields" : "");
        if (!empty($setting_option['hidden'])) {
            $option_class .= " hidden";
        }
        $data_attr = (isset($setting_option['childs']['onopen_value']) ? " data-dependent-value='{$setting_option['childs']['onopen_value']}'" : "");
        $setting_option['control_wrapper'] = "<div class='{$option_class}'{$data_attr}>" .
            (isset($setting_option['title_type']) && $setting_option['title_type'] == 'inline' ? "<div class='settings-inline-title'>" : "") .
            "%s" .
            (!empty($setting_option['after_title']) ? $setting_option['after_title'] : '') .
            "<div class='settings-value" . (!empty($setting_option['childs']['fields']) ? " dependent-block" : "") . "'>%s</div>" .
            (isset($setting_option['title_type']) && $setting_option['title_type'] == 'inline' ? "</div>" : "") .
            "%s" .
            (empty($setting_option['childs']['fields']) ? "</div>" : "");
        $setting_option['field_id'] = $field['id'];
        $setting_option['tab_id'] = $tab_id;
        // Выполяем построение
        waHtmlControl::makeId($setting_option, $tab_id . '[' . $option_id . ']', $field['id']);
        $html = waHtmlControl::getControl($setting_option['control_type'], $tab_id . '[' . $option_id . ']', $setting_option);

        // Зависимые поля
        if (!empty($setting_option['childs']['fields'])) {
            $html .= "<div class='settings-dependent-field'>";
            foreach ($setting_option['childs']['fields'] as $child_option_id => $child) {
                $html .= self::buildSettingOption($child, $field, $tab_id, $child_option_id);
            }
            $html .= "</div>";
            $html .= "</div>"; // Закрываем родительский блок
        }
        return $html;
    }

    /**
     * Build field with settings in admin page
     *
     * @param int|array $field
     * @return string
     */
    public static function getFullControl($field)
    {
        $html = "";
        // Получаем информацию о поле
        $field = self::getFieldInfo($field);
        $type = $field['type'];
        // Если тип поля существует среди перечисленных в настройках
        if (self::register($type)) {
            // Строим поле
            $field_control = multiformFormBuilder::getControl($field) . self::getJs(true) . self::getCss(true);
            if ($field_control) {

                // Определяем, стоит ли показывать иконки меню для поля
                $instance = self::getFieldInstance($type);
                $show_menu = method_exists($instance, 'showMenuIcons') && !$instance->showMenuIcons() ? 0 : 1;

                $html .= "<div class='form-builder-row" . (!$field['status'] ? ' not-visible' : "") . "' data-id='{$field['id']}' data-type='{$field['type']}'>";
                $html .= "<i class='icon16 sort' title='" . _w('Drag to sort') . "'></i>";
                if ($show_menu) {
                    $html .= "<ul class='menu-h f-icons'>
                          <li><a href='#/change/status/{$field['id']}' title='" . _w('Change status') . "' class='js-action' data-type='fieldStatus'><i class='icon-custom lightbulb" . (!$field['status'] ? '-off' : '') . "'></i></a></li>
                          <li><a href='#/copy/field/{$field['id']}' title='" . _w('Duplicate') . "' class='js-action'><i class='icon-custom copy'></i></a></li>
                          <li><a href='#/edit/field/' title='" . _w('Edit') . "' class='js-action'><i class='icon16 edit'></i></a></li>
                          <li><a href='#/delete/field/{$field['id']}' title='" . _w('Delete') . "' class='js-action'><i class='icon16 delete'></i></a></li>
                          </ul>";
                }

                if ($type !== 'section') {
                    $html .= $field_control;
                }

                if ($field['hidden'] || $field['private']) {
                    $html .= "<div class='corner-left-top no-margin'>";
                    if ($field['hidden']) {
                        $html .= "<i class='icon-custom eye' title='" . _w("Hidden from user") . "'></i>";
                    }
                    if ($field['private']) {
                        $html .= "<i class='icon16 lock-bw' title='" . _w("Private field") . "'></i>";
                    }
                    $html .= "</div>";
                }
                // Получаем настройки поля          
                $settings_control = multiformFormBuilder::getSettingsControl($field);
                if ($settings_control && $show_menu) {
                    // Формируем вкладки настроек
                    $html .= "<div class='settings-block'>
                                    <a href='#/field/closeSettings/' class='js-action close-settings' title='" . _w('close') . "'>" . _w('close') . "</a>
                                    <span class=\"grey s-field-id\">{Field:{$field['unique_id']}}</span>
                                    <ul class='tabs'>";
                    foreach (self::$settings_tabs as $k => $tab) {
                        if (!empty($settings_control[$tab])) {
                            $html .= "<li class='" . ($k == 0 ? "selected " : "") . "{$tab}-tab'><a href='#/field/toggleTab/' class='js-action' data-tab='{$tab}'>" . _w(ucfirst($tab)) . "</a></li>";
                        }
                    }
                    $html .= "</ul>
                                    <form action=''>
                                        <div class='tab-content'>";
                    foreach (self::$settings_tabs as $tab) {
                        if (!empty($settings_control[$tab])) {
                            $html .= "<div class='tab-{$tab}'>{$settings_control[$tab]}</div>";
                        }
                    }
                    $html .= "</div>
                                        <input type='hidden' name='field_id' value='{$field['id']}' />
                                        <div class='errormsg'></div>
                                        <div class='submit-block'><input type='submit' class='button green' value='" . _w('Save') . "' /></div>
                                    </form>
                                </div>
                            <div class='settings-save-status'><a href='javascript:void(0)' onclick=\"$.form_edit.saveFormFields($(this).closest('.form-builder-row').find('> .settings-block form'));\" title='" . _w('Save') . "?'><i class='icon16 status-yellow'></i></a></div>";
                }

                if ($type == 'section') {
                    $html .= $field_control;
                }

                $html .= "</div>";
            }
        }
        return $html;
    }

    /**
     * Build fields from tree array in admin page.
     *
     * @param array $fields
     * @return string
     */
    public static function getFieldsControlFromTree($fields)
    {
        $html = "";
        foreach ($fields as $field) {
            $type = $field['type'];
            // Если тип поля существует среди перечисленных в настройках
            if (self::register($type)) {
                if ($type == 'section') {
                    $section_instance = self::getFieldInstance($type);
                    $html .= $section_instance->openSection($field);
                    // Дочерние поля
                    if (!empty($field['fields'])) {
                        $html .= self::getFieldsControlFromTree($field['fields']);
                    }
                    $html .= $section_instance->closeSection();
                } else {
                    $html .= self::getFullControl($field);
                }
            }
        }
        return $html;
    }

    /**
     * Get unique form ID
     *
     * @param int $form_id
     * @return string
     */
    public static function registerForm($form_id)
    {
        static $count = array();

        $count[$form_id] = !isset($count[$form_id]) ? 1 : $count[$form_id] + 1;
        self::$active_form_id = $form_id . '-' . $count[$form_id];
        return self::$active_form_id;
    }

    /**
     * Get active form ID
     *
     * @return string
     */
    public static function getFormId()
    {
        return self::$active_form_id;
    }

    public static function buildForm($form, $options = array())
    {
        wa('multiform')->setLocale(wa()->getLocale());
        static $head = "";
        static $themes = array();
        $html = $form_head = $js_inline = "";
        $form_has_fields = $has_submit_button = $captcha_is_shown = 0;

        // Присваиваем форме уникальный ID
        $form_id = self::registerForm($form['id']);

        // Вывод формы по расписанию
        $today = time();
        if ((!empty($form['start']) && $today < strtotime($form['start'])) || (!empty($form['end']) && $today > strtotime($form['end'])) || !$form['status']) {
            return $html;
        }

        // Ограничение по витринам
        if (!empty($form['domain_limitations'])) {
            $routing_url = wa()->getRouting()->getRootUrl();
            $storefront = wa('multiform')->getConfig()->getDomain() . ($routing_url ? '/' . $routing_url : '');

            $break = false;
            foreach ($form['domain_limitations'] as $route) {
                foreach ($route as $r) {
                    if ($storefront == $r || ($storefront . '*') == $r || ($storefront . '/*') == $r) {
                        $break = true;
                        break;
                    }
                }
                if ($break) {
                    break;
                }
            }
            if (!$break) {
                return $html;
            }
        }

        // Ограничения формы
        if ($limit_errors = multiformHelper::formLimitationErrors($form)) {
            if (!empty($form['form_after_limit']) && $form['form_after_limit'] == 'hide') {
                return $html;
            }
        }

        // Тема оформления
        if (!empty($form['theme_id'])) {
            if (!isset($themes[$form['theme_id']])) {
                $mtm = new multiformThemeModel();
                $theme = $themes[$form['theme_id']] = $mtm->getTheme($form['theme_id']);
                $form_head .= $mtm->generateThemeCss($theme, !empty($form['popup']));
            } else {
                $theme = $themes[$form['theme_id']];
            }
        }

        // Условия
        if (!empty($form['conditions'])) {
            // Условия полей
            if (!empty($form['conditions']['field'])) {

                $js_inline .= '$.extend($.multiformFrontend.conditions, ';
                foreach ($form['conditions']['field'] as &$field_condition) {
                    // Проверяем, существут ли поле, указанное в условии.
                    // Если существует, то добавляем ему дополнительные параметры
                    if (isset($form['fields'][$field_condition['target']]) && $field_condition['action'] == 'show') {
                        $form['fields'][$field_condition['target']]['condition_id'][] = $field_condition['id'];
                        $form['fields'][$field_condition['target']]['hide'] = 1;
                    }
                    // Отмечаем поля, участвующие в условии
                    foreach ($field_condition['params'] as $p) {
                        $form['fields'][$p['source']]['condition_id'][] = $field_condition['id'];
                    }
                    // Удаляем лишние данные
                    unset($field_condition['id'], $field_condition['form_id'], $field_condition['sort'], $field_condition['tab']);
                }
                $js_inline .= json_encode($form['conditions']['field']);

                $js_inline .= "); 
                        $(\".multiform-wrap[data-uid='{$form_id}']\").on('multiform-loaded-{$form_id}', function() {
                            ";
                foreach (array_keys($form['conditions']['field']) as $cond_id) {
                    $js_inline .= "$.multiformFrontend.condition.checkCondition({$cond_id}, $(this));";
                }
                $js_inline .= "});";
            }
        }

        $wrap_params = array(
            'class' => 'multiform-wrap' . (!empty($form['css_class']) ? " " . multiformHelper::secureString($form['css_class']) : "") . (!empty($theme) ? " multiform-theme{$theme['id']}" : " default-theme") . (!empty($theme['settings']['sizes']['form']['size']) && $theme['settings']['sizes']['form']['size'] == 'adaptive' ? ' multiform-adaptive-width' : ''),
            'data-id' => $form['id'],
            'data-uid' => $form_id,
            'data-action' => wa()->getRouteUrl("multiform/frontend/handler", array("domain" => wa('multiform')->getConfig()->getDomain()), true)
        );
        $form_params = array(
            'action' => ((!empty($form['captcha']) && $form['captcha'] == 'basic') || waRequest::get("nojs")) ? wa()->getRouteUrl("multiform/frontend/handler", array("domain" => wa('multiform')->getConfig()->getDomain()), true) : "about:blank",
            'class' => 'multiform-gap-form',
            'method' => 'post'
        );

        if (!empty($form['captcha']) && $form['captcha'] !== 'basic') {
            $form_params['onsubmit'] = 'return false';
        }

        // Получаем сессию для формы и проверяем есть ли ошибки
        $session_name = 'multiform-session-' . $form_id;
        $form_session = wa()->getStorage()->get($session_name);

        // Если есть ошибки
        $errormsg = "";

        // Если у формы имеются ограничения, то не допускаем отправку данных
        if ($limit_errors) {
            $wrap_params['data-action'] = $form_params['action'] = 'about:blank';
            $errormsg .= $limit_errors . "<br>";
        }
        if (!empty($form_session['errors']['messages'])) {
            $errormsg .= implode("<br>", $form_session['errors']['messages']);
        }
        if ($errormsg) {
            $html .= "<div class='multiform-errorfld s-first'>
                    <em class='errormsg'" . ($errormsg ? " style='display: inline-block'" : "") . ">{$errormsg}</em>
              </div>";
        }

        $html .= multiformHelper::csrf() . "<div class='multiform-gap-fields'>";

        if (!empty($form_session['data']['message'])) {
            $html .= $form_session['data']['message'];
        } else {
            if (!empty($form['fields'])) {
                $private_field_access = multiformHelper::privateFieldAccess($form);
                $active_section = $hidden_section_element = 0;
                $open_sections = [];
                // Подготавливаем товары
                $form['fields'] = self::prepareFormFields($form['fields']);
                $fields_count = count($form['fields']);
                $c = 0;
                foreach ($form['fields'] as $field) {

                    $c++;
                    $is_last = $fields_count === $c;

                    // Если секция закончилась, прекращаем скрывать элементы
                    if (empty($field['additional']['section'])) {
                        $hidden_section_element = $active_section = 0;
                    }

                    if ($field['status'] && !$hidden_section_element) {
                        if ($field['type'] == 'file') {
                            $form_params['enctype'] = 'multipart/form-data';
                        }
                        // Если у поля имеются ошибки
                        if (!empty($form_session['errors']['fields'][$field['id']])) {
                            $field['has_error'] = 1;
                        }
                        if (($field['private'] && !$private_field_access) || $field['hidden']) {
                            continue;
                        }

                        // Устанавливаем значение полей по умолчанию
                        $default = null;
                        if (isset($options['default'][$field['unique_id']])) {
                            $default = multiformHelper::getFieldDefaultValue($field, $options['default'][$field['unique_id']]);
                        }

                        // Закрываем секцию в секции
                        if ($open_sections && !empty($field['additional']['section']) && $active_section !== (int) $field['additional']['section']) {
                            $html .= self::inlineCloseSection($open_sections, true);
                        }
                        // Если поле не принадлежит ни одной секции, значит можно закрывать все секции от начала до конца
                        if ($open_sections && empty($field['additional']['section'])) {
                            $html .= self::inlineCloseSection($open_sections);
                        }
                        if ($field['type'] == 'section') {
                            $open_sections[] = $field;
                        }

                        // Запоминаем, что в форме имеется кнопка подтверждения и что не нужно выводить дефолтную
                        if ($field['type'] == 'button' && (!isset($field['properties']['action']) || $field['properties']['action'] == 'submit')) {
                            $has_submit_button = 1;

                            if ($is_last) {
                                $html .= self::showCaptcha($form_id, $form);
                                $captcha_is_shown = 1;
                            }
                        }

                        // Выводим содержимое поля
                        $html .= self::getControl($field, $default);

                        if (!empty($field['additional']['section']) || $field['type'] == 'section') {
                            $active_section = $field['type'] == 'section' ? (int) $field['id'] : (int) $field['additional']['section'];
                        }

                        if ($field['type'] !== 'section') {
                            $form_has_fields = 1;
                        }
                    }
                    // Если секция скрыта, то даем сигнал скрывать все ее элементы
                    if (!$field['status'] && $field['type'] == 'section' && !$hidden_section_element) {
                        $hidden_section_element = 1;
                        $active_section = (int) $field['id'];
                    }
                }
                // Если форма заканчивается секцией, ее нужно закрыть
                $html .= self::inlineCloseSection($open_sections);
            }

            // Дополнительные параметры
            if (!empty($options['params'])) {
                if (is_array($options['params'])) {
                    foreach ($options['params'] as $param_name => $param_value) {
                        $html .= "<input type='hidden' name='params[][" . multiformHelper::secureString($param_name) . "]' value='" . multiformHelper::secureString($param_value) . "'>";
                    }
                } else {
                    $html .= "<input type='hidden' name='params[][" . _wd(self::$app, "Parameter") . "]' value='" . multiformHelper::secureString($options['params']) . "'>";
                }
                $form_has_fields = 1;
            }

            // Кнопка всплывающего окна
            if (!empty($form['popup'])) {
                $form_head .= "<div class='multiform-popup-link'>" . (!empty($form['popup_button']) ? $form['popup_button'] : '') . "</div>";
            }

            $form_head .= "<div " . self::addCustomParams(array('action', 'class', 'data-id', 'data-action', 'data-uid'), $wrap_params) . ">";
            if (!isset($form['use_tag_form']) || !empty($form['use_tag_form'])) {
                $form_head .= "<form " . self::addCustomParams(array('action', 'class', 'onsubmit', 'enctype', 'method'), $form_params) . ">";
            }
            // Вывод хедера согласно настройкам темы дизайна
            if (!empty($theme['settings']) && $theme['settings']['header']['logo'] !== 'hide') {
                $form_head .= "<div class=\"multiform-header\">";
                if ($theme['settings']['header']['logo'] !== 'none' && ($theme['settings']['header']['logo'] == 'default' || !empty($theme['settings']['header']['logo_src']))) {
                    $form_head .= "<img src=\"" . ($theme['settings']['header']['logo'] == 'default' ? wa()->getAppStaticUrl('multiform', true) . 'img/multiform24.png' : multiformHelper::secureString($theme['settings']['header']['logo_src'])) . "\" />";
                }
                if (!empty($theme['settings']['header']['text'])) {
                    $form_head .= "<span>" . _wd(self::$app, $theme['settings']['header']['text']) . "</span>";
                }
                $form_head .= "</div>";
            }

            if ($form['title']) {
                $form_head .= "<div class=\"multiform-title\">{$form['title']}</div>";
            }
            if ($form['description']) {
                $form_head .= "<div class=\"multiform-form-description\">{$form['description']}</div>";
            }

            /* @actions js_validate */
            if ($form['actions']) {
                $ma = new multiformActions();
                foreach ($form['actions'] as $fa) {
                    if ($fa['status'] && isset($fa['info']['handlers']['js_validate'])) {

                    }
                }
            }

            $js_inline .= "$(\".multiform-wrap[data-uid='{$form_id}']\").trigger(\"multiform-loaded\").trigger(\"multiform-loaded-{$form_id}\");";
            self::addToJs($js_inline);

            $html = (!$head ? multiformHelper::addCss() . multiformHelper::addJs() : '') . self::getCss() . self::getJs() . $form_head . $html;

            // Вывод капчи
            if (!$captcha_is_shown) {
                $html .= self::showCaptcha($form_id, $form);
            }

            $html .= "<div class='multiform-errorfld'>
                        <em class='errormsg'" . ($errormsg ? " style='display: inline-block'" : "") . ">{$errormsg}</em>
                  </div>";
        }

        // Удаляем сессию формы, если она существует
        wa()->getStorage()->remove($session_name);

        $html .= "</div>"; // End of div.multiform-gap-fields

        if (!$limit_errors && $form_has_fields && !$has_submit_button) {
            $html .= "<div class='multiform-submit'>
                    <button data-submit class='mf-button'>" .
                (isset($theme['settings']['buttons']['submit']['text']) ? multiformHelper::secureString($theme['settings']['buttons']['submit']['text']) : _wd(self::$app, "Send")) .
                "</button>" .
                "</div>";
        }

        $html .= "<input type='hidden' name='form_id' value ='" . $form['id'] . "' />";
        $html .= "<input type='hidden' name='form_uid' value ='" . $form_id . "' />";
        if (!isset($form['use_tag_form']) || !empty($form['use_tag_form'])) {
            $html .= "</form>";
        }
        $html .= "</div>";

        $head = 1;

        return $html;
    }

    private static function showCaptcha($form_id, $form)
    {
        if (!empty($form['captcha']) && ($form['captcha'] == 'basic' || $form['captcha'] == 'advanced')) {
            $recaptcha = 0;
            if ($form['captcha'] == 'advanced' && !empty($form['recaptcha']) && !empty($form['recaptcha_sitekey']) && !empty($form['recaptcha_secretkey'])) {
                $recaptcha = 1;
                $captcha_html = wa(self::$app)->getCaptcha()->getFactory('recaptcha')->getHtml($form_id, $form['recaptcha_sitekey']);
            } else {
                $captcha_html = wa(self::$app)->getCaptcha()->getFactory('captcha')->getHtml($form_id);
            }
            return "<div class='multiform-gap-field multiform-gap-captcha s-required size-medium" . ($recaptcha ? " multiform-recaptcha" : "") . (isset($form['captcha_field']) && !$form['captcha_field'] ? ' pos-hide' : '') . "' data-type='input'>
                        <div class='multiform-gap-name'>
                            <label for='wahtmlcontrol_fields_captcha_{$form_id}'>" . (!isset($form['captcha_field']) ? _wd(self::$app, "Captcha field") : multiformHelper::secureString($form['captcha_field'])) . "</label>
                        </div>
                        <div class='multiform-gap-value'>{$captcha_html}</div>
                      </div>";
        }
        return '';
    }

    /**
     * Close all opened sections or only the last
     *
     * @param array $open_sections
     * @param bool $close_last
     * @return string
     */
    private static function inlineCloseSection(&$open_sections, $close_last = false)
    {
        $html = '';
        if ($close_last) {
            $sections = array_slice($open_sections, -1, 1, true);
        } else {
            $sections = array_reverse($open_sections, true);
        }
        foreach ($sections as $k => $section) {
            if (!empty($section['properties']['multiply_fields'])) {
                $instance = self::getFieldInstance($section['type']);
                $html .= $instance->getMultiplyField($section);
            }
            $html .= '</div>';
            unset($open_sections[$k]);
        }
        return $html;
    }

    private static function prepareFormFields($fields)
    {
        $sections = array();

        // Собираем всех детей для секций
        foreach ($fields as $k => $f) {
            if (!$f['status'] || $f['type'] == 'hidden') {
                continue;
            }
            if ($f['type'] == 'section') {
                $sections[$f['id']] = array('fields' => array(), 'weight' => 0, 'info' => $f);
            }
            if (!empty($f['additional']['section'])) {
                $section_id = $f['additional']['section'];
                // Если макет секции "Горизонтальный" и указан Автоматический режим размеров, то подсчитываем вес полей
                if (!empty($sections[$section_id]['info']['display']) && $sections[$section_id]['info']['display']['layout'] == 'horizontal' && !empty($sections[$section_id]['info']['sizes']['autowidth'])) {
                    $sections[$section_id]['fields'][] = $f['id'];
                    if (empty($f['title_pos']) || (!empty($f['title_pos']) && $f['title_pos'] == 'left')) {
                        $sections[$section_id]['weight'] += 2;
                        $fields[$k]['double_block_width'] = 1;
                    } else {
                        $sections[$section_id]['weight'] += 1;
                    }
                }
            }
        }
        if ($sections) {
            foreach ($sections as $section_id => $s) {
                // Если макет секции "Горизонтальный"
                if (!empty($s['info']['display']) && $s['info']['display']['layout'] == 'horizontal') {
                    $auto_block_width = empty($sections[$section_id]['info']['sizes']['all_block_width']) || (!empty($sections[$section_id]['info']['sizes']['all_block_width']) && $sections[$section_id]['info']['sizes']['all_block_width'] == 'auto');
                    $auto_field_width = empty($sections[$section_id]['info']['sizes']['field_width']) || (!empty($sections[$section_id]['info']['sizes']['field_width']) && $sections[$section_id]['info']['sizes']['field_width'] == 'auto');
                    if ($s['weight']) {
                        $block_width_tail = 0;
                        // Если установлен режим автоматического вычисления ширины блоков
                        if ($auto_block_width) {
                            $block_width = floor(12 / $s['weight']);
                            // Если вес полей больше 12 или ширина поля получается слишком маленькой, устанавливаем ширину у всех полей равную Типу 2 (16.66%)
                            if ($block_width === 0 || $block_width < 2) {
                                $block_width = 2;
                            } // Если не получается установить всем полям одинаковую ширину, высчитываем остаток
                            elseif ($block_width % 2 === 0) {
                                $block_width_tail = 12 - $s['weight'] * $block_width;
                            }
                        }
                        $fields_count = count($s['fields']);
                        // В одну линию можно поместить элементов шириной не более 12
                        $line_width = $width = 0;
                        foreach ($s['fields'] as $k => $field_id) {
                            // Ширина блока
                            if ($auto_block_width) {
                                $width = self::getFormFieldWidth($block_width, !empty($fields[$field_id]['double_block_width']), $k + 1, $fields_count, $block_width_tail);
                                // Если существует следующий элемент, вычисляем его ширину
                                if (($k + 2) <= $fields_count) {
                                    $next_elem_width = self::getFormFieldWidth($block_width, !empty($fields[$s['fields'][$k + 1]]['double_block_width']), $k + 2, $fields_count, $block_width_tail);
                                    if (($line_width + $width + $next_elem_width) > 12) {
                                        $width = 12 - $line_width;
                                    }
                                }
                            }
                            $fields[$field_id]['sizes']['block_width'] = $auto_block_width ? 'colm' . $width : $sections[$section_id]['info']['sizes']['all_block_width'];

                            // Ширина полей
                            if (!empty($fields[$field_id]['double_block_width'])) {
                                $fields[$field_id]['sizes']['title_width'] = $fields[$field_id]['sizes']['field_width'] = 'colm6';
                                if (!$auto_field_width) {
                                    $fields[$field_id]['sizes']['field_width'] = $sections[$section_id]['info']['sizes']['field_width'];
                                    $fields[$field_id]['sizes']['title_width'] = 'colm' . (12 - (int) substr($fields[$field_id]['sizes']['field_width'], 4));
                                }
                            } else {
                                $fields[$field_id]['sizes']['title_width'] = $fields[$field_id]['sizes']['field_width'] = 'colm12';
                                if (!$auto_field_width) {
                                    $fields[$field_id]['sizes']['field_width'] = $sections[$section_id]['info']['sizes']['field_width'];
                                }
                            }

                            $fields[$field_id]['sizes']['width'] = 'colm12';
                            // Считаем, сколько элементов поместилось в строку. Если строка заполнена, обнуляем значение.
                            $line_width += $width;
                            if ($line_width == 12) {
                                $line_width = 0;
                            }
                        }
                    }
                }
            }
        }
        return $fields;
    }

    private static function getFormFieldWidth($block_width, $double_width, $k, $fields_count, $block_width_tail)
    {
        return ($block_width * ($double_width ? 2 : 1) + ($k == $fields_count ? $block_width_tail : 0));
    }

    /**
     * Get field information
     *
     * @param int|array $field
     * @return array
     */
    private static function getFieldInfo($field)
    {
        if (!is_array($field)) {
            // Получаем информацию о поле
            $mf = new multiformFieldModel();
            $field = $mf->getDataById($field);
        }
        return $field;
    }

    /*     * ******
     * Функции вида prepare%Field_id%Params необходимы, чтобы подготовить (обработать) данные полей, которые приходят из БД.
     * Устанавливаются значения по умолчанию, форматируются данные.
     * Созданые (измененные) ключи и их значения в массиве $params будут переданы в функцию построения поля вида get%Field_id%Control.
     * ****** */

    /**
     * General prepare for all fields.
     * Each field will have params: 'type', 'title', 'readonly', 'class', 'description'
     *
     * @param array $params
     * @param array $field
     * @return array
     */
    private static function prepareParams($params, $field)
    {
        $params['title'] = $field['title'];
        $params['type'] = $field['type'];
        $params['readonly'] = $field['disabled'] ? true : false;
        $params['class'] = $field['class'] . (!empty($field['properties']['class']['value']) ? " " . $field['properties']['class']['value'] : "");
        $params['class'] = (strpos($params['class'], " ") !== false ? explode(" ", $params['class']) : (array) $params['class']);
        $params['description'] = $field['description'];
        $params['field'] = $field;

        $instance = self::$register_fields[$field['type']];
        // Дополнительная обработка полей
        if (method_exists($instance, 'preExecute')) {
            $params = self::getRawFieldOptions($params);
            $instance->preExecute($params, $field);
        }
        return $params;
    }

    /**
     * Get and validate storefront params
     *
     * @param array $params
     * @param array $available_params
     */
    protected static function getStorefrontParams(&$params, $available_params = array())
    {
        if (isset($params['params'])) {
            foreach ($available_params as $param) {
                if (isset($params['params'][$param])) {
                    $params[$param] = $params['params'][$param];
                }
            }
        }
    }

    /*     * ******
     * Функции вида prepare%Field_id%SettingsParams необходимы, чтобы подготовить (обработать) данные полей
     * для вывода в настройках поля в функции get%Setting_id%SettingsControl
     * ****** */

    /**
     * Prepare setting params for mask field
     *
     * @param array $params
     * @param array $field
     * @return array
     */
    private function prepareMaskSettingsParams(&$params, $field)
    {
        $params['regexp'] = !empty($field['validation']['regexp']) ? $field['validation']['regexp'] : "";
        $params['regexp_error'] = !empty($field['validation']['regexp_error']) ? $field['validation']['regexp_error'] : "";
        $params['regexp_casein'] = !empty($field['validation']['regexp_casein']) ? $field['validation']['regexp_casein'] : "";
    }

    /**
     * Get field options
     *
     * @param string $type - field type
     * @return array
     */
    public function getFieldOptions($type)
    {
        $field_options = $this->fieldOptions();

        /**
         * Extra field options
         *
         * @event multiform_field_options
         */
        $plugin_field_options = wa()->event('multiform_field_options');
        if ($plugin_field_options) {
            foreach ($plugin_field_options as $options) {
                if (!empty($options[$type])) {
                    $field_options = array_merge_recursive($field_options, $options[$type]);
                }
            }
        }

        return $field_options;
    }

    /**
     * Get raw field options
     *
     * @param $field_options
     * @return array
     */
    private static function getRawFieldOptions($field_options)
    {
        foreach (self::getSettingsTabs() as $tab) {
            if (isset($field_options[$tab])) {
                foreach ($field_options[$tab] as $option_tab_value) {
                    if (!empty($option_tab_value['childs']['fields'])) {
                        $field_options[$tab] += $option_tab_value['childs']['fields'];
                    }
                }
            }
        }
        return $field_options;
    }

    public static function getSettingsTabs()
    {
        return self::$settings_tabs;
    }

    protected static function addToJs($js, $one_time = '')
    {
        if ($one_time && !isset(self::$js[$one_time])) {
            self::$js[$one_time] = $js;
        }
        if (!$one_time) {

            self::$js['inline'] .= $js;
        }
    }

    protected static function addToCss($css, $one_time = '')
    {
        if ($one_time && !isset(self::$css[$one_time])) {
            self::$css[$one_time] = $css;
        }
        if (!$one_time) {
            self::$css['inline'] .= $css;
        }
    }

    public static function getJs($only_inline = false)
    {
        $inline_js = $js = "";
        if (!empty(self::$js['inline'])) {
            $inline_js .= "<script>(function($){" . '' . "$(function(){setTimeout(function(){" . self::$js['inline'] . "})},1000)})(jQuery);</script>";
            self::$js['inline'] = '';
        }
        if (!$only_inline) {
            foreach (self::$js as $k => $script) {
                $js .= $script;
                self::$js[$k] = '';
            }
        }

        return $js . $inline_js;
    }

    public static function getCss($only_inline = false)
    {
        $inline_css = $css = "";
        if (!empty(self::$css['inline'])) {
            $inline_css = "<style>" . self::$css['inline'] . "</style>";
            self::$css['inline'] = '';
        }
        if (!$only_inline) {
            foreach (self::$css as $k => $link) {
                $css .= $link;
                self::$css[$k] = '';
            }
        }

        return $css . $inline_css;
    }

}
