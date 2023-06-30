(function ($) {
    $.form_settings = {
        initPageSettings: function () {
            // Переключатель активности
            $('.s-form-status').each(function () {
                $(this).iButton({labelOn: "", labelOff: "", className: 'mini'}).change(function () {
                    var onLabelSelector = '.s-form-on-label',
                        offLabelSelector = '.s-form-off-label';
                    if (!this.checked) {
                        $(onLabelSelector).addClass('unselected');
                        $(offLabelSelector).removeClass('unselected');
                        $(this).closest(".s-ibutton-checkbox").find(".s-ibutton-hide").hide();
                    } else {
                        $(onLabelSelector).removeClass('unselected');
                        $(offLabelSelector).addClass('unselected');
                        $(this).closest(".s-ibutton-checkbox").find(".s-ibutton-hide").show();
                    }
                });
            });

            // Редактор
            $('.redactor-editor').redactor({
                deniedTags: false,
                plugins: ['source', 'fontcolor', 'fontsize', 'fontfamily'],
                lang: $.multiform.lang
            });

            // Форма выбора даты для расписания
            var params = {};
            if ($.multiform.locale == 'ru_RU') {
                params["months"] = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
                params["days_abbr"] = ["Вск", "Пон", "Вт", "Ср", "Чтв", "Пят", "Суб"];
                params["show_select_today"] = "Сегодня";
                params["lang_clear_date"] = "Очистить";
            }
            params["inside"] = false;
            params["show_clear_date"] = false;
            params["onSelect"] = function (formatDate, origDate, obj) {
                var dates = origDate.split("-");
                var scheduleBlock = $(this).closest(".schedule-time ");
                if (dates[2] !== undefined) {
                    scheduleBlock.find(".schedule-day").val(function () {
                        return dates[2];
                    });
                }
                if (dates[1] !== undefined) {
                    scheduleBlock.find(".schedule-month").val(function () {
                        return dates[1];
                    });
                }
                if (dates[0] !== undefined) {
                    scheduleBlock.find(".schedule-year").val(function () {
                        return dates[0];
                    });
                }
            };
            $(".schedule-time .multiform-datepicker").Zebra_DatePicker(params);

            // Сохранение формы
            $("#s-form-settings-save").submit(function () {
                $.form_settings.saveHandler($(this));
                return false;
            });

        },
        changeEventHandler: function (btn) {
            var btn = $(btn);
            // Confirmation options
            if (btn.data("field") == 'confirmation') {
                btn.closest(".value").find(".f-confirmation-message, .f-confirmation-redirect").hide();
                btn.closest(".value").find(".f-confirmation-" + btn.val()).show();
            }
            // Добавление оповещения для пользователя
            else if (btn.data("field") == 'confirmation-email') {
                if (btn.prop("checked")) {
                    btn.closest(".field").find(".s-confirmation-email-block").show();
                } else {
                    btn.closest(".field").find(".s-confirmation-email-block").hide();
                }
            }
            // Переключение ограничений по домену
            else if (btn.data("field") == 'domain-limitations') {
                if (btn.val() == '1') {
                    btn.closest(".value").find(".f-domain-list").show().find("select").prop("disabled", false);
                } else {
                    btn.closest(".value").find(".f-domain-list").hide().find("select").prop("disabled", true);
                }
            }
            // Появление/скрытие выбора правил маршрутизации у домена
            else if (btn.hasClass('s-limit-domains')) {
                if (btn.val() !== '') {
                    btn.next().val('').show().find("option").hide().siblings(".domain-" + btn.find("option:selected").data("id")).show();
                } else {
                    btn.next().hide();
                }
            }
            // Появление/скрытие настройки расписания формы
            else if (btn.data("field") == 'schedule') {
                if (btn.prop("checked")) {
                    btn.closest(".value").find(".f-schedule-block").show();
                } else {
                    btn.closest(".value").find(".f-schedule-block").hide();
                }
            }
            // Выбор типа капчи
            else if (btn.hasClass('s-captcha-select')) {
                if (btn.val() == 'advanced') {
                    btn.closest(".field").find(".captcha-types").show();
                } else {
                    btn.closest(".field").find(".captcha-types").hide();
                }
                if (btn.val() == 'basic' || btn.val() == 'advanced') {
                    btn.closest(".field").find(".s-only-captcha").show();
                } else {
                    btn.closest(".field").find(".s-only-captcha").hide();
                }
            } else if (btn.data("field") == 'captcha_type') {
                if (btn.val() == '1') {
                    $('.s-recaptcha-fields').show();
                } else {
                    $('.s-recaptcha-fields').hide();
                }
            }
        },
        // Добавление нового поля для ограничения формы по доменам
        addFormDomainLimitAction: function (btn) {
            var domainRow = btn.closest(".domain-limit-row");
            var clone = domainRow.clone();
            clone.find(".s-limit-routes").hide();
            clone.find(".s-limit-domains").val('');
            domainRow.after(clone);
        },
        // Удаление формы ограничения по доменам
        removeFormDomainLimitAction: function (btn) {
            if (btn.closest(".f-domain-list").find(".domain-limit-row").length == 1) {
                $("#domain-limit-off").click();
            } else {
                btn.closest(".domain-limit-row").remove();
            }
        },
        // Сохранение формы
        saveHandler: function (form) {
            var btn = form.find("input[type='submit']");
            if (!btn.next(".loading").length) {
                btn.after("<i class='icon16 loading'></i>");
                var i = btn.next("i");
                form.find('.errormsg').html('');

                $.ajax({
                    url: "?module=form&action=settingsSave",
                    dataType: "json",
                    type: "post",
                    data: form.serializeArray(),
                    success: function (response) {
                        if (response.status == 'ok') {
                            i.removeClass("loading").addClass("yes");
                        }
                        if (response.status == 'fail' && response.errors) {
                            i.removeClass("loading").addClass("cross");
                            if (typeof response.errors.messages !== 'undefined') {
                                for (var error in response.errors.messages) {
                                    form.find('.errormsg').append(response.errors.messages[error] + '<br>');
                                }
                            }
                            if (typeof response.errors.fields !== 'undefined') {
                                for (var field in response.errors.fields) {
                                    form.find("." + response.errors.fields[field]).addClass("error");
                                }
                            }
                        }
                        setTimeout(function () {
                            i.remove();
                        }, 3000);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (console) {
                            console.log(jqXHR, textStatus, errorThrown);
                        }
                        $.multiform.showError($__("Error"));
                    }
                });
            }
            return false;
        },
        // Удаление формы 
        deleteFormAction: function (btn, form_id) {
            $("#backend-delete-form-dialog").waDialog({
                'height': '180px',
                'width': '600px',
                'class': 'dialog-red',
                'content': '<h1 class="align-center">' + $__('Attention') + '</h1><div class="margin-block align-center" style="margin-top: 30px">' + $__('If you delete this form, <b>all records</b> with files and data will be <b>removed also</b>. Are you sure?') + '</div>',
                'buttons': '<div class="align-center"><input type="submit" value="' + $__('Delete anyway') + '" class="button red"> ' + $__('or') + ' <a class="button yellow cancel" href="javascript:void(0)">' + $__('cancel') + '</a></div>',
                disableButtonsOnSubmit: false,
                onSubmit: function () {
                    var form = $(this);
                    if (!form.find(".temp").length) {
                        form.find("input[type='submit']").after("<i class='icon16 loading temp'></i>");
                        $.post("?action=handler", {data: "deleteForm", id: form_id}, function (response) {
                            if (response.status == 'ok') {
                                $.multiform.showError($__("Form removed"));
                                window.location = "?module=backend";
                            } else {
                                $(".dialog").remove();
                                $.multiform.showError($__("Error. Check log files"));
                            }
                        });
                    }
                    return false;
                }
            });
        }
    };
})(jQuery);