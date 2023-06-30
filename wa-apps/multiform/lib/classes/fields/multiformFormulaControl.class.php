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
class multiformFormulaControl extends multiformFormBuilder
{

    private $functions = array();

    public function preExecute(&$params, $field)
    {
        $this->getFormulaFunctions();

        $params['visibility'] = (!empty($field['display']['formula_visibility']) ? $field['display']['formula_visibility'] : $params['display']['formula_visibility']['value']);
        $params['formula'] = (!empty($field['formula']['formula_mode']) ? $field['formula']['formula_mode'] : "");
        $params['rounding'] = (isset($field['validation']['rounding']) ? $field['validation']['rounding'] : $params['validation']['rounding']['value']);
        $params['unique_id'] = $field['unique_id'];
        $params['field_id'] = $field['id'];
        $params['form_id'] = $field['form_id'];
        $params['form_uid'] = self::getFormId();
        $params['functions'] = $this->functions;
        $params['field_size'] = multiformHelper::getFieldSizeWidth($field);

        // Необходимо ли отображать для пользователя формулу?
        if ($params['visibility'] == 'hide' && wa()->getEnv() == 'frontend') {
            $field_attr = array(
                "class" => multiformHelper::getFieldCssClasses($field),
                "data-id" => $field['unique_id'],
                "data-type" => $field['type'],
                "data-field-id" => $field['id'],
                "id" => "multiformField" . $field['id'] . ($params['form_uid'] ? '_' . $params['form_uid'] : '')
            );
            if (!empty($field['condition_id'])) {
                $field_attr["data-condition"] = $field['condition_id'];
            }
            $params['control_wrapper'] = "<div style='display: none;' " . self::addCustomParams(array_keys($field_attr), $field_attr) . ">"
                    . "%s"
                    . "<div class='multiform-gap-value " . multiformHelper::getFieldWidth($field) . "'>"
                    . (!empty($field['display']['prefix']) ? "<div class='prefix'>" . $field['display']['prefix'] . "</div>" : "")
                    . "%s"
                    . (!empty($field['display']['suffix']) ? "<div class='suffix'>" . $field['display']['suffix'] . "</div>" : "")
                    . "%s"
                    . "</div>"
                    . "</div>";
        }

        if (wa()->getEnv() == 'backend') {
            $field_class = array("type-" . $field['type']);
            if ($field['title_pos']) {
                $field_class[] = "pos-" . $field['title_pos'];
            }
            $field_class[] = multiformHelper::getBlockWidth($field);

            // Получаем все доступные функции
            $functions = array();
            foreach ($this->functions as $func) {
                foreach ($func as $f_name => $f) {
                    $functions[] = $f_name;
                }
            }

            $params['formula'] = preg_replace("/(\%)/", "", $params['formula']);
            $formula = strip_tags(multiformHelper::secureString($params['formula']));
            $formula = preg_replace(array("/(\+|\-|\/|\*|\~)/", "/({Field:\d+(\|string)?})/", "/(\(|\))/", "/(" . implode("|", $functions) . ")/"), array("<font class='field-helper s-formula'>\$1</font>", "<font class='field-helper'>\$1</font>", "<font class='field-helper s-bracket'>\$1</font>", "<font class='field-helper s-function'>\$1</font>"), $formula);
            $params['control_wrapper'] = "<div class='multiform-gap-field " . implode(" ", $field_class) . "' data-id='" . $field['unique_id'] . "' data-field-id='" . $field['id'] . "'>"
                    . "%s"
                    . (!empty($field['title']) ? '<div class="multiform-gap-name '.multiformHelper::getTitleWidth($field).'">' . multiformHelper::secureString($field['title']) . '</div>' : '')
                    . "<div class='multiform-gap-value " . multiformHelper::getFieldWidth($field) . "'>"
                    . (!empty($field['display']['prefix']) ? "<div class='prefix'>" . $field['display']['prefix'] . "</div>" : "")
                    . "%s"
                    . (!empty($field['display']['suffix']) ? "<div class='suffix'>" . $field['display']['suffix'] . "</div>" : "")
                    . "%s"
                    . "</div>"
                    . "<div class=\"formula-field\">
                            <div class=\"left-side\">f(x)</div>
                            <div class=\"right-side\"><span>&#65309;</span>
                                <textarea style=\"display: none\">" . multiformHelper::secureString($params['formula']) . "</textarea>
                                <div class=\"formula-value\">" . $formula . "</div>
                            </div>
                        </div>"
                    . "</div>";
        }
    }

