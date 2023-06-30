/*global jQuery, location, $_*/
(function ($) {
    // js controller
    $.mylang = {
        page:null,
        // init js controller
        init: function (options) {
            // if history exists
            if (typeof($.History) != "undefined") {
                $.History.bind(function (hash) {
                    $.mylang.dispatch(hash);
                });
            }
            if (options.page) {
                this.page = options.page;
            }
            this.dispatch();
        },
        // dispatch call method by hash
        dispatch: function (hash) {
            if (hash === undefined) {
                hash = location.hash.replace(/^[^#]*#\/*/, '');
            }
            if (hash) {
                // clear hash
                hash = hash.replace(/^.*#/, '');
                hash = hash.split('/');
                if (hash[0]) {
                    var actionName = "";
                    var attrMarker = hash.length;
                    for (var i = 0; i < hash.length; i++) {
                        var h = hash[i];
                        if (i < 2) {
                            if (i === 0) {
                                actionName = h;
                            } else if (parseInt(h, 10) != h) {
                                attrMarker = i;
                                //actionName += h.substr(0,1).toUpperCase() + h.substr(1);
                                break;
                            } else {
                                attrMarker = i;
                                break;
                            }
                        } else {
                            attrMarker = i;
                            break;
                        }
                    }
                    var attr = hash.slice(attrMarker);
                    // call action if it exists
                    if (this[actionName + 'Action']) {
                        this.currentAction = actionName;
                        this.currentActionAttr = attr;
                        this[actionName + 'Action'](attr);
                    } else {
                        if (console) {
                            console.log('Invalid action name:', actionName+'Action');
                        }
                    }
                } else {
                    // call default action
                    this.defaultAction();
                }
            } else {
                // call default action
                this.defaultAction();
            }
        },
        defaultAction: function () {
            if (this.page=='contacts') {
                this.contactsAction();
            }
            if (this.page=='shop') {
                this.shopAction();
            }
            if (this.page=='site') {
                this.siteAction();
            }
            if (this.page=='blog') {
                this.blogAction();
            }
            if (this.page=='photos') {
                this.tagsAction();
            }
        },
        contactsAction: function () {
            $("#content").load('?module=contacts');
        },
        shopAction: function () {
            $("#content").load('?module=shop&action=stocks');
        },
        featuresAction: function (id) {
            var path = '?module=shop&action=features';
            if (id) {
                path += '&feature=' + id;
            }
            $("#content").load(path);
        },
        stocksAction: function () {
            $("#content").load('?module=shop&action=stocks');
        },
        tagsAction: function () {
            $('[data-id="tags"]').addClass('selected');
            var path = '?module='+ $.mylang.page + '&action=tags';
            $("#content").load(path);

        },
        checkoutAction: function (id) {
            $('[data-id="checkout"]').addClass('selected');
            var path = '?module=shop&action=checkout';
            if (id) {
                path += '&param=' + id;
            }
            $("#content").load(path);
        },
        workflowAction: function (id) {
            $('[data-id="workflow"]').addClass('selected');
            var path = '?module=shop&action=workflow';
            if (id) {
                path += '&param=' + id;
            }
            $("#content").load(path);
        },
        servicesAction: function (id) {
            $('[data-id="services"]').addClass('selected');
            var path = '?module=shop&action=services';
            if (id) {
                path += '&service=' + id;
            }
            $("#content").load(path);
        },
        promosAction: function (id) {
            $('[data-id="promos"]').addClass('selected');
            var path = '?module=shop&action=promos';
            if (id) {
                path += '&promo=' + id;
            }
            $("#content").load(path);
        },
        siteAction: function () {
            $.mylang.themesAction();
        },
        blogAction: function () {
            $.mylang.themesAction();
        },
        //universal
        themesAction: function (id) {
            $('[data-id="themes"]').addClass('selected');
            var path = '?module=design&app_id=' + $.mylang.page;
            if (id) {
                path += '&theme=' + id;
            }
            $("#content").load(path);
        },
        settingsAction: function () {
            $('[data-id="settings"]').addClass('selected');
            if (this.page=='site') {
                $("#content").load('?module=site&action=settings');
            }
            if (this.page=='shop') {
                $("#content").load('?module=shop&action=settings');
            }
        },
        generalAction: function () {
            $("#content").load('?module=settings');
        },
        selectorAction: function () {
            $("#content").load('?module=settings&action=selector');
        },
        langpackAction: function (id) {
            $('[data-id="langpack"]').addClass('selected');
            var path = '?module=settings&action=langpack';
            if (id) {
                path += '&plugin_id=' + id;
            }
            $("#content").load(path);
        },
        maintenanceAction: function () {
            $("#content").load('?module=settings&action=maintenance');
        },
        providersAction: function () {
            $("#content").load('?module=settings&action=providers');
        },
        autotranslateAction: function () {
            $("#content").load('?module=shop&action=autotranslate');
        },
        exportAction: function () {
            $("#content").load('?module=shop&action=export');
        },
        authAction: function () {
            $("#content").load('?module=site&action=auth');
        },

        /**
        * Make input (or textarea) with field_id flexible,
        * what means that depends on length and threshold this field turn into input or textarea and back
        * shop.js 7
        * @param String field_id
        * @param Number threshold (default 50)
        */
        makeFlexibleInput: function (field_id, threshold) {
            var timeout = 250;
            threshold = threshold || 50;
            var height = 45;
            var timer_id = null;
            if ($('#'+field_id).length > 0) {
                field_id = '#' + field_id;
            }
            var field = $(field_id);

            var onFocus = function () {
                this.selectionStart = this.selectionEnd = this.value.length;
            };
            var handler = function () {
                if (timer_id) {
                    clearTimeout(timer_id);
                    timer_id = null;
                }
                timer_id = setTimeout(function () {
                    var val = field.val();
                    if (val.length > threshold && field.is('input')) {
                        var textarea = $.mylang.input2textarea(field);
                        textarea.css('height', height);
                        field.replaceWith(textarea);
                        field = textarea;
                        field.focus();
                    } else if (val.length <= threshold && field.is('textarea')) {
                        var input = $.mylang.textarea2input(field);
                        input.css('height', '');
                        field.replaceWith(input);
                        field = input;
                        field.focus();
                    }
                }, timeout);
            };
            var p = field.parent();
            p.off('keydown', field_id).
                on('keydown',  field_id, handler);
            p.off('focus',    field_id).
                on('focus',     field_id, onFocus);
            // initial shot
            handler();
        },

        input2textarea: function (input) {
            var p = input.parent();
            var rm = false;
            if (!p.length) {
                p = $('<div></div>');
                p.append(input);
                rm = true;
            }
            var val = input.val();
            input.attr('value', '').val('');

            var html = p.html();
            html = html.replace(/value(\s*?=\s*?['"][\s\S]*?['"])*/, '');
            html = html.replace(/type\s*?=\s*?['"]text['"]/, '');
            html = html.replace('input', 'textarea');
            html = $.trim(html).replace(/\/\s*>$/, '>') + '</textarea>';
            if (rm) {
                p.remove();
            }
            return $(html).val(val);
        },

        textarea2input: function (textarea) {
            var p = textarea.parent();
            var rm = false;
            if (!p.length) {
                p = $('<div></div>');
                p.append(textarea);
                rm = true;
            }
            var val = textarea.val();
            textarea.html('').val('');

            var html = p.html();
            html = html.replace('textarea', 'input type="text" value=""');
            html = html.replace('</textarea>', '');

            if (rm) {
                p.remove();
            }
            return $(html).val(val);
        },
    };
    $.mylang.backend = {
        translate: function(provider) {
            $('tr[data-id]').each(function(){
                let title = $(this).find('span.title').text();
                $(this).find('input[value=""]').each(function() {
                    let input = $(this);
                    $.post('?module=backend&action=translate', { provider:provider, target:$(this).data('mylang'), text:title}, function (response) {
                        if (response.status === 'ok') {
                            $(input).val(response.data).trigger('change');
                        } else {
                            $('#wa-form-error').text(response.errors).removeClass('stealth');
                        }
                    })
                })
            });
            return null;
        },
    };

    $.storage = new $.store();

    $(document).on('submit','#s-mylang-form',function (e) {
        e.preventDefault();
        var datastring = $(this).find(':input.changed').serializeArray();
        if (datastring.length < 1) {
            return 0;
        }
        datastring.push({'name':'app_id', 'value':$.mylang.page});
        $.ajax({
            type: "POST",
            url: $(this).prop('action'),
            data: datastring,
            dataType: "json",
            success: function (data) {
                if (data.status == "ok") {
                    $('.changed').removeClass('changed');
                    $("#wa-save-button").removeClass("yellow").addClass("green");
                    $('#wa-form-status').css('opacity',1);
                    setTimeout(function () {
                        $('#wa-form-status').css('opacity',0);
                    },1500);
                }
            },
            error: function (data) {
                $('#wa-form-error').css('opacity',1).html(data.error);
            }
        });
    });

    $(document).on('change','#s-mylang-form',function () {
        $(this).removeClass("green").addClass("yellow");
    });

    // Ctrl+S hotkey handler
    $(document).on('keydown', function (e) {
        if ($(e.target).is(':input') && (e.ctrlKey || e.metaKey) && e.keyCode == 83) {
            var focused = $(document.activeElement).focus();
            $('#wa-save-button').focus();
            $(focused).focus();
            $('#wa-save-button').trigger('click');
            return false;
        }
    });
    //Theme
    $(document).on('submit','#s-mylang-saveall-form',function (e) {
        e.preventDefault();
        var datastring = $(this).find(':input').serialize();
        if (datastring.length < 1) {
            return 0;
        }
        $.ajax({
            type: "POST",
            url: $(this).prop('action'),
            data: datastring,
            dataType: "json",
            success: function (data) {
                if (data.status == "ok") {
                    $('.changed').removeClass('changed');
                    $("#wa-save-button").removeClass("yellow").addClass("green");
                    $('#wa-form-status').css('opacity',1);
                    setTimeout(function () {
                        $('#wa-form-status').css('opacity',0);
                    },1500);
                }
            },
            error: function (data) {
                $('#wa-form-error').css('opacity',1).html(data.error);
            }
        });
    });

    $(document).on('change','#s-mylang-saveall-form',function () {
        $(this).removeClass("green").addClass("yellow");
    });
    //

    $(document).on('change','input', function () {
        $(this).addClass("changed");
        $("#wa-save-button").removeClass("green").addClass("yellow");
    });

    $(document).on('change','textarea',function () {
        $(this).addClass("changed");
        $("#wa-save-button").removeClass("green").addClass("yellow");
    });

    $(document).on('click','#delete-dialog', function (e) {
        e.preventDefault();
        $("#dialog-dellocale").waDialog({
            'height': '60%',
            'width': '40%',
        });
    });

    $(document).on('click','.menu-v.with-icons>li', function () {
        $('.selected','.menu-v.with-icons').removeClass('selected');
        $(this).addClass('selected');
    });

    $(".auto_submit_item").change(function () {
        $(this).parents("form").submit();
    });

    $(document).on('click','#mylang-widen-all', function () {
        let tbl = $(this).closest('table');
        $('input', tbl).toggleClass('short');
        $('th', tbl).toggleClass('min-width').toggleClass('short');
        tbl.toggleClass('auto-width');
    });

    //----EDITOR ----//

    $(document).on('submit', '#editor_strings', function (e) {
        e.preventDefault();
        var datastring = $(this).find(':input.changed').serialize();
        if (datastring.length < 1) {
            return 0;
        }
        datastring += '&'+$.param({ slug:$.mylang.editor.slug, locale:$.mylang.editor.locale.id});
        $.ajax({
            type: "POST",
            url: $(this).prop('action'),
            data: datastring,
            dataType: "json",
            success: function (data) {
                if (data.status == "ok") {
                    $('.changed').removeClass('changed');
                    $("#wa-save-button").removeClass("yellow").addClass("green");
                    $('#wa-form-status').css('opacity',1);
                    setTimeout(function () {
                        $('#wa-form-status').css('opacity',0);
                    },1500);
                }
            },
            error: function (data) {
                $('#wa-form-error').css('opacity',1).html(data.error);
            }
        });
    });

    $(document).on('submit', '#pofile-upload', function (e) {
        e.preventDefault();
        if ($('#upload_pofile').val()=='') {
            $('.wa-upload-dialog-error').text($_('Locale is not selected.'));
            return false;
        }
        if ($('input[type="file"]').val()=='') {
            $('.wa-upload-dialog-error').text($_('File is not selected.'));
            return false;
        }
        $.ajax({
            url: $(this).prop('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false
        }).done(function () {
            $("#wa-locale-upload-dialog").trigger('close');
            if ($('#upload_pofile').val() == $('#editor_locale').val()) {
                $('#editor_locale').trigger('change');
            }
        }).fail(function (data) {
            $('.wa-upload-dialog-error').text(data.error);
        });
    });

    $.longAction = function () {
        var data = {},
            processId = 0,
            stopFlag = 0,
            pauseFlag = 0,
            timerQueue = [];

        var options = {
            process_url: null, // endpoint
            delay: 2000, // between requests
            parallel: 2, // how many steps to run
            debug: false,
            afterCleanUp: function () {
            }, // after all done and all timers trashed
            onServerError: function (response) {
            },
            // start related options
            start: {
                data: {}, // data to be passed to url
                onStart: function () {}, // just after start

                // inside callback after first response come
                onBegin: function (response) {}, // first line
                onEnd: function (response) {}, // last line
                onSuccess: function (response) {}, // success response
                onError: function (response) {}, // error response
                onUnknown: function (response) {} // some unknown happened
            },
            // step related options
            step: {
                data: {}, // data to be passed to url
                responseParams: {
                    ready: 'ready',
                    error: 'error',
                    progress: 'progress',
                    pause: 'pause'
                },
                stopData: {'stop': 1}, // stop param
                pauseData: {'pause': 1}, // pause param
                immediately: false, // start steps immediately after 'start' succeeds
                repeatOnError: 3, // how many times to repeat step after error comes

                // inside step post callback
                // first line @ step
                onBegin: function (response) {},
                // last line @ step
                onEnd: function (response) {},
                // no response come...
                onNo: function (response) {
                    // should return true to step after
                    return true;
                },
                // on 'ready' param come in response
                onReady: function (response) {},
                // progressando
                onProgress: function (response) {},
                // on 'pause' param come in response
                onPause: function (response) {},
                // on 'stop' param come in response
                onStop: function (response) {},
                // some unknown params in response
                onUnknown: function (response) {},
                onError: function (response) {},
                // on 'error' param come in response
                onCustom: function (response) {
                    // custom callback
                    // should return false to go to the next condition, or true to break it
                    return false;
                }
            }
        };

        function _cleanUp()
        {
            _debug('cleanUp');
            processId = null;
            stopFlag = 0;
            pauseFlag = 0;
            var timer_id = timerQueue.pop();
            while (timer_id) {
                clearTimeout(timer_id);
                timer_id = timerQueue.pop();
            }
            timerQueue = [];
            if ($.isFunction(data.afterCleanUp)) {
                data.afterCleanUp();
            }
        }

        function _step(delay)
        {
            delay = delay || data.delay;

            var repeatOnError = data.step.repeatOnError;

            if (!Date.now) {
                Date.now = function () {
                    return new Date().getTime();
                };
            }

            var timer_id = setTimeout(function () {
                var step_data = $.extend(
                    true,
                    {},
                    data.step.data,
                    pauseFlag ? data.step.pauseData : {},
                    stopFlag ? data.step.stopData : {},
                    {
                        processId: processId,
                        ts: Date.now()
                    }
                );

                _debug({'step start': step_data});

                $.post(data.process_url, step_data, function (r) {
                    if ($.isFunction(data.step.onBegin)) {
                        data.step.onBegin(r);
                    }

                    if (!r) {
                        _debug('step: something wrong - no response');
                        if ($.isFunction(data.step.onNo) && data.step.onNo(r)) {
                            _step(data.delay * 2);
                        }
                    } else if (data.step.onCustom && $.isFunction(data.step.onCustom) && data.step.onCustom(r)) {
                        _debug({'step: custom callback': r});
                    } else if (r[data.step.responseParams.ready]) {
                        _debug({'step: all done': r});
                        if ($.isFunction(data.step.onReady)) {
                            data.step.onReady(r);
                        }
                        _cleanUp();
                    } else if (r[data.step.responseParams.error]) {
                        _debug({'step: error': r});
                        if ($.isFunction(data.step.onError)) {
                            data.step.onError(r);
                        }
                        if (repeatOnError--) {
                            _step(delay * 3);
                        } else {
                            _cleanUp();
                        }
                    } else if (r[data.step.responseParams.pause]) {
                        _debug({'step: in pause': r});
                        if ($.isFunction(data.step.onPause)) {
                            data.step.onPause(r);
                        }
                        _step(delay * 2); // next step
                    } else if ([data.step.responseParams.progress]) {
                        _debug({'step: in progress': r});
                        if ($.isFunction(data.step.onProgress)) {
                            data.step.onProgress(r);
                        }
                        repeatOnError = data.step.repeatOnError;
                        _step(delay); // next step
                    } else {
                        _debug({'step: some unknown': r});
                        if ($.isFunction(data.step.onUnknown)) {
                            data.step.onUnknown(r);
                        }
                        if (repeatOnError--) {
                            _step(delay * 3);
                        } else {
                            _cleanUp();
                        }
                    }

                    if ($.isFunction(data.step.onEnd)) {
                        data.step.onEnd(r);
                    }
                }, 'json').fail(function () {
                    _debug('step: server error');
                    if ($.isFunction(data.onServerError)) {
                        data.onServerError();
                    }
                    if (repeatOnError--) {
                        _step(delay * 3);
                    } else {
                        _cleanUp();
                    }
                });
            }, delay);
            timerQueue.push(timer_id);
        }
        
        function _debug(msg)
        {
            if (console.log && data.debug) {
                console.log({'LongAction': msg});
            }
        }

        return {
            // start all process
            start: function (o) {
                data = $.extend(true, {}, options, o);

                if ($.isFunction(data.start.onStart)) {
                    data.start.onStart();
                }

                if (!data.process_url) {
                    return false;
                }

                _debug('post to long action');
                $.post(data.process_url, data.start.data, function (r) {
                    _debug({'start: post ok': r});
                    if ($.isFunction(data.start.onBegin)) {
                        data.start.onBegin(r);
                    }

                    _debug({'start: after init()': r});
                    if (r && r.processId) {
                        processId = r.processId;
                        if ($.isFunction(data.start.onSuccess)) {
                            data.start.onSuccess(r);
                        }
                        for (var i = 1; i <= data.parallel; i++) {
                            _step(data.step.immediately ? 0 : data.delay * i);
                        }
                    } else if (r && r.error) {
                        _debug({'start: error': r});
                        if ($.isFunction(data.start.onError)) {
                            data.start.onError(r);
                        }
                    } else {
                        _debug({'start: some error': r});
                        if ($.isFunction(data.start.onUnknown)) {
                            data.start.onUnknown(r);
                        }
                        _cleanUp();
                    }

                    if ($.isFunction(data.start.onEnd)) {
                        data.start.onEnd(r);
                    }
                }, 'json').fail(function () {
                    _debug('start: post fail');
                    if ($.isFunction(data.onServerError)) {
                        data.onServerError();
                    }
                    _cleanUp();
                });
            },
            step: _step,
            // will send stop data with next step
            stop: function () {
                if (processId) {
                    stopFlag = 1;
                }
            },
            halt: _cleanUp,
            // will send pause data with next step
            pause: function () {
                pauseFlag = 1;
            },
            continue: function () {
                pauseFlag = 0;
            }
        };
    };
})(jQuery);
