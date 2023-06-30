(function ($) {
    $.form_conditions = {
        // Инициализация всплывающего календаря
        initDatepicker: function (field) {
            var params = {};
            if ($.multiform.locale == 'ru_RU') {
                params["months"] = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
                params["days_abbr"] = ["Вск", "Пон", "Вт", "Ср", "Чтв", "Пят", "Суб"];
                params["show_select_today"] = "Сегодня";
                params["lang_clear_date"] = "Очистить";
            }
            params["inside"] = true;
            params["show_clear_date"] = true;
            field.Zebra_DatePicker(params);
        },
        editor: [],
        initPageConditions: function () {
            // Переключатель активности
            $('#s-condition-status').iButton({labelOn: "", labelOff: "", className: 'mini'}).change(function () {
                var onLabelSelector = '#s-condition-on-label',
                    offLabelSelector = '#s-condition-off-label',
                    status = 0;
                if (!this.checked) {
                    $(onLabelSelector).addClass('unselected');
                    $(offLabelSelector).removeClass('unselected');
                } else {
                    status = 1;
                    $(onLabelSelector).removeClass('unselected');
                    $(offLabelSelector).addClass('unselected');
                }
                // Обновляем статус
                $.post("?action=handler", {data: 'conditionStatus', id: $.form.currentForm, status: status});
            });

            // Редактор
            $('.redactor-editor').each(function () {
                $.form_conditions.initRedactor($(this));
            });

            // Сохранение формы
            $("#s-form-conditions-save").submit(function () {
                $.form_conditions.saveHandler($(this));
                return false;
            });

            // Инициализация полей даты
            this.initDatepicker($(".condition-tabs .field:not(.s-template) .multiform-datepicker"));
        },
        // Выбор одного из вариантов в выпадающем окне
        selectDropdownItemAction: function (btn) {
            btn.closest(".dropdown-ul").find(".s-name").html(btn.html()).closest(".dropdown-ul").find("ul").hide();
            btn.closest("li").addClass("selected").siblings().removeClass("selected");
            btn.closest(".dropdown-ul").find("input").val(btn.data("value"));
            // Если выбор произошел в списке полей
            var field = btn.closest(".s-condition-field");
            var additionalField = field.find(".dropdown-ul.f-additional-field");
            if (btn.data("dropdown") == 'fields') {
                if (additionalField.length) {
                    additionalField.hide().find("input").prop("disabled", true);
                    field.find(".dropdown-ul.f-additional-field." + btn.data("id")).show().find("input").prop("disabled", false);
                }

                // Изменяем поля операторов поля
                var operatorFields = field.find(".dropdown-ul.f-operator .s-operator-" + btn.data("operator"));
                if (!operatorFields.length) {
                    operatorFields = field.find(".dropdown-ul.f-operator .s-operator-formula");
                }
                operatorFields.siblings().hide();
                operatorFields.show();
                operatorFields.first().find("a").click();
                // Изменяем поля значений
                var valueFields = field.find(".field-values");
                valueFields.children().hide();
                valueFields.find("input").prop("disabled", true);
                var valueTypeField = valueFields.find(".f-" + btn.data("type") + "-value");
                if (valueTypeField.length) {
                    if (valueTypeField.hasClass(btn.data("id"))) {
                        valueFields.find(".f-" + btn.data("type") + "-value." + btn.data("id")).css('display', 'inline').find("li:first a").click();
                        valueFields.find(".f-" + btn.data("type") + "-value." + btn.data("id") + " input").prop("disabled", false);
                    } else {
                        valueTypeField.css('display', 'inline').find("input").prop("disabled", false);
                    }
                } else {
                    valueFields.find(".f-input-value").show().find("input").prop("disabled", false);

                }
                if (btn.data("type") == 'table_grid' || btn.data("type") == 'checkbox') {
                    field.find(".dropdown-ul.f-additional-field." + btn.data("id") + " li:first a").click();
                }
            } else if (btn.data("dropdown") == 'andor') {
                btn.closest(".field").find(".s-andor .s-name").html(btn.html());
                btn.closest(".field").find(".s-andor input").val(btn.data("value"));
            }
            if (additionalField.length && btn.data("dropdown") == 'additional') {
                additionalField.hide().find("input").prop("disabled", true);
                field.find(".dropdown-ul.f-additional-field." + btn.data("id")).show().find("input").prop("disabled", false);
            }

        },
        // Добавление условия внутри условия
        addConditionInsideAction: function (btn) {
            var clone = btn.closest(".s-condition-field").clone();
            clone.find(".s-andor").show();
            clone.find(".s-buttons a").css('display', 'inline-block');
            // Генерируем ID поля
            var pos = 0;
            var field = btn.closest(".field");
            while (field.find(".s-condition-field[data-pos='p" + pos + "']").length) {
                pos++;
            }
            // Изменяем поле name у всех input-полей
            clone.attr('data-pos', "p" + pos).find("input").each(function () {
                var input = $(this);
                input.attr("name", input.attr("name").replace(/\[params\]\[\d+\]/, "[params][" + pos + "]"));
                if (input.hasClass("multiform-datepicker")) {
                    clone.find(".field-values .f-date-value").html("<input type='text'" + (input.prop('disabled') ? ' disabled' : '') + " name='" + input.attr("name") + "' class='multiform-datepicker'/>");
                }
            });
            this.initDatepicker(clone.find(".multiform-datepicker"));
            btn.closest(".s-condition-field").after(clone);
        },
        // Удаление условия внутри условия
        removeConditionInsideAction: function (btn) {
            btn.closest(".s-condition-field").remove();
        },
        // Добавление условия
        addConditionAction: function (btn) {
            var tab = btn.closest(".f-tab"),
                template = tab.find(".field.s-template"),
                clone = template.clone();
            clone.find("input, texarea").prop("disabled", false);
            // Генерируем ID поля
            var pos = 0;
            while (tab.find(".field[data-pos='p" + pos + "']").length) {
                pos++;
            }
            // Изменяем поле name у всех input-полей
            clone.find("input, textarea").each(function () {
                var input = $(this);
                input.attr("name", input.attr("name").replace(/(newID)/, "p" + pos));
            });
            this.initDatepicker(clone.find(".multiform-datepicker"));

            // Инициализируем визуальный редактор
            if (clone.find(".init-redactor").length) {
                clone.find(".init-redactor").each(function () {
                    $(this).removeClass().addClass("redactor-editor");
                    $.form_conditions.initRedactor($(this));
                });
            }

            template.before(clone);
            clone.removeClass("s-template").attr('data-pos', "p" + pos);
            var operator = clone.find(".dropdown-ul.f-fields ul li:first a").data("operator");
            clone.find(".dropdown-ul").not(".f-additional-field").each(function () {
                if ($(this).hasClass("f-operator")) {
                    $(this).find("ul li.s-operator-" + operator + ":first a").click();
                } else {
                    $(this).find("ul li:first a").click();
                }
            });
            template.siblings("p").hide();
            // Если добавляем условия для формы, инициируем нажатие на активную вкладку
            if (tab.data("tab") == 'form') {
                clone.find(".cloud-style > .name:first a").click();
            }
        },
        // Удаление условия
        removeConditionAction: function (btn) {
            var tab = btn.closest(".f-tab");
            tab.children(".field").not(".s-template").length == 1 && tab.find("> p").show();
            btn.closest(".field").remove();
        },
        // Сортировка условий
        sortConditionAction: function (btn) {
            var field = btn.closest(".field");
            if (btn.data("dir") == 'up') {
                var fieldBefore = field.prev(".field");
                if (fieldBefore.length) {
                    field.insertBefore(fieldBefore);
                }
            } else {
                var fieldAfter = field.next(".field").not(".s-template");
                if (fieldAfter.length) {
                    field.insertAfter(fieldAfter);
                }
            }
        },
        // Сохранение формы
        saveHandler: function (form) {
            var btn = form.find("input[type='submit']");
            if (!btn.next(".loading").length) {
                btn.after("<i class='icon16 loading'></i>");
                var i = btn.next("i");
                form.find('.errormsg').html('');

                var tabs = $(".condition-tabs > .f-tab");
                var errors = 0;

                $.form_conditions.updateTextarea();

                tabs.each(function (tabNumber, v) {
                    var tab = $(this).data("tab");
                    var tabName = $(".conditions-tab .s-" + tab + "-tab-name");
                    tabName.next("i").remove();
                    tabName.after("<i class='icon16 loading'></i>");
                    var tabI = tabName.next("i");
                    $.ajax({
                        url: "?module=form&action=conditionsSave",
                        dataType: "json",
                        type: "post",
                        data: $(this).find("input, textarea").serializeArray(),
                        success: function (response) {
                            if (response.status == 'ok') {
                                tabI.removeClass("loading").addClass("yes");
                            }
                            if (response.status == 'fail' && response.errors) {
                                errors = 1;
                                tabI.removeClass("loading").addClass("cross");
                                if (typeof response.errors.messages !== 'undefined') {
                                    for (var error in response.errors.messages) {
                                        form.find('.errormsg').append(response.errors.messages[error] + '<br>');
                                    }
                                }
                                if (typeof response.errors.fields !== 'undefined') {
                                    for (var field in response.errors.fields) {
                                        form.find(response.errors.fields[field]).addClass("error");
                                    }
                                }
                            }

                            setTimeout(function () {
                                tabI.remove();
                            }, 3000);
                            if (tabNumber == (tabs.length - 1)) {
                                if (!errors) {
                                    i.removeClass("loading").addClass("yes");
                                } else {
                                    i.removeClass("loading").addClass("cross");
                                }
                                setTimeout(function () {
                                    i.remove();
                                }, 3000);

                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            if (console) {
                                console.log(jqXHR, textStatus, errorThrown);
                            }
                            i.remove();
                            $.multiform.showError($__("Error"));
                        }
                    });

                });
            }
            return false;
        },
        // Инициализация визуального редактора
        initRedactor: function (textarea) {
            $.form_conditions.editor.push(textarea.redactor({
                deniedTags: false,
                plugins: ['fontcolor', 'fontsize', 'fontfamily'],
                lang: $.multiform.lang
            }));
        },
        updateTextarea: function () {
            if ($.form_conditions.editor.length) {
                $.each($.form_conditions.editor, function (i, v) {
                    var editor = $(this).data('redactor');
                    if (typeof editor !== 'undefined') {
                        $(this).val(editor.code.get());
                    }
                });
            }
        }
    };
})(jQuery);