    public static function execute($name, $params)
    {
        $control = "";
        $control_name = multiformHelper::secureString($name);
        $params['class'][] = 'multiform-formula';
        $params['class'][] = $params['field_size'];
        $control .= "<div " . self::addCustomParams(array('class'), $params) . (isset($params['record_value']) ? " style='display: none'" : '') . " id=\"{$params['id']}\">";
        $control .= "<span>&nbsp;</span>";
        $control .= "</div>";
        $control .= "<input type=\"" . (isset($params['record_value']) ? 'text' : 'hidden') . "\" name=\"{$control_name}\"" . (!empty($params['disabled']) ? " disabled" : "") . (isset($params['record_value']) ? " value = '" . (is_array($params['record_value']) ? multiformHelper::secureString(reset($params['record_value'])) : multiformHelper::secureString($params['record_value'])) . "'" : "") . ">";
        // Если не переданы значения записи, значит мы не на странице редактирования
        if (!isset($params['record_value'])) {
            if (wa()->getEnv() == 'backend') {
                static $js = null;
                if (!$js) {
                    $js = "<script src=\"" . wa()->getAppStaticUrl('multiform', true) . "js/jquery.textareaExpand.min.js\"></script>";
                }
                $control .= $js;
            } else {
                static $fields = array();
                static $loaded_form = array();

                $js_inline = "";
                if (!isset($fields[$params['form_id']])) {
                    $mf = new multiformFieldModel();
                    $form_fields = $mf->getByField('form_id', $params['form_id'], 'id');
                    foreach ($form_fields as $field_id => $f) {
                        if ($f['unique_id']) {
                            $fields[$params['form_id']][$f['unique_id']] = $f;
                        } else {
                            $fields[$params['form_id']][$f['id']] = $f;
                        }
                    }
                    self::addToJs("<script src=\"" . wa()->getAppStaticUrl('multiform', true) . "js/formula.js\"></script>", 'formula');
                }
                if (!isset($loaded_form[$params['form_id']])) {
                    $js_inline .= "$(\".multiform-wrap[data-id='{$params['form_id']}']\").on('change keydown paste input multiform-loaded-{$params['form_uid']}', function() {
                                        $.multiformFrontend.updateFormula($(this)); 
                                    });";
                    $loaded_form[$params['form_id']] = 1;
                }
                $search = $replacement = array();
                // Строим формулу
                $js_inline .= "$.multiformFrontend['formulaFunction" . $params['field_id'] . "_" . $params['form_uid'] . "'] = function(form) {
                                    return (";
                foreach ($params['functions'] as $func) {
                    foreach ($func as $f_name => $f) {
                        $search[] = "/({$f_name}\()/";
                        $replacement[] = "$.multiformFrontend.formula.{$f_name}(";
                    }
                }
                // Заменяем поля формулы
                preg_match_all("/{Field:(\d+)(\|string)?}/", $params['formula'], $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $k => $field_id) {
                        if (isset($fields[$params['form_id']][$field_id])) {
                            $search[] = "/" . str_replace("|", "\|", $matches[0][$k]) . "/";
                            $replacement[] = "$.multiformFrontend.formula.getValue(form,{$fields[$params['form_id']][$field_id]['id']}, \"{$params['form_uid']}\", \"{$fields[$params['form_id']][$field_id]['type']}\", \"" . ((!empty($matches[2][$k]) && $matches[2][$k] == '|string') ? 'string' : 'number') . "\", \"{$params['rounding']}\")";
                        }
                    }
                }

                $search[] = "/\~/";
                $replacement[] = "+''+";
                $formula = preg_replace($search, $replacement, $params['formula']);
                $js_inline .= $formula . "); };";
                self::addToJs($js_inline);
            }
        }
        return $control;
    }

    public function prepareFormula_modeSettingsParams(&$params, $field, $tab_id, $property_id)
    {
        $params['form_id'] = $field['form_id'];
    }

    public function GetFormulaModeSettingsControl($name, $params)
    {
        static $fields = array();

        $control_name = multiformHelper::secureString($name);

        $control = "<div class=\"s-formula-mode\">";
        $control .= "<div class=\"s-formula-buttons\">";
        $control .= "<a href=\"#/formula/enable\" class=\"js-action button red s-enable\">" . _w("Enable formula mode") . "</a>";
        $control .= "<a href=\"#/formula/cancel\" style=\"display: none;\" class=\"js-action button yellow s-cancel\">" . _w("Cancel") . "</a>";
        $control .= "<a href=\"#/formula/save\" style=\"display: none;\" class=\"js-action button green\">" . _w("Save") . "</a>";
        $control .= "</div>";
        $control .= "<div class='errormsg'></div>";

        $control .= "<input type=\"hidden\" name=\"{$control_name}\" class=\"f-formula-clon\" value=\"" . multiformHelper::secureString($params['value']) . "\">";

        if (!$fields) {
            $mf = new multiformFieldModel();
            $fields = $mf->getDataById($params['form_id'], 'form');
        }

        $control .= "<a href=\"javascript:void(0)\" class=\"inline-link corner-right-top\" onclick=\"$(this).next().toggle()\" style=\"top: 50px;\"><b>" . _w("Show helper") . "</b></a>";
        $control .= "<div class=\"info-helper\">"
                . "<span><i class=\"icon16 light-bulb\"></i> " . _w("For the fields, the result of which is a number, you can use arithmetic operators: <b>+</b>, <b>-</b>, <b>*</b>, <b>/</b>") . "</span><br>"
                . "<span><i class=\"icon16 light-bulb\"></i> " . _w("For string results you can use <b>~</b> to concatenate results") . "</span>"
                . "<br><i class=\"icon16 exclamation\"></i> " . _w("Any syntax errors may cause incorrect calculations!") . "<br>"
                . "<p style=\"margin: 10px 0;\"><span class=\"demo-formula int\">{Field:168}</span> - " . _w("the result of this fields will be a number (use modifier `string` to change the type of the result)") . "</p>"
                . "<p style=\"margin: 10px 0 0;\"><span class=\"demo-formula\">{Field:168}</span> - " . _w("the result of this fields will be a string") . "</p>"
                . "</div>";
        $control .= "<div class=\"s-formula-fields\">"
                . "<ul class=\"menu-v fields-list\">";
        foreach ($fields as $f_id => $f) {
            if ($f_id !== (int) $params['field_id'] && !in_array($f['type'], array('file', 'html', 'formula', 'section')) && !$f['hidden']) {
                $control .= "<li class=\"" . (!in_array($f['type'], array('email', 'textarea', 'phone', 'date', 'time')) ? "int" : "str") . "\">"
                        . "<a href=\"#/formula/addField/\" class=\"js-action\"><span>" . multiformHelper::secureString($f['title']) . "&nbsp;</span><b>{Field:{$f['unique_id']}}</b></a>"
                        . "</li>";
            }
        }
        $control .= "</ul>";
        $control .= "<h5 style=\"margin-top: 20px; \">" . _w("Functions") . "</h5>";
        $control .= "<div class=\"fields-list lightblue\">";
        foreach ($this->functions as $func_name => $func) {
            $control .= "<h5>" . _w($func_name) . "</h5>";
            $control .= "<ul>";
            $count = 0;
            foreach ($func as $f) {
                $control .= "<li" . ($count > 4 ? " style=\"display: none\"" : "") . ">" . $f . "</li>";
                $count++;
            }
            if ($count > 4) {
                $control .= "<li style=\"list-style: none\"><a class=\"inline-link\" title=\"" . _w("Show more") . "\" style=\"border-bottom: 1px dotted;\" onclick=\"$(this).closest('li').siblings().show(); $(this).closest('li').remove();\" href=\"javascript:void(0)\">" . _w("Show more") . "</a></li>";
            }
            $control .= "</ul>";
        }
        $control .= "</div>";
        $control .= "<h5 style=\"margin-top: 20px; \">" . _w("Modifiers") . "</h5>";
        $control .= "<div class=\"fields-list lightblue\">";
        $control .= "<ul>";
        $control .= "<li><strong>string</strong> - " . _w("convert field result to string.") . "<br>" . _w("Usage:") . "<span class=\"demo-formula int\">{Field:168|string}</span></li>";
        $control .= "</ul>";
        $control .= "</div>";
        $control .= "</div>";
        $control .= "</div>";

        return $control;
    }

    public function beforeFormula_modeSettingsSave($tab_id, $f_id, $field, &$data, $post_data, $response, &$errors)
    {
        // Проверка на синтаксические ошибки
        $formula = $post_data[$tab_id][$f_id];
        $data[$tab_id][$f_id] = $formula;

        if ($formula) {
            if (substr_count($formula, '(') !== substr_count($formula, ')')) {
                $errors['tabs'][] = 'formula';
                throw new waException(_w('Syntax error. Number of opened brackets should be equal to number of closed.'));
            }
            if (substr_count($formula, '{') !== substr_count($formula, '}')) {
                $errors['tabs'][] = 'formula';
                throw new waException(_w('Syntax error. Number of opened braces should be equal to number of closed.'));
            }
        }
    }

    public function validate_field($field, $value, $form_fields, $post_fields)
    {
        static $fields_unique_keys = array();

        $result = array('errors' => array());

        // Если происходит редактирование записи, то пропускаем проверку
        if (wa()->getEnv() == 'backend') {
            return $result;
        }

        // Для работы с формулами создаем массив всех полей, ключами которого будут unique_id 
        if (!$fields_unique_keys) {
            foreach ($form_fields as $ff) {
                $fields_unique_keys[$ff['unique_id']] = $ff;
            }
        }
        $formula = (!empty($field['formula']['formula_mode']) ? $field['formula']['formula_mode'] : "");
        $rounding = (isset($field['validation']['rounding']) ? $field['validation']['rounding'] : 1);
        $checked_value = multiformFormula::execute($formula, $rounding, $fields_unique_keys, $post_fields['unique_ids']);
        // Если проверочное значение не равно тому, которое передал пользователь - сохраняем оба значения
        if ($checked_value != $value) {
            $result['field'] = array($checked_value => $value);
        }

        return $result;
    }

    private function getFormulaFunctions()
    {
        if (!$this->functions) {
            $this->functions = array(
                'Logical' => array(
                    'IF' => '<strong>IF(logical_expression, true_value, false_value)</strong> - ' . _w('Check, whether the logical expression is correct. If it is correct, then function will return true_value, else - false_value.'),
                    'AND' => '<strong>AND(logical1, [logical2], ...)</strong> - ' . _w('Returns TRUE if all its arguments evaluate to TRUE; returns FALSE if one or more arguments evaluate to FALSE.'),
                    'OR' => '<strong>OR(logical1, [logical2], ...)</strong> - ' . _w('Returns TRUE if any argument is TRUE; returns FALSE if all arguments are FALSE.'),
                    'NOT' => '<strong>NOT(logical)</strong> - ' . _w('Reverses the value of its argument. Use NOT when you want to make sure a value is not equal to one particular value.'),
                    'FALSE' => '<strong>FALSE()</strong> - ' . _w('Returns the logical value FALSE.'),
                    'TRUE' => '<strong>TRUE()</strong> - ' . _w('Returns the logical value TRUE.'),
                    'GT' => '<strong>GT(number1, number2)</strong> - ' . _w('Determines if number1 &gt; number2 (greater than).'),
                    'GTE' => '<strong>GTE(number1, number2)</strong> - ' . _w('Determines if number1 &gt;= number2 (greater than or equal to).'),
                    'LT' => '<strong>LT(number1, number2)</strong> - ' . _w('Determines if number1 &lt; number2 (less than).'),
                    'LTE' => '<strong>LTE(number1, number2)</strong> - ' . _w('Determines if number1 &lt;= number2 (less than or equal to).'),
                    'EQ' => '<strong>EQ(number1, number2)</strong> - ' . _w('Determines if number1 == number2 (equal to).'),
                    'NE' => '<strong>NE(number1, number2)</strong> - ' . _w('Determines if number1 != number2 (not equal).'),
                    'NEA' => '<strong>NEA(number1, number2)</strong> - ' . _w('Determines if number1 !== number2 (not equal value or not equal type).'),
                ),
                'Mathematical' => array(
                    'ABS' => '<strong>ABS(number)</strong> - ' . _w('Returns the absolute value of a number. The absolute value of a number is the number without its sign.'),
                    'ACOS' => '<strong>ACOS(number)</strong> - ' . _w('Returns the arccosine, or inverse cosine, of a number.'),
                    'ACOSH' => '<strong>ACOSH(number)</strong> - ' . _w('Returns the inverse hyperbolic cosine of a number.'),
                    'ACOT' => '<strong>ACOT(number)</strong> - ' . _w('Returns the principal value of the arccotangent, or inverse cotangent, of a number.'),
                    'ACOTH' => '<strong>ACOTH(number)</strong> - ' . _w('Returns the inverse hyperbolic cotangent of a number.'),
                    'ASIN' => '<strong>ASIN(number)</strong> - ' . _w('Returns the arcsine, or inverse sine, of a number.'),
                    'ASINH' => '<strong>ASINH(number)</strong> - ' . _w('Returns the inverse hyperbolic sine of a number.'),
                    'ATAN' => '<strong>ATAN(number)</strong> - ' . _w('Returns the arctangent, or inverse tangent, of a number.'),
                    'ATAN2' => '<strong>ATAN2(x_num, y_num)</strong> - ' . _w('Returns the arctangent, or inverse tangent, of the specified x- and y-coordinates.'),
                    'ATANH' => '<strong>ATANH(number)</strong> - ' . _w('Returns the inverse hyperbolic tangent of a number.'),
                    'AVERAGE' => '<strong>AVERAGE(number1, [number2], ...)</strong> - ' . _w('Returns the average (arithmetic mean) of the arguments.'),
                    'AVERAGEA' => '<strong>AVERAGEA(value1, [value2], ...)</strong> - ' . _w('Calculates the average (arithmetic mean) of the values in the list of arguments.'),
                    'COS' => '<strong>COS(number)</strong> - ' . _w('Returns the cosine of the given angle.'),
                    'COSH' => '<strong>COSH(number)</strong> - ' . _w('Returns the hyperbolic cosine of a number.'),
                    'COT' => '<strong>COT(number)</strong> - ' . _w('Return the cotangent of an angle specified in radians.'),
                    'COTH' => '<strong>COTH(number)</strong> - ' . _w('Return the hyperbolic cotangent of a hyperbolic angle.'),
                    'CSC' => '<strong>CSC(number)</strong> - ' . _w('Returns the cosecant of an angle specified in radians.'),
                    'CSCH' => '<strong>CSCH(number)</strong> - ' . _w('Return the hyperbolic cosecant of an angle specified in radians.'),
                    'DECIMAL' => '<strong>DECIMAL(number, radix)</strong> - ' . _w('Converts a text representation of a number in a given base into a decimal number.'),
                    'DEGREES' => '<strong>DEGREES(angle)</strong> - ' . _w('Converts radians into degrees.'),
                    'EXP' => '<strong>EXP(number)</strong> - ' . _w('Returns e raised to the power of number.'),
                    'FACT' => '<strong>FACT(number)</strong> - ' . _w('Returns the factorial of a number.'),
                    'INT' => '<strong>INT(number)</strong> - ' . _w('Rounds a number down to the nearest integer.'),
                    'LN' => '<strong>LN(number)</strong> - ' . _w('Returns the natural logarithm of a number.'),
                    'LOG' => '<strong>LOG(number, base)</strong> - ' . _w('Returns the logarithm of a number to the base you specify.'),
                    'LOGTEN' => '<strong>LOGTEN(number)</strong> - ' . _w('Returns the base-10 logarithm of a number.'),
                    'MAX' => '<strong>MAX(number1, [number2], ...)</strong> - ' . _w('Returns the largest value in a set of values.'),
                    'MIN' => '<strong>MIN(number1, [number2], ...)</strong> - ' . _w('Returns the smallest number in a set of values.'),
                    'MOD' => '<strong>MOD(number, divisor)</strong> - ' . _w('Returns the remainder after number is divided by divisor. The result has the same sign as divisor.'),
                    'ODD' => '<strong>ODD(number)</strong> - ' . _w('Returns number rounded up to the nearest odd integer.'),
                    'PI' => '<strong>PI()</strong> - ' . _w('Returns the number 3.14159265358979, the mathematical constant pi'),
                    'POW' => '<strong>POW(number, power)</strong> - ' . _w('Returns the result of a number raised to a power.'),
                    'RADIANS' => '<strong>RADIANS(number)</strong> - ' . _w('Converts degrees to radians.'),
                    'ROUND' => '<strong>ROUND(number, precision)</strong> - ' . _w('Round number with precision. ROUND(11.478, 0-1) = 11.5; ROUND(11.478, 0-2) = 11.48; ROUND(11.478, 0) = 11; ROUND(11.478, 1) = 20; etc'),
                    'SEC' => '<strong>SEC(number)</strong> - ' . _w('Returns the secant of an angle.'),
                    'SECH' => '<strong>SECH(number)</strong> - ' . _w('Returns the hyperbolic secant of an angle.'),
                    'SIN' => '<strong>SIN(number)</strong> - ' . _w('Returns the sine of the given angle.'),
                    'SQRT' => '<strong>SQRT(number)</strong> - ' . _w('Returns a positive square root.'),
                    'SQRTPI' => '<strong>SQRTPI(number)</strong> - ' . _w('Returns the square root of (number * pi).'),
                    'TAN' => '<strong>TAN(number)</strong> - ' . _w('Returns the tangent of the given angle.'),
                    'TANH' => '<strong>TANH(number)</strong> - ' . _w('Returns the hyperbolic tangent of a number.'),
                ),
                'Text ' => array(
                    'CHAR' => '<strong>CHAR(number)</strong> - ' . _w('Returns the character specified by a number.'),
                    'CODE' => '<strong>CODE(text)</strong> - ' . _w('Returns a numeric code for the first character in a text string.'),
                    'LEFT' => '<strong>LEFT(text, num_chars)</strong> - ' . _w('Returns the first character or characters in a text string, based on the number of characters you specify.'),
                    'LEN' => '<strong>LEN(text)</strong> - ' . _w('Returns the number of characters in a text string.'),
                    'LOWER' => '<strong>LOWER(text)</strong> - ' . _w('Converts all uppercase letters in a text string to lowercase.'),
                    'MID' => '<strong>MID(text, start, length)</strong> - ' . _w('Returns a Variant (String) containing a specified number of characters from a string.'),
                    'REGEXREPLACE' => '<strong>REGEXREPLACE(text, regular_expression, replacement)</strong> - ' . _w('Searches a string for a specified value, or a regular expression, and returns a new string where the specified values are replaced.'),
                    'RIGHT' => '<strong>RIGHT(text, num_chars)</strong> - ' . _w('Returns the last character or characters in a text string, based on the number of characters you specify.'),
                    'SEARCH' => '<strong>SEARCH(find_string, text, position)</strong> - ' . _w('Returns the position of the first occurrence of a specified value in a string.'),
                    'TRIM' => '<strong>TRIM(text)</strong> - ' . _w('Remove whitespace from both sides of a string.'),
                    'UPPER' => '<strong>UPPER(text)</strong> - ' . _w('Converts text to uppercase.'),
                )
            );
        }
        return $this->functions;
    }

    public function fieldOptions()
    {
        return array(
            'control_type' => 'GetFormulaFieldControl',
            'title' => _w('New formula'),
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
                'formula_visibility' => array(
                    'title' => _w('Visibility to user'),
                    'value' => 'show',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'description' => _w("Show the results of calculation to user or not"),
                    'options' => array(
                        array('title' => _w('Show the field'), 'value' => 'show'),
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
            ),
            'formula' => array(
                'formula_mode' => array(
                    'control_type' => waHtmlControl::CUSTOM . ' ' . 'GetFormulaModeSettingsControl',
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
                'width' => array(
                    'title' => _w('Field size'),
                    'value' => 'colm6',
                    'title_type' => 'inline',
                    'control_type' => waHtmlControl::SELECT,
                    'options' => multiformHelper::getWidthOptionValues(),
                ),
            ),
            'validation' => array(
                'rounding' => array(
                    'title' => _w('Number fields rounding'),
                    'value' => '',
                    'title_type' => 'inline',
                    'class' => 'f-update-select',
                    'description' => _w('Pay attention! Only number fields will be rounded, not the result. For result rounding use function ROUND()'),
                    'control_type' => waHtmlControl::SELECT,
                    'options' => array(
                        array('title' => _w('not round'), 'value' => ''),
                        array('title' => _w('0.01'), 'value' => '-2'),
                        array('title' => _w('0.1'), 'value' => '-1'),
                        array('title' => _w('1'), 'value' => '0'),
                        array('title' => _w('10'), 'value' => '1'),
                        array('title' => _w('100'), 'value' => '2'),
                    ),
                ),
            ),
        );
    }

}
