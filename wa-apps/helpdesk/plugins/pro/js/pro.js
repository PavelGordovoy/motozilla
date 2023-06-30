(function ($) {
    $.helpdesk_pro = {
        ignoreAjaxErrors: 0,
        createNewRequest: 0,
        initLocale: function (localeStrings) {
            window.$__ = puttext(localeStrings);
        },
        init: function (options) {
            var self = this;

            self.ignoreAjaxErrors = options.ignoreAjaxErrors || 0;
            self.createNewRequest = options.createNewRequest || 0;

            /* Если необходимо игнорировать AJAX ошибки */
            if (self.ignoreAjaxErrors) {
                $(window).on('beforeunload', function () {
                    setTimeout(function () {
                        $.wa.helpdesk_controller.ignore_ajax_errors = true;
                    }, 1100);
                });
            }

            $("#c-core-content").on('helpdesk_after_load', function () {
                /* Закрываем все всплывающие окна */
                $(".w-dialog-wrapper").each(function () {
                    var d = $(this);
                    d.data('proDialog') !== undefined && d.data('proDialog').close();
                });

                /* Работаем со страницами создания новых запросов */
                var hash = $.wa.helpdesk_controller.currentHash;
                if (hash.indexOf('#/request/add') !== -1 || hash.indexOf('#/request/new') !== -1) {
                    /* Вешаем событие на отправку формы, чтобы удостовериться, что запрос создается для email адреса,
                     * не являющегося спамом 
                     * */
                    $('.backend-new-request-form').find('input[type="submit"]').click(function () {
                        var html = $(document),
                            btn = $(this),
                            email = '';
                        /* Определяем какая форма используется: стандартная или одна из созданных */

                        /* Стандартная форма */
                        if (!html.find(".backend-new-request-form").is('form')) {
                            var form = html.find(".backend-new-request-form").closest('form');
                            /* Если заполнено поле с ID клиента, значит контакт был выбран из существующих
                             * Вытаскиваем email из текстового поля
                             **/
                            if (form.find('input[name="contact_id"]').val()) {
                                email = form.find(".contacts-data .profile .details a[href^='mailto:']").text();
                            }
                            /* Если создается новый контакт, используем в качестве email поле из формы */
                            else {
                                email = form.find('input[name="client[email]"]').val();
                            }
                        }
                        /* Не стандартная форма */
                        else {
                            var form = html.find(".backend-new-request-form");
                            var emailFld = form.find('input[name="fldc_data[email]"]');
                            if (emailFld.length) {
                                email = form.find('input[name="fldc_data[email]"]').val();
                            } else {
                                email = form.find(".profile .details a[href^='mailto:']").text();
                            }
                        }
                        if (email && form.length) {
                            btn.prop('disabled', true);
                            form.find('.loading').show();
                            $.post("?plugin=pro&action=isSpam", { email: email }, function (response) {
                                btn.prop('disabled', false);
                                form.find('.loading').hide();
                                if (response && response.status !== undefined && response.status == 'ok') {
                                    new helpdeskProDialog({
                                        title: $__('Spam email'),
                                        class: 'red-header',
                                        content: $__("Email is in spam. Do you really want to create request?"),
                                        buttons: '<input type="submit" class="button green t-button" value="' + $__('Continue') + '"> ' + $__('or') + ' <a href="javascript:void(0)" class="js-close-dialog">' + $__('cancel') + '</a>',
                                        onSubmit: function () {
                                            form.submit();
                                            this.close();
                                        }
                                    });
                                } else {
                                    form.submit();
                                }
                            });
                            return false;
                        }
                    });
                }
            });

            /* Работа со списком запросов */
            $(document).on('helpdesk_list_result', function (e) {
                if (e.list_result !== undefined && e.list_result.requests.length) {
                    var ids = [];

                    $.each(e.list_result.requests, function (i, v) {
                        ids.push(v.id);
                    });

                    /* Проверка списка запросов на спам */
                    $.post("?plugin=pro&action=isRequestEmailSpam", { requests: ids }, function (response) {
                        if (response.status == 'ok' && response.data) {
                            if ($(".requests-table").length) {
                                for (var i in response.data) {
                                    $(".requests-table tr[rel='" + response.data[i] + "']").addClass("hp-spam");
                                }
                            }
                        }
                    });

                    setTimeout(function () {
                        $.each(e.list_result.requests, function (i, v) {
                            /* Вывод тегов для запросов */
                            if (void 0 !== e.list_result.request_tags && void 0 !== e.list_result.request_tags[v.id] && void 0 !== e.list_result.tags) {
                                var html = "<div class='hp-tags'>";
                                $.each(e.list_result.request_tags[v.id], function (i2, tagId) {
                                    if (void 0 !== e.list_result.tags[tagId]) {
                                        html += "<div class='hp-tag'><i class='icon16 tags'></i> " + e.list_result.tags[tagId] + "</div>";
                                    }
                                });
                                html += "</div>";
                                $(".requests-table tr[rel='" + v.id + "'] .description-col").append(html);
                            }

                            /* Вывод дополнительных полей */
                            if (void 0 !== e.list_result.additional_fields && void 0 !== e.list_result.additional_fields[v.id]) {
                                $.each(e.list_result.additional_fields[v.id], function (i, af) {
                                    $(".requests-table tr[rel='" + v.id + "'] .description-col").append(af);
                                });
                            }
                        });
                    }, 0);
                }
            });
        },
        /* Инициализируем выбор цвета */
        initColorpicker: function () {
            $(".color-icon").not('.colorpicker-inited').each(function () {
                var btn = $(this);
                btn.css('background-color', '#' + btn.attr("data-color")).ColorPicker({
                    color: btn.attr("data-color"),
                    onChange: function (hsb, hex, rgb) {
                        btn.css('backgroundColor', '#' + hex);
                        btn.attr("data-color", hex).next().val(hex);
                        btn.hasClass("print-hex") && btn.val(hex);
                        btn.trigger('input').next(":hidden").trigger('input');
                    },
                    onShow: function (colpkr) {
                        $(colpkr).css({ left: 0, bottom: '110%', top: 'inherit', position: 'absolute' });
                        btn.ColorPickerSetColor(btn.attr("data-color") !== undefined ? btn.attr("data-color") : btn.val());
                    }
                }).on('keyup', function () {
                    var hex = btn.ColorPickerFixHex(this.value);
                    btn.ColorPickerSetColor(hex).css('backgroundColor', '#' + hex).attr('data-color', hex).trigger('input');
                });
                setTimeout(function () {
                    if (btn.data('colorpickerId') !== undefined) {
                        btn.addClass('colorpicker-inited');
                        $("#" + btn.data('colorpickerId')).insertBefore(btn);
                    }
                }, 1);
            });
        },
        initMobile: function () {
            var that = this;
            /* DOM */
            $(document).find('head').append('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
            $("#wa-header").hide();
            $("#wa-app").prepend('<div id="top-fixed"><ul class="menu-h"><li class="first"><a href="javascript:void(0)" class="hp-open-sidebar">&#187;</a></li><li class="hp-apps"><a href="' + $.wa.helpdesk_controller.backend_url + '" title="' + $__('Applications') + '"><i class="icon16 apps"></i></a></li></ul></div>');
            $("#wa-app [data-collapsible-id='sidebar']").toggleClass().addClass('h-sidebar').attr('id', 'mobile-sidebar-content').wrap('<div class="block" id="mobile-sidebar"><div class="sidebar"></div></div>');
            $("#wa-app > .content").removeClass("left200px");
            $("#c-core-content").addClass("mobile-content");

            /* Bind events */

            /* Сайдбар */
            $(document).on('click', ".hp-open-sidebar", function () {
                var btn = $(this);
                var sidebar = $("#mobile-sidebar");
                if (btn.hasClass("opened")) {
                    sidebar.animate({ left: '-100%' }, 500, function () {
                        btn.removeClass("opened").html('&#187;');
                    });
                } else {
                    sidebar.animate({ left: 0 }, 500, function () {
                        btn.addClass("opened").html('&#171;');
                    });
                }
            });
            $(document).on('click', "#mobile-sidebar-content a:not(.heading), #s-core", function () {
                var elem = $(this);
                if (elem.is('a') || (elem.attr('id') === 's-core' && $(".hp-open-sidebar").hasClass('opened'))) {
                    $(".hp-open-sidebar").click();
                }
            });

            /* Открытие запроса */
            $(document).on('click', '.requests-table td:not(.checkboxes-col)', function () {
                $.wa.helpdesk_controller.setHash($(this).closest('tr').find('.request-summary').attr('href'));
            });

            $(document).on('helpdesk_list_result', function (e) {
                that.mobileRedispatch(e);
                $.wa.helpdesk_controller.hideLoading();
                $('#wa').removeClass('hp-nomobile');
                /* Создаем панель инструментов */
                setTimeout(function () {
                    if ($(".header-above-requests-table").length && !$(".js-sorting-menu").length) {
                        var order = $.wa.helpdesk_controller.currentGrid.getSettings().order;
                        $(".header-above-requests-table").append(
                            '<div class="js-sorting-menu">\n' +
                            '                ' + $__('Sorting:') + '\n' +
                            '                <select class="js-sort-menu">\n' +
                            '                        <option value="!id"' + (order === '!id' ? ' selected' : '') + '>' + $__('newest on top') + '</option>\n' +
                            '                        <option value="id"' + (order === 'id' ? ' selected' : '') + '>' + $__('oldest on top') + '</option>\n' +
                            '                        <option value="!updated"' + (order === '!updated' ? ' selected' : '') + '>' + $__('recently updated on top') + '</option>\n' +
                            '                </select>\n' +
                            '            </div>    ');
                        // $(".header-above-requests-table").wrapInner('<div class="hp-js-toolbar"></div>').append("<a href='javascript:void(0)' class='inline-link align-center' style='width:100%;display:block;padding:5px 0' onclick='$(this).prev().slideToggle()'><b><i>" + $__('Toolbar') + "</i></b></a>");
                    }
                }, 100);
            });

            $(window).on('wa_before_dispatched', function (e) {
                that.mobileRedispatch(e);
            });

            /* Сортировка записей */
            $(document).on('change', '.js-sort-menu', function () {
                var value = $(this).val();
                var p = Math.ceil(parseInt($.wa.helpdesk_controller.currentGrid.getSettings().offset) / parseInt($.wa.helpdesk_controller.currentGrid.getSettings().limit)) + 1;
                $.wa.setHash($.wa.helpdesk_controller.currentGrid.getPagingHash(p, undefined, value));
            });

            $("#c-core-content").on('helpdesk_after_load', function () {
                /* Работаем с потоками */
                var hash = $.wa.helpdesk_controller.currentHash;
                if (hash.indexOf('#/settings/workflow') !== -1) {
                    $('#wa').addClass('hp-nomobile');
                } else {
                    $('#wa').removeClass('hp-nomobile');
                }
            });

            /* Изменение отображение для мобильной версии */
            $.storage.set('helpdesk/grid/view', 'list');

            $.fn.fixedBlock = function (options) {
                return $("<div></div>");
            };

            $.wa.helpdesk_controller.showLoading = function () {
                if (!$("#wa").hasClass('hp-loading')) {
                    $("#wa").addClass('hp-loading');
                }
            };
            $.wa.helpdesk_controller.hideLoading = function () {
                $("#wa").removeClass('hp-loading');
            };
        },
        mobileRedispatch: function (e) {
            var currentHash = $.wa.helpdesk_controller.getHash();
            var hash = $.wa.helpdesk_controller.currentHash || currentHash;
            if (hash) {
                if (hash && (hash.indexOf('split') !== -1 || hash.indexOf('table') !== -1)) {
                    e.preventDefault();
                    var mobileHash = currentHash.replace('id/split', 'id/list').replace('updated/split', 'id/list').replace('id/table', 'id/list').replace('updated/table', 'id/list');
                    var parts = mobileHash.split('id/list/');
                    /* Перенаправляем на страницу запроса */
                    if (void 0 !== parts[1]) {
                        mobileHash = $.wa.helpdesk_controller.options.backend_url + 'helpdesk#/request/' + parts[1];
                    }
                    $.wa.helpdesk_controller.setHash(mobileHash);
                    $.wa.helpdesk_controller.redispatch();
                }
            }
        }
    };

    /* Plugin settings */
    $.wa.helpdesk_controller.proPluginSettingsAction = function (p) {
        this.loadHTML('?plugin=pro&module=settings');
    };

    /* Plugin contact constructor */
    $.wa.helpdesk_controller.proPluginConstructorAction = function (p) {
        this.loadHTML('?plugin=pro&module=constructor');
    };

    /* Plugin additional constructor */
    $.wa.helpdesk_controller.proPluginAdditionalConstructorAction = function (p) {
        this.loadHTML('?plugin=pro&module=constructorAdditional');
    };

    /* Plugin attachments */
    $.wa.helpdesk_controller.proPluginAttachmentsAction = function (p) {
        this.loadHTML('?plugin=pro&module=attachments');
    };

    /* Plugin spam */
    $.wa.helpdesk_controller.proPluginSpamAction = function (p) {
        this.loadHTML('?plugin=pro&module=spam');
    };

})(jQuery);

var helpdeskProDialog = (function ($) {

    helpdeskProDialog = function (options) {
        var that = this;

        // DOM
        that.$wrapper = (options["html"] ? $(options["html"]) : '');
        that.url = (options["url"] || '');
        if (that.url && !that.$wrapper) {
            that.$wrapper = $('<div class="w-dialog-wrapper is-full-screen"><div class="w-dialog-background"></div><div class="w-dialog-block gray-header compact-header"><i class="loader"></i></div></div>');
        }
        if (!that.url && options.content && !that.$wrapper) {
            options.title = options.title || $('Your title');
            that.$wrapper = $('<div class="w-dialog-wrapper is-full-screen">' +
                '<div class="w-dialog-background"></div>' +
                '<div class="w-dialog-block gray-header compact-header">' +
                '<form action="" method="post">' +
                '<header class="w-dialog-header">' +
                '<h1>' + options.title + '</h1>' + '</header>' +
                '<div class="w-dialog-content margin-block">' + options.content + '</div>' +
                '<footer class="w-dialog-footer">' +
                '<div class="margin-block errormsg"></div>' +
                '<div class="t-actions">' +
                '<div class="t-layout">' +
                '<div class="t-column left">' +
                (options.buttons ? options.buttons : '<input type="submit" class="button green t-button" value="' + $__('Save') + '"> ' + $__('or') + ' <a href="javascript:void(0)" class="js-close-dialog">' + $__('cancel') + '</a>') +
                '</div>' +
                '</div>' +
                '</div>' +
                '</footer>' +
                '</form></div></div>');
        }
        that.$block = (options["block"] ? $(options["block"]) : false);
        that.$form = that.$wrapper.find("form");
        that.$submitBtn = that.$form.find("input[type='submit']");
        that.is_full_screen = (that.$wrapper.hasClass("is-full-screen"));
        if (that.is_full_screen) {
            that.$block = that.$wrapper.find(".w-dialog-block");
        }
        if (options.class) {
            that.$block.addClass(options.class);
        }

        // VARS
        that.position = (options["position"] || false);
        that.dialogClass = 't-' + Date.now();
        that.saveUrl = (options["saveUrl"] || '');
        that.options = options;
        that.rand;

        // DYNAMIC VARS
        that.is_closed = false;
        that.is_locked = false;
        that.xhr = false;

        that.userPosition = (options["setPosition"] || false);

        // HELPERS
        that.onBgClick = (options["onBgClick"] || false);
        that.onBlockClick = (options["onBlockClick"] || false);
        that.onOpen = (options["onOpen"] || function () {
        });
        that.onClose = (options["onClose"] || function () {
        });
        that.onRefresh = (options["onRefresh"] || false);
        that.onResize = (options["onResize"] || false);
        that.onSubmit = (options["onSubmit"] || null);
        that.onSuccess = (options["onSuccess"] || null);
        that.onSuccessCallback = (options["onSuccessCallback"] || null);

        // INIT
        that.initClass();
    };

    helpdeskProDialog.prototype.initClass = function () {
        var that = this;

        that.$wrapper.data('proDialog', that).addClass(that.dialogClass).find('.w-dialog-background').addClass(that.dialogClass);

        that.show();
        if (that.url) {
            that.loadDataFromUrl();
        } else {
            that.bindEvents();
        }
    };

    helpdeskProDialog.prototype.loadDataFromUrl = function () {
        var that = this,
            href = that.url;
        that.is_locked = true;

        if (that.xhr) {
            that.xhr.abort();
            that.xhr = false;
        }

        that.xhr = $.get(href, function (html) {
            var params = {
                html: html,
                onBgClick: function (e, d) {
                    d.close();
                },
                url: ''
            };
            new helpdeskProDialog($.extend(that.options, params));
            that.close();

            that.is_locked = false;

        });
    };

    helpdeskProDialog.prototype.bindEvents = function () {
        var that = this,
            $document = $(document),
            $block = (that.$block) ? that.$block : that.$wrapper;

        that.$form.on("submit", function (event) {
            event.preventDefault();
            if (!that.is_locked) {
                if (typeof that.onSubmit == 'function') {
                    that.onSubmit(that.$form, that);
                } else {
                    that.save();
                }
            }
            return false;
        });

        $document.on('focus', 'input.error, textarea.error', function () {
            $(this).removeClass("error");
        });

        // Delay binding close events so that dialog does not close immidiately
        // from the same click that opened it.
        setTimeout(function () {

            $document.on("click", close);
            $document.on("wa_before_load", close);
            that.$wrapper.on("close", close);

            // Click on background, default nothing
            if (that.is_full_screen) {
                that.$wrapper.on("click", ".w-dialog-background." + that.dialogClass, function (event) {
                    if (!that.onBgClick) {
                        event.stopPropagation();
                    } else {
                        that.onBgClick(event, that);
                    }
                });
            }

            $block.on("click", function (event) {
                if (!that.onBlockClick) {
                    event.stopPropagation();
                } else {
                    that.onBlockClick(event, that);
                }
            });

            $(document).on("keyup", function (event) {
                var escape_code = 27;
                if (event.keyCode === escape_code) {
                    that.close();
                }
            });

            $block.on("click", ".js-close-dialog", function () {
                close();
            });

            function close() {
                if (!that.is_closed) {
                    that.close();
                }
                $document.off("click", close);
                $document.off("wa_before_load", close);
            }

            if (that.is_full_screen) {
                $(window).on("resize", onResize);
            }

            function onResize() {
                var is_exist = $.contains(document, that.$wrapper[0]);
                if (is_exist) {
                    that.resize();
                } else {
                    $(window).off("resize", onResize);
                }
            }

        }, 0);

    };

    helpdeskProDialog.prototype.show = function () {
        var that = this;

        $("body").append(that.$wrapper);

        that.setPosition();
        //
        that.onOpen(that.$wrapper, that);
    };

    helpdeskProDialog.prototype.setPosition = function () {
        var that = this,
            $window = $(window),
            window_w = $window.width(),
            window_h = (that.is_full_screen) ? $window.height() : $(document).height(),
            $block = (that.$block) ? that.$block : that.$wrapper,
            wrapper_w = $block.outerWidth(),
            wrapper_h = $block.outerHeight(),
            pad = 10,
            css;

        if (that.position) {
            css = that.position;

        } else {
            var getPosition = (that.userPosition) ? that.userPosition : getDefaultPosition;
            css = getPosition({
                width: wrapper_w,
                height: wrapper_h
            });
        }

        if (css.left > 0) {
            if (css.left + wrapper_w > window_w) {
                css.left = window_w - wrapper_w - pad;
            }
        }

        if (css.top > 0) {
            if (css.top + wrapper_h > window_h) {
                css.top = window_h - wrapper_h - pad;
            }
        } else {
            css.top = pad;

            if (that.is_full_screen) {
                var $content = $block.find(".w-dialog-content");

                $content.hide();

                var block_h = $block.outerHeight(),
                    content_h = window_h - block_h - pad * 2;

                $content
                    .height(content_h)
                    .addClass("is-long-content")
                    .show();

            }
        }

        $block.css(css);

        function getDefaultPosition(area) {
            return {
                left: parseInt((window_w - area.width) / 2),
                top: parseInt((window_h - area.height) / 2)
            };
        }
    };

    helpdeskProDialog.prototype.close = function () {
        var that = this;
        that.is_closed = true;
        that.$wrapper.remove();
        if (!that.url) {
            that.onClose(that.$wrapper, that);
        }
    };

    helpdeskProDialog.prototype.refresh = function (options) {
        var that = this;

        if (that.onRefresh) {
            that.onRefresh();
            that.close();
        } else {
            new helpdeskProDialog($.extend(that.options, options));
            that.close();
        }
    };

    helpdeskProDialog.prototype.resize = function () {
        var that = this,
            animate_class = "is-animated",
            do_animate = true;

        if (do_animate) {
            that.$block.addClass(animate_class);
        }

        that.setPosition();

        if (that.onResize) {
            that.onResize(that.$wrapper, that);
        }
    };

    helpdeskProDialog.prototype.save = function (data, callback) {
        var that = this,
            successTimeout = null;
        if (!that.is_locked) {
            addLoading();

            that.is_locked = true;

            errorText();
            successTimeout && clearTimeout(successTimeout);
            that.$form.find('.w-dynamic-content').html('');

            /* Проверка обязательных полей */
            if (!checkRequired()) {
                that.is_locked = false;
                removeLoading();
                return false;
            }

            data = data || that.$form.serializeArray();

            $.ajax({
                url: that.saveUrl,
                dataType: "json",
                type: "post",
                data: data,
                success: function (response) {
                    that.is_locked = false;
                    removeLoading();

                    if (typeof response !== 'undefined' && response.status == 'fail' && response.errors) {
                        errorText(response.errors);
                    } else {
                        if (typeof that.onSuccess == 'function') {
                            that.onSuccess(that, response);
                        } else {
                            successMessage();
                        }
                        try {
                            if (typeof that.onSuccessCallback == 'function') {
                                that.onSuccessCallback(response);
                            }
                            if (typeof callback === 'function') {
                                callback.call(this, that);
                            }
                        } catch (e) {
                            console.log('Callback error: ' + e.message, e);
                        }
                    }
                    that.resize();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    that.is_locked = false;
                    removeLoading();
                    if (console) {
                        console.log(jqXHR, textStatus, errorThrown);
                    }
                    errorText($__('Something wrong'));
                }
            });
        }

        function addLoading() {
            that.$submitBtn.after("<i class='icon16 loading'></i>");
        }

        function removeLoading() {
            that.$submitBtn.next("i").remove();
        }

        function successMessage() {
            that.$submitBtn.after("<i class='icon16 yes'></i>");
            successTimeout = setTimeout(function () {
                removeLoading();
            }, 3000);
        }

        function errorText(text) {
            text = text || '';
            that.$form.find('.errormsg').html(text);
        }

        function checkRequired() {
            var error = 0;
            that.$form.find(".s-required").each(function () {
                var elem = $(this);
                if ($.trim(elem.val()) == '') {
                    elem.addClass('error');
                    error = 1;
                }
            });
            if (error) {
                errorText($__('Fill in required fields'));
                return false;
            }
            return true;
        }
    };

    return helpdeskProDialog;

})(jQuery);