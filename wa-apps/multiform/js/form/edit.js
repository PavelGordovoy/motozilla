/* TODO удалить */
var MultiformScripts = (function ($) {
    return {
        /* Инициализация календаря */
        initDatepicker: function ($field, params, defaultDate) {
            defaultDate = defaultDate || '';
            params = params || {};
            let $datepicker = $field.find(".multiform-datepicker");

            if ($datepicker.length) {
                let datepickerParams = $.extend($datepicker.data('params'), params);
                let defaultDateParams = $datepicker.attr('data-setDefault') || defaultDate;
                $datepicker.data('params', JSON.stringify(datepickerParams));

                datepickerParams['onSelect'] = function (formatDate, origDate) {
                    let dates = origDate.split("-");
                    if ($field.find(".multiform-day").length && dates[2] !== undefined) {
                        $field.find(".multiform-day").val(function () {
                            return $(this).is("select") ? parseInt(dates[2]) : dates[2];
                        }).trigger('change');
                    }
                    if ($field.find(".multiform-month").length && dates[1] !== undefined) {
                        $field.find(".multiform-month").val(function () {
                            return $(this).is("select") ? parseInt(dates[1]) : dates[1];
                        }).trigger('change');
                    }
                    if ($field.find(".multiform-year").length && dates[0] !== undefined) {
                        $field.find(".multiform-year").val(function () {
                            return $(this).is("select") ? parseInt(dates[0]) : dates[0];
                        }).trigger('change');
                    }
                    if ($field.find(".multiform-full-date").length) {
                        $field.find(".multiform-full-date").val(formatDate).attr('data-value', origDate).trigger('change');
                        $field.find(".multiform-formatted-date").val(origDate);
                    }
                    $field.closest(".multiform-wrap").change();
                };
                if ($datepicker.data('Zebra_DatePicker') === undefined) {
                    $datepicker.Zebra_DatePicker(datepickerParams);
                    if (defaultDateParams) {
                        $datepicker.attr('data-setDefault', defaultDateParams).data("Zebra_DatePicker").setDefault(defaultDateParams);
                    }
                } else {
                    $datepicker.data('Zebra_DatePicker').update();
                }
            }
        },
        initFileupload: function ($field, params) {
            $field.attr('data-params', JSON.stringify(params));
            $.multiformFrontend.initFileuploadFrontend($field, params);
        },
        initPhone: function ($field) {
            $field.mfMask($field.data('field-mask')).addClass("inited");
        },
        initRangeSlider: function ($field, params) {
            $field.attr('data-params', JSON.stringify(params));
            $field.ionRangeSlider(params);
        },
        initTime: function ($field) {
            $field.find('.multiform-full-time').mfMask('00:00:00');
        }
    };
})(jQuery);


