<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformHelper
{

    /**
     * Get csrf protection
     *
     * @return string
     */
    public static function csrf()
    {
        // Проверка наличия куки csrf
        if (!waRequest::cookie('_csrf')) {
            waSystem::getInstance()->getResponse()->setCookie('_csrf', uniqid('', true));
        }
        return '<input type="hidden" name="_csrf" value="' . waRequest::cookie('_csrf', '') . '" />';
    }

    /**
     * Get public/protected path to file
     * @param string $file - filename or path
     * @param $original - if true - return original path to file
     * @return string - protected path to file
     */
    public static function path($file, $original = false)
    {
        $path = wa()->getDataPath($file, true, 'multiform', true);
        if ($original) {
            return dirname(__FILE__) . '/../../' . $file;
        }
        if (!file_exists($path)) {
            waFiles::copy(dirname(__FILE__) . '/../../' . $file, $path);
        }
        return $path;
    }

    public static function secureString($str, $mode = ENT_QUOTES, $charset = 'UTF-8')
    {
        return htmlentities($str, $mode, $charset);
    }

    /**
     * Write to log
     *
     * @param string $message
     */
    public static function log($message)
    {
        $path = wa('multiform')->getConfig()->getPath('log');
        waFiles::create($path . '/multiform/multiform.log');
        waLog::log($message, 'multiform/multiform.log');
    }

    /**
     * Count days in months
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    public static function days_in_month($month, $year)
    {
        $month = $month ? (int) $month : date('n');
        $year = $year ? (int) $year : date("Y");
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }

    /**
     * Get domain routes
     *
     * @param string $domain
     * @return array
     */
    public static function getRoutes($domain)
    {
        $storefronts = array();
        $routing = wa()->getRouting();

        $routes = $routing->getRoutes($domain);

        foreach ($routes as $route) {
            $storefronts[] = $domain . '/' . $route['url'];
        }

        return $storefronts;
    }

    /**
     * Add form CSS files and copy dirs if nesessary
     *
     * @return string
     */
    public static function addCss()
    {
        $is_debug = waSystemConfig::isDebug();
        return '<link rel="stylesheet" href="' . wa()->getAppStaticUrl('multiform', true) . 'css/frontend.' . (!$is_debug ? 'min.' : '') . 'css?v=' . wa('multiform')->getVersion() . '">';
    }

    /**
     * Add form JS
     *
     * @return string
     */
    public static function addJs()
    {
        $is_debug = waSystemConfig::isDebug();

        $locale = wa()->getLocale();
        $frontend_js_locale_path = wa()->getAppPath('locale/' . $locale . '/LC_MESSAGES/multiform_js_frontend.json', 'multiform');
        $frontend_locale_strings = "{}";
        if (file_exists($frontend_js_locale_path)) {
            $frontend_locale_strings = file_get_contents($frontend_js_locale_path);
        }

        $js = '<script src="' . wa()->getAppStaticUrl('multiform', true) . 'js/frontend.' . (!$is_debug ? 'min.' : '') . 'js?v=' . wa('multiform')->getVersion() . '"></script>';
        $js .= '<script>
                    (function($) { 
                        $(function() { 
                            $.multiformFrontend.init({ 
                                url: "' . wa()->getRouteUrl('multiform/frontend', array("domain" => wa('multiform')->getConfig()->getDomain()), true) . '", 
                                appUrl: "' . wa()->getAppStaticUrl('multiform', true) . '", 
                                locale: "' . $locale . '",
                                frontendLocaleStrings: ' . $frontend_locale_strings . '
                            }); 
                        }); 
                    })(jQuery)
                </script>';

        return $js;
    }

    /**
     * Get all field classes
     *
     * @param array $field
     * @return string
     */
    public static function getFieldCssClasses($field)
    {
        $field_class = array("multiform-gap-field", "type-" . $field['type']);
        if (!empty($field['required'])) {
            $field_class[] = "s-required";
        }
        if ($field['title_pos']) {
            $field_class[] = "pos-" . $field['title_pos'];
        }
        $field_class[] = self::getBlockWidth($field);
        if (!empty($field['condition_id'])) {
            $field_class[] = "has-condition";
            if (!empty($field['hide'])) {
                $field_class[] = "multiform-hide";
            }
        }
        if (!empty($field['has_error'])) {
            $field_class[] = "multiform-error-field";
        }
        return implode(" ", $field_class);
    }

    public static function getFieldWidth($field)
    {
        $class = 'mf-colm8';
        if (!empty($field['sizes']['field_width']) && wa()->getEnv() == 'frontend') {
            $class = 'mf-' . $field['sizes']['field_width'];
        }
        return $class;
    }

    public static function getFieldSizeWidth($field)
    {
        $class = 'mf-colm6';
        if (!empty($field['sizes']['width'])) {
            $class = 'mf-' . $field['sizes']['width'];
        }
        return $class;
    }

    public static function getTitleWidth($field)
    {
        $class = 'mf-colm4';
        if (!empty($field['sizes']['title_width']) && wa()->getEnv() == 'frontend') {
            $class = 'mf-' . $field['sizes']['title_width'];
        }
        return $class;
    }

    public static function getBlockWidth($field)
    {
        $class = 'mf-colm12';
        if (!empty($field['sizes']['block_width']) && wa()->getEnv() == 'frontend') {
            $class = 'mf-' . $field['sizes']['block_width'];
        }
        return $class;
    }

    /**
     * Get formatted record id
     *
     * @param int $record_id
     * @param array $form
     * @return string
     */
    public static function formatRecordId($record_id, $form)
    {
        $form['record_format'] = isset($form['record_format']) ? $form['record_format'] : '#100{$record_id}';
        return str_replace('{$record_id}', $record_id, $form['record_format']);
    }

    /**
     * Get user IP
     *
     * @return string
     */
    public static function getUserIp()
    {
        $ip = waRequest::getIp();
        if (strpos($ip, ",")) {
            $ips = array_values(array_filter(explode(',', $ip)));
            return end($ips);
        }
        return $ip;
    }

    /**
     * Check form for limitations
     *
     * @param array $form
     * @return bool
     */
    public static function formLimitationErrors($form)
    {
        $mr = new multiformRecordModel();

        // Ограничение по количеству записей формы
        if (!empty($form['form_submissions']) && $form['form_submissions'] == 'limit') {
            $error = "Form has reached submission limit";
            switch ($form['form_submit_limit_freq']) {
                case 'day':
                case 'week':
                case 'month':
                    $count = $mr->query("SELECT COUNT(*) FROM {$mr->getTableName()} WHERE create_datetime > DATE_SUB(NOW(), INTERVAL 1 " . strtoupper($form['form_submit_limit_freq']) . ") AND status = '1' AND form_id = '" . (int) $form['id'] . "'")->fetchField();
                    $error .= ' of the ' . $form['form_submit_limit_freq'];
                    break;
                case 'ever':
                default:
                    $count = $mr->countByField(array("status" => 1, "form_id" => $form['id']));
            }

            if ($form['form_submit_limit'] <= $count) {
                return _wd('multiform', $error);
            }
        }

        // Ограничение по количеству записей пользователя
        $contact_id = wa()->getUser()->getId();
        if (!empty($form['form_submissions_auth']) && $form['form_submissions_auth'] == 'limit' && $contact_id) {
            $error = "You have reached submission limit";
            switch ($form['form_submit_limit_freq_auth']) {
                case 'day':
                case 'week':
                case 'month':
                    $count = $mr->query("SELECT COUNT(*) FROM {$mr->getTableName()} WHERE create_datetime > DATE_SUB(NOW(), INTERVAL 1 " . strtoupper($form['form_submit_limit_freq_auth']) . ") AND status = '1' AND create_contact_id = '" . (int) $contact_id . "' AND form_id = '" . (int) $form['id'] . "'")->fetchField();
                    $error .= ' of the ' . $form['form_submit_limit_freq_auth'];
                    break;
                case 'ever':
                default:
                    $count = $mr->countByField(array("status" => 1, "form_id" => $form['id'], "create_contact_id" => $contact_id));
            }

            if ($form['form_submit_limit_auth'] <= $count) {
                return _wd('multiform', $error);
            }
        }

        // Ограничение по IP
        if (!empty($form['form_submissions_ip'])) {
            $userIp = multiformHelper::getUserIp();
            if ($mr->countByField(array("form_id" => $form['id'], "status" => 1, "ip" => $userIp)) >= 1) {
                return _wd('multiform', "Form has reached submission limit");
            }
        }

        return false;
    }

    /**
     * Get field names for CSV export file
     *
     * @param int $form_id
     * @return array
     */
    public static function getMapFields($form_id)
    {
        $map = array();

        $mf = new multiformFormModel();
        $form = $mf->getFullForm($form_id);
        if (!empty($form['fields'])) {
            $map['m_record_id'] = _w("Record ID(#)");

            foreach ($form['fields'] as $f) {
                if (in_array($f['type'], ['html', 'section', 'button']) || !$f['status']) {
                    continue;
                }
                if (($f['type'] == 'checkbox' || $f['type'] == 'table_grid') && !empty($f['option_fields'])) {
                    $options = $f['type'] == 'checkbox' ? $f['option_fields'] : $f['option_fields']['statement'];
                    foreach ($options as $o) {
                        $map[$f['type'] . '_' . $f['id'] . '_' . $o['id']] = ($f['type'] == 'checkbox' ? ($f['title'] ? $f['title'] : $f['type'] . '_' . $f['id'] . '_' . $o['id']) : ($o['value'] ? $o['value'] : $f['type'] . '_' . $f['id'] . '_' . $o['id']));
                    }
                }
                $map[$f['type'] . '_' . $f['id']] = $f['title'] ? $f['title'] : $f['type'] . '_' . $f['id'];
            }

            $map['m_created'] = _w("Created");
            $map['m_created_by'] = _w("Created by");
            $map['m_updated'] = _w("Updated");
            $map['m_updated_by'] = _w("Updated by");
            $map['m_ip'] = _w("IP");
            $map['m_status'] = _w("Status");
        }

        return $map;
    }

    /**
     * Check if user has full access to form
     *
     * @param array $form
     * @return bool
     */
    public static function hasFullFormAccess($form)
    {
        if (empty($form)) {
            return false;
        }
        return !!(wa()->getUser()->getRights("multiform", "forms.{$form['id']}") == 4 || (wa()->getUser()->getRights("multiform", "create") && $form['create_contact_id'] == wa()->getUser()->getId()) || wa()->getUser()->isAdmin('multiform'));
    }

    /**
     * Check if user has access to private fields
     *
     * @param array $form
     * @return bool
     */
    public static function privateFieldAccess($form)
    {
        if (empty($form)) {
            return false;
        }
        return !!(wa()->getUser()->getRights("multiform", "forms.{$form['id']}") == 4 || (wa()->getUser()->getRights("multiform", "create") && $form['create_contact_id'] == wa()->getUser()->getId()) || wa()->getUser()->isAdmin('multiform') || wa()->getUser()->getRights("multiform", "forms.{$form['id']}") == 1 || wa()->getUser()->getRights("multiform", "forms.{$form['id']}") == 3);
    }

    /**
     * Get access to records
     *
     * @param array $form
     * @return bool
     */
    public static function recordAccess($form)
    {
        $full_access = !!(wa()->getUser()->getRights("multiform", "records.{$form['id']}") == 3 || (wa()->getUser()->getRights("multiform", "create") && $form['create_contact_id'] == wa()->getUser()->getId()) || wa()->getUser()->isAdmin('multiform'));
        $edit_access = !!(wa()->getUser()->getRights("multiform", "records.{$form['id']}") == 2);
        $read_access = !!(wa()->getUser()->getRights("multiform", "records.{$form['id']}") == 1);
        return ($full_access ? 3 : ($edit_access ? 2 : ($read_access ? 1 : 0)));
    }

    public static function notificationsAccess()
    {
        $user = wa()->getUser();
        if ($user->isAdmin()) {
            return 3;
        } else {
            return wa()->getUser()->getRights("multiform", "notifications");
        }
    }

    /**
     * Check if string is valid URL
     *
     * @param string $string
     * @return bool
     */
    public static function validateUrl($string)
    {
        return !!filter_var($string, FILTER_VALIDATE_URL);
    }

    /**
     * Get formatted default value for the field
     *
     * @param array $field
     * @param array|string|int $dirty_value
     * @return array|string|int
     */
    public static function getFieldDefaultValue($field, $dirty_value)
    {
        $default = null;
        if ($field['type'] !== 'file' && $field['type'] !== 'formula') {
            $default = $dirty_value;
            if (in_array($field['type'], array('radio', 'checkbox', 'select', 'table_grid'))) {
                $clean_default = array();
                if (is_array($default)) {
                    foreach ($default as $d) {
                        if ($field['type'] == 'table_grid') {
                            $parts = explode("-", $d);
                            $clean_default[$parts[0]] = $parts[1];
                        } else {
                            $clean_default[$d] = $d;
                        }
                    }
                } else {
                    if ($field['type'] == 'table_grid') {
                        $parts = explode("-", $default);
                        $clean_default[$parts[0]] = $parts[1];
                    } else {
                        $clean_default[$default] = $default;
                    }
                }
                $default = $clean_default;
            } elseif (is_array($default) && !isset($default['value'])) {
                reset($default);
                $default = current($default);
            }
        }
        return $default;
    }

    public static function getWidthOptionValues($auto = false)
    {
        $values = array(
            array('title' => _w('Type') . ' 1 (8.33%)', 'value' => 'colm1'),
            array('title' => _w('Type') . ' 2 (16.66%)', 'value' => 'colm2'),
            array('title' => _w('Type') . ' 3 (25%)', 'value' => 'colm3'),
            array('title' => _w('Type') . ' 4 (33.33%)', 'value' => 'colm4'),
            array('title' => _w('Type') . ' 5 (41.66%)', 'value' => 'colm5'),
            array('title' => _w('Type') . ' 6 (50%)', 'value' => 'colm6'),
            array('title' => _w('Type') . ' 7 (58.33%)', 'value' => 'colm7'),
            array('title' => _w('Type') . ' 8 (66.66%)', 'value' => 'colm8'),
            array('title' => _w('Type') . ' 9 (75%)', 'value' => 'colm9'),
            array('title' => _w('Type') . ' 10 (83.33%)', 'value' => 'colm10'),
            array('title' => _w('Type') . ' 11 (91.66%)', 'value' => 'colm11'),
            array('title' => _w('Type') . ' 12 (100%)', 'value' => 'colm12'),
        );
        return $auto ? array_merge(array(array('title' => _w('Auto'), 'value' => 'auto')), $values) : $values;
    }

    /**
     * Get default field option settings. For hidden fields replace values
     *
     * @param array $field
     * @param array $field_options
     * @return array
     */
    public static function getDefaultSettings(&$field, $field_options)
    {
        $options = [];
        foreach (['properties', 'display', 'sizes', 'validation'] as $tab_id) {
            if (isset($field_options[$tab_id])) {
                foreach ($field_options[$tab_id] as $k => $field_option) {
                    if (isset($field_option['value'])) {
                        if (!empty($field_option['basic'])) {
                            $options[$k] = $field_option['value'];
                            // Если перед нами скрытое поле - заменяем им переданное значение
                            if (!empty($field_option['hidden']) && isset($field[$k])) {
                                unset($field[$k]);
                            }
                        } else {
                            $options[$tab_id][$k] = $field_option['value'];
                            if (!empty($field_option['hidden']) && isset($field[$tab_id][$k])) {
                                unset($field[$tab_id][$k]);
                            }
                        }
                    }
                }
                if (isset($field[$tab_id]) && isset($options[$tab_id])) {
                    $field[$tab_id] += $options[$tab_id];
                }
            }
        }
        $field += $options;
        return $field;
    }
}
