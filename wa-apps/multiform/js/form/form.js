(function ($) {
    $.form = {
        currentForm: 0,
        activePage: 'edit',
        xhr: null,
        pages: {},
        init: function (options) {
            this.currentForm = options.currentForm || 0;

            $.storage = new $.store();

            // Скрытие выпадающих окон при нажатии в любое место
            $("html, body").click(function (e) {
                if (!$(e.target).closest(".dropdown-ul").length) {
                    $(".dropdown-ul ul").hide();
                }
            });

            $(window).bind('hashchange', function () {
                $.form.dispatch();
            });

            if (!$.multiform.getHash()) {
                $.multiform.setHash("#/page/edit/");
            } else {
                $.form.dispatch();
            }
        },
        // if this is > 0 then this.dispatch() decrements it and ignores a call
        skipDispatch: 0,
        // Change location hash without triggering dispatch
        forceHash: function (hash) {
            if ($.multiform.getHash() != hash) {
                this.skipDispatch++;
                $.multiform.setHash(hash);
            }
        },
        setHash: function(hash) {
            this.previousHash = null;
            $.multiform.setHash(hash);
        },
        // dispatch call method by hash
        dispatch: function (hash, force) {
            if (this.skipDispatch > 0) {
                this.skipDispatch--;
                return false;
            }
            force = force || 0;
            if (hash === undefined) {
                hash = $.multiform.getHash();
            } else {
                hash = $.multiform.cleanHash(hash);
            }
            if (this.previousHash == hash && !force) {
                return;
            }
            this.previousHash = hash;
            hash = hash.replace(/^[^#]*#\/*/, '');
            if (hash) {
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
                                actionName += h.substr(0, 1).toUpperCase() + h.substr(1);
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

                    if (this[actionName + 'Action'] || $['form_' + this.activePage][actionName + 'Action']) {
                        this.currentAction = actionName;
                        this.currentActionAttr = attr;
                        if (this[actionName + 'Action']) {
                            this[actionName + 'Action'](attr);
                        } else if ($['form_' + this.activePage][actionName + 'Action']) {
                            $['form_' + this.activePage][actionName + 'Action'](attr);
                        }
                    } else {
                        if (console) {
                            console.log('Invalid action name:', actionName + 'Action');
                        }
                    }
                } else {
                    return false;
                }
                if (hash.join) {
                    hash = hash.join('/');
                }

            } else {
                return false;
            }

            $(document).trigger('hashchange', [hash]);
        },
        activateJsAction: function (actionName, btn, param) {
            if ($[$.multiform.module][actionName + 'Action']) {
                $[$.multiform.module][actionName + 'Action'](btn, param);
            } else if ($['form_' + this.activePage][actionName + 'Action']) {
                $['form_' + this.activePage][actionName + 'Action'](btn, param);
            } else {
                if (console) {
                    console.log('Invalid action name:', actionName + 'Action', 'for module ' + $.multiform.module + ' and page ' + this.activePage);
                }
            }
        },
        /* Подгрузка вкладок формы */
        pageEditAction: function () {
            this.formPageLoad('Edit');
        },
        pageConditionsAction: function () {
            this.formPageLoad('Conditions');
        },
        pageSettingsAction: function () {
            this.formPageLoad('Settings');
        },
        pageActionsAction: function (params) {
            if ($.form.activePage == 'actions' && params.length && params[0] !== '') {
                $.form_actions.dispatch(params);
            } else {
                this.formPageLoad('Actions', function () {
                    try {
                        var lastAction = $.storage.get("multiform-lastaction-" + $.multiform.domainHash);
                    } catch(e) {
                        lastAction = '';
                    }
                    if (lastAction && lastAction.length >= 2 && ((params.length && params[0] == '') || !params.length)) {
                        if ($(".actions-tab .s-inner-sidebar.main li[data-id='" + lastAction[1] + "']").length) {
                            params = lastAction;
                        } else {
                            $.storage.del("multiform-lastaction-" + $.multiform.domainHash);
                        }
                    }
                    $.form_actions.dispatch(params);
                });
            }
        },
        pageRecordsAction: function () {
            this.formPageLoad('Records', function () {
                /* Удаляем результаты поиска при каждой загрузки Записей */
                var params = $.form_records.getOrderParams("order-" + $.form.currentForm) || {};
                if (typeof params === 'string') {
                    params = {};
                } else {
                    params['page'] = 1;
                    params['query'] = '';
                    params['query-filters'] = [];
                }
                $.form_records.setOrderParams(params);
            });
        },
        pageNotificationsAction: function () {
            this.formPageLoad('Notifications');
        },
        pageShareAction: function () {
            this.formPageLoad('Share');
        },
        pageAppearanceAction: function () {
            this.formPageLoad('Appearance');
        },
        formPageLoad: function (page, callback) {
            if (this.xhr) {
                this.xhr.abort();
                $.multiform.hideLoading();
            }
            $.form.activePage = page.toLowerCase();
            $.multiform.showLoading();
            this.xhr = $.ajax({
                url: "?module=form&action=page" + page + "&id=" + $.form.currentForm + ($.form.pages[$.form.activePage] === undefined ? "&addJs=1" : ""),
                dataType: "html",
                type: "get",
                success: function (response) {
                    $.multiform.hideLoading();

                    // Удаляем лишние неиспользуемые блоки
                    $.form.cleanup();

                    $(".page-tab-content").html(response);
                    $(".tab").removeClass("selected");
                    $(".tab.s-page-" + page.toLowerCase()).addClass("selected");

                    $.form.pages[$.form.activePage] = 'initialized';

                    if (callback) {
                        callback.call($.multiform);
                    }
                },
                error: function () {
                    $.multiform.showError($__("Page cannot be loaded"));
                }
            });
        },
        cleanup: function () {
            $(".redactor-toolbar-tooltip, .Zebra_DatePicker").remove();
            this.xhr = null;
        },
        // Переключение между вкладками условий
        changeCloudTabAction: function (btn) {
            btn = $(btn);
            var field = btn.closest(".cloud-style");
            btn.parent().removeClass("inactive").siblings(".name").addClass("inactive");
            field.find("> .value > .f-tab").hide().siblings(".s-" + btn.data("tab") + "-tab").show();
            // Если переключились в условия для формы
            if (btn.siblings("input").length) {
                btn.siblings("input").prop("checked", true);
                field.find(".f-tab input, .f-tab textarea").prop("disabled", true);
                field.find(".s-" + btn.data("tab") + "-tab input, .s-" + btn.data("tab") + "-tab textarea").prop("disabled", false);
            }
        },
        // Получение переменных формы
        getTemplateVarsAction: function (btn) {
            if (!btn.next(".loading").length) {
                btn.after("<i class='icon16 loading'></i>");
                $.post("?action=handler", {data: 'getTemplateVars', form_id: this.currentForm}, function (response) {
                    if (response.status == 'ok' && response.data) {
                        $(".s-helper-block").remove();
                        $("body").append(response.data);
                    }
                    btn.next(".loading").remove();
                });
            }
        },
        // Переключение между вкладками переменных шаблона
        changeTabAction: function (btn) {
            var id = btn.closest("li").attr("id");
            var helperBlock = btn.closest(".s-helper-block");
            if (helperBlock.find("#" + id + "-content").length) {
                btn.closest(".tabs").find("li").removeClass("selected");
                btn.closest("li").addClass("selected");
                helperBlock.find("#" + id + "-content").show().siblings("div").hide();
            }
        },
        // Появление выпадающего окна
        showDropdownUlAction: function (btn) {
            var ul = btn.closest(".dropdown-ul").find("ul");
            $(".dropdown-ul ul").not(ul).hide();
            ul.toggle();
        }
    };
})(jQuery);