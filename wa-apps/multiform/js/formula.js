(function ($) {
    /* Обновляем значения у всех формул */
    $.multiformFrontend.updateFormula = function (form) {
        $.each(form.find(".multiform-gap-field.type-formula"), function () {
            var that = $(this),
                functionId = 'formulaFunction' + that.data("field-id") + '_' + form.data('uid');
            if (typeof $.multiformFrontend[functionId] == 'function') {
                var result = $.multiformFrontend[functionId].call(this, form);
                that.find("input").val(result);
                if (that.find(".multiform-formula").length) {
                    result = result == '' || (typeof result == 'number' && isNaN(result)) ? '&nbsp;' : result;
                    that.find(".multiform-formula span").html(result);
                    if (that.closest(".has-condition").length) {
                        var conditionIds = (that.closest(".has-condition").data("condition") + "").split(",");
                        for (var i in conditionIds) {
                            $.multiformFrontend.condition.checkCondition(conditionIds[i], that.closest(".multiform-wrap"));
                        }
                    }
                }
            }
        });
    };

    $.multiformFrontend.formula = {};

    /* Получить значение поля */
    $.multiformFrontend.formula.getValue = function (form, field_id, form_uid, type, formula_type, rounding) {
        var field = form.find("#wahtmlcontrol_fields_field_" + field_id + '_' + form_uid);
        var value = formula_type == 'number' ? 0 : '';
        if (field.length && !field.closest('.multiform-hide').length) {
            var functionName = 'get_' + type;
            if (typeof $.multiformFrontend.formula[functionName] === 'function') {
                value = $.multiformFrontend.formula[functionName](field, value, formula_type, rounding);
            } else {
                value += formula_type == 'number' ? $.multiformFrontend.formula.ROUND(field.val(), rounding) : field.val() + '';
            }
        }
        return value;
    };

    /* Значения полей */
    $.multiformFrontend.formula['get_checkbox'] = function (field, value, formula_type, rounding) {
        $.each(field.find("input:checked"), function () {
            value += formula_type == 'number' ? $.multiformFrontend.formula.ROUND($(this).data("formula"), rounding) : $(this).data("formula") + '';
        });
        return value;
    };
    $.multiformFrontend.formula['get_table_grid'] = function (field, value, formula_type, rounding) {
        return $.multiformFrontend.formula['get_checkbox'](field, value, formula_type, rounding);
    };
    $.multiformFrontend.formula['get_radio'] = function (field, value, formula_type, rounding) {
        var otherField = field.find("input:checked").closest(".multiform-gap-option").find(".multiform-other-field");
        if (otherField.length) {
            value = formula_type == 'number' ? $.multiformFrontend.formula.ROUND($.trim(otherField.val()), rounding) : $.trim(otherField.val()) + '';
        } else {
            value = formula_type == 'number' ? $.multiformFrontend.formula.ROUND(field.find("input:checked").data("formula"), rounding) : field.find("input:checked").data("formula") + '';
        }
        return value;
    };
    $.multiformFrontend.formula['get_select'] = function (field, value, formula_type, rounding) {
        value = formula_type == 'number' ? $.multiformFrontend.formula.ROUND(field.find(":selected").data("formula"), rounding) : field.find(":selected").data("formula");
        return value;
    };
    $.multiformFrontend.formula['get_rating'] = function (field, value, formula_type, rounding) {
        value = formula_type == 'number' ? $.multiformFrontend.formula.ROUND(field.find("input:checked").val(), rounding) : field.find("input:checked").val() + '';
        return value;
    };
    $.multiformFrontend.formula['get_number'] = function (field, value, formula_type, rounding) {
        var val = field.is("input") ? field.val() : field.find("input").val();
        value += formula_type == 'number' ? $.multiformFrontend.formula.ROUND(val, rounding) : val + '';
        return value;
    };
    $.multiformFrontend.formula['get_date'] = function (field, value, formula_type, rounding) {
        value = '';
        var count = field.find("input:not(.multiform-datepicker, .multiform-formatted-date), select").length;
        $.each(field.find("input:not(.multiform-datepicker, .multiform-formatted-date), select"), function (i, v) {
            value += $(this).val() + (i + 1 !== count && field.data('separator') ? field.data('separator') : '');
        });
        if (field.find(".multiform-full-date").length) {
            value += field.find(".multiform-full-date").val();
        }
        return value;
    };
    $.multiformFrontend.formula['get_time'] = function (field, value, formula_type, rounding) {
        return $.multiformFrontend.formula['get_date'](field, value, formula_type, rounding);
    };
    $.multiformFrontend.formula['get_phone'] = function (field, value, formula_type, rounding) {
        value = field.val();
        return value;
    };
    $.multiformFrontend.formula['get_email'] = function (field, value, formula_type, rounding) {
        return  $.multiformFrontend.formula['get_phone'](field, value, formula_type, rounding);
    };
    $.multiformFrontend.formula['get_textarea'] = function (field, value, formula_type, rounding) {
        return  $.multiformFrontend.formula['get_phone'](field, value, formula_type, rounding);
    };

    /* Получаем в качестве значения число */
    $.multiformFrontend.formula.parseNumber = function (string) {
        if (string === undefined || string === '') {
            return 0;
        }
        if (!isNaN(string)) {
            return parseFloat(string);
        }
        return 0;
    };

    /* Функция округления */
    $.multiformFrontend.formula.ROUND = function (value, precision) {
        value = parseFloat(value);
        var result;
        if (precision !== '') {
            var roundUnit = Math.pow(10, precision);
            if (precision <= 0) {
                value = +value;
                precision = +precision;
                /* Сдвиг разрядов */
                value = value.toString().split('e');
                value = Math['round'](+(value[0] + 'e' + (value[1] ? (+value[1] - precision) : -precision)));
                /* Обратный сдвиг */
                value = value.toString().split('e');
                result = +(value[0] + 'e' + (value[1] ? (+value[1] + precision) : precision));
            } else {
                result = Math.ceil(value / roundUnit) * roundUnit;
            }
        } else {
            result = value;
        }

        return isNaN(result) ? 0 : result;
    };

    /* Логическое ЕСЛИ */
    $.multiformFrontend.formula.IF = function (condition, true_statement, false_statement) {
        return condition ? true_statement : false_statement;
    };

    /* Логическое И */
    $.multiformFrontend.formula.AND = function () {
        var args = arguments;
        var len = args.length;
        var result = true;
        for (var i = 0; i < len; i++) {
            if (!args[i]) {
                result = false;
            }
        }
        return result;
    };

    /* Логическое ИЛИ */
    $.multiformFrontend.formula.OR = function () {
        var args = arguments;
        var len = args.length;
        var result = false;
        for (var i = 0; i < len; i++) {
            if (args[i]) {
                result = true;
            }
        }
        return result;
    };

    /* Логическое НЕ */
    $.multiformFrontend.formula.NOT = function (logical) {
        return !logical;
    };

    $.multiformFrontend.formula.FALSE = function () {
        return false;
    };

    $.multiformFrontend.formula.TRUE = function () {
        return true;
    };

    /* Логическая: сравнивает 2 значения. Больше */
    $.multiformFrontend.formula.GT = function (num1, num2) {
        num1 = this.parseNumber(num1);
        num2 = this.parseNumber(num2);
        return num1 > num2;
    };

    /* Логическая: сравнивает 2 значения. Больше или равно */
    $.multiformFrontend.formula.GTE = function (num1, num2) {
        num1 = this.parseNumber(num1);
        num2 = this.parseNumber(num2);
        return num1 >= num2;
    };

    /* Логическая: сравнивает 2 значения. Меньше */
    $.multiformFrontend.formula.LT = function (num1, num2) {
        num1 = this.parseNumber(num1);
        num2 = this.parseNumber(num2);
        return num1 < num2;
    };

    /* Логическая: сравнивает 2 значения. Меньше или равно*/
    $.multiformFrontend.formula.LTE = function (num1, num2) {
        num1 = this.parseNumber(num1);
        num2 = this.parseNumber(num2);
        return num1 <= num2;
    };

    /* Логическая: сравнивает 2 значения. Равно */
    $.multiformFrontend.formula.EQ = function (value1, value2) {
        return value1 == value2;
    };

    /* Логическая: сравнивает 2 значения. Равно */
    $.multiformFrontend.formula.EQA = function (value1, value2) {
        return value1 === value2;
    };

    /* Логическая: сравнивает 2 значения. Не равно */
    $.multiformFrontend.formula.NE = function (value1, value2) {
        return value1 != value2;
    };

    /* Логическая: сравнивает 2 значения. Не равно */
    $.multiformFrontend.formula.NEA = function (value1, value2) {
        return value1 !== value2;
    };

    /* Математическая: возвращает модуль */
    $.multiformFrontend.formula.ABS = function (number) {
        return Math.abs(this.parseNumber(number));
    };

    /* Математическая: возвращает арккосинус */
    $.multiformFrontend.formula.ACOS = function (number) {
        number = this.parseNumber(number);
        return Math.acos(number);
    };

    /* Математическая: возвращает гиперболический арккосинус */
    $.multiformFrontend.formula.ACOSH = function (number) {
        number = this.parseNumber(number);
        return Math.log(number + Math.sqrt(number * number - 1));
    };

    /* Математическая: возвращает арккотангенс */
    $.multiformFrontend.formula.ACOT = function (number) {
        number = this.parseNumber(number);
        return Math.atan(1 / number);
    };

    /* Математическая: возвращает гиперболический арккотангенс */
    $.multiformFrontend.formula.ACOTH = function (number) {
        number = this.parseNumber(number);
        return 0.5 * Math.log((number + 1) / (number - 1));
    };

    /* Математическая: возвращает арксинус */
    $.multiformFrontend.formula.ASIN = function (number) {
        number = this.parseNumber(number);
        return Math.asin(number);
    };

    /* Математическая: возвращает гиперболический арксинус */
    $.multiformFrontend.formula.ASINH = function (number) {
        number = this.parseNumber(number);
        return Math.log(number + Math.sqrt(number * number + 1));
    };

    /* Математическая: возвращает арктангенс */
    $.multiformFrontend.formula.ATAN = function (number) {
        number = this.parseNumber(number);
        return Math.atan(number);
    };

    /* Математическая: возвращает арктангенс для заданных координат */
    $.multiformFrontend.formula.ATAN2 = function (number_x, number_y) {
        number_x = this.parseNumber(number_x);
        number_y = this.parseNumber(number_y);
        return Math.atan2(number_x, number_y);
    };

    /* Математическая: возвращает гиперболический арктангенс */
    $.multiformFrontend.formula.ATANH = function (number) {
        number = this.parseNumber(number);
        return Math.log((1 + number) / (1 - number)) / 2;
    };

    /* Математическая: вычисляет среднее */
    $.multiformFrontend.formula.AVERAGE = function () {
        var range = arguments;
        var n = range.length;
        var sum = 0;
        var count = 0;
        for (var i = 0; i < n; i++) {
            sum += range[i];
            count += 1;
        }
        return sum / count;
    };

    /* Математическая: вычисляет среднее арифметическое */
    $.multiformFrontend.formula.AVERAGEA = function () {
        var range = arguments;
        var n = range.length;
        var sum = 0;
        var count = 0;
        for (var i = 0; i < n; i++) {
            var el = range[i];
            if (typeof el === 'number') {
                sum += el;
            }
            if (el === true) {
                sum++;
            }
            if (el !== null) {
                count++;
            }
        }
        return sum / count;
    };

    /* Математическая: возвращает косинус */
    $.multiformFrontend.formula.COS = function (number) {
        number = this.parseNumber(number);
        return Math.cos(number);
    };

    /* Математическая: возвращает гиперболический косинус */
    $.multiformFrontend.formula.COSH = function (number) {
        number = this.parseNumber(number);
        return (Math.exp(number) + Math.exp(-number)) / 2;
    };

    /* Математическая: возвращает котангенс */
    $.multiformFrontend.formula.COT = function (number) {
        number = this.parseNumber(number);
        return 1 / Math.tan(number);
    };

    /* Математическая: возвращает гиперболический котангенс */
    $.multiformFrontend.formula.COTH = function (number) {
        number = this.parseNumber(number);
        var e2 = Math.exp(2 * number);
        return (e2 + 1) / (e2 - 1);
    };

    /* Математическая: возвращает косеканс */
    $.multiformFrontend.formula.CSC = function (number) {
        number = this.parseNumber(number);
        return 1 / Math.sin(number);
    };

    /* Математическая: возвращает гиперболический косеканс */
    $.multiformFrontend.formula.CSCH = function (number) {
        number = this.parseNumber(number);
        return 2 / (Math.exp(number) - Math.exp(-number));
    };

    /* Математическая: возвращает десятичное из любой системы счисления */
    $.multiformFrontend.formula.DECIMAL = function (number, radix) {
        radix = radix || 10;
        return parseInt(number, radix);
    };

    /* Математическая: преобразует радианы в градусы */
    $.multiformFrontend.formula.DEGREES = function (rad) {
        rad = this.parseNumber(rad);
        return rad * 180 / Math.PI;
    };

    /* Математическая: возвращает экспоненту числа */
    $.multiformFrontend.formula.EXP = function (number) {
        number = this.parseNumber(number);
        return Math.exp(number);
    };

    /* Математическая: возвращает факториал числа */
    var MEMOIZED_FACT = [];
    $.multiformFrontend.formula.FACT = function (number) {
        number = this.parseNumber(number);
        var n = Math.floor(number);
        n = n > 170 ? 170 : n;
        if (n === 0 || n === 1) {
            return 1;
        } else if (MEMOIZED_FACT[n] > 0) {
            return MEMOIZED_FACT[n];
        } else {
            MEMOIZED_FACT[n] = this.FACT(n - 1) * n;
            return MEMOIZED_FACT[n];
        }
    };

    /* Математическая: Округляет до ближайшего меньшего целого */
    $.multiformFrontend.formula.INT = function (number) {
        number = this.parseNumber(number);
        return Math.floor(number);
    };

    /* Математическая: вычисляет натуральный логарифм */
    $.multiformFrontend.formula.LN = function (number) {
        number = this.parseNumber(number);
        return Math.log(number);
    };

    /* Математическая: вычисляет логарифм */
    $.multiformFrontend.formula.LOG = function (number, base) {
        number = this.parseNumber(number);
        base = this.parseNumber(base);
        base = (base === undefined) ? 10 : base;
        return Math.log(number) / Math.log(base);
    };

    /* Математическая: вычисляет десятичный логарифм */
    $.multiformFrontend.formula.LOGTEN = function (number) {
        number = this.parseNumber(number);
        return Math.log(number) / Math.log(10);
    };

    /* Математическая: вычисляет максимум */
    $.multiformFrontend.formula.MAX = function () {
        var range = arguments;
        return (range.length === 0) ? 0 : Math.max.apply(Math, range);
    };

    /* Математическая: вычисляет минимум */
    $.multiformFrontend.formula.MIN = function () {
        var range = arguments;
        return (range.length === 0) ? 0 : Math.min.apply(Math, range);
    };

    /* Математическая: вычисляет остаток от деления */
    $.multiformFrontend.formula.MOD = function (dividend, divisor) {
        dividend = this.parseNumber(dividend);
        divisor = this.parseNumber(divisor);

        if (divisor === 0) {
            divisor = 1;
        }
        var modulus = Math.abs(dividend % divisor);
        return (divisor > 0) ? modulus : -modulus;
    };

    /* Математическая: округляет до ближайшего целого нечетного. */
    $.multiformFrontend.formula.ODD = function (number) {
        number = this.parseNumber(number);
        var temp = Math.ceil(Math.abs(number));
        temp = (temp & 1) ? temp : temp + 1;
        return (number > 0) ? temp : -temp;
    };

    /* Математическая: возвращает число PI */
    $.multiformFrontend.formula.PI = function () {
        return Math.PI;
    };

    /* Математическая: возведение в степень */
    $.multiformFrontend.formula.POW = function (number, power) {
        number = this.parseNumber(number);
        power = this.parseNumber(power);
        var result = Math.pow(number, power);
        if (isNaN(result)) {
            return 0;
        }
        return result;
    };

    /* Математическая: преобразует градусы в радианы */
    $.multiformFrontend.formula.RADIANS = function (number) {
        number = this.parseNumber(number);
        return number * Math.PI / 180;
    };

    /* Математическая: вычисляет секанс */
    $.multiformFrontend.formula.SEC = function (number) {
        number = this.parseNumber(number);
        return 1 / Math.cos(number);
    };

    /* Математическая: вычисляет гиперболический секанс */
    $.multiformFrontend.formula.SECH = function (number) {
        number = this.parseNumber(number);
        return 2 / (Math.exp(number) + Math.exp(-number));
    };

    /* Математическая: вычисляет синус */
    $.multiformFrontend.formula.SIN = function (number) {
        number = this.parseNumber(number);
        return Math.sin(number);
    };

    /* Математическая: вычисляет гиперболический синус */
    $.multiformFrontend.formula.SINH = function (number) {
        number = this.parseNumber(number);
        return (Math.exp(number) - Math.exp(-number)) / 2;
    };

    /* Математическая: вычисляет квадратный корень числа */
    $.multiformFrontend.formula.SQRT = function (number) {
        number = this.parseNumber(number);
        if (number < 0) {
            number = 0;
        }
        return Math.sqrt(number);
    };

    /* Математическая: вычисляет квадратный корень из значения выражения (число*PI) */
    $.multiformFrontend.formula.SQRTPI = function (number) {
        number = this.parseNumber(number);
        return Math.sqrt(number * Math.PI);
    };

    /* Математическая: вычисляет тангенс */
    $.multiformFrontend.formula.TAN = function (number) {
        number = this.parseNumber(number);
        return Math.tan(number);
    };

    /* Математическая: вычисляет гиперболический тангенс */
    $.multiformFrontend.formula.TANH = function (number) {
        number = this.parseNumber(number);
        var e2 = Math.exp(2 * number);
        return (e2 - 1) / (e2 + 1);
    };

    /* Текстовая: возвращает символ с заданным кодом  */
    $.multiformFrontend.formula.CHAR = function (number) {
        number = this.parseNumber(number);
        return String.fromCharCode(number);
    };

    /* Текстовая: возвращает код символа */
    $.multiformFrontend.formula.CODE = function (text) {
        text = text || '';
        return text.charCodeAt(0);
    };

    /* Текстовая: возвращает указанное количество знаков с начала строки текста */
    $.multiformFrontend.formula.LEFT = function (text, number) {
        number = (number === undefined) ? 1 : number;
        number = this.parseNumber(number);
        if (typeof text !== 'string') {
            return '';
        }
        return text ? text.substring(0, number) : '';
    };

    /* Текстовая: возвращает длину строки */
    $.multiformFrontend.formula.LEN = function (text) {
        if (typeof text === 'string') {
            return text ? text.length : 0;
        }
        if (text.length) {
            return text.length;
        }
        return 0;
    };

    /* Текстовая: переводит строку в нижний регистр */
    $.multiformFrontend.formula.LOWER = function (text) {
        if (typeof text !== 'string') {
            return text;
        }
        return text ? text.toLowerCase() : text;
    };

    /* Текстовая: возвращает подстроку  */
    $.multiformFrontend.formula.MID = function (text, start, length) {
        start = this.parseNumber(start);
        length = this.parseNumber(length);
        var begin = start;
        var end = begin + length;

        return text.substring(begin, end);
    };

    /* Текстовая: замена по регулярному выражению  */
    $.multiformFrontend.formula.REGEXREPLACE = function (text, regular_expression, replacement) {
        return text.replace(new RegExp(regular_expression), replacement);
    };

    /* Текстовая: возвращает указанное количество знаков с конца строки текста */
    $.multiformFrontend.formula.RIGHT = function (text, number) {
        number = (number === undefined) ? 1 : number;
        number = this.parseNumber(number);
        return text ? text.substring(text.length - number) : null;
    };

    /* Текстовая: возвращает номер символа в строке */
    $.multiformFrontend.formula.SEARCH = function (find_text, within_text, position) {
        if (typeof find_text !== 'string' || typeof within_text !== 'string') {
            return -1;
        }
        position = (position === undefined) ? 0 : position;
        return within_text.toLowerCase().indexOf(find_text.toLowerCase(), position);
    };

    /* Текстовая: обрезает пустые поля с обеих сторон строки */
    $.multiformFrontend.formula.TRIM = function (text) {
        return text.replace(/ +/g, ' ').trim();
    };

    /* Текстовая: переводит строку в верхний регистр */
    $.multiformFrontend.formula.UPPER = function (text) {
        if (typeof text !== 'string') {
            return text;
        }
        return text.toUpperCase();
    };

})(jQuery);

