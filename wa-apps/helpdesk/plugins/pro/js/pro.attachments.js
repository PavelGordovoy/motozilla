var helpdeskProAttachments = (function ($) {
    helpdeskProAttachments = function (options) {
        var that = this;

        // DOM
        that.$wrapper = options.$wrapper;
        that.$form = options.$form;

        // DYNAMIC VARS
        that.is_locked = false;

        // INIT
        that.initClass();
    };

    helpdeskProAttachments.prototype.initClass = function () {
        var that = this;

        that.bindEvents();
    };

    helpdeskProAttachments.prototype.bindEvents = function () {
        var that = this;

        /* Удаление вложения */
        that.$wrapper.on('click', '.js-delete-attachment', function () {
            var btn = $(this);
            var ids = [];
            if (btn.data('selected')) {
                ids = $.makeArray($('.attachment-item :checkbox:checked').map(function () {
                    return $(this).val();
                }));
            } else {
                ids.push(btn.data('id'));
            }
            if (!btn.find('.loading').length && ids.length && confirm($__('Are you sure you want to delete attachment?'))) {
                btn.find('i').removeClass('icon10 no').addClass('icon16 loading');
                $.post('?plugin=pro&action=removeAttachment', { params: ids }, function (response) {
                    if (response.status == 'ok') {
                        if (btn.data('selected')) {
                            location.reload();
                        } else {
                            btn.closest('tr').fadeOut(function () {
                                $(this).remove();
                            });
                        }
                    }
                }).always(function () {
                    if (!btn.data('selected')) {
                        btn.find('i').removeClass('icon16 loading').addClass('icon10 no');
                    }
                });
            }
            return false;
        });

        /* Индексирование файлов */
        that.$wrapper.on('click', '.js-index-files', function () {
            that.indexFiles();
            return false;
        });

        /* Выделение вложений */
        that.$wrapper.on('change', '.f-checker', function () {
            var itemCheckboxes = that.$wrapper.find('.attachment-item :checkbox');
            itemCheckboxes.prop('checked', $(this).prop('checked'));
            that.$wrapper.find('.indicator').text(itemCheckboxes.filter(':checked').length);
        });
        that.$wrapper.on('change', '.attachment-item :checkbox', function () {
            that.$wrapper.find('.indicator').text(that.$wrapper.find('.attachment-item :checkbox:checked').length);
        });

        /* Количество вложений на странице */
        that.$wrapper.on('change', '.hp-per-page', function () {
            var select = $(this);
            that.updatePage({ per_page: select.val() });
        });

        /* Постраничная навигация */
        that.$wrapper.on('click', '.pagination a', function () {
            that.updatePage({ page: $(this).data('page') });
            return false;
        });

        /* Сортировка */
        that.$wrapper.on('click', '.th-sort', function () {
            that.updatePage({ sort: $(this).data('field'), direction: $(this).data('direction') || 'DESC' });
            return false;
        });
    };

    /* Индексирование файлов */
    helpdeskProAttachments.prototype.updatePage = function (params) {
        var that = this;
        $('#wa').addClass('hp-loading');
        $.post('?plugin=pro&module=attachments', params, function (response) {
            that.$wrapper.replaceWith($(response).find('#hp-attachments-wrapper'));
            new helpdeskProAttachments({
                $wrapper: $("#hp-attachments-wrapper"),
                $form: $("#hp-attachments-form")
            });
        }, 'html').always(function () {
            $('#wa').removeClass('hp-loading');
        });
    };

    /* Индексирование файлов */
    helpdeskProAttachments.prototype.indexFiles = function () {
        var that = this;

        new helpdeskProDialog({
            url: "?plugin=pro&module=dialog&action=indexFiles",
            onOpen: function ($wrapper, dialog) {
                LongAction.setParams("?plugin=pro&module=attachments&action=index");
                LongAction.start($wrapper, function () {
                    this.ELEMENTS.BUTTONS.show();
                });
            },
            closeOnlyByLink: true,
            onClose: function () {
                LongAction.stopAll();
            }
        });
    };

    return helpdeskProAttachments;
})(jQuery);

