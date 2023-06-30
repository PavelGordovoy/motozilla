/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
(function ($) {
    $.multiform = {
        locale: '',
        csrf: null,
        module: '',
        lang: 'en',
        domainHash: '',
        init: function (options) {
            this.locale = options.locale || '';
            this.csrf = options.csrf || null;
            this.module = options.module || '';
            this.lang = options.lang || '';
            this.domainHash = options.domainHash || '';
            this.localeStrings = options.localeStrings || {};

            /* Устанавливаем локализацию текста */
            window.$__ = puttext(this.localeStrings);

            // Set up AJAX to never use cache
            $.ajaxSetup({
                cache: false
            });

            // Вызываем js функции
            $(document).on('click', 'a.js-action', function () {
                $.multiform.activateJsAction($(this));
                return false;
            });

            // Placeholder формы
            $(document).on({
                focus: function () {
                    var input = $(this);
                    if (input.hasClass("empty")) {
                        input.val('');
                        input.removeClass('empty');
                    }
                },
                blur: function () {
                    var input = $(this);
                    var v = input.val();
                    if (v == '' || v == input.attr('placeholder')) {
                        input.addClass('empty');
                        input.val(input.attr('placeholder'));
                    }
                }
            }, '[placeholder]');

            $(document).on('focus', 'input.error, textarea.error', function () {
                $(this).removeClass("error");
            });
            if (!window.wa_skip_csrf_prefilter) {
                $.ajaxPrefilter(function (settings, originalSettings, xhr) {
                    if ((settings.type || '').toUpperCase() !== 'POST') {
                        return;
                    }
                    var matches = document.cookie.match(new RegExp("(?:^|; )_csrf=([^;]*)"));
                    if (!matches || !matches[1]) {
                        return;
                    }
                    var csrf = decodeURIComponent(matches[1]);
                    if (!settings.data && settings.data !== 0) {
                        settings.data = '';
                    }
                    if (typeof (settings.data) == 'string') {
                        if (settings.data.indexOf('_csrf=') == -1) {
                            settings.data += (settings.data.length > 0 ? '&' : '') + '_csrf=' + csrf;
                            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        }
                    } else if (typeof (settings.data) == 'object') {
                        if (window.FormData && settings.data instanceof window.FormData) {
                            settings.data.set('_csrf', csrf);
                        } else {
                            settings.data['_csrf'] = csrf;
                        }
                    }
                });
            }
        },
        setHash: function (hash) {
            hash = this.cleanHash(hash);
            if (!(hash instanceof String) && hash.toString) {
                hash = hash.toString();
            }
            hash = hash.replace(/\/\//g, "/");
            hash = hash.replace(/^.*#/, '');
            if ($.browser && $.browser.safari) {
                // Work around bug in safari 5.0.5 and down that broke UTF8 hashes
                if (parent) {
                    parent.window.location = parent.window.location.href.replace(/#.*/, '') + '#' + hash;
                } else {
                    window.location = location.href.replace(/#.*/, '') + '#' + hash;
                }
            } else if (parent && (!$.browser || !$.browser.msie)) {
                parent.window.location.hash = hash;
            } else {
                location.hash = hash;
            }
            return true;
        },
        getHash: function () {
            return this.cleanHash();
        },
        cleanHash: function (hash) {
            if (typeof hash == 'undefined') {
                hash = window.location.hash.toString();
            }

            if (!hash.length) {
                hash = '' + hash;
            }
            while (hash.length > 0 && hash[hash.length - 1] === '/') {
                hash = hash.substr(0, hash.length - 1);
            }
            hash += '/';
            if (hash[0] != '#') {
                if (hash[0] != '/') {
                    hash = '/' + hash;
                }
                hash = '#' + hash;
            } else if (hash[1] && hash[1] != '/') {
                hash = '#/' + hash.substr(1);
            }

            if (hash == '#/') {
                return '';
            }
            return hash;
        },
        isLoading: function () {
            return $(".loading-process").hasClass("loading");
        },
        showLoading: function (text) {
            var loadingBlock = $(".loading-process");
            if (loadingBlock.is(':animated')) {
                return false;
            }
            text = text || $__("Loading") + "...";
            loadingBlock.text(text).addClass("loading").animate({'bottom': 0}, 500);
        },
        showError: function (error) {
            this.showLoading(error);
            setTimeout(function () {
                $.multiform.hideLoading();
            }, 3000);
        },
        hideLoading: function () {
            var loadingBlock = $(".loading-process");
            if (loadingBlock.is(':animated')) {
                loadingBlock.stop();
            }
            loadingBlock.removeClass("loading").animate({'bottom': '-100%'}, 1000);
        },
        activateJsAction: function (btn) {
            var hash = this.cleanHash(btn.attr("href"));
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
                if ($[this.module][actionName + 'Action']) {
                    $[this.module][actionName + 'Action'](btn, param);
                } else if ($[this.module]['activateJsAction']) {
                    $[this.module]['activateJsAction'](actionName, btn, param);
                } else {
                    if (console) {
                        console.log('Invalid action name:', actionName + 'Action', 'for module ' + this.module);
                    }
                }
            }
        },
        initCodeMirror: function (elem) {
            if (!elem.hasClass("initialized")) {
                var c = CodeMirror.fromTextArea(elem[0], {
                    tabMode: "indent",
                    height: "dynamic",
                    lineWrapping: true,
                    onChange: function (cm, obj) {
                        $(elem).val(cm.getValue());
                    }
                });
                $(elem).addClass("initialized");
            }
        },
        isValidInput: function (evt, regexp) {
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
        }
    };
})(jQuery);
/*
  (c) 2013 Instant Communication Ltd.
  under terms of ISC License
*/
(function (define) {
    return define(function () {
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
})("undefined" != typeof define ? define : function (r) {
    return "undefined" != typeof module && "undefined" != typeof exports ? module.exports = r() : window.puttext = r()
});