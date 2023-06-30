(function ($) {
    $.action_conditions = {
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
        initPageConditions: function () {
            var self = this;
            // Сохранение формы
            $(".actions-tab .s-conditions").submit(function () {
                self.saveHandler($(this));
                return false;
            });

            // Вызываем js функции
            $(document).off('click', 'a.js-action-act-cond').on('click', 'a.js-action-act-cond', function () {
                self.activateJsAction($(this));
                return false;
            });

            // Инициализация полей даты
            self.initDatepicker($(".condition-tabs .field:not(.s-template) .multiform-datepicker"));
        },
        activateJsAction: function (btn) {
            var hash = $.multiform.cleanHash(btn.attr("href"));
            if (hash) {
                hash = hash.replace(/^[^#]*#\/*/, '');
                hash = hash.split('/');
                var actionName = "";
                var param = "";
                for (var i = 0; i < hash.length; i++) {
                    var h = hash[i];
                    if (i === 0) {
                        actionName = h;
                    } else if (parseInt(h, 10) == h) {
                        param = h;
                    } else {
                        actionName += h.substr(0, 1).toUpperCase() + h.substr(1);
                    }
                }
                if (this[actionName + 'Action']) {
                    this[actionName + 'Action'](btn, param);
                } else {
                    if (console) {
                        console.log('Invalid action name:', actionName + 'Action', 'for module ' + this.module);
                    }
                }
            }
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
                    clone.find(".field-values .f-date-value").html("<input type='text' name='" + input.attr("name") + "' class='multiform-datepicker'/>");
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
            if (!form.hasClass("is-loading")) {
                var btn = form.find("input[type='submit']");
                var errormsg = form.find(".errorfld .errormsg");
                var showError = function (error) {
                    if (error !== '') {
                        errormsg.html(error).parent().css('display', 'table');
                    }
                };
                var hideError = function () {
                    btn.next(".temp").remove();
                    errormsg.html('').parent().hide();
                    form.find(".multiform-error-field").removeClass("multiform-error-field");
                };
                var showLoading = function (message) {
                    btn.hide();
                    if (!btn.next(".multiform-temp-loading").length) {
                        btn.after("<div class=\"multiform-temp-loading\" style=\"display: inline-block !important;\"><span>" + (message ? message : "") + "</span><i class='icon16 loading multiform-temp-loading'></i><div>");
                    } else {
                        btn.next(".multiform-temp-loading").find("span").text(message);
                    }
                };

                hideError();

                showLoading($__('Please, wait, while data is saving'));
                form.addClass("is-loading");

                $.ajax({
                    url: "?module=conditionsAction&action=save",
                    dataType: "json",
                    type: "post",
                    crossDomain: false,
                    data: form.find("input, textarea").serializeArray(),
                    success: function (response) {
                        btn.show().next(".multiform-temp-loading").remove();
                        form.removeClass("is-loading");
                        if (response.status == 'ok' && response.data) {

                            /* 
                             * Если нужна перезагрузка страницы, то верните "reload = 1"
                             * If you want to reload page after saving, return "reload = 1" 
                             * */
                            if (typeof response.data.reload !== 'undefined') {
                                $.form_actions.reloadAction(form, response.data.id);
                            }


                            btn.after('<span class="temp"><i class="icon16 yes"></i><span>' + $__('Saved') + '</span></span>');
                            setTimeout(function () {
                                btn.next('.temp').remove();
                            }, 4000);


                            /*
                             * Обновление данных в сайдбаре
                             * Update sidebar info 
                             **/
                            $.form_actions.updateSidebar(response.data.id);

                        } else if (typeof response.errors !== 'undefined') {
                            if (typeof response.errors.messages !== 'undefined') {
                                var errors = "";
                                $.each(response.errors.messages, function (i, v) {
                                    errors += v + "<br>";
                                });
                                showError(errors);
                            }
                        } else {
                            showError($__('Something wrong. Please, reload the page'));
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        showError($__('Something wrong. Please, reload the page'));
                        btn.show().next(".multiform-temp-loading").remove();
                        form.removeClass("is-loading");
                    }
                });
            }

            return false;
        }
    };
})(jQuery);