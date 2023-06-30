/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

if (typeof jQuery == 'undefined') {
    var script = document.createElement('script');
    script.src = "https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js";
    document.getElementsByTagName('head')[0].appendChild(script);
} else {
    var MultiformForm = (function ($) {

        MultiformForm = function (options) {
            var that = this;

            // DOM
            that.$form = options.form;

            // INIT
            that.initClass();
        };

        MultiformForm.prototype.initClass = function () {
            var that = this;

            if (that.$form.hasClass("inited")) {
                return false;
            }

            that.initSubmit();
            that.bindEvents();

            /* Условия */
            $(".multiform-wrap .has-condition :input:not(':file'), .multiform-wrap .has-condition select, .multiform-wrap .has-condition textarea").on('change keyup paste input', function () {
                var that = $(this);
                var conditionIds = (that.closest(".has-condition").data("condition") + "").split(",");
                var form = that.closest(".multiform-wrap");
                for (var i in conditionIds) {
                    $.multiformFrontend.condition.checkCondition(conditionIds[i], form);
                }
            });

            /* Обновление капчи */
            $('.multiform-gap-captcha img').click(function () {
                var captcha = $(this);
                captcha.hide().after("<img src='" + $.multiformFrontend.appUrl + 'css/loading16.gif' + "'>");
                captcha.attr('src', captcha.attr('src').replace(/\?.*$/, '?form_uid=' + captcha.closest('.multiform-wrap').attr('data-uid') + '&rid=' + Math.random()));
                captcha.one('load', function () {
                    captcha.next("img").remove();
                    captcha.show().next('input').focus();
                });
                captcha.next('input').val('');
                return false;
            });

            /* Всплывающее окно */
            $(".multiform-popup-link").click(function () {
                $.multiformFrontend.dialog.show($(this));
                return false;
            });
            $(".multiform-popup-link a").click(function () {
                $(this).closest(".multiform-popup-link").click();
                return false;
            });

        };

        MultiformForm.prototype.initSubmit = function () {
            $(".multiform-wrap input[type='submit']").click(function () {
                $.multiformFrontend.execute($(this));
                return false;
            });
        };

        MultiformForm.prototype.bindEvents = function () {

        };

    })(jQuery);
    var MultiformScripts = (function ($) {
        return {
            init: function ($form) {
                var self = this;
                $form.find('.multiform-gap-field').each(function () {
                    self.initDatepicker($(this));
                });
            },
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
                $.isFunction($.fn.mfMask) && $field.mfMask($field.data('field-mask')).addClass("inited");
            },
            initRangeSlider: function ($field, params) {
                $field.attr('data-params', JSON.stringify(params));
                $field.ionRangeSlider(params);
            },
            initTime: function ($field) {
                $.isFunction($.fn.mfMask) && $field.find('.multiform-full-time').mfMask('00:00:00');
            }
        };
    })(jQuery);
    (function ($) {
        $.multiformFrontend = {
            locale: '',
            eventOrigin: '',
            eventSource: {},
            init: function (options) {
                const self = this;

                if (!this.inited) {
                    this.appUrl = options.appUrl || '';
                    this.locale = options.locale || 'en_US';
                    this.frontendLocaleStrings = options.frontendLocaleStrings || {};

                    /* Устанавливаем локализацию текста */
                    $.ig_frontend_locale = $.extend(this.frontendLocaleStrings, $.ig_frontend_locale);
                    window.__ = puttext($.ig_frontend_locale);

                    /* Отправление данных */
                    $(".multiform-wrap button[data-submit]").click(function () {
                        $.multiformFrontend.execute($(this));
                        return false;
                    });

                    /* Кнопка-ссылка */
                    $(".multiform-wrap button[data-redirect]").click(function () {
                        var btn = $(this);
                        if (btn.data('onblank')) {
                            window.open(btn.data('link'), '_blank');
                        } else {
                            window.location.href = btn.data('link');
                        }
                        return false;
                    });

                    /* Условия */
                    var conditionFields = ".multiform-wrap .has-condition :input:not(':file'), .multiform-wrap .has-condition select, .multiform-wrap .has-condition textarea";
                    $(conditionFields).off('change keyup paste input').on('change keyup paste input', function () {
                        var that = $(this);
                        var conditionIds = (that.closest(".has-condition").data("condition") + "").split(",");
                        var form = that.closest(".multiform-wrap");
                        for (var i in conditionIds) {
                            $.multiformFrontend.condition.checkCondition(conditionIds[i], form);
                        }
                    });

                    /* Обновление капчи */
                    $('.multiform-gap-captcha img').off('click').on('click', function () {
                        var captcha = $(this);
                        captcha.hide().after("<img src='" + $.multiformFrontend.appUrl + 'css/loading16.gif' + "'>");
                        captcha.attr('src', captcha.attr('src').replace(/\?.*$/, '?form_uid=' + captcha.closest('.multiform-wrap').attr('data-uid') + '&rid=' + Math.random()));
                        captcha.one('load', function () {
                            captcha.next("img").remove();
                            captcha.show().next('input').focus();
                        });
                        captcha.next('input').val('');
                        return false;
                    });

                    /* Всплывающее окно */
                    $(document).off('click', '.multiform-popup-link').on('click', '.multiform-popup-link', function () {
                        $.multiformFrontend.dialog.show($(this));
                        return false;
                    });
                    $(document).off('click', '.multiform-popup-link a').on('click', '.multiform-popup-link a', function () {
                        $(this).closest(".multiform-popup-link").click();
                        return false;
                    });

                    /* Добавление/удаление доп полей для секции */
                    $(document).off("click", ".multiform-wrap .multiform-section-multiply-inner").on("click", ".multiform-wrap .multiform-section-multiply-inner", function () {
                        let elem = $(this);
                        let section = elem.closest('.multiform-section');
                        /* Прототип */
                        let parentSection = section;
                        if (section.hasClass('cloned-section')) {
                            parentSection = section.closest('.cloned-sections').closest('.type-section');
                        } else if (!section.find('> .cloned-sections').length) {
                            section.append('<div class="cloned-sections"></div>');
                        }
                        if (elem.attr('data-action') == 'add') {
                            let removeButton = elem.attr('data-remove-link');
                            let parentAddButton = parentSection.find('> .multiform-section-multiply').first();
                            let limit = parentAddButton.attr('data-limit');

                            let cloneSection = parentSection.clone();
                            let cloneRemoveButton = parentAddButton.clone();
                            let cloneSectionIndex = parentSection.find('> .cloned-sections > .cloned-section').length + 1;

                            /* Проверка на максимальное количество добавлений */
                            if (limit && parentSection.find('> .cloned-sections .type-section[data-id="' + parentSection.attr('data-id') + '"]').length + 1 > parseInt(limit)) {
                                elem.closest('.multiform-section-multiply').hide();
                            }

                            /* Кнопка удаления */
                            cloneRemoveButton.show().attr('data-action', 'remove').find('.multiform-section-multiply-inner').attr('data-action', 'remove').html(removeButton);

                            cloneSection.attr('data-parent-id', parentSection.attr('data-id')).addClass('cloned-section').removeAttr('id')
                                .find('.type-formula, .type-button').remove();
                            cloneSection.find('.cloned-sections').remove();
                            cloneSection.find('.errormsg').remove();
                            cloneSection.find('.multiform-error-field').removeClass('multiform-error-field');
                            cloneSection.find('.has-condition').removeClass('has-condition');

                            /* Изменяем названия полей */
                            cloneSection.find(":input, select, textarea").each(function (i) {
                                let elem = $(this);
                                let name = elem.attr('name');
                                if (name) {
                                    let newName = '';
                                    let cloneIndex = '';
                                    if (new RegExp(/fields/).test(name)) {
                                        let matchArr = name.match(/fields(.*)/);
                                        newName += 'cloned_section[' + parentSection.attr('data-field-id') + '][' + cloneSectionIndex + ']' + matchArr[1];
                                    } else {
                                        let splitResult = name.split(/cloned_section\]?(\[\d+\]\[\d+\])/);
                                        let splitLen = splitResult.length;
                                        newName = 'cloned_section';
                                        for (let i in splitResult) {
                                            i = parseInt(i);
                                            let chunk = splitResult[i];
                                            if (chunk !== '[' && chunk !== '') {
                                                if ((splitLen - 1) === i) {
                                                    newName += '[' + parentSection.attr('data-field-id') + '][' + cloneSectionIndex + ']';
                                                    cloneIndex += parentSection.attr('data-field-id') + '.' + cloneSectionIndex;
                                                }
                                                newName += chunk;
                                                if ((splitLen - 1) !== i) {
                                                    cloneIndex += chunk.substring(1, chunk.length - 1).replace('][', '.');
                                                    newName += '[cloned_section]';
                                                    cloneIndex += '._.';
                                                }
                                            }
                                        }
                                    }
                                    elem.attr('name', newName).attr('id', newName + i).attr('data-index', cloneIndex);
                                    if (elem.attr('type') === 'file') {
                                        elem.attr('data-name', 'files_' + elem.attr('data-field-id') + '_' + cloneIndex.replace(/\./g, '_') + '[]');
                                    }
                                    /* Для рейтинга меняем атрибуты у label */
                                    if (elem.is(':radio') && elem.parent().is('.multiform-rating')) {
                                        elem.next('label').attr('for', newName + i);
                                    }
                                }
                            });
                            cloneSection.find(':radio, :checkbox').closest('label').removeAttr('for');

                            /* Отображаем кнопку добавления у вложенных секций */
                            cloneSection.find('.type-section .multiform-section-multiply').show();
                            /* Добавляем кнопку удаления, предварительно удалив старую, если она есть */
                            cloneSection.find('> .multiform-section-multiply[data-action="remove"]').remove();
                            cloneSection.find('> .multiform-section-multiply[data-action="add"]').before(cloneRemoveButton).show();

                            /* Скрываем кнопку добавления у предыдущей секции */
                            elem.closest('.multiform-section-multiply').hide();

                            /* Проверка на максимальное количество добавлений */
                            if (limit && parentSection.find('> .cloned-sections .type-section[data-id="' + parentSection.attr('data-id') + '"]').length + 1 >= parseInt(limit)) {
                                cloneSection.find('> .multiform-section-multiply[data-action="add"]').hide();
                            }

                            self.reinitScriptsByParams(cloneSection);

                            parentSection.find('> .cloned-sections').append(cloneSection);
                        } else {
                            section.remove();
                            /* Отображаем кнопку добавления у последней секции */
                            parentSection.find('> .cloned-sections .type-section[data-id="' + parentSection.attr('data-id') + '"]:last > .multiform-section-multiply').show();
                            /* Если остался только прототип - отображаем кнопку добавления у него */
                            if (!parentSection.find('> .cloned-sections .type-section').length) {
                                parentSection.find('> .multiform-section-multiply').show();
                            }
                        }
                    });

                    /* Событие при открытии формы */
                    $(document).off('multiform-form-opened', '.multiform-wrap').on('multiform-form-opened', '.multiform-wrap', function () {
                        var form = $(this);
                        /* Инициируем маску */
                        if ($.isFunction($.fn.mfMask)) {
                            var phoneFields = form.find('.type-phone .f-mask:not(.inited)');
                            phoneFields.each(function () {
                                var elem = $(this);
                                elem.mfMask(elem.data('field-mask'));
                                elem.addClass('inited');
                            });
                        }
                        /* Активируем формулы */
                        setTimeout(function () {
                            $.multiformFrontend.updateFormula !== undefined && $.multiformFrontend.updateFormula(form);
                        }, 10);
                    });

                    this.addEvent(window, "message", function (event) {
                        if (typeof event.data === "string" && event.data.substring(0, 15) == 'resizeMultiform') {
                            $.multiformFrontend.eventOrigin = event.origin;
                            var data = event.data.split('|');
                            if (typeof data[1] !== 'undefined' && parseInt(data[1])) {
                                var formId = data[1];
                                var height = parseInt($(".multiform-wrap[data-id='" + formId + "']")[0].scrollHeight) + 100;
                                $.multiformFrontend.eventSource[formId] = event.source;
                                $.multiformFrontend.postMessageAction(event.source, height + '|' + formId);
                            }
                        }
                    });

                    /* Событие при загрузке формы */
                    $(document).off('multiform-loaded', '.multiform-wrap').on('multiform-loaded', '.multiform-wrap', function () {
                        MultiformScripts.init($(this));
                    });

                    $.ajaxSetup({ 'cache': false });
                    this.inited = 1;
                }
            },
            reinitScriptsByParams: function ($wrap) {
                $wrap.find('.multiform-gap-field').each(function () {
                    let $field = $(this);
                    switch ($field.data('type')) {
                        case 'date':
                            let $datepickerField = $field.find('.multiform-datepicker');
                            if ($datepickerField.length) {
                                $field.find('.Zebra_DatePicker_Icon_Wrapper').before($datepickerField);
                                $field.find('.Zebra_DatePicker_Icon_Wrapper').remove();
                                setTimeout(function () {
                                    MultiformScripts.initDatepicker($field);
                                }, 5);
                            }
                            break;
                        case 'file':
                            $field.find('.multiform-files').remove();
                            let $fileuploadField = $field.find('.multiform-fileupload');
                            if ($fileuploadField.length) {
                                MultiformScripts.initFileupload($fileuploadField, $fileuploadField.data('params'));
                            }
                            break;
                        case 'phone':
                            MultiformScripts.initPhone($field.find('.f-mask'));
                            break;
                        case 'number':
                            $field.find('.irs').remove();
                            let $rangeslider = $field.find('.range-slider');
                            if ($rangeslider.length) {
                                $rangeslider.removeClass('irs-hidden-input');
                                MultiformScripts.initRangeSlider($rangeslider, $rangeslider.data('params'));
                            }
                            break;
                        case 'time':
                            let $timeField = $field.find('.multiform-full-time');
                            if ($timeField.length) {
                                MultiformScripts.initTime($timeField);
                            }
                            break;
                    }
                });
            },
            /* Всплывающее окно */
            dialog: {
                show: function (btn) {
                    var that = this;

                    /* Удаляем все формы, открытые на странице */
                    if ($(".multiform-popup-window").length) {
                        var forms = $(".multiform-popup-window").find(".multiform-wrap");
                        forms.each(function () {
                            $("multiform-body[data-id='" + $(this).data("id") + "']").append($(this).removeAttr("style"));
                        });
                    }
                    $(".multiform-popup-window, .multiform-overlay").remove();

                    $("body").addClass('multiform-popup-overlay').append("<div class='multiform-popup-window'></div>");

                    var form = btn.closest(".multiform-body").find(".multiform-wrap");
                    $(".multiform-body[data-id='" + form.data("id") + "']").attr('data-old-style', form.attr('style'));

                    $("<a href='javascript:void(0)' class='s-close' title='" + __('Close') + "'>x</a>").click(function () {
                        that.hide(form, btn);
                    }).appendTo(form);


                    form.appendTo($(".multiform-popup-window"));
                    $("<div class='multiform-overlay'></div>").click(function () {
                        that.hide(form, btn);
                    }).appendTo("body");

                    /* Плавное появление */
                    setTimeout(function () {
                        $('.multiform-overlay, .multiform-popup-window .multiform-wrap').animate({ opacity: 1 }, function () {
                            form.trigger('multiform-form-opened');
                        });
                    }, 0);

                    this.autoSize(form);

                    /* Если форма еще не открывалась */
                    if (!form.hasClass("inited-popup")) {
                        setTimeout(function () {
                            form.find("input.range-slider").each(function () {
                                $(this).val($(this).data("value")).next().find(".range-slider-output").text($(this).data("value"));
                            });
                            form.find(".multiform-gap-field.type-date").each(function () {
                                MultiformScripts.initDatepicker($(this));
                            });
                            form.addClass("inited-popup");
                        }, 50);
                    }

                    $(window).on('resize', function () {
                        that.updatePopupSize(form);
                    });
                },
                hide: function (form, btn) {
                    $(".multiform-popup-window").fadeOut(200, function () {
                        var multBody = btn.closest(".multiform-body");
                        form.removeAttr("style").attr('style', multBody.attr('data-old-style'));
                        multBody.append(form);
                        $(this).remove();
                        $(".multiform-overlay").remove();
                        $('body').removeClass('multiform-popup-overlay');
                    });
                },
                /* Изменяем размер всплывающего окна */
                updatePopupSize: function (form) {
                    if (form.hasClass("inited-popup")) {
                        form.css('height', 'auto');
                        form.attr("data-real-height", form.outerHeight());
                        this.autoSize(form);
                    }
                },
                autoSize: function (form) {
                    if (!form.hasClass("inited-popup")) {
                        form.attr("data-real-height", form.outerHeight());
                    }
                    var realHeight = form.attr("data-real-height");
                    var width = form.width();
                    var wh = $(window).height();
                    var ww = $(window).width();
                    if (realHeight > wh) {
                        form.css('height', (wh - 90) + "px");
                    }
                    if (width > ww) {
                        form.width(ww - 10);
                    }
                    this.center(form);
                },
                center: function (form) {
                    var w = form.outerWidth();
                    var h = form.outerHeight();
                    var mt = (-1) * Math.max(0, h / 2);
                    var ml = (-1) * Math.max(0, w / 2);
                    form.css("marginTop", mt + "px");
                    form.css("marginLeft", ml + "px");
                }
            },
            appUrl: null,
            updateEvent: null,
            conditions: {},
            execute: function (btn) {
                var form = btn.closest(".multiform-wrap");
                var errormsg = form.find(".multiform-errorfld .errormsg");
                btn = form.find('button[data-submit]');

                var showError = function (error) {
                    if (error !== '') {
                        errormsg.html(error).css("display", "inline-block");
                    }

                    /* Меняем размер фрейма */
                    $.multiformFrontend.changeFrameSize(form);

                    $.multiformFrontend.dialog.updatePopupSize(form);
                    /* Если это всплывающая форма, отправляем пользователя в самый низ формы, чтобы он видел текст ошибки */
                    if (form.hasClass("inited-popup")) {
                        form.scrollTop(btn.position().top);
                    }
                };
                var hideError = function () {
                    errormsg.html('').hide();
                    form.find(".errormsg").hide();
                    form.find(".multiform-error-field").removeClass("multiform-error-field");
                };
                var showLoading = function (message) {
                    btn.hide();
                    if (!btn.next(".multiform-temp-loading").length) {
                        btn.after("<div class=\"multiform-temp-loading\" style=\"display: inline-block !important;\"><span>" + (message ? message : "") + "</span><img src='" + $.multiformFrontend.appUrl + "css/loading16.gif" + "' class='multiform-temp-loading' style='width: auto !important; display: inline-block !important; height: auto !important;' /><div>");
                    } else {
                        btn.next(".multiform-temp-loading").find("span").text(message);
                    }
                };

                hideError();

                /* Валидация обязательных полей формы */
                if ($.multiformFrontend.validate.requiredFields.execute(form)) {
                    showError(__('Fill in the required fields'));
                    return false;
                }

                /* Валидация файлов */
                if (form.find(".multiform-temp-file.error").length) {
                    showError(__('Added files are not valid'));
                    return false;
                }

                /* Валидация полей по маскам */
                if (!$.multiformFrontend.validate.masks(form)) {
                    showError(__('Fix the errors above'));
                    return false;
                }

                /* Проведение оставшихся проверок */
                var stop = 0;
                $.each($.multiformFrontend.validate.others, function (i, validateField) {
                    if (typeof validateField === 'function') {
                        var result = validateField(form);
                        if (!result) {
                            stop = true;
                            return false;
                        }
                    }
                });
                if (stop) {
                    showError(__('Fix the errors above'));
                    return false;
                }

                showLoading(__('Please, wait, while data is saving'));

                $.ajax({
                    url: form.data("action"),
                    data: form.find(":input, textarea, select").serializeArray(),
                    dataType: "json",
                    type: "post",
                    /* Если не поставить false, то будут проблемы на кириллических доменах */
                    crossDomain: false,
                    success: function (response) {
                        btn.show().next(".multiform-temp-loading").remove();
                        if (response.status == 'ok' && response.data) {
                            /* Если имеются файлы, запускаем их загрузку */
                            var fileFields = form.find(".multiform-gap-field.type-file").not(".multiform-hide");
                            if (fileFields.length) {
                                var first = "";
                                var tempFiles = "";
                                var totalFiles = 0;
                                fileFields.each(function () {
                                    var ffield = $(this);
                                    if (ffield.closest('.multiform-hide').length) {
                                        return true;
                                    }
                                    tempFiles = ffield.find(".multiform-temp-file");
                                    totalFiles += ffield.find(".multiform-temp-file-name").length;
                                    if (!first.length && tempFiles.length) {
                                        first = tempFiles.find('.multiform-cell').parent().first();
                                    }
                                });
                                if (first.length) {
                                    /* Общее количество файлов */
                                    form.attr('data-files', totalFiles).attr('data-files-done', tempFiles.filter('.f-abstract').length ? tempFiles.filter('.f-abstract').length : 0);
                                    var file = first.data('file');
                                    if (file !== undefined) {
                                        showLoading(__('Files uploading'));
                                        file.formData['record_id'] = response.data.id;

                                        file.submit();
                                        return false;
                                    }
                                }
                            }

                            if (response.data.has_files !== undefined) {
                                showLoading(__('Saving'));
                                $.post(form.data("action"), {
                                    data: "files",
                                    record_id: response.data.id,
                                    "isLast": 1,
                                    "_csrf": form.find("input[name='_csrf']").val()
                                }, function (response2) {
                                    $.multiformFrontend.postExecute(form, response2);
                                }, "json");
                            }
                            if (response.postExecute !== undefined) {
                                $.multiformFrontend.postExecute(form, response);
                            }
                        } else if (typeof response.errors !== 'undefined') {
                            if (typeof response.errors.messages !== 'undefined') {
                                var errors = "";
                                $.each(response.errors.messages, function (i, v) {
                                    errors += v + "<br>";
                                });
                                showError(errors);
                            }
                            if (typeof response.errors.fields !== 'undefined') {
                                $.each(response.errors.fields, function (i, data) {
                                    var $field = form.find("#multiformField" + data.id + '_' + form.data('uid'));
                                    /* Если указан индекс поля, тогда формируем его имя, чтобы по нему найти элемент среди дублированных полей */
                                    if (data.index && data.index !== '' && data.index !== 0) {
                                        var parts = data.index.split('_');
                                        var name = "cloned_section";
                                        for (var i in parts) {
                                            if (i !== '0') {
                                                name += '[cloned_section]';
                                            }
                                            name += '[' + parts[i].replace('-', '][') + ']';
                                        }
                                        name += '[field_' + data.id + '_' + form.data('uid') + ']';
                                        $field = form.find('[name^="' + name + '"]');
                                    }
                                    $.multiformFrontend.validate.addError($field, "", false);
                                });
                            }
                        } else {
                            showError(__('Something wrong. Please, reload the page'));
                        }
                    },
                    error: function () {
                        showError(__('Something wrong. Please, reload the page'));
                        btn.show().next(".multiform-temp-loading").remove();
                    }
                });
                return false;
            },
            postExecute: function (form, response) {
                var errormsg = form.find(".multiform-errorfld .errormsg");
                var btn = form.find("button[data-submit]");

                btn.show().next(".multiform-temp-loading").remove();

                if (typeof response === 'undefined') {
                    errormsg.html(__('Data saved with errors'));
                    /* Меняем размер фрейма */
                    $.multiformFrontend.changeFrameSize(form);

                    $.multiformFrontend.dialog.updatePopupSize(form);
                    return false;
                }
                if (response.status == 'ok' && response.data) {
                    /* Перенаправление */
                    if (typeof response.data.redirect !== 'undefined') {
                        btn.hide().after("<div class=\"multiform-temp-loading\" style=\"display: inline-block !important;\"><span>" + __('Wait, please. Redirecting') + "</span><img src='" + $.multiformFrontend.appUrl + "css/loading16.gif" + "' class='multiform-temp-loading' style=\"display: inline-block !important; height: auto !important;\" alt='" + __('Loading') + "...' /><div>");
                        location.href = response.data.redirect;
                        /* Меняем размер фрейма */
                        $.multiformFrontend.changeFrameSize(form);

                        $.multiformFrontend.dialog.updatePopupSize(form);
                        return false;
                    }
                    if (!form.closest(".multiform-popup-window").length && !form.closest(".record-page").length) {
                        /* Направляем пользователя в начало формы, если форма находится не во всплывающем окне */
                        $('html, body').animate({
                            scrollTop: btn.closest(".multiform-wrap").offset().top - 50
                        }, 800);
                    }

                    btn.closest(".multiform-wrap").trigger('multiform-form-submitted');

                    /* Текст после успешной отправки формы */
                    if (typeof response.data.message !== 'undefined') {
                        form.find(".multiform-gap-fields").html(response.data.message);
                        btn.remove();
                    }
                    /* JS callback */
                    if (typeof response.data.js_callback !== 'undefined') {
                        form.find(".multiform-gap-fields").append(response.data.js_callback);
                    }
                } else if (typeof response.errors !== 'undefined') {
                    if (typeof response.errors.messages !== 'undefined') {
                        var errors = "";
                        $.each(response.errors.messages, function (i, v) {
                            errors += v + "<br>";
                        });
                        if (errors !== '') {
                            errormsg.html(errors).css("display", "inline-block");
                        }
                    }
                    if (typeof response.errors.fields !== 'undefined') {
                        $.each(response.errors.fields, function (i, v) {
                            $.multiformFrontend.validate.addError(form.find("#multiformField" + v + '_' + form.data('uid')), "", false);
                        });
                    }
                } else {
                    errormsg.html(__('Something wrong. Please, reload the page'));
                }
                /* Меняем размер фрейма */
                $.multiformFrontend.changeFrameSize(form);

                $.multiformFrontend.dialog.updatePopupSize(form);
            },
            /* Проверка условий */
            condition: {
                checkCondition: function (conditionId, form) {
                    if ($.multiformFrontend.conditions[conditionId] !== undefined && $.multiformFrontend.conditions[conditionId].params !== undefined) {
                        var params = $.multiformFrontend.conditions[conditionId].params;
                        var results = [];
                        var conditionComplete;
                        /* Получаем результаты выполнения условий */
                        for (var i in params) {
                            if (typeof this['operator_' + params[i].operator] === 'function' && this['operator_' + params[i].operator](this.getValue(params[i].source, params[i].source_ext, form), params[i].value)) {
                                results.push(1);
                            }
                        }

                        /* Проверка условия and/or */
                        if ($.multiformFrontend.conditions[conditionId].andor == 1) {
                            conditionComplete = results.length === params.length;
                        } else {
                            conditionComplete = results.length > 0;
                        }

                        var showTarget;
                        if ($.multiformFrontend.conditions[conditionId].action == 'show') {
                            showTarget = conditionComplete;
                        } else {
                            showTarget = !conditionComplete;
                        }

                        var target = form.find("#multiformField" + $.multiformFrontend.conditions[conditionId].target + '_' + form.data('uid'));
                        if (target.length) {
                            if (showTarget) {
                                target.find(':input, textarea, select').prop('disabled', false);
                                target.show().removeClass("multiform-hide");
                                /* Если это поле с датой, то обновляем его */
                                MultiformScripts.initDatepicker(target);
                            } else {
                                target.find(':input, textarea, select').prop('disabled', true);
                                target.hide().addClass("multiform-hide");
                            }
                            /* Меняем размер фрейма */
                            var form = target.closest(".multiform-wrap");
                            $.multiformFrontend.changeFrameSize(form);

                            if (form.hasClass("inited-popup")) {
                                /* Пытаемся сымитировать .outerHeight(). */
                                form.attr("data-real-height", form.prop('scrollHeight') + parseInt(form.css('border-width')));
                                $.multiformFrontend.dialog.autoSize(form);
                            }
                        }
                    }

                },
                getValue: function (field_id, option_id, form) {
                    var formUid = form.data('uid');
                    var field = form.find("#multiformField" + field_id + '_' + formUid);
                    option_id = option_id || 0;
                    var value;
                    if (field.length) {
                        var functionName = 'get_' + field.data("type");
                        if (typeof $.multiformFrontend.condition[functionName] === 'function') {
                            value = $.multiformFrontend.condition[functionName](field, option_id);
                        } else {
                            value = field.find("#wahtmlcontrol_fields_field_" + field_id + '_' + formUid).val();
                        }
                    }
                    return value;
                },
                get_date: function (field, option_id) {
                    var value;
                    var textfield = field.find(".multiform-full-date");
                    if (textfield.length) {
                        var textfieldValue = (textfield.hasClass("multiform-datepicker") || textfield.prop("readonly")) ? textfield.attr("data-value") : textfield.val();
                        var chunks = textfieldValue.split('-');
                        switch (chunks.length) {
                            case 3:
                                value = Date.UTC(chunks[0], chunks[1], chunks[2]);
                                break;
                            case 2:
                                value = Date.UTC(chunks[0], chunks[1], 0);
                                break;
                            case 1:
                                value = Date.UTC(chunks[0], 0, 0);
                                break;
                            default:
                                value = Date.UTC(0, 0, 0);
                        }
                    } else {
                        var year = field.find(".multiform-year").length ? field.find(".multiform-year").val() : 0;
                        var month = field.find(".multiform-month").length ? field.find(".multiform-month").val() : 0;
                        var day = field.find(".multiform-day").length ? field.find(".multiform-day").val() : 0;
                        value = Date.UTC(year, month, day);
                    }
                    return value;
                },
                get_time: function (field, option_id) {
                    var value;
                    var textfield = field.find(".multiform-full-time");
                    if (textfield.length) {
                        value = this.getTimeFromString(textfield.val());
                    } else {
                        var hour = field.find(".multiform-hour").length ? field.find(".multiform-hour").val() : 0;
                        if (field.find(".time-formats").length) {
                            var ampm = field.find(".time-formats select").val();
                            if (hour < 12 && ampm == 'pm') {
                                hour = (parseInt(hour) + 12);
                            } else if (hour == '12' && ampm == 'am') {
                                hour = 0;
                            }
                        }
                        var minute = field.find(".multiform-minute").length ? field.find(".multiform-minute").val() : 0;
                        var second = field.find(".multiform-second").length ? field.find(".multiform-second").val() : 0;
                        value = Date.UTC(1970, 1, 1, hour, minute, second);
                    }
                    return value;
                },
                get_checkbox: function (field, option_id) {
                    var value;
                    value = field.find("#fields\\[field_" + field.data("field-id") + "_" + field.closest('.multiform-wrap').data("uid") + "\\]-" + option_id).prop("checked");
                    value = value ? 'checked' : value;
                    return value;
                },
                get_radio: function (field, option_id) {
                    return field.find("#wahtmlcontrol_fields_field_" + field.data("field-id") + "_" + field.closest('.multiform-wrap').data("uid") + " input:checked").data("id");
                },
                get_select: function (field, option_id) {
                    return field.find("#wahtmlcontrol_fields_field_" + field.data("field-id") + "_" + field.closest('.multiform-wrap').data("uid") + " option:selected").data("id");
                },
                get_rating: function (field, option_id) {
                    return field.find("input:checked").val();
                },
                get_table_grid: function (field, option_id) {
                    return field.find("input[data-statement='" + option_id + "']:checked").data("column");
                },
                get_number: function (field, option_id) {
                    return field.find("input").val();
                },
                get_formula: function (field, option_id) {
                    return $.multiformFrontend.condition['get_number'](field, option_id);
                },
                getTimeFromString: function (string) {
                    var chunks = string.split(':');
                    var value;
                    switch (chunks.length) {
                        case 3:
                            value = Date.UTC(1970, 1, 1, chunks[0], chunks[1], chunks[2]);
                            break;
                        case 2:
                            value = Date.UTC(1970, 1, 1, chunks[0], chunks[1], 0);
                            break;
                        case 1:
                            value = Date.UTC(1970, 1, 1, chunks[0], 0, 0);
                            break;
                        default:
                            value = Date.UTC(1970, 1, 1);
                    }
                    return value;
                },
                /* Input operators */
                operator_is: function (fieldValue, value) {
                    return fieldValue == value;
                },
                operator_is_not: function (fieldValue, value) {
                    return fieldValue !== value;
                },
                operator_contains: function (fieldValue, value) {
                    return fieldValue.toLowerCase().indexOf(value.toLowerCase()) > -1;
                },
                operator_begins_with: function (fieldValue, value) {
                    return fieldValue.toLowerCase().indexOf(value.toLowerCase()) === 0;
                },
                operator_ends_with: function (fieldValue, value) {
                    return fieldValue.toLowerCase().lastIndexOf(value.toLowerCase()) === fieldValue.length - value.length;
                },
                operator_doesnt_contain: function (fieldValue, value) {
                    return fieldValue.toLowerCase().indexOf(value.toLowerCase()) <= -1;
                },
                operator_is_blank: function (fieldValue, value) {
                    return fieldValue == '';
                },
                operator_is_not_blank: function (fieldValue, value) {
                    return fieldValue !== '';
                },
                /* Number operators */
                operator_is_equal_to: function (fieldValue, value) {
                    var epsilon = 0.000001;
                    return Math.abs(parseFloat(fieldValue) - parseFloat(value)) < epsilon;
                },
                operator_is_not_equal_to: function (fieldValue, value) {
                    var epsilon = 0.000001;
                    return Math.abs(parseFloat(fieldValue) - parseFloat(value)) >= epsilon;
                },
                operator_is_greater_than: function (fieldValue, value) {
                    return parseFloat(fieldValue) > parseFloat(value);
                },
                operator_is_less_than: function (fieldValue, value) {
                    return parseFloat(fieldValue) < parseFloat(value);
                },
                /* Date operators */
                operator_is_on: function (fieldValue, value) {
                    var chunks = value.split("-");
                    var date = Date.UTC(chunks[0], chunks[1], chunks[2]);
                    return fieldValue === date;
                },
                operator_is_before: function (fieldValue, value) {
                    var chunks = value.split("-");
                    var date = Date.UTC(chunks[0], chunks[1], chunks[2]);
                    return fieldValue < date;
                },
                operator_is_after: function (fieldValue, value) {
                    var chunks = value.split("-");
                    var date = Date.UTC(chunks[0], chunks[1], chunks[2]);
                    return fieldValue > date;
                },
                /* Time operators */
                operator_is_at: function (fieldValue, value) {
                    return fieldValue === this.getTimeFromString(value);
                },
                operator_is_time_before: function (fieldValue, value) {
                    return fieldValue < this.getTimeFromString(value);
                },
                operator_is_time_after: function (fieldValue, value) {
                    return fieldValue > this.getTimeFromString(value);
                }
            },
            validate: {
                regexp: function (evt, regexp) {
                    var theEvent = evt || window.event;
                    var key = theEvent.keyCode || theEvent.which;
                    key = String.fromCharCode(key);
                    var regex = regexp;
                    if (!regex.test(key) && evt.charCode !== 0) {
                        theEvent.returnValue = false;
                        if (theEvent.preventDefault) {
                            theEvent.preventDefault();
                        }
                    }
                },
                addError: function (field, error, onlyMessage) {
                    var errormsg = field.hasClass("multiform-gap-field") ? field.find(".errormsg") : field.closest(".multiform-gap-field").find(".errormsg");
                    var errorBlock = field.hasClass("multiform-gap-field") ? field.find(".multiform-gap-value") : field.closest(".multiform-gap-value");
                    if (error) {
                        if (!errormsg.length) {
                            errorBlock.append("<div class='errormsg'>" + error + "</div>");
                            errormsg = errorBlock.find(".errormsg");
                        } else {
                            errormsg.html(error).show();
                        }
                    }

                    !onlyMessage && field.addClass('multiform-error-field');

                    field.click(function () {
                        !onlyMessage && $(this).removeClass("multiform-error-field");
                        errormsg.hide();
                    });
                },
                fieldIsEmpty: function (field) {
                    this.addError(field, __('Field is required'));
                },
                requiredFields: {
                    input: function (field) {
                        field = field.find("input, textarea");
                        if ($.trim(field.val()) == '') {
                            $.multiformFrontend.validate.fieldIsEmpty(field);
                            return false;
                        }
                        return true;
                    },
                    textarea: function (field) {
                        return this.input(field);
                    },
                    email: function (field) {
                        return this.input(field);
                    },
                    phone: function (field) {
                        return this.input(field);
                    },
                    number: function (field) {
                        return this.input(field);
                    },
                    radio: function (field) {
                        var checked = field.find(":checked");
                        if (!checked.length) {
                            $.multiformFrontend.validate.fieldIsEmpty(field.find(".multiform-gap-value > div"));
                            return false;
                        }
                        if (checked.is(":radio")) {
                            var otherField = checked.closest(".multiform-gap-option").find(".multiform-other-field");
                            if (otherField.length && $.trim(otherField.val()) == '') {
                                $.multiformFrontend.validate.fieldIsEmpty(otherField);
                                return false;
                            }
                        }
                        return true;
                    },
                    checkbox: function (field) {
                        return this.radio(field);
                    },
                    table_grid: function (field) {
                        var empty = false;
                        $.each(field.find(".multiform-grid tbody tr"), function () {
                            if (!$(this).find("input:checked").length) {
                                $.multiformFrontend.validate.fieldIsEmpty($(this));
                                empty = true;
                            }
                        });
                        if (empty) {
                            return false;
                        }
                        return true;
                    },
                    date: function (field) {
                        var empty = false;
                        $.each(field.find("input, select"), function (i, v) {
                            if (($(v).is("input") && $.trim($(v).val()) == '') || ($(v).is("select") && !$(v).val())) {
                                empty = true;
                                $.multiformFrontend.validate.fieldIsEmpty($(this));
                            }
                        });
                        if (empty) {
                            return false;
                        }
                        return true;
                    },
                    time: function (field) {
                        return this.date(field);
                    },
                    rating: function (field) {
                        if (!field.find("input:checked").length) {
                            $.multiformFrontend.validate.fieldIsEmpty(field.find(".multiform-rating"));
                            return false;
                        }
                        return true;
                    },
                    file: function (field) {
                        if (!field.find(".multiform-temp-file").length) {
                            $.multiformFrontend.validate.fieldIsEmpty(field.find("input"));
                            return false;
                        }
                        return true;
                    },
                    select: function (field) {
                        if (field.find("option:first:selected").length) {
                            $.multiformFrontend.validate.fieldIsEmpty(field.find("select"));
                            return false;
                        }
                        return true;
                    },
                    execute: function (form) {
                        var that = this;
                        var required = false;
                        $.each(form.find(".s-required").not(".multiform-hide"), function (i, elem) {
                            elem = $(elem);
                            if (elem.closest(".multiform-hide").length) {
                                return true;
                            }
                            var type = elem.data("type");
                            if (typeof that[type] == 'function') {
                                var result = that[type](elem);
                                if (!result) {
                                    required = true;
                                }
                            }
                        });
                        return required;
                    }
                },
                /* Проверка по регулярным выражениям (маскам) */
                masks: function (form) {
                    var masks = form.find(".f-mask");
                    if (masks.length) {
                        var error = false;
                        $.each(masks, function (i, v) {
                            var field = $(v);
                            if (field.closest(".multiform-hide").length) {
                                return;
                            }
                            if (field.val() !== '') {
                                var pattern = field.data("field-mask");
                                if (field.closest(".multiform-gap-field").data("type") == 'phone') {
                                    pattern = "^" + pattern.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&").replace(/\s/g, '\s').replace(/[0]/g, '\\d') + "$";
                                }
                                try {
                                    var regexp = new RegExp(pattern, field.data("field-mask-casein"));
                                    if (!regexp.test(field.val())) {
                                        error = true;
                                        $.multiformFrontend.validate.addError(field, field.data("field-mask-error"));
                                    }
                                } catch (e) {
                                    error = true;
                                    $.multiformFrontend.validate.addError(field, e.message);
                                }
                            }
                        });
                        if (error) {
                            return false;
                        }
                    }
                    return true;
                },
                /* Оставшиеся проверки */
                others: {
                    /* Проверка числовых полей */
                    number: function (form) {
                        var numbers = form.find(".multiform-gap-field.type-number").not(".multiform-hide");
                        if (numbers.length) {
                            var error = false;
                            $.each(numbers, function (i, v) {
                                if ($(v).closest('.multiform-hide').length) {
                                    return true;
                                }
                                var field = $(v).find("input");
                                var step = typeof field.attr("step") !== 'undefined' ? field.attr("step") : (typeof field.attr("data-step") !== 'undefined' ? field.attr("data-step") : '');
                                var max = typeof field.attr("max") !== 'undefined' ? field.attr("max") : (typeof field.attr("data-max") !== 'undefined' ? field.attr("data-max") : '');
                                var min = typeof field.attr("min") !== 'undefined' ? field.attr("min") : (typeof field.attr("data-min") !== 'undefined' ? field.attr("data-min") : '');
                                var onlyInt = field.attr("integer");
                                var value = parseInt(onlyInt) ? parseInt(field.val()) : parseFloat(field.val());

                                if (step !== '' || max !== '' || min !== '') {
                                    if (min !== '' && value < parseFloat(min)) {
                                        error = true;
                                        $.multiformFrontend.validate.addError(field, __('Value must be more than ') + min);
                                        return false;
                                    }
                                    if (max !== '' && value > parseFloat(max)) {
                                        error = true;
                                        $.multiformFrontend.validate.addError(field, __('Value must be less than ') + max);
                                        return false;
                                    }
                                    var decimals = $.multiformFrontend.countDecimals(step);
                                    if (step !== '' && parseFloat(step) !== 0 && (value * Math.pow(10, decimals)) % (parseFloat(step) * Math.pow(10, decimals) !== 0)) {
                                        step = parseFloat(step);
                                        error = true;
                                        $.multiformFrontend.validate.addError(field, __('Value must be a multiple of ') + step + ". " + __("For example") + ", " + step + ", " + (step * 2).toFixed(decimals) + ", " + (step * 3).toFixed(decimals));
                                        return false;
                                    }
                                }
                            });
                            if (error) {
                                return false;
                            }
                        }
                        return true;
                    },
                    /* Проверка checkbox */
                    checkbox: function (form) {
                        var checkboxes = form.find(".multiform-gap-field.type-checkbox").not(".multiform-hide");
                        if (checkboxes.length) {
                            var error = false;
                            $.each(checkboxes, function (i, field) {
                                field = $(field);
                                if (field.closest('.multiform-hide').length) {
                                    return true;
                                }
                                var count = field.find(":checked").length;
                                var max = typeof field.data("max") !== 'undefined' && field.data("max") !== '' ? parseInt(field.data("max")) : 0;
                                var min = typeof field.data("min") !== 'undefined' && field.data("min") !== '' ? parseInt(field.data("min")) : 0;

                                if (min && count < min) {
                                    error = true;
                                    $.multiformFrontend.validate.addError(field, __('You have to select at least 1 checkbox', 'You have to select at least {n} checkboxes', min, { n: min }));
                                    return false;
                                }
                                if (max && count > max) {
                                    error = true;
                                    $.multiformFrontend.validate.addError(field, __('Checkboxes cannot exceed 1 selected value', 'Checkboxes cannot exceed {n} selected values', max, { n: max }));
                                    return false;
                                }
                            });
                            if (error) {
                                return false;
                            }
                        }
                        return true;
                    }
                }
            },
            countDecimals: function (value) {
                if (Math.floor(value) !== value) {
                    var parts = value.toString().split(".");
                    return (typeof parts[1] !== 'undefined' ? parts[1].length : 0) || 0;
                }
                return 0;
            },
            addEvent: function (obj, type, fn) {
                obj.attachEvent ? (obj["e" + type + fn] = fn, obj[type + fn] = function () {
                    obj["e" + type + fn](window.event);
                }, obj.attachEvent("on" + type, obj[type + fn])) : obj.addEventListener(type, fn, !1);
            },
            postMessageAction: function (win, data) {
                if (window.postMessage && $.multiformFrontend.eventOrigin) {
                    win.postMessage(data, $.multiformFrontend.eventOrigin);
                }
            },
            changeFrameSize: function (form) {
                if (typeof $.multiformFrontend.eventSource[form.data("id")] !== 'undefined') {
                    $.multiformFrontend.postMessageAction($.multiformFrontend.eventSource[form.data("id")], (form[0].scrollHeight + 100) + '|' + form.data("id"));
                }
            }
        };
    })(jQuery);
}
(function (define2) {
    return define2(function () {
        var pluralRe = /^Plural-Forms:\s*nplurals\s*=\s*(\d+);\s*plural\s*=\s*([^a-zA-Z0-9\$]*([a-zA-Z0-9\$]+).+)$/im;

        function format(r, e) {
            return r.replace(/(^|.)?\{([^\}]+)\}/g, function (r, t, n) {
                return "\\" === t ? "{" + n + "}" : (t = t || "") + e[n.split("#")[0].trim()]
            })
        }

        function parsePlural(header) {
            var rv = {
                pluralNum: 2, isPlural: function (r) {
                    return 1 !== r
                }
            };
            if (!header) return rv;
            var match = header.match(pluralRe);
            if (!match) return rv;
            if (rv.pluralNum = parseInt(match[1], 10), 1 == rv.pluralNum) return rv.isPlural = function () {
                return 0
            }, rv;
            var expr = match[2], varName = match[3], code = "(function (" + varName + ") { return " + expr + " })";
            try {
                rv.isPlural = eval(code)
            } catch (r) {
                console.log("Could not evaluate: " + code)
            }
            return rv
        }

        function gettrans(r, e, t, n, u) {
            if (!r || !r[t]) return void 0 !== u && e(u) ? n : t;
            var a = r[t];
            if (void 0 === n && void 0 === u) return "string" == typeof a ? a : a[0];
            if (void 0 !== u && "string" == typeof a) throw format('Plural number ({num}) provided for "{msg1}", but only singular translation exists: {trans}', {
                num: u,
                msg1: t,
                trans: a
            });
            return a[+e(u)]
        }

        return function (r) {
            function e(r, t, n, u) {
                "object" == typeof t && void 0 === n && void 0 === u && (u = t, t = void 0);
                var a = gettrans(e.messages, e.plural, r, t, n);
                return u ? format(a, u) : a
            }

            return e.format = format, e.setMessages = function (r) {
                e.messages = r;
                var t = parsePlural(r && r[""]);
                e.pluralNum = t.pluralNum, e.plural = t.isPlural
            }, e.setMessages(r), e
        }
    })
})("undefined" != typeof define2 ? define2 : function (r) {
    return "undefined" != typeof module && "undefined" != typeof exports ? module.exports = r() : window.puttext = r()
});