var LongAction = (function ($) {
    return {
        pull: [],
        stop: false,
        url: null,
        onLoad: null,
        postParams: {},
        ELEMENT_CLASSES: {
            BUTTONS: '.w-dialog-footer',
            WRAP: '.longaction-progressbar',
            REPORT: '.longaction-progressbar__report',
            LOADER: '.longaction-progressbar__loader',
            PROGRESSBAR: '.longaction-progressbar__progressbar',
            ERRORFLD: '.errormsg',
            SUBMIT_BTN: 'form :submit',
            PARAGRAPH: 'p',
            PROGRESSBAR_INNER: '.longaction-progressbar__progressbar-inner',
            PROGRESS: '.longaction-progressbar__progress',
        },
        ELEMENTS: {},
        BLOCKS: {
            WARNING: '<i class="icon16 exclamation"></i>'
        },
        setParams: function (url, postParams) {
            this.url = url;
            this.postParams = postParams;
        },
        resetVars: function () {
            this.pull = [];
            this.stop = false;
        },
        stopAll: function () {
            var that = this;

            var timer_id = that.pull.pop();
            while (timer_id) {
                clearTimeout(timer_id);
                timer_id = that.pull.pop();
            }
            that.stop = true;
        },
        initElements: function (dialog) {
            var that = this;

            var notFound = [];
            $.each(that.ELEMENT_CLASSES, function (name, elClass) {
                that.ELEMENTS[name] = dialog.find(elClass);
                if (!that.ELEMENTS[name].length) {
                    notFound.push(name);
                }
            });
            if (notFound.length && console) {
                console.warn('LongAction. The following elements not found: ' + notFound.join(', '));
            }
        },
        addWarning: function (warning) {
            return this.BLOCKS.WARNING + (warning ? '<p>' + warning + '</p>' : '');
        },
        /* Всплывающее окно (waDialog) с запросом подтверждения импорта/экспорта */
        openDialog: function (buttonName, title, content, height) {
            var that = this;

            that.resetVars();
            content = content || '';
            var contentBlock = content +
                '<div class="longaction-progressbar" style="display:none; margin-top: 20px;">' +
                '<div class="longaction-progressbar__progressbar progressbar blue float-left" style="display: none; width: 90%;">' +
                '<div class="longaction-progressbar__progressbar-outer progressbar-outer">' +
                '<div class="longaction-progressbar__progressbar-inner progressbar-inner" style="width: 0;"></div>' +
                '</div>' +
                '</div>' +
                '<img style="float:left; margin-top:8px;" class="longaction-progressbar__loader" src="' + Routing.WA_URL + 'wa-content/img/loading32.gif" />' +
                '<div class="clear"></div>' +
                '<span class="longaction-progressbar__progress">0.000%</span> ' +
                '<em class="hint">' + $__("Please don't close your browser window until process is over") + '</em>' +
                '<br style="clear:left" />' +
                '<em class="margin-block top errormsg" style="display: none;">' +
                '<span></span>' +
                '</em>' +
                '</div>' +
                '<div class="longaction-progressbar__report"></div>';
            var dialogParams = {
                height: height || '200px',
                width: '550px',
                title: '<h1 class="align-center">' + title + '</h1>',
                content: contentBlock,
                buttons: '<div class="align-center"><input type="submit" value="' + buttonName + '" class="button green"> ' + $__('or') + ' <a class="button red cancel" href="javascript:void(0)">' + $__('cancel') + '</a></div>',
                disableButtonsOnSubmit: false,
                onSubmit: function (d) {
                    that.start(d);
                    return false;
                },
                onClose: function () {
                    var d = $(this);
                    var timer_id = that.pull.pop();
                    while (timer_id) {
                        clearTimeout(timer_id);
                        timer_id = that.pull.pop();
                    }
                    d.find('.s-longaction-progressbar').hide();
                    d.find(".s-longaction-report").hide();
                    that.stop = true;
                    $("#longaction-dialog-wrap").remove();
                }
            };
            if (this.onLoad && typeof this.onLoad === 'function') {
                dialogParams['onLoad'] = this.onLoad;
            }
            $("body").append("<div id='longaction-dialog-wrap'></div>");
            var dialog = $("#longaction-dialog").clone();
            dialog.attr("id", "longaction-dialog-clone").appendTo("#longaction-dialog-wrap").waDialog(dialogParams);
            this.ELEMENT_CLASSES.BUTTONS = '.dialog-buttons';
        },
        start: function (dialog, successCallback) {
            var that = this;

            that.initElements(dialog);
            that.resetVars();

            that.ELEMENTS.BUTTONS.hide();
            var showError = function (error) {
                that.ELEMENTS.ERRORFLD.show().text(error);
                that.ELEMENTS.BUTTONS.show();
                that.ELEMENTS.LOADER.remove();
                that.ELEMENTS.PROGRESSBAR.hide();
            };
            that.ELEMENTS.REPORT.html('').hide();
            that.ELEMENTS.SUBMIT_BTN.prop("disabled", true);
            that.ELEMENTS.PARAGRAPH.hide();
            that.ELEMENTS.WRAP.show();
            that.ELEMENTS.PROGRESSBAR_INNER.css('width', '0%');
            that.ELEMENTS.PROGRESS.text('0.000%');
            that.ELEMENTS.PROGRESSBAR.show();
            var processId;
            var cleanup = function () {
                if (!that.stop) {
                    that.stop = true;
                    $.post(that.url, { processId: processId, cleanup: 1 }, function (r) {
                        that.ELEMENTS.WRAP.hide();
                        if (r.report) {
                            that.ELEMENTS.REPORT.append(r.report).show();
                        }
                        if (r.data.finish && successCallback) {
                            successCallback.call(that);
                        }
                    }, 'json');
                }
            };
            var step = function (delay) {
                delay = delay || 2000;
                if (that.stop) {
                    return false;
                }
                var timer_id = setTimeout(function () {
                    $.post(that.url, { processId: processId }, function (r) {
                        if (!r) {
                            step(3000);
                        } else {
                            if (r && r.error_message) {
                                showError(r.error_message);
                                return false;
                            }
                            if (r && r.ready) {
                                that.ELEMENTS.PROGRESSBAR_INNER.css({
                                    width: '100%'
                                });
                                that.ELEMENTS.PROGRESS.text('100%');
                                cleanup();
                            } else if (r && r.error) {
                                showError(r.error);
                                return false;
                            } else {
                                if (r && r.progress) {
                                    var progress = parseFloat(r.progress.replace(/,/, '.'));
                                    that.ELEMENTS.PROGRESSBAR_INNER.animate({
                                        'width': progress + '%'
                                    });
                                    that.ELEMENTS.PROGRESS.text(r.progress);
                                }
                                if (r && r.warning) {
                                    that.ELEMENTS.PROGRESS.append(that.addWarning(r.warning));
                                }
                                step();
                            }
                        }
                    }, 'json').error(function () {
                        step(3000);
                    });
                }, delay);
                that.pull.push(timer_id);
            };
            $.post(that.url, that.postParams, function (r) {
                if (r && r.processId) {
                    if (r && r.error_message) {
                        showError(r.error_message);
                        return false;
                    }
                    processId = r.processId;
                    step(1000); // invoke Runner
                    step(); // invoke Messenger
                } else if (r && r.error) {
                    showError(r.error);
                    return false;
                } else {
                    showError($__('Server error'));
                    return false;
                }
            }, "json").error(function () {
                showError($__('Server error'));
                return false;
            });
        }
    }
})(jQuery);
