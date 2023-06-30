<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformConditions
{

    public static function check_conditions($conditions, $form_fields, $post_fields, $form = array())
    {
        foreach ($conditions as $c) {
            if (!empty($c['params'])) {
                $results = array();
                foreach ($c['params'] as $param) {
                    $function_name = 'operator_' . $param['operator'];
                    // Если не существует полей формы, используемых в условии, пропускаем обработку
                    if (!isset($post_fields[$param['source']]) || !isset($form_fields[$param['source']]) || (!$form && !isset($form_fields[$c['target']]))) {
                        continue;
                    }
                    // Проверяем выполнение условия
                    if (method_exists('multiformConditions', $function_name) && self::$function_name(self::getValue($post_fields[$param['source']], $param['source_ext'], $form_fields[$param['source']]['type']), $param['value'])) {
                        $results[] = 1;
                    }
                }

                // Проверка условия and/or
                if ($c['andor'] == 1) {
                    $conditionComplete = count($results) === count($c['params']);
                } else {
                    $conditionComplete = count($results) > 0;
                }

                // Действия с полями формы
                if (!$form) {
                    if ($c['action'] == 'show') {
                        $showTarget = $conditionComplete;
                    } else {
                        $showTarget = !$conditionComplete;
                    }
                    // Если поле необходимо скрыть, то указываем, что его обрабатывать не нужно
                    if (!$showTarget) {
                        $form_fields[$c['target']]['multiform_hide'] = 1;
                        // Если перед нами секция, блокируем всех ее детей, чтобы они не мешали дальнейшей обработки полей 
                        if ($form_fields[$c['target']]['type'] == 'section') {
                            $children = multiformSectionControl::findAllChildrenIds($form_fields, $c['target']);
                            if ($children) {
                                foreach ($children as $ch_id) {
                                    $form_fields[$ch_id]['multiform_hide'] = 1;
                                }
                            }
                        }
                    }
                } // Действия с формой
                elseif ($form && $conditionComplete) {
                    // Перенаправление
                    if ($c['action'] == 'redirect') {
                        $form['active_conditions']['redirect'] = $c['target'];
                    } // Показ сообщения
                    else if ($c['action'] == 'message') {
                        $form['active_conditions']['message'] = $c['target'];
                    } // Отправка email
                    else if ($c['action'] == 'email') {
                        if (!isset($form['active_conditions']['email'])) {
                            $form['active_conditions']['email'] = array();
                        }
                        $form['active_conditions']['email'][] = $c['target'];
                    }
                }
            }
        }
        return $form ? $form : $form_fields;
    }

    private static function getValue($field_value, $option_id, $type)
    {
        switch ($type) {
            case "date":
                $value = time();
                $date = isset($field_value['formatted']) ? $field_value['formatted'] : $field_value['value'];
                if (strpos($date, "-") !== false) {
                    $chunks = explode("-", $date);
                    switch (count($chunks)) {
                        case 3:
                            $value = mktime(0, 0, 0, $chunks[1], $chunks[2], $chunks[0]);
                            break;
                        case 2:
                            $value = mktime(0, 0, 0, $chunks[1], 0, $chunks[0]);
                            break;
                        case 1:
                            $value = mktime(0, 0, 0, 0, 0, $chunks[0]);
                            break;
                    }
                }
                break;
            case "time":
                $time = isset($field_value['formatted']) ? $field_value['formatted'] : $field_value['value'];
                $value = self::getTimeFromString($time);
                break;
            case "checkbox":
                $value = isset($field_value[$option_id]) ? 'checked' : false;
                break;
            case "table_grid":
                $value = isset($field_value['formatted']) && !empty($field_value['formatted'][$option_id]) ? (int) $field_value['formatted'][$option_id] : 0;
                break;
            case "select":
            case "radio":
                $value = !empty($field_value['formatted']) ? (int) $field_value['formatted'] : (int) $field_value['value'];
                break;
            default:
                $instance = multiformFormBuilder::getFieldInstance($type);

                $method_name = 'condition_value';
                if (method_exists($instance, $method_name)) {
                    $value = $instance->$method_name($field_value, $option_id);
                } else {
                    $value = $field_value;
                }
        }
        return $value;
    }

    private static function getTimeFromString($string)
    {
        $value = mktime(0, 0, 0, 1, 1, 1970);
        if (strpos($string, ":") !== false) {
            $chunks = explode(":", $string);
            if (count($chunks)) {
                $value = mktime(ifempty($chunks, 0, 0), ifempty($chunks, 1, 0), ifempty($chunks, 2, 0), 1, 1, 1970);
            }
        }
        return $value;
    }

    /* Input operators */

    private static function operator_is($field_value, $value)
    {
        return $field_value == $value;
    }

    private static function operator_is_not($field_value, $value)
    {
        return $field_value !== $value;
    }

    private static function operator_contains($field_value, $value)
    {
        return strpos($field_value, $value) !== false;
    }

    private static function operator_begins_with($field_value, $value)
    {
        return strpos($field_value, $value) === 0;
    }

    private static function operator_ends_with($field_value, $value)
    {
        $strlen = strlen($field_value);
        $testlen = strlen($value);
        if ($testlen > $strlen) {
            return false;
        }
        return substr_compare($field_value, $value, $strlen - $testlen, $testlen) === 0;
    }

    private static function operator_doesnt_contain($field_value, $value)
    {
        return !self::operator_contains($field_value, $value);
    }

    private static function operator_is_blank($field_value, $value)
    {
        return $field_value == '';
    }

    private static function operator_is_not_blank($field_value, $value)
    {
        return $field_value !== '';
    }

    /* Number operators */

    private static function operator_is_equal_to($field_value, $value)
    {
        $epsilon = 0.000001;
        return abs((float) $field_value - (float) $value) < $epsilon;
    }

    private static function operator_is_not_equal_to($field_value, $value)
    {
        $epsilon = 0.000001;
        return abs((float) $field_value - (float) $value) >= $epsilon;
    }

    private static function operator_is_greater_than($field_value, $value)
    {
        return (float) $field_value > (float) $value;
    }

    private static function operator_is_less_than($field_value, $value)
    {
        return (float) $field_value < (float) $value;
    }

    /* Date operators */

    private static function operator_is_on($field_value, $value)
    {
        if (strpos($value, '-') === false) {
            return false;
        }
        $chunks = explode("-", $value);
        if (count($chunks) !== 3) {
            return false;
        }
        $date = mktime(0, 0, 0, $chunks[1], $chunks[2], $chunks[0]);
        return $field_value === $date;
    }

    private static function operator_is_before($field_value, $value)
    {
        if (strpos($value, '-') === false) {
            return false;
        }
        $chunks = explode("-", $value);
        if (count($chunks) !== 3) {
            return false;
        }
        $date = mktime(0, 0, 0, $chunks[1], $chunks[2], $chunks[0]);
        return $field_value < $date;
    }

    private static function operator_is_after($field_value, $value)
    {
        if (strpos($value, '-') === false) {
            return false;
        }
        $chunks = explode("-", $value);
        if (count($chunks) !== 3) {
            return false;
        }
        $date = mktime(0, 0, 0, $chunks[1], $chunks[2], $chunks[0]);
        return $field_value > $date;
    }

    /* Time operators */

    private static function operator_is_at($field_value, $value)
    {
        return $field_value === self::getTimeFromString($value);
    }

    private static function operator_is_time_before($field_value, $value)
    {
        return $field_value < self::getTimeFromString($value);
    }

    private static function operator_is_time_after($field_value, $value)
    {
        return $field_value > self::getTimeFromString($value);
    }

}