(function ($) {

    $.form_edit = {
        masks: {},
        init: function () {
            // Изменение полей "на лету"
            $(document).on("keyup", ".f-update-text", function () {
                var that = $(this);
                if (that.hasClass("data-title")) {
                    var value = $.trim(that.val());
                    var row = that.closest('.form-builder-row');
                    if (that.hasClass("data-section")) {
                        value = value || $__('Section');
                        value += ' #' + row.find(".multiform-gap-field").attr("data-id");
                    }
                    row.find(".multiform-gap-name:first").text(value);
                } else if (that.hasClass("data-placeholder")) {
                    $("#wahtmlcontrol_fields_field_" + that.closest(".form-builder-row").find(".multiform-gap-field").data("field-id")).attr('placeholder', that.val());
                } else if (that.hasClass("data-default")) {
                    $("#wahtmlcontrol_fields_field_" + that.closest(".form-builder-row").find(".multiform-gap-field").data("field-id")).val(that.val());
                } else if (that.hasClass("data-description")) {
                    var field = that.closest('.form-builder-row').find(".multiform-gap-field"),
                        descr = field.find(".multiform-gap-description");
                    if (descr.length) {
                        descr.html(that.val());
                    } else {
                        field.find(".multiform-gap-value").append("<div class='multiform-gap-description'>" + that.val() + "</div>");
                    }
                } else if (that.hasClass("data-prefix")) {
                    var field = that.closest('.form-builder-row').find(".multiform-gap-field"),
                        prefix = field.find(".multiform-gap-value .prefix");
                    if (prefix.length) {
                        prefix.html(that.val());
                    } else {
                        field.find(".multiform-gap-value [id^=wahtmlcontrol]").before("<div class='prefix'>" + that.val() + "</div>");
                    }
                } else if (that.hasClass("data-suffix")) {
                    var field = that.closest('.form-builder-row').find(".multiform-gap-field"),
                        suffix = field.find(".multiform-gap-value .suffix");
                    if (suffix.length) {
                        suffix.html(that.val());
                    } else {
                        field.find(".multiform-gap-value [id^=wahtmlcontrol]").after("<div class='suffix'>" + that.val() + "</div>");
                    }
                }
            });
            $(document).off("change", ".f-update-select").on("change", ".f-update-select", function () {
                var that = $(this);
                if (that.hasClass("data-pos")) {
                    var elem = that.closest('.form-builder-row').find(".multiform-gap-field");
                    elem.removeClass("pos-top pos-left pos-hide pos-").addClass("pos-" + that.val());
                } else if (that.hasClass("data-mask-id")) {
                    var value = that.val();
                    if ($.form_edit.masks !== undefined && $.form_edit.masks[value] !== undefined) {
                        var fields = that.closest(".fields");
                        fields.find("textarea[name='validation[regexp]']").val($.form_edit.masks[value].mask);
                        fields.find("textarea[name='validation[regexp_error]']").val($.form_edit.masks[value].error);
                    }
                } else if (that.hasClass("data-date-type")) {
                    var tab = that.closest(".tab-display");
                    if (that.val() == 'fields') {
                        tab.find(".f-date-textfield").closest(".settings-field").hide();
                        tab.find(".f-date-fields").closest(".settings-field").show();
                    } else {
                        tab.find(".f-date-textfield").closest(".settings-field").show();
                        tab.find(".f-date-fields").closest(".settings-field").hide();
                    }
                } else if (that.hasClass("data-options-layout")) {
                    var elem = that.closest('.form-builder-row').find(".multiform-gap-option");
                    elem.removeClass("one-layout two-layout three-layout line-layout").addClass(that.val() + "-layout");
                } else if (that.hasClass("data-hidden-default")) {
                    if (that.val() == 'text' && !that.next().is(":visible")) {
                        that.next().show().find("input").prop("disabled", false);
                    } else {
                        that.next().hide().find("input").prop("disabled", true);
                    }
                }
            });
            $(document).on("change", ".f-update-checkbox", function () {
                var that = $(this);
                if (that.hasClass("data-required")) {
                    if (that.prop('checked')) {
                        that.closest('.form-builder-row').find(".multiform-gap-field").removeClass("s-required").addClass("s-required");
                    } else {
                        that.closest('.form-builder-row').find(".multiform-gap-field").removeClass("s-required");
                    }
                } else if (that.hasClass("data-disabled")) {
                    if (that.prop('checked')) {
                        that.closest('.form-builder-row').find(".multiform-gap-value [id^=wahtmlcontrol]").prop('readonly', true);
                    } else {
                        that.closest('.form-builder-row').find(".multiform-gap-value [id^=wahtmlcontrol]").prop('readonly', false);
                    }
                } else if (that.hasClass("data-mask")) {
                    if (that.prop('checked')) {
                        that.next().show();
                    } else {
                        that.next().hide();
                    }
                } else if (that.hasClass("data-date-default")) {
                    if (!that.prop('checked')) {
                        that.closest('.settings-field').next().show(function () {
                            that.trigger("init-default-datepicker");
                        });
                    }
                } else if (that.hasClass("data-option-customize")) {
                    var table = that.closest('.tab-options').find("table" + (that.hasClass('f-statement') ? '.s-statement' : (that.hasClass('f-column') ? '.s-column' : '')));
                    if (that.prop('checked')) {
                        !table.hasClass("show-customize") && table.addClass("show-customize");
                    } else {
                        table.removeClass("show-customize");
                    }
                } else if (that.hasClass("data-option-other")) {
                    var table = that.closest('.tab-options').find("table");
                    if (that.prop('checked')) {
                        if (!table.find(".other-field").length) {
                            $.form_edit.addOptionFieldAction(table.find(".add-option").last(), 'other');
                        }
                    } else {
                        table.find(".other-field").next().remove();
                        table.find(".other-field").remove();
                    }
                }

                return false;
            });

            // Сохранение полей формы
            $(document).on('click', '.settings-block input[type="submit"]', function () {
                $.form_edit.saveFormFields($(this).closest('form'));
                return false;
            });

            // Изменение полей формы
            $(document).on('change', '.settings-block form', function () {
                var that = $(this);
                var button = that.find("input[type='submit']");
                if (!button.hasClass('yellow')) {
                    button.removeClass('green').addClass('yellow');
                    that.closest(".form-builder-row").find("> .settings-save-status").show();
                }
            });

            $(document).on('keypress', '.f-only-number', function (event) {
                $.multiform.isValidInput(event, /[\-0-9\.\,]/);
            });
            $(document).on('keypress', '.f-only-digits', function (event) {
                $.multiform.isValidInput(event, /[0-9]/);
            });
            // Удаление изображений у полей вариантов
            $(document).on('click', '.settings-block .image-cell .delete-block', function () {
                $(this).closest("a").html("<i class=\"icon16 image\"></i>").closest("form").change();
                return false;
            });
            // Подсветка активных форматов файлов
            $(document).on("click", ".extension-item input", function () {
                if (!$(this).prop('checked')) {
                    $(this).parent().removeClass("selected");
                } else {
                    $(this).parent().addClass("selected");
                }
            });

            /* Отображение / скрытие зависимых полей */
            $(document).on('input', ".has-dependent-fields .dependent-block input, .has-dependent-fields .dependent-block select", function () {
                let elem = $(this);
                let field = elem.closest('.has-dependent-fields');
                let dependentValue = field.data('dependent-value');
                if ((elem.is(':checkbox') && (!!dependentValue === elem.prop('checked'))) || (!elem.is(':checkbox') && dependentValue == elem.val())) {
                    field.find('.settings-dependent-field').show();
                } else {
                    field.find('.settings-dependent-field').hide();
                }
            });
        },
        /* Инициализируем выбор цвета */
        initColorpicker: function () {
            $(".colorpicker").not('.colorpicker-inited').each(function () {
                var that = $(this);
                var params = {cellWidth: 14, cellHeight: 14, displayCSS: {width: '20px', borderRadius: '50%'}, chooserCSS: {left: '50px'}};
                if (that.attr('position') !== undefined && that.attr('position') == 'bottom-top') {
                    params['chooserCSS']['top'] = 'inherit';
                    params['chooserCSS']['bottom'] = 0;
                }
                if (that.hasClass("fly-save")) {
                    params['onSelect'] = function (color) {
                        $(".s-actions-block > form").find("input[name='custom_color']").val('#' + color);
                        $.post("?action=handler", {data: 'saveActionField', name: 'custom_color', value: '#' + color, action_id: that.data('id'), form_id: $.form.currentForm}, function (response) {
                            $.form_actions.updateSidebar(that.data('id'));
                        });
                    };
                }
                that.simpleColor(params).addClass("colorpicker-inited");
            });
        },
        // Вызывается каждый раз при открытии вкладки
        initPageEdit: function () {
            this.initFieldsSortAction();
            this.adjustBuilderFields();
            this.initFileuploadOptionImages();
            this.initFileuploadAttachments();
            // Плавающие сайдбар с полями формы
            $(window).scroll(function () {
                $.form_edit.adjustBuilderFields();
            });
            this.triggerFields();
        },
        triggerFields: function () {
            /* Инициируем обновление зависимых полей */
            $("#form-builder").find('.has-dependent-fields .dependent-block input, .has-dependent-fields .dependent-block select').trigger('input');
        },
        // Добавление полей формы 
        addFieldAction: function (btn) {
            let that = this;
            if (!$.multiform.isLoading()) {
                $.multiform.showLoading($__("Adding a new field"));
                var type = btn.data("type");
                $.ajax({
                    url: "?action=handler",
                    dataType: "json",
                    type: "post",
                    data: {data: "addField", id: $.form.currentForm, type: type},
                    success: function (response) {
                        $.multiform.hideLoading();
                        if (response.status == 'ok' && response.data && typeof response.data.html !== 'undefined') {
                            var html = $(response.data.html);
                            that.appendNewField(type, html);
                            $('html, body').animate({
                                scrollTop: $(".form-builder-row[data-id='" + response.data.id + "']").offset().top
                            }, 500);
                        }
                    },
                    error: function () {
                        $.multiform.showError($__("Field cannot be loaded"));
                    }
                });
            }
        },
        appendNewField: function(type, html) {
            html.addClass("highlighted draggable");
            if (type == 'radio' || type == 'select' || type == 'checkbox' || type == 'table_grid') {
                $.form_edit.initOptionsSort(html.find(".settings-block .tab-options table tbody"));
            }
            if (type == 'file') {
                $.form_edit.initFileuploadAttachments(html.find(".fileupload-attachment"));
            }
            $("#form-builder").append(html);
            if (type == 'radio' || type == 'checkbox') {
                $.form_edit.initFileuploadOptionImages();
            }
            this.triggerFields();
        },
        // Изменение статуса поля 
        changeStatusAction: function (btn, id) {
            var i = btn.find("i"),
                oldClass = i.attr("class"),
                status = i.hasClass("lightbulb") ? 0 : 1;
            if (!i.hasClass("loading")) {
                i.removeClass().addClass("icon16 loading");
                $.post("?action=handler", {data: btn.data("type"), id: id, status: status}, function (response) {
                    i.removeClass();
                    if (response.status == 'ok') {
                        i.addClass("icon-custom lightbulb" + (response.data ? "" : "-off"));
                        var row = btn.closest(".form-builder-row");
                        if (btn.data("type") == 'fieldStatus') {
                            if (response.data) {
                                row.removeClass("not-visible");
                            } else {
                                row.addClass("not-visible");
                            }
                        }
                    } else {
                        i.addClass(oldClass);
                    }
                });
            }
        },
        // Создание дубликата поля 
        copyFieldAction: function (btn, id) {
            let that = this;
            var i = btn.find("i");
            if (!i.hasClass("loading")) {
                i.removeClass().addClass("icon16 loading");
                $.post("?action=handler", {data: 'copyField', id: id}, function (response) {
                    i.removeClass().addClass("icon-custom copy");
                    if (response.status == 'ok' && response.data) {
                        var html = $(response.data);
                        var type = html.data("type");
                        that.appendNewField(type, html);
                        $.form_edit.fieldSort();
                    } else {
                        $.multiform.showError($__("Duplicate error"));
                    }
                });
            }
        },
        // Добавление атрибутов поля
        addAttributeAction: function (btn) {
            var table = btn.closest('.settings-value').find("table");
            var tabId = btn.closest('.settings-block').find('.tabs .selected a').data('tab');
            if (!table.length) {
                table = $("<table class='attribute-table'><tr><th>" + $__("Name") + "</th><th>" + $__("Value") + "</th></tr></table>");
                btn.after(table);
            }
            table.append("<tr><td style='width: 100px;'><input type='text' name='" + tabId + "[attribute][name][]'></td><td><input type='text' name='" + tabId + "[attribute][value][]'></td><td><a href='javascript:void(0)' onclick='$(this).closest(\"tr\").remove();'><i class='icon16 no'></i></a></td></tr>");

        },
        // Открытие настроек поля
        editFieldAction: function (btn) {
            btn.parent().hide().closest(".form-builder-row").addClass("editing").removeClass("highlighted").find("> .settings-block").slideDown(function () {
                $(this).trigger('field-is-editing');
            });
        },
        // Удаление поля
        deleteFieldAction: function (btn, field_id) {
            if (field_id) {
                var type = btn.closest('.form-builder-row').attr('data-type');
                var dialogContent = '<p style="margin-top: 30px">' + $__("All records belonging to the field will be deleted") + '</p>';
                var dialogParams = {
                    loading_header: $__("Wait, please..."),
                    buttons: '<div class="align-center">'
                        + '<input type="submit" value="' + $__("Delete") + '" class="button red">'
                        + (type == 'section' ? ' <a onclick="$(this).closest(\'form\').addClass(\'deleteAll\').submit()" class="button red">' + $__("Delete section and fields inside") + '</a>' : '')
                        + '<span class="errormsg" style="margin-left: 10px; display: inline-block"></span> '
                        + $__('or')
                        + ' <a href="#" class="cancel">' + $__('cancel') + '</a></div>',
                    title: $__("Do you really want to delete field?"),
                    content: dialogContent,
                    height: '150px',
                    'min-height': '150px',
                    onClose: function () {
                        $("#delete-field-dialog").remove();
                    },
                    onSubmit: function (form) {
                        if (!form.hasClass("is-loading")) {
                            form.addClass("is-loading");
                            var params = {data: 'deleteField', id: field_id};
                            if (type == 'section') {
                                params['delete_all'] = form.find('form').hasClass('deleteAll') ? 1 : 0;
                            }
                            $.post("?action=handler", params, function (response) {
                                if (response.status == 'ok') {
                                    var row = btn.closest(".form-builder-row");
                                    if ((type == 'section' && params['delete_all']) || type !== 'section') {
                                        row.fadeOut(function () {
                                            $(this).remove();
                                        });
                                    } else if (type == 'section') {
                                        var fields = row.find("> .multiform-gap-field > .form-builder-row");
                                        fields.insertAfter(row);
                                        row.fadeOut(function () {
                                            $(this).remove();
                                        });
                                    }
                                }
                                form.trigger('close');
                            });
                        }
                        return false;
                    }
                };
                $("body").append("<div id='delete-field-dialog'></div>");
                var dialog = $("#delete-field-dialog");
                dialog.waDialog(dialogParams);
            }
        },
        // Переключение табов настроек
        fieldToggleTabAction: function (btn) {
            var settingsBlock = btn.closest(".settings-block");
            settingsBlock.find(".tabs li").removeClass("selected");
            btn.parent().addClass("selected").removeClass("error");
            settingsBlock.find(".tab-content > div").hide().siblings(".tab-" + btn.data("tab")).show();
        },
        //Закрытие табов с настройками
        fieldCloseSettingsAction: function (btn) {
            btn.parent().slideUp(function () {
                btn.closest(".form-builder-row").find(".f-icons li").show();
            }).closest(".form-builder-row").removeClass("editing");
        },
        // Редактирование символьного кода
        editSymbolicCodeAction: function (btn) {
            btn.closest("div").hide().next().show();
        },
        // Сохранение полей формы
        saveFormFields: function (form) {
            if (!this.isFormLoading(form)) {
                $.form_edit.formSaveStatus(form, 'loading');
                form.find('.errormsg').html('');
                form.closest(".settings-block").find(".tabs li").removeClass("error");
                $.ajax({
                    url: "?module=form&action=save&form_id=" + $.form.currentForm,
                    dataType: "json",
                    type: "post",
                    data: form.serializeArray(),
                    success: function (response) {
                        $.form_edit.formSaveStatus(form, response.status);
                        if (response.status == 'ok' && response.data) {
                            $.post("?action=handler", {
                                data: 'getField',
                                id: $.form.currentForm,
                                field_id: response.data.id,
                                update_settings: typeof response.data.updateSettings !== 'undefined'
                            }, function (resp) {
                                var field = $(".multiform-gap-field[data-id='" + resp.data.id + "']");
                                if (field.length && typeof resp.data.html !== 'undefined' && resp.data.html !== '') {
                                    field.replaceWith(resp.data.html);
                                    form.find(".s-formula-mode .s-cancel").click();
                                    // Обновляем ID у вариантов полей и загружаем изображения
                                    if (typeof response.data.uploadOptions !== 'undefined') {
                                        field.closest(".form-builder-row").find(".settings-block .tab-options tr.sortable, .settings-block .tab-options tr.other-field").each(function (i) {
                                            var tr = $(this),
                                                optionId = tr.hasClass("other-field") ? $(resp.data.settings_html.options).find("tr.other-field").attr("data-id") : $(resp.data.settings_html.options).find("tr.sortable").eq(i).attr("data-id"),
                                                oldId = tr.attr("data-id");
                                            tr.attr("data-id", optionId).find("input").each(function () {
                                                var input = $(this);
                                                input.attr("name", input.attr("name").replace(/(\[new\d+\])/, "[update" + optionId + "]"));
                                                if (input.is(":radio") && (input.attr("name") == 'options[default]' || input.attr("name") == 'options_column[default]')) {
                                                    input.val("update" + optionId);
                                                }
                                            });
                                            tr.siblings("tr[data-image-status-id=\"" + oldId + "\"]").attr("data-image-status-id", optionId);
                                            if (tr.find(".image-cell").data("image") !== undefined) {
                                                tr.find(".image-cell").data("image").submit();
                                            }
                                        });

                                    }
                                }
                            }, "json");
                        }
                        if (response.status == 'fail' && response.errors) {
                            if (typeof response.errors.messages !== 'undefined') {
                                for (var error in response.errors.messages) {
                                    form.find('.errormsg').append(response.errors.messages[error]) + '<br>';
                                }
                            }
                            if (typeof response.errors.tabs !== 'undefined') {
                                for (var tab in response.errors.tabs) {
                                    form.closest(".settings-block").find("." + response.errors.tabs[tab] + "-tab").addClass("error");
                                }
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $.form_edit.formSaveStatus(form, 'fail');
                        $.multiform.showError($__("Field cannot be loaded"));
                    }
                });
            }
            return false;
        },
        // Сортировка полей формы
        fieldSort: function () {
            var sortData = {};
            $("#form-builder .form-builder-row").each(function (i) {
                sortData[$(this).data('id')] = i;
            });
            $.post("?action=handler", {data: "fieldSort", id: $.form.currentForm, sort: sortData});
        },
        adjustBuilderFields: function () {
            var scrollOffset = $(window).scrollTop(),
                formBuilder = $("#form-builder-fields");
            if (formBuilder.is(':animated') || !formBuilder.length) {
                return false;
            }
            clearTimeout(formBuilder.data('timer'));
            formBuilder.data('timer', setTimeout(function () {
                if (formBuilder.data('offset') === undefined) {
                    $("#form-builder-fields").attr('data-offset', formBuilder.offset().top);
                }
                if (scrollOffset >= formBuilder.data('offset')) {
                    formBuilder.stop().animate({"top": scrollOffset - formBuilder.data('offset') + 20}, 500);
                } else {
                    formBuilder.stop().animate({"top": 0}, 500);
                }
            }, 200));
        },
        // Изменение статуса формы
        formSaveStatus: function (form, status) {
            var btn = form.find("input[type='submit']");
            btn.next("i").remove();
            if (status == 'ok') {
                form.removeClass("is-loading");
                btn.removeClass("red yellow").addClass("green").after("<i class='icon16 yes'></i>");
                form.closest(".form-builder-row").find("> .settings-save-status").hide().find("i").removeClass("loading").addClass("status-yellow");
            } else if (status == 'fail') {
                form.removeClass("is-loading");
                btn.removeClass("green yellow").addClass("red").after("<i class='icon16 no'></i>");
                form.closest(".form-builder-row").find("> .settings-save-status").show().find("i").removeClass("loading").addClass("status-yellow");
            } else if (status == 'loading') {
                form.addClass("is-loading");
                btn.removeClass("green red").addClass("yellow").after("<i class='icon16 loading'></i>");
                form.closest(".form-builder-row").find("> .settings-save-status").show().find("i").removeClass("status-yellow").addClass("loading");
            }
            clearTimeout(btn.data('timer'));
            btn.data('timer', setTimeout(function () {
                btn.next("i").remove();
            }, 3000));
        },
        isFormLoading: function (form) {
            return form.hasClass("is-loading") ? 1 : 0;
        },
        initOptionsSort: function (options) {
            var initSort = function (field) {
                // Сортировка полей вариантов у списков
                field.jsortable({
                    handle: '.sort',
                    containerSelector: '.zebra tbody',
                    itemSelector: "tr.sortable",
                    placeholder: "<tr class='sortable-placeholder'><td colspan='7'></td></tr>",
                    bodyClass: "",
                    onDrop: function ($item, container, _super) {
                        var table = $item.closest("table");
                        table.find("tr[data-image-status-id]").each(function () {
                            $(this).insertAfter(table.find("tr[data-id='" + $(this).attr("data-image-status-id") + "']"));
                        });
                        table.closest("form").change();
                        _super($item, container);
                    }
                });
            };
            if (!options) {
                initSort($(".settings-block .tab-options table tbody"));
            } else {
                initSort(options);
            }
        },
        // Сортировка полей
        initFieldsSortAction: function () {
            this.initOptionsSort();
            $("#form-builder").jsortable({
                handle: '.form-builder-row > .sort',
                containerSelector: '.sort-list',
                itemSelector: ".form-builder-row",
                placeholder: "<div class='sortable-placeholder'></div>",
                onDrop: function ($item, container, _super) {
                    $.form_edit.fieldSort();

                    /* Определяем принадлежность поля к комплекту */
                    var section = $item.closest('.multiform-section');
                    $.post("?action=handler", {
                        data: "updateSection",
                        field_id: $item.data('id'),
                        section_id: section.length ? section.data('field-id') : 0
                    });

                    /* Обновляем все секции */
                    $.form_edit.updateSections();

                    _super($item, container);
                }
            });
        },
        // Добавление полей вариантов для списков
        addOptionFieldAction: function (btn, isOtherField) {
            var clone = btn.closest("tbody").find("tr:first").clone();

            var table = btn.closest("table");
            var count = 1,
                fieldId = "new" + count;

            while (table.find("tr.sortable[data-id='" + fieldId + "']").length) {
                count++;
                fieldId = "new" + count;
            }
            clone.find("input").each(function () {
                var that = $(this);
                if (that.is(":radio") && (that.attr('name') == 'options[default]' || that.attr('name') == 'options_column[default]')) {
                    that.val(fieldId);
                }
                that.attr("name", that.attr("name").replace(/(update)\d+/, fieldId));
                that.is(":text") && that.attr('value', '');
                that.is(":checkbox") && that.removeAttr('checked');
            });
            !clone.hasClass("sortable") && clone.addClass("sortable");
            clone.find(".delete-option").show();
            clone.find(".sort").css('display', 'block');
            clone.attr("data-id", fieldId).find(".image-cell").html("<i class=\"icon16 image\"></i>");
            // Если необходимо добавить поле для произвольного текста
            if (isOtherField) {
                clone.removeClass("sortable").addClass("other-field").find(".sort").remove();
                clone.find(".customize").html('');
                clone.find(".option-value input").attr("placeholder", $__("Other"));
                clone.find(".add-option").parent().html("<input type=\"hidden\" name=\"options[sort][" + fieldId + "]\" value=\"999999\">");
            }
            btn.closest("tr").after(clone.prop('outerHTML') + ("<tr data-image-status-id=\"" + fieldId + "\" class=\"hidden\"><td></td><td></td><td colspan=\"4\" class=\"image-info\"></td></tr>"));
            table.find("tr[data-image-status-id]").each(function () {
                $(this).insertAfter(table.find("tr[data-id='" + $(this).attr("data-image-status-id") + "']"));
            });
            this.initFileuploadOptionImages(table.find("tr[data-id=\"" + fieldId + "\"]"));
            btn.closest("form").change();
        },
        // Удаление полей вариантов для списков
        deleteOptionFieldAction: function (btn) {
            var tr = btn.closest("tr");
            btn.closest("form").change();
            if (tr.siblings(".sortable").length) {
                tr.siblings("tr[data-image-status-id='" + tr.attr("data-id") + "']").remove();
                tr.remove();
            }
            // Если остался только один вариант, то очищаем его поля, но не удаляем
            else {
                tr.find("input").each(function () {
                    var that = $(this);
                    that.attr("name", that.attr("name").replace(/(update)\d+/, ""));
                    that.is(":text") && that.attr('value', '');
                });
                tr.find(".image-cell").removeData("image").html("<i class=\"icon16 image\"></i>");
                tr.siblings("tr[data-image-status-id='" + tr.attr("data-id") + "']").addClass("hidden");
            }
        },
        // Открытие формы загрузки файлов
        addOptionImageAction: function (btn) {
            btn.siblings(".fileupload-option-images").click();
        },
        // Импорт вариантов множественных полей
        importOptionValuesAction: function (btn) {
            var text = btn.closest(".dialog-window").find("textarea").val(),
                lines = text.split('\n');
            if (lines.length == 1 && lines[0] == '') {
                $("#multiform-wa-dialog").trigger('close');
                return false;
            }
            var table = $("#form-builder .import-options.open").closest(".settings-value").find("table tbody");
            for (var i in lines) {
                $.form_edit.addOptionFieldAction(table.find(".add-option").last());
                var chunks = lines[i].split("|");
                var tr = table.find("tr.sortable").last();
                // Если имеются ключи
                if (chunks.length >= 2) {
                    tr.find(".customize input").val(chunks.shift());
                    tr.find(".option-value input").val(chunks.join("|"));
                } else {
                    tr.find(".option-value input").val(chunks[0]);
                }
            }
            $("#multiform-wa-dialog").trigger('close');
        },
        openOptionImportDialogAction: function (btn) {
            btn.addClass("open");
            $("#multiform-wa-dialog").waDialog({
                'height': '400px',
                'width': '500px',
                'content': btn.next().html(),
                'buttons': "<a href=\"#/import/optionValues\" class=\"button green js-action\">" + $__("Save") + "</a> " + $__("or") + ' <a class="cancel" href="javascript:void(0)">' + $__('hide') + '</a>',
                onClose: function () {
                    btn.removeClass("open");
                }
            });
        },
        // Сбросить дефолтные поля у полей вариантов
        cancelOptionDefaultValueAction: function (btn) {
            btn.closest('.settings-value').find('input[name*="options[default]"], input[name*="options_column[default]"]').prop("checked", false);
        },
        // Показать / скрыть ID вариантов полей
        showOptionIdsAction: function (btn) {
            var table = btn.closest(".settings-field").find(".settings-value > table"),
                optionIdsFields = table.find("td.grey, th.grey");

            if (optionIdsFields.length) {
                optionIdsFields.remove();
            } else {
                table.find('thead > tr').prepend('<td class="min-width grey"></td>');
                table.find('tbody > tr').each(function () {
                    var tr = $(this);
                    if (tr.attr('data-id') !== undefined && tr.attr('data-id').indexOf('new') === -1) {
                        tr.prepend('<td class="grey">#' + tr.attr('data-id') + '</td>');
                    }
                });
            }
        },
        // Обновляем все секции
        updateSections: function () {
            $(".multiform-section").each(function () {
                var section = $(this);
                if (section.find('.form-builder-row').length) {
                    section.removeClass("empty");
                } else {
                    section.addClass("empty");
                }
                var parentSection = section.parent().closest(".multiform-section");
                if (parentSection.length) {
                    if (parentSection.hasClass("even")) {
                        section.removeClass("even");
                    } else if (!section.hasClass("even")) {
                        section.addClass("even");
                    }
                }
            });
        },
        // Выделение всех расширений для файлов
        extensionSelectAllAction: function (btn) {
            if (!btn.hasClass("all-selected")) {
                btn.addClass("all-selected").closest(".extension-group").find("input").each(function () {
                    $(this).prop("checked", true).parent().addClass("selected");
                });
            } else {
                btn.removeClass("all-selected").closest(".extension-group").find("input").each(function () {
                    $(this).prop("checked", false).parent().removeClass("selected");
                });
            }
        },
        // Удаление прикрепленных файлов к полю
        deleteFileAttachmentAction: function (btn) {
            btn.parent().remove();
        },
        // Активация режима редактирования формул
        formulaEnableAction: function (btn) {
            var row = btn.closest(".form-builder-row");
            var hidden = row.find(".f-formula-clon");
            btn.hide().siblings().show().closest(".s-formula-mode").find(".s-formula-fields").show();
            row.find(".formula-field textarea").show().expanding({
                update: function (that, obj) {
                    var text = obj.text.replace(/&/g, '&amp;').replace(/>/g, '&gt;').replace(/</g, '&lt;')
                        .replace(/({Field:\d+(\|string)?})/g, '<font class="field-helper">$1</font>');
                    obj.cloneField.html(text);
                    hidden.val(obj.text);
                }
            }).focus().expanding('update');
            row.find(".formula-value").hide().closest(".formula-field").addClass("initialized");
        },
        formulaCancelAction: function (btn) {
            var row = btn.closest(".form-builder-row");
            btn.hide().siblings().hide().closest(".s-formula-mode").find(".s-formula-fields").hide();
            btn.prev().show();
            row.find(".formula-field textarea").expanding('destroy').hide().val(row.find(".formula-value").text());
            row.find(".formula-value").show().closest(".formula-field").removeClass("initialized");
            row.find(".f-formula-clon").val(row.find(".formula-value").text());
        },
        formulaSaveAction: function (btn) {
            this.saveFormFields(btn.closest("form"));
        },
        formulaAddFieldAction: function (btn) {
            var row = btn.closest(".form-builder-row");
            if (row.find(".formula-field").hasClass("initialized")) {
                var textarea = row.find(".formula-field textarea")[0];
                var snippet = btn.find("b").text();
                if (document.selection) {
                    //For browsers like Internet Explorer
                    textarea.focus();
                    var sel = document.selection.createRange();
                    sel.text = snippet;
                    textarea.focus();
                } else if (textarea.selectionStart || textarea.selectionStart == '0') {
                    //For browsers like Firefox and Webkit based
                    var startPos = textarea.selectionStart;
                    var endPos = textarea.selectionEnd;
                    var scrollTop = textarea.scrollTop;
                    textarea.value = textarea.value.substring(0, startPos) + snippet + textarea.value.substring(endPos, textarea.value.length);
                    textarea.focus();
                    textarea.selectionStart = startPos + snippet.length;
                    textarea.selectionEnd = startPos + snippet.length;
                    textarea.scrollTop = scrollTop;
                } else {
                    textarea.value += snippet;
                    textarea.focus();
                }
                $(textarea).expanding('update');
            }
            return false;
        },
        // Инициализация загрузки изображений для вариантов списков
        initFileuploadOptionImages: function (field) {
            var initField = function (field) {
                var tr = field.closest("tr"),
                    progressField = tr.siblings("tr[data-image-status-id='" + tr.attr("data-id") + "']"),
                    imageBlock = tr.find(".image-cell");
                field.fileupload({
                    autoUpload: false,
                    dataType: 'json',
                    url: "?module=form&action=optionsUpload",
                    add: function (e, data) {
                        var file = data.files[0];
                        var acceptedTypes = /(\.|\/)(jpe?g|png|gif)$/i;

                        imageBlock.removeData("image");

                        // Проверка расширений
                        if (!acceptedTypes.test(file.type)) {
                            progressField.removeClass("hidden").find(".image-info").html("<i class='red'>" + $__('Accepted file types: JPG, PNG, GIF') + "</i>");
                            return false;
                        }

                        imageBlock.data("image", data);

                        // Если поле было сохранено, то сразу инициируем загрузку файла
                        if (tr.attr("data-id").search("new") === -1) {
                            data.submit();
                        } else {
                            progressField.removeClass("hidden").find(".image-info").html($__("Press <span class=\"highlighted\">Save</span> to start upload file:") + file.name).append($("<i class=\"multiform-broom\" title=\"" + $__("Cancel") + "\" style=\"margin-left: 10px;\"></i>").click(function () {
                                $(this).closest("td").html('').closest("tr").addClass("hidden");
                                imageBlock.removeData("image");
                            }));
                        }
                    },
                    progressall: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        progressField.find(".progressbar-inner").css('width', progress + '%');
                    },
                    submit: function (e, data) {
                        data.formData = {
                            option_id: $(data.fileInputClone[0]).closest("tr").attr("data-id"),
                            _csrf: $.multiform.csrf
                        };
                        progressField.removeClass("hidden").find(".image-info").html("<div class=\"progressbar green small float-left\" style=\"width: 70%;\"><div class=\"progressbar-outer\"><div class=\"progressbar-inner\" style=\"width: 0%;\"></div></div></div><i class=\"icon16 loading\" style=\"margin: 7px 0 0 5px;\"></i><br clear=\"left\" />");
                    },
                    done: function (e, data) {
                        var response = data._response.result;
                        if (response && response.status == 'ok') {
                            progressField.addClass("hidden");
                            if (response.data.image) {
                                tr.find(".image-cell").html("<span class=\"delete-block\">" + $__("delete") + "</span><img src=\"" + response.data.image_url + "\"><input type=\"hidden\" value=\"" + response.data.image + "\" name=\"options[image][update" + $(data.fileInputClone[0]).closest("tr").attr("data-id") + "]\">");
                            } else {
                                tr.find(".image-cell").html("<i class=\"icon16 image\"></i>");
                            }
                        } else {
                            progressField.find(".image-info").html("<span class=\"red\">" + response.errors + "</span>");
                        }
                    },
                    fail: function (e, data) {
                        progressField.find(".image-info").html("<span class=\"red\">" + $__("Upload failed") + "</span>");
                    },
                    always: function (e, data) {
                        imageBlock.removeData("image");
                    }
                });
            };
            if (field) {
                initField(field);
            } else {
                // Находим все поля файлов
                $("#form-builder .fileupload-option-images").each(function () {
                    initField($(this));
                });
            }
        },
        // Инициализация загрузки изображений для вариантов списков
        initFileuploadAttachments: function (field) {
            var initField = function (field) {
                var fieldId = field.closest(".form-builder-row").attr("data-id"),
                    progressField = field.siblings(".progressfield-block");
                field.fileupload({
                    autoUpload: true,
                    dataType: 'json',
                    url: "?module=form&action=attachmentUpload",
                    progressall: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        progressField.find(".progressbar-inner").css('width', progress + '%');
                    },
                    submit: function (e, data) {
                        data.formData = {fieldId: fieldId, _csrf: $.multiform.csrf};
                        progressField.removeClass("hidden").html("<div class=\"progressbar green small float-left\" style=\"width: 70%;\"><div class=\"progressbar-outer\"><div class=\"progressbar-inner\" style=\"width: 0%;\"></div></div></div><i class=\"icon16 loading\" style=\"margin: 7px 0 0 5px;\"></i><br clear=\"left\" />");
                    },
                    done: function (e, data) {
                        var response = data._response.result;
                        if (response && response.status == 'ok') {
                            progressField.addClass("hidden");
                            if (response.data.file) {
                                var fieldBlock = data.fileInputClone;
                                if (fieldBlock.siblings(".attachment-block").length) {
                                    fieldBlock.siblings(".attachment-block").find(".f-attachment").attr("href", "?module=frontend&action=downloadAttachment&hash=" + response.data.file)
                                        .find("span").text(response.data.name).parent().siblings("input[name*='hash']").val(response.data.file).siblings("input[name*='name']").val(response.data.name);
                                } else {
                                    var html = "<div class=\"block attachment-block\">"
                                        + "<a href=\"?module=frontend&action=downloadAttachment&hash=" + response.data.file + "\" class=\"f-attachment\"><i class=\"icon-custom attachment\"></i> <span>" + response.data.name + "</span></a>"
                                        + " - <a href=\"#/delete/fileAttachment/\" class=\"js-action\" title=\"" + $__("delete") + "\"><i class=\"icon10 delete\"></i> " + $__("delete") + "</a>"
                                        + "<input type=\"hidden\" name=\"" + field.data("name") + "[hash]\" value=\"" + response.data.file + "\">"
                                        + "<input type=\"hidden\" name=\"" + field.data("name") + "[name]\" value=\"" + response.data.name + "\">"
                                        + "</div>";
                                    fieldBlock.after(html);
                                }
                            }
                        } else {
                            progressField.html("<span class=\"red\">" + response.errors + "</span>");
                        }
                    },
                    fail: function (e, data) {
                        progressField.html("<span class=\"red\">" + $__("Upload failed") + "</span>");
                    }
                });

            };
            if (field) {
                initField(field);
            } else {
                // Находим все поля файлов
                $("#form-builder .fileupload-attachment").each(function () {
                    initField($(this));
                });
            }
        }
    };
    $(function () {
        $.form_edit.init();
    });
})(jQuery);