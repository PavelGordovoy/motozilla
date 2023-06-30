<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormula
{

    // Разделитель аргументов в функциях
    const ARG_SEPARATOR = ',';

    // Обработанные функции
    private static $closed_functions = array();
    // Все доступные функции и количество их аргументов
    private static $functions = array(
        'IF' => array('count' => 3),
        'AND' => array('count' => 0),
        'OR' => array('count' => 0),
        'NOT' => array('count' => 1),
        'FALSE' => array('count' => -1),
        'TRUE' => array('count' => -1),
        'GT' => array('count' => 2),
        'GTE' => array('count' => 2),
        'LT' => array('count' => 2),
        'LTE' => array('count' => 2),
        'EQ' => array('count' => 2),
        'NE' => array('count' => 2),
        'NEA' => array('count' => 2),
        'ABS' => array('count' => 1),
        'ACOS' => array('count' => 1),
        'ACOSH' => array('count' => 1),
        'ACOT' => array('count' => 1),
        'ACOTH' => array('count' => 1),
        'ASIN' => array('count' => 1),
        'ASINH' => array('count' => 1),
        'ATAN' => array('count' => 1),
        'ATAN2' => array('count' => 2),
        'ATANH' => array('count' => 1),
        'AVERAGE' => array('count' => 0),
        'AVERAGEA' => array('count' => 0),
        'COS' => array('count' => 1),
        'COSH' => array('count' => 1),
        'COT' => array('count' => 1),
        'COTH' => array('count' => 1),
        'CSC' => array('count' => 1),
        'CSCH' => array('count' => 1),
        'DECIMAL' => array('count' => 2),
        'DEGREES' => array('count' => 1),
        'EXP' => array('count' => 1),
        'FACT' => array('count' => 1),
        'INT' => array('count' => 1),
        'LN' => array('count' => 1),
        'LOG' => array('count' => 2),
        'LOGTEN' => array('count' => 1),
        'MAX' => array('count' => 0),
        'MIN' => array('count' => 0),
        'MOD' => array('count' => 2),
        'ODD' => array('count' => 1),
        'PI' => array('count' => -1),
        'POW' => array('count' => 2),
        'RADIANS' => array('count' => 1),
        'ROUND' => array('count' => 2),
        'SEC' => array('count' => 1),
        'SECH' => array('count' => 1),
        'SIN' => array('count' => 1),
        'SQRT' => array('count' => 1),
        'SQRTPI' => array('count' => 1),
        'TAN' => array('count' => 1),
        'TANH' => array('count' => 1),
        'CHAR' => array('count' => 1),
        'CODE' => array('count' => 1),
        'LEFT' => array('count' => 2),
        'LEN' => array('count' => 1),
        'LOWER' => array('count' => 1),
        'MID' => array('count' => 3),
        'REGEXREPLACE' => array('count' => 3),
        'RIGHT' => array('count' => 2),
        'SEARCH' => array('count' => 3),
        'TRIM' => array('count' => 1),
        'UPPER' => array('count' => 1),
    );
    // Доступные операторы
    private static $operators = array('+', '-', '*', '/', '>', '<', '==', '>=', '<=', '=', '~');

    /**
     * Calculate formula
     * 
     * @param string $formula 
     * @param string $rounding - 'number'|'string'
     * @param array $fields - Form fields with "unique_id" keys
     * @param array $post_fields - Values of all post fields
     * @return string
     */
    public static function execute($formula, $rounding, $fields, $post_fields)
    {
        $value = '';
        if ($formula) {
            // Заменяем поля формулы
            preg_match_all("/{Field:(\d+)(\|string)?}/", $formula, $matches);
            $search = $replacement = array();
            if (!empty($matches[1])) {
                foreach ($matches[1] as $k => $field_id) {
                    if (isset($post_fields[$field_id]) && isset($fields[$field_id])) {
                        $search[] = "/" . str_replace("|", "\|", $matches[0][$k]) . "/";
                        $replacement[] = "'" . self::get_value($fields[$field_id], $post_fields[$field_id], (!empty($matches[2][$k]) && $matches[2][$k] == '|string') ? 'string' : 'number', $rounding) . "'";
                    } else {
                        $search[] = "/" . str_replace("|", "\|", $matches[0][$k]) . "/";
                        $replacement[] = (!empty($matches[2][$k]) && $matches[2][$k] == '|string') ? '' : 0;
                    }
                }
            }
            if ($search && $replacement) {
                $formula = preg_replace($search, $replacement, $formula);
            }
            $value = self::calculate_RPN(self::get_RPN($formula));
        }
        return $value;
    }

    /**
     * Calculate RPN
     * 
     * @param array $rnp
     * @return string
     * @throws waException
     */
    private static function calculate_RPN($rnp)
    {
        $stack = array();
        foreach ($rnp as $token) {
            $function = self::is_function($token);
            if (!in_array($token, self::$operators) && !$function) {
                $stack[] = filter_var($token, FILTER_CALLBACK, array("options" => "multiformFormula::remove_quotes"));
            } else {
                if (in_array($token, self::$operators)) {
                    if (count($stack) < 2) {
                        throw new waException(_w('Invalid expression'));
                    }
                    array_push($stack, self::execute_operator($token, array_pop($stack), array_pop($stack)));
                } elseif ($function) {
                    if ((self::$functions[$function]['count'] > 0 && count($stack) < self::$functions[$function]['count']) ||
                            (self::$functions[$function]['count'] === 0 && (!isset(self::$closed_functions[$token]) || count($stack) < self::$closed_functions[$token]))) {
                        throw new waException(_w('Invalid expression'));
                    }
                    $params = array();
                    $params_count = isset(self::$closed_functions[$token]) ? self::$closed_functions[$token] : self::$functions[$function]['count'];
                    if ($params_count >= 0) {
                        for ($i = 0; $i < $params_count; $i++) {
                            $params[] = filter_var(array_pop($stack), FILTER_CALLBACK, array("options" => "multiformFormula::remove_quotes"));
                        }
                    }
                    array_push($stack, self::execute_function($function, $params));
                }
            }
        }
        if (count($stack) === 1) {
            return array_pop($stack);
        }

        throw new waException(_w('Invalid expression'));
    }

    /**
     * Convert string to Reverse Polish Notation
     * 
     * @param string $string
     * @return array
     */
    private static function get_RPN($string)
    {
        $exit = $stack = $opened_functions = array();
        $length = strlen($string);
        $i = 0;
        while ($i < $length) {
            $char = $string[$i];
            $str = self::is_string($string, $i);
            if ($str !== false) {

                array_push($exit, $str);
                $i += strlen($str);
            } elseif (in_array($char, self::$operators)) {
                $operator = self::get_operator($string, $i);
                $top = end($stack);
                if ($top && self::get_precedence($operator) <= self::get_precedence($top)) {
                    array_push($exit, array_pop($stack));
                }
                array_push($stack, $operator);
                $i += strlen($operator);
            } elseif ($char == '(') {
                array_push($stack, $char);
                $i++;
            } elseif ($char == ')') {
                if (in_array('(', $stack)) {
                    $function_pop = 0;
                    do {
                        $operator = array_pop($stack);
                        if ($operator == '(') {
                            $end = end($stack);
                            if (($end && !in_array($end, self::$operators) && $end !== '(')) {
                                array_push($exit, array_pop($stack));
                                $function_pop = 1;
                            }
                            break;
                        } else {
                            array_push($exit, $operator);
                        }
                    } while ($operator);
                    if ($opened_functions && $function_pop) {
                        self::$closed_functions[key($opened_functions)] = array_pop($opened_functions) + 1;
                        end($opened_functions);
                    }
                }
                $i++;
            } else if ($char == self::ARG_SEPARATOR) {
                if (in_array('(', $stack)) {
                    while (end($stack) !== '(') {
                        array_push($exit, array_pop($stack));
                    }
                }
                // Добавляем вхождение для функции
                if ($opened_functions) {
                    $key = key($opened_functions);
                    $opened_functions[$key] += 1;
                }
                $i++;
            } else {
                if ($function = self::get_function($string, $i)) {
                    $i += strlen($function);
                    // Начинаем подсчет вхождений в функцию
                    $opened_functions[$i . $function] = 0;
                    end($opened_functions);

                    if (self::$functions[$function]['count'] === 0) {
                        $function = $i . $function;
                    }

                    array_push($stack, $function);
                } else {
                    $i++;
                }
            }
        }
        while ($oper_elem = array_pop($stack)) {
            array_push($exit, $oper_elem);
        }
        return $exit;
    }

    /**
     * Call formula function
     * 
     * @param string $function - Function name
     * @param array $params - Function params
     * @return string|bool
     */
    private static function execute_function($function, $params)
    {
        return call_user_func_array(array('multiformFormula', 'func_' . $function), array_reverse($params));
    }

    /**
     * Execute function operator
     * 
     * @param string $operator - should be on of operators from self::$operators
     * @param string $val1
     * @param string $val2
     * @return string|bool
     * @throws waException
     */
    private static function execute_operator($operator, $val1, $val2)
    {
        $val1 = filter_var($val1, FILTER_CALLBACK, array("options" => "multiformFormula::remove_quotes"));
        $val2 = filter_var($val2, FILTER_CALLBACK, array("options" => "multiformFormula::remove_quotes"));

        if ($operator !== '~') {
            $val1 = str_replace(',', '.', $val1);
            $val2 = str_replace(',', '.', $val2);
        }
        if ($operator === '+') {
            return $val1 + $val2;
        } else if ($operator === '-') {
            return $val2 - $val1;
        } else if ($operator === '*') {
            return $val1 * $val2;
        } else if ($operator === '/') {
            if ($val1 === 0) {
                throw new waException(_w('Division by zero'));
            }
            return $val2 / $val1;
        } else if ($operator === '>') {
            return $val2 > $val1;
        } else if ($operator === '>=') {
            return $val2 >= $val1;
        } else if ($operator === '<') {
            return $val2 < $val1;
        } else if ($operator === '<=') {
            return $val2 <= $val1;
        } else if ($operator === '=' || $operator === '==') {
            return $val2 == $val1;
        } else if ($operator === '~') {
            return $val2 . $val1;
        }
        throw new waException(_w('Invalid expression'));
    }

    /**
     * Remove quotes from the beginning and the end of the string
     * 
     * @param string $str
     * @return string
     */
    private static function remove_quotes($str)
    {
        if (strpos($str, '"') === 0 || strpos($str, "'") === 0) {
            $str = substr($str, 1);
        }
        $last_symbol = substr($str, -1);
        if ($last_symbol == '"' || $last_symbol == "'") {
            $str = substr($str, 0, -1);
        }
        return $str;
    }

    /**
     * Check, whether the string is number or not
     * 
     * @param string $string
     * @param int $i
     * @return string|bool - Returns full string/number or FALSE 
     */
    private static function is_string($string, $i)
    {
        // Если первый символ - число (без кавычек)
        if (self::is_number($string[$i])) {
            return self::get_number($string, $i);
        } elseif ($string[$i] == '"' || $string[$i] == "'") {
            return self::get_string($string, $i);
        }

        return false;
    }

    /**
     * Check, whether the char is number
     * 
     * @param string $char
     * @return bool
     */
    private static function is_number($char)
    {
        return (($char == '.') || ($char >= '0' && $char <= '9'));
    }

    /**
     * Check, whether the string is function from self::$functions
     * 
     * @param string $token - Function name
     * @return string|bool - Returns function name or FALSE
     */
    private static function is_function($token)
    {
        if ($token) {
            if (isset(self::$functions[$token])) {
                return $token;
            } else {
                $i = 0;
                $len = strlen($token);
                while (self::is_number($token[$i])) {
                    if (($len - 1) <= $i) {
                        break;
                    }
                    $i++;
                }
                $function = substr($token, $i);
                if (isset(self::$functions[$function])) {
                    return $function;
                }
            }
        }
        return false;
    }

    /**
     * Get operator from string
     * 
     * @param string $string
     * @param int $i
     * @return string
     */
    private static function get_operator($string, $i)
    {
        $operator = $string[$i];
        // Проверяем оператор на наличие второго знака
        if (strlen($string) !== $i + 1) {
            $second_part = in_array($string[$i + 1], self::$operators) ? $string[$i + 1] : '';
            $operator .= $second_part;
        }
        return $operator;
    }

    /**
     * Get full number from string
     * 
     * @param string $string
     * @param int $i
     * @return string
     */
    private static function get_number($string, $i)
    {
        $number = '';
        $len = strlen($string);
        while (self::is_number($string[$i])) {
            $number .= $string[$i];
            if (($len - 1) <= $i) {
                break;
            }
            $i++;
        }
        return $number;
    }

    /**
     * Get string
     * 
     * @param string $string
     * @param int $i
     * @return string
     */
    private static function get_string($string, $i)
    {
        $str = $first = $string[$i];
        $len = strlen($string);
        if ($len > $i + 1) {
            $i++;
            while ($string[$i] !== $first) {
                $str .= $string[$i];
                if (($len - 1) <= $i) {
                    break;
                }
                $i++;
            }
            $str .= $string[$i];
        }
        return $str;
    }

    /**
     * Get function from string
     * 
     * @param string $string
     * @param int $i
     * @return string
     */
    private static function get_function($string, $i)
    {
        $function = '';
        $len = strlen($string);
        for ($i; $i < $len; $i++) {
            if (self::is_number($string[$i]) || $string[$i] == '(') {
                break;
            }
            $function .= $string[$i];
        }
        if (isset(self::$functions[$function])) {
            return $function;
        }
        return null;
    }

    /**
     * Get operator precedence
     * 
     * @param string $operator
     * @return int
     */
    private static function get_precedence($operator)
    {
        if (in_array($operator, array('>', '>=', '<', '<=', '=', '=='))) {
            return 6;
        } elseif ($operator === '*' || $operator === '/') {
            return 4;
        } else if ($operator === '+' || $operator === '-' || $operator === '~') {
            return 2;
        }
        return 1;
    }

    /**
     * Get field value from the post data
     * 
     * @param array $field - field info
     * @param array|int|string $post_value
     * @param string $formula_type - Formula type can be 'number'|'string'. 
     * @param int $rounding
     * @return type
     */
    private static function get_value($field, $post_value, $formula_type, $rounding)
    {
        $value = $formula_type == 'number' ? 0 : '';

        switch ($field['type']) {
            case "checkbox":
                foreach ($post_value as $k => $v) {
                    $field_value = !empty($field['options']['customize_keys']) && isset($field['option_fields'][$k]['formula']) ? $field['option_fields'][$k]['formula'] : '';
                    if ($formula_type == 'number') {
                        $value += self::ROUND($field_value, $rounding);
                    } else {
                        $value .= $field_value;
                    }
                }
                break;
            case "date":
            case "time":
                if (isset($post_value['value'])) {
                    $field_value = $post_value['formatted'];
                    if ($formula_type == 'number') {
                        $value += self::ROUND($field_value, $rounding);
                    } else {
                        $value .= $field_value;
                    }
                }
                break;
            case "select":
            case "radio":
                // Получаем значение для Таблицы
                if (isset($post_value['value'])) {
                    if (!empty($post_value['other'])) {
                        $field_value = $post_value['value'];
                    } else {
                        $field_value = !empty($field['options']['customize_keys']) && isset($field['option_fields'][$post_value['formatted']]['formula']) ? $field['option_fields'][$post_value['formatted']]['formula'] : '';
                    }
                    if ($formula_type == 'number') {
                        $value += self::ROUND($field_value, $rounding);
                    } else {
                        $value .= $field_value;
                    }
                }
                break;
            case "table_grid":
                // Получаем значение для Таблицы
                if (isset($post_value['formatted']) && is_array($post_value['formatted'])) {
                    foreach ($post_value['formatted'] as $st_id => $col_id) {
                        if ($formula_type == 'number') {
                            $field_value = (!empty($field['options']['customize_column_keys']) && !empty($field['option_fields']['column'][$col_id]['formula']) ? (float) $field['option_fields']['column'][$col_id]['formula'] : 0) + (!empty($field['options']['customize_statement_keys']) && !empty($field['option_fields']['statement'][$st_id]['formula']) ? (float) $field['option_fields']['statement'][$st_id]['formula'] : 0);
                            $value += self::ROUND($field_value, $rounding);
                        } else {
                            $field_value = (!empty($field['options']['customize_column_keys']) && !empty($field['option_fields']['column'][$col_id]['formula']) ? (float) $field['option_fields']['column'][$col_id]['formula'] : 0) + (!empty($field['options']['customize_statement_keys']) && !empty($field['option_fields']['statement'][$st_id]['formula']) ? (float) $field['option_fields']['statement'][$st_id]['formula'] : 0);
                            $value .= $field_value;
                        }
                    }
                }
                break;
            case "phone":
            case "email":
            case "textarea":
                $value = $post_value;
                break;
            default:
                $instance = multiformFormBuilder::getFieldInstance($field['type']);
                $method_name = 'formula_value';
                if (method_exists($instance, $method_name)) {
                    $value = $instance->$method_name($field, $post_value, $formula_type, $rounding);
                } else {
                    if ($formula_type == 'number') {
                        $value += self::ROUND($post_value, $rounding);
                    } else {
                        $value .= $post_value;
                    }
                }
        }
        return $value;
    }

    // Получаем в качестве значения число
    private static function parse_number($string)
    {
        return !$string ? 0 : (float) $string;
    }

    // Функция округления 
    private static function ROUND($value, $precision)
    {
        $value = (float) $value;
        if ($precision !== '') {
            if ($precision <= 0) {
                $precision *= (-1);
                $result = round($value, $precision);
            } else {
                $roundUnit = pow(10, $precision);
                $result = ceil($value / $roundUnit) * $roundUnit;
            }
        } else {
            $result = $value;
        }
        return $result;
    }

    // Логическое ЕСЛИ 
    private static function func_IF($condition, $true_statement, $false_statement)
    {
        return $condition ? $true_statement : $false_statement;
    }

    // Логическое И
    private static function func_AND()
    {
        $args = func_get_args();
        $result = true;
        foreach ($args as $arg) {
            if (!$arg) {
                $result = false;
            }
        }
        return $result;
    }

    // Логическое ИЛИ 
    private static function func_OR()
    {
        $args = func_get_args();
        $result = false;
        foreach ($args as $arg) {
            if ($arg) {
                $result = true;
            }
        }
        return $result;
    }

    // Логическое НЕ 
    private static function func_NOT($logical)
    {
        return !$logical;
    }

    private static function func_FALSE()
    {
        return false;
    }

    private static function func_TRUE()
    {
        return true;
    }

    // Логическая: сравнивает 2 значения. Больше 
    private static function func_GT($num1, $num2)
    {
        $num1 = self::parse_number($num1);
        $num2 = self::parse_number($num2);
        return $num1 > $num2;
    }

    // Логическая: сравнивает 2 значения. Больше или равно
    private static function func_GTE($num1, $num2)
    {
        $num1 = self::parse_number($num1);
        $num2 = self::parse_number($num2);
        return $num1 >= $num2;
    }

    // Логическая: сравнивает 2 значения. Меньше 
    private static function func_LT($num1, $num2)
    {
        $num1 = self::parse_number($num1);
        $num2 = self::parse_number($num2);
        return $num1 < $num2;
    }

    // Логическая: сравнивает 2 значения. Меньше или равно
    private static function func_LTE($num1, $num2)
    {
        $num1 = self::parse_number($num1);
        $num2 = self::parse_number($num2);
        return $num1 <= $num2;
    }

    // Логическая: сравнивает 2 значения. Равно 
    private static function func_EQ($value1, $value2)
    {
        return $value1 == $value2;
    }

    // Логическая: сравнивает 2 значения. Равно
    private static function func_EQA($value1, $value2)
    {
        return $value1 === $value2;
    }

    // Логическая: сравнивает 2 значения. Не равно
    private static function func_NE($value1, $value2)
    {
        return $value1 != $value2;
    }

    // Логическая: сравнивает 2 значения. Не равно 
    private static function func_NEA($value1, $value2)
    {
        return $value1 !== $value2;
    }

    // Математическая: возвращает модуль
    private static function func_ABS($number)
    {
        return abs(self::parse_number($number));
    }

    // Математическая: возвращает арккосинус 
    private static function func_ACOS($number)
    {
        $number = self::parse_number($number);
        return acos($number);
    }

    // Математическая: возвращает гиперболический арккосинус
    private static function func_ACOSH($number)
    {
        $number = self::parse_number($number);
        return acosh($number);
    }

    // Математическая: возвращает арккотангенс 
    private static function func_ACOT($number)
    {
        $number = self::parse_number($number);
        return atan(1 / $number);
    }

    // Математическая: возвращает гиперболический арккотангенс
    private static function func_ACOTH($number)
    {
        $number = self::parse_number($number);
        return 0.5 * log(($number + 1) / ($number - 1));
    }

    // Математическая: возвращает арксинус
    private static function func_ASIN($number)
    {
        $number = self::parse_number($number);
        return asin($number);
    }

    // Математическая: возвращает гиперболический арксинус
    private static function func_ASINH($number)
    {
        $number = self::parse_number($number);
        return asinh($number);
    }

    // Математическая: возвращает арктангенс
    private static function func_ATAN($number)
    {
        $number = self::parse_number($number);
        return atan($number);
    }

    // Математическая: возвращает арктангенс для заданных координат
    private static function func_ATAN2($number_x, $number_y)
    {
        $number_x = self::parse_number($number_x);
        $number_y = self::parse_number($number_y);
        return atan2($number_x, $number_y);
    }

    // Математическая: возвращает гиперболический арктангенс 
    private static function func_ATANH($number)
    {
        $number = self::parse_number($number);
        return atanh($number);
    }

    // Математическая: вычисляет среднее 
    private static function func_AVERAGE()
    {
        $range = func_get_args();
        $sum = $count = 0;
        foreach ($range as $r) {
            $sum += $r;
            $count += 1;
        }
        return $sum / $count;
    }

    // Математическая: вычисляет среднее арифметическое 
    private static function func_AVERAGEA()
    {
        $range = func_get_args();
        $sum = $count = 0;
        foreach ($range as $r) {
            if ($r === true) {
                $sum++;
            } else {
                $sum += $r;
            }
            if ($r !== null) {
                $count++;
            }
        }
        return $sum / $count;
    }

    // Математическая: возвращает косинус 
    private static function func_COS($number)
    {
        $number = self::parse_number($number);
        return cos($number);
    }

    // Математическая: возвращает гиперболический косинус
    private static function func_COSH($number)
    {
        $number = self::parse_number($number);
        return cosh($number);
    }

    // Математическая: возвращает котангенс
    private static function func_COT($number)
    {
        $number = self::parse_number($number);
        return 1 / tan($number);
    }

    // Математическая: возвращает гиперболический котангенс
    private static function func_COTH($number)
    {
        $number = self::parse_number($number);
        $e2 = exp(2 * $number);
        return ($e2 + 1) / ($e2 - 1);
    }

    // Математическая: возвращает косеканс 
    private static function func_CSC($number)
    {
        $number = self::parse_number($number);
        return 1 / sin($number);
    }

    // Математическая: возвращает гиперболический косеканс
    private static function func_CSCH($number)
    {
        $number = self::parse_number($number);
        return 2 / (exp($number) - exp((-1) * $number));
    }

    // Математическая: возвращает десятичное из любой системы счисления
    private static function func_DECIMAL($number, $radix)
    {
        $radix = $radix || 10;
        return intval($number, $radix);
    }

    // Математическая: преобразует радианы в градусы 
    private static function func_DEGREES($rad)
    {
        $rad = self::parse_number($rad);
        return $rad * 180 / pi();
    }

    // Математическая: возвращает экспоненту числа 
    private static function func_EXP($number)
    {
        $number = self::parse_number($number);
        return exp($number);
    }

    // Математическая: возвращает факториал числа
    private static function func_FACT($number)
    {
        static $MEMOIZED_FACT = array();
        $number = self::parse_number($number);
        $n = floor($number);
        $n = $n > 170 ? 170 : $n;
        if ($n === 0 || $n === 1) {
            return 1;
        } else if ($MEMOIZED_FACT[$n] > 0) {
            return $MEMOIZED_FACT[$n];
        } else {
            $MEMOIZED_FACT[$n] = self::func_FACT($n - 1) * $n;
            return $MEMOIZED_FACT[$n];
        }
    }

    // Математическая: Округляет до ближайшего меньшего целого 
    private static function func_INT($number)
    {
        $number = self::parse_number($number);
        return floor($number);
    }

    // Математическая: вычисляет натуральный логарифм
    private static function func_LN($number)
    {
        $number = self::parse_number($number);
        return log($number);
    }

    // Математическая: вычисляет логарифм
    private static function func_LOG($number, $base = 10)
    {
        $number = self::parse_number($number);
        $base = self::parse_number($base);
        return log($number, $base);
    }

    // Математическая: вычисляет десятичный логарифм 
    private static function func_LOGTEN($number)
    {
        $number = self::parse_number($number);
        return log10($number);
    }

    // Математическая: вычисляет максимум 
    private static function func_MAX()
    {
        $range = func_get_args();
        return (count($range) === 0) ? 0 : max($range);
    }

    // Математическая: вычисляет минимум 
    private static function func_MIN()
    {
        $range = func_get_args();
        return (count($range) === 0) ? 0 : min($range);
    }

    // Математическая: вычисляет остаток от деления 
    private static function func_MOD($dividend, $divisor)
    {
        $dividend = self::parse_number($dividend);
        $divisor = self::parse_number($divisor);
        if ($divisor === 0) {
            $divisor = 1;
        }
        $modulus = abs(fmod($dividend, $divisor));
        return ($divisor > 0) ? $modulus : (-1) * $modulus;
    }

    // Математическая: округляет до ближайшего целого нечетного.
    private static function func_ODD($number)
    {
        $number = self::parse_number($number);
        $temp = ceil(abs($number));
        $temp = ($temp & 1) ? $temp : $temp + 1;
        return ($number > 0) ? $temp : (-1) * $temp;
    }

    // Математическая: возвращает число PI 
    private static function func_PI()
    {
        return pi();
    }

    // Математическая: возведение в степень 
    private static function func_POW($number, $power)
    {
        $number = self::parse_number($number);
        $power = self::parse_number($power);
        return pow($number, $power);
    }

    // Математическая: преобразует градусы в радианы 
    private static function func_RADIANS($number)
    {
        $number = self::parse_number($number);
        return $number * pi() / 180;
    }

    // Математическая: округление с точностью
    private static function func_ROUND($number, $precision)
    {
        $number = self::parse_number($number);
        $precision = self::parse_number($precision);
        return self::ROUND($number, $precision);
    }

    // Математическая: вычисляет секанс 
    private static function func_SEC($number)
    {
        $number = self::parse_number($number);
        return 1 / cos($number);
    }

    // Математическая: вычисляет гиперболический секанс 
    private static function func_SECH($number)
    {
        $number = self::parse_number($number);
        return 2 / (exp($number) + exp((-1) * $number));
    }

    // Математическая: вычисляет синус
    private static function func_SIN($number)
    {
        $number = self::parse_number($number);
        return sin($number);
    }

    // Математическая: вычисляет гиперболический синус
    private static function func_SINH($number)
    {
        $number = self::parse_number($number);
        return sinh($number);
    }

    // Математическая: вычисляет квадратный корень числа 
    private static function func_SQRT($number)
    {
        $number = self::parse_number($number);
        if ($number < 0) {
            $number = 0;
        }
        return sqrt($number);
    }

    // Математическая: вычисляет квадратный корень из значения выражения (число*PI)
    private static function func_SQRTPI($number)
    {
        $number = self::parse_number($number);
        return sqrt($number * pi());
    }

    // Математическая: вычисляет тангенс 
    private static function func_TAN($number)
    {
        $number = self::parse_number($number);
        return tan($number);
    }

    // Математическая: вычисляет гиперболический тангенс 
    private static function func_TANH($number)
    {
        $number = self::parse_number($number);
        return tanh($number);
    }

    // Текстовая: возвращает символ с заданным кодом
    private static function func_CHAR($number)
    {
        $number = self::parse_number($number);
        return chr($number);
    }

    // Текстовая: возвращает код символа 
    private static function func_CODE($text)
    {
        $text = $text ? $text : '';
        return ord($text);
    }

    // Текстовая: возвращает указанное количество знаков с начала строки текста 
    private static function func_LEFT($text, $number = 1)
    {
        $number = self::parse_number($number);
        return $text ? mb_substr($text, 0, $number, 'UTF-8') : '';
    }

    // Текстовая: возвращает длину строки
    private static function func_LEN($text)
    {
        return mb_strlen($text, "UTF-8");
    }

    // Текстовая: переводит строку в нижний регистр
    private static function func_LOWER($text)
    {
        return $text ? mb_strtolower($text, 'UTF-8') : $text;
    }

    // Текстовая: возвращает подстроку
    private static function func_MID($text, $start, $length)
    {
        $start = self::parse_number($start);
        $length = self::parse_number($length);
        $begin = $start;
        $end = $length;

        return mb_substr($text, $begin, $end, 'UTF-8');
    }

    // Текстовая: замена по регулярному выражению
    private static function func_REGEXREPLACE($text, $regular_expression, $replacement)
    {
        return preg_replace('/' . $regular_expression . '/', $replacement, $text);
    }

    // Текстовая: возвращает указанное количество знаков с конца строки текста
    private static function func_RIGHT($text, $number = 1)
    {
        $number = self::parse_number($number);
        return $text ? mb_substr($text, mb_strlen($text, 'UTF-8') - $number, $number, 'UTF-8') : null;
    }

    // Текстовая: возвращает номер символа в строке
    private static function func_SEARCH($find_text, $within_text, $position = 0)
    {
        $foundAt = mb_strpos(mb_strtolower($within_text, 'UTF-8'), mb_strtolower($find_text, 'UTF-8'), $position, 'UTF-8');
        return ($foundAt === false) ? -1 : $foundAt;
    }

    // Текстовая: обрезает пустые поля с обеих сторон строки
    private static function func_TRIM($text)
    {
        return trim($text);
    }

    // Текстовая: переводит строку в верхний регистр
    private static function func_UPPER($text)
    {
        return mb_strtoupper($text, 'UTF-8');
    }

}
