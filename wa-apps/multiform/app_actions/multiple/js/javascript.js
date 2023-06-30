(function ($) {
    $.action_multiple = {
        parentId: 0,
        initPageMultiple: function () {
            var self = this;
            // Необходимо для редактирования действий
            $(document).on('click', ".s-multiple .s-actions-save input[type='submit']", function () {
                var fields = $(this).closest(".fields");
                var form = fields.parent().is('form') ? fields.parent() : fields;
                $.form_actions.saveActions(form, function (response) {
                    /* После каждого сохранения перезагружаем форму */
                    $.action_multiple.editAction(response.data.id);
                });
                return false;
            });
            // Необходимо для сохранения новых действий
            $(".actions-tab .s-multiple").submit(function () {
                self.saveHandler($(this));
                return false;
            });
            
            $(".actions-tab .s-inner-sidebar.act-multiple > ul").sortable({
                distance: 5,
                helper: 'clone',
                items: 'li:not(.skip-sort)',
                opacity: 0.75,
                tolerance: 'pointer',
                update: function (event, ui) {
                    var actionIds = $.makeArray($(".actions-tab .s-inner-sidebar.act-multiple > ul > li:not(.skip-sort)").map(function () {
                        return $(this).attr('data-id');
                    }));
                    $.post("?action=handler", {data: 'actionSort', ids: actionIds, id: $.form.currentForm});
                }
            });

            // Вызываем js функции
            $(document).off('click', 'a.js-action-act-mult').on('click', 'a.js-action-act-mult', function () {
                self.activateJsAction($(this));
                return false;
            });

            self.parentId = $(".actions-tab .s-inner-sidebar.act-multiple").attr('data-parent-id');

            self.dispatch();
        },
        dispatch: function () {
            var params = [];
            var hash = $.multiform.getHash();
            hash = hash.split('/');
            if (hash[0]) {
                var actionParams = [];
                for (var i = 0; i < hash.length; i++) {
                    if (i > 2) {
                        actionParams.push(hash[i]);
                    }
                }
                params = actionParams;
            }
            if (params.length > 3) {
                var module = params[2];
                var attr = params[3];
                if (this[module + 'Action']) {
                    this[module + 'Action'](attr);
                } else {
                    if (console) {
                        console.log('Invalid action name:', module + 'Action');
                    }
                }
            }
        },
        /* Открытие списка всех действий */
        multipleShowActionAction: function (btn, id) {
            btn.hide();
            var self = this;
            var actionsSelect = btn.next(".actions-select");
            if (!actionsSelect.hasClass("inited")) {
                actionsSelect.show().chosen({disable_search_threshold: 10, no_results_text: $__("No result text")}).trigger('chosen:open')
                    .on('chosen:hiding_dropdown', function (evt, params) {
                        btn.show();
                        actionsSelect.addClass("inited");
                        $(this).parent().find('.chosen-container').hide();
                    })
                    .on("change", function () {
                        actionsSelect.trigger('chosen:close');
                        var that = $(this);
                        var value = that.val();
                        if (value !== '') {
                            $.form.forceHash('#/page/actions/edit/' + id + '/add/' + value + '/');
                            self.dispatch();
                        }
                    });
            } else {
                var chosen = actionsSelect.data('chosen');
                /* Сбрасываем результат */
                if (typeof chosen !== 'undefined') {
                    chosen.results_reset();
                }
                actionsSelect.trigger('chosen:open');
                btn.siblings('.chosen-container').show();
            }
        },
        /* Добавление/редактирование действия */
        addAction: function (actionId) {
            this.editAction(actionId, 'create');
        },
        multipleEditAction: function (btn, actionId) {
            this.editAction(actionId);
        },
        editAction: function (actionId, create) {
            create = create || null;
            var self = this;
            $.form_actions.highlightSidebar(create ? 0 : actionId);
            if (!self.isLoading()) {
                self.addLoading();
                $.form.cleanup();
                $.form.forceHash('#/page/actions/edit/' + self.parentId + '/' + (create ? 'add' : 'edit') + '/' + actionId + '/');
                $.post("?module=actions", {action_id: actionId, form_id: $.form.currentForm, create: create, parent_id: self.parentId}, function (response) {
                    self.hideLoading();
                    if (response) {
                        $(".s-actions-block-multiple").html(response);
                        $.form_actions.afterActionLoad();
                    } else {
                        $.multiform.showError($__("Page cannot be loaded"));
                        $(".s-actions-block-multiple").html($__("Page cannot be loaded"));
                    }
                    $.form_actions.updateSidebar(actionId, 'multiple');
                });
            }
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
                    url: "?module=multipleAction&action=save",
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
        },
        isLoading: function () {
            return $(".s-actions-block-multiple").hasClass("is-loading");
        },
        addLoading: function () {
            if (!this.isLoading()) {
                $(".s-actions-block-multiple").addClass("is-loading");
                $(".s-actions-block-multiple").html($__("Loading") + "... <i class='icon16 loading'></i>");
            }
        },
        hideLoading: function () {
            $(".s-actions-block-multiple").removeClass("is-loading").html('');
        }
    };
    $.action_multiple.initPageMultiple();
})(jQuery);