(function ($) {
    $.form_actions = {
        initPageActions: function () {
            var self = this;
            $(document).on('submit', ".s-actions-save", function () {
                self.saveActions($(this));
                return false;
            });

            /* Сворачивание/разворачивание групп */
            $(document).on('click', ".actions-tab .s-inner-sidebar .collapse-handler", function () {
                var that = $(this),
                    i = that.find('i'),
                    collapsed = i.hasClass('rarr');
                if (collapsed) {
                    i.removeClass("rarr").addClass("darr");
                    that.siblings('ul').removeClass('collapsed');
                } else {
                    i.removeClass("darr").addClass("rarr");
                    that.siblings('ul').addClass('collapsed');
                }
                $.post("?action=handler", {data: 'actionGroupCollapse', id: $.form.currentForm, group_id: parseInt(that.parent().data('id')) * (-1), collapsed: collapsed ? 0 : 1});
            });

            /* Сортировка */
            var oldContainer;
            $(".actions-tab .s-inner-sidebar > ul").jsortable({
                group: 'nested',
                itemSelector: "li:not(.skip-sort)",
                placeholder: "<li class='sortable-placeholder'></li>",
                distance: '5',
                afterMove: function ($placeholder, container, $closestItemOrContainer) {
                    if (oldContainer != container) {
                        if (oldContainer) {
                            oldContainer.el.closest('li').removeClass('highlighted');
                        }
                        if (container.el.hasClass('collapsed') && container.el.hasClass('s-action-group')) {
                            container.el.closest('li').addClass('highlighted');
                        }
                        oldContainer = container;
                    }
                },
                onDrop: function ($item, container, _super) {
                    if (oldContainer) {
                        oldContainer.el.closest('li').removeClass('highlighted');
                        oldContainer = null;
                    }
                    var actionIds = $.makeArray(container.el.closest('.s-inner-sidebar').find("li:not(.skip-sort)").map(function () {
                        return $(this).attr('data-id');
                    }));
                    $.post("?action=handler", {data: 'actionSort', ids: actionIds, id: $.form.currentForm});
                    $.post("?action=handler", {data: 'changeActionGroup', action_id: $item.attr('data-id'), id: $.form.currentForm, group_id: container.el.hasClass("s-action-group") ? parseInt(container.el.closest('li').data("id")) * (-1) : 0});
                    _super($item, container);
                }
            });
        },
        dispatch: function (params) {
            if (params.length && params[0] !== '') {
                var module = params[0];
                var attr = params[1];
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
        showActionAction: function (btn) {
            btn.hide();
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
                            $.form.forceHash('#/page/actions/add/' + value + '/');
                            $.form.dispatch(undefined, 1);
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
        /* Создание/изменение/удаление групп */
        editActionGroupAction: function (btn, id) {
            var dialogId = 'group-action-dialog',
                groupId = btn.attr('data-group-id'),
                groupName = btn.attr('data-group-name');
            var dialogParams = {
                title: groupId == 'new' ? $__('Creating of new action group') : $__("Editing of action group") + '<a href="#/delete/actionGroup/' + groupId + '" title="' + $__('delete') + '" class="js-action top-right"><i class="icon16 delete"></i> ' + $__('delete') + '</a>',
                content: '<div style="margin-top: 30px"><input type="text" style="font-size: 1.5em; padding: 5px; width: 95%" name="name" value="' + (groupId == 'new' ? $__('New group') : groupName) + '"></div>',
                loading_header: $__("Wait, please..."),
                buttons: '<div class="align-center">'
                    + '<input type="submit" value="' + $__("Save") + '" class="button green">'
                    + '<span class="errormsg" style="margin-left: 10px; display: inline-block"></span> '
                    + $__('or')
                    + ' <a href="#" class="cancel">' + $__('cancel') + '</a></div>',
                height: '150px',
                width: '500px',
                'min-height': '150px',
                onClose: function () {
                    $("#" + dialogId).remove();
                },
                onSubmit: function (form) {
                    if (!form.hasClass("is-loading")) {
                        form.addClass("is-loading");
                        if (form.find('.s-delete-confirm').length && form.find('.s-delete-confirm').is(":visible")) {
                            $.post("?action=handler", {data: 'deleteActionGroup', form_id: $.form.currentForm, group_id: groupId}, function (response) {
                                $.form_actions.updateSidebar();
                                form.trigger('close');
                            });
                        } else {
                            $.post("?action=handler", {data: 'editActionGroup', form_id: $.form.currentForm, name: form.find("input[name='name']").val(), group_id: groupId}, function (response) {
                                if (response.status == 'ok' && response.data) {
                                    if (groupId == 'new') {
                                        var groupHtml = '<li data-id="-' + response.data.id + '">' +
                                            '<h5 class="heading collapse-handler"><i class="icon16 darr"></i> <span>' + response.data.name + '</span></h5>' +
                                            '<ul class="menu-v with-icons s-action-group stack"></ul>' +
                                            '<a href="#/edit/actionGroup/" title="' + $__('Edit group') + '" class="js-action top-right" data-group-id="' + response.data.id + '" data-group-name="' + response.data.name + '">' +
                                            '<i class="icon16 settings"></i>' +
                                            '</a>' +
                                            '</li>';
                                        $('.s-inner-sidebar.main > ul .skip-sort:first').before(groupHtml);
                                    } else if ($('.s-inner-sidebar.main li[data-id="-' + response.data.id + '"]').length) {
                                        $('.s-inner-sidebar.main li[data-id="-' + response.data.id + '"] a[data-group-id]').attr('data-group-name', response.data.name).siblings('h5').find('span').text(response.data.name);
                                    }
                                }
                                form.trigger('close');
                            });
                        }
                    }
                    return false;
                }
            };
            $("body").append("<div id='" + dialogId + "'></div>");
            var dialog = $("#" + dialogId);
            dialog.waDialog(dialogParams);

        },
        deleteActionGroupAction: function (btn, id) {
            var dialogWindow = btn.closest(".dialog-window"),
                dialogContent = dialogWindow.find('.dialog-content-indent'),
                dialogButtons = dialogWindow.find('.dialog-buttons-gradient > .align-center');

            dialogContent.hide();
            dialogButtons.hide();

            if (!dialogWindow.find(".s-delete-confirm").length) {
                var $contentHtml = $("<div class='double-padded block s-delete-confirm'>" +
                    "<h1>" + $__("Are you sure?") + "</h1>" +
                    "<div class='margin-block'>" + $__("Actions inside this group WILL NOT BE DELETED, only group will disappear") + "</div>");
                var $buttonsHtml = $("<div class='align-center s-delete-confirm'>" + '<input type="submit" value="' + $__("Delete") + '" class="button red"> ' + $__('or') + " </div>");
                $(' <a href="#">' + $__('cancel') + '</a>').click(function () {
                    dialogContent.show();
                    dialogButtons.show();
                    $buttonsHtml.hide();
                    $contentHtml.hide();
                    return false;
                }).appendTo($buttonsHtml);
                dialogContent.after($contentHtml);
                dialogButtons.after($buttonsHtml);
            } else {
                dialogWindow.find(".s-delete-confirm").show();
            }
        },
        /* Добавление/редактирование действия */
        addAction: function (actionId) {
            $.form_actions.editAction(actionId, 'create');
        },
        editAction: function (actionId, create) {
            create = create || null;
            var self = this;
            self.highlightSidebar(create ? 0 : actionId);
            if (!self.isLoading()) {
                self.addLoading();
                $.form.cleanup();
                $.post("?module=actions", {action_id: actionId, form_id: $.form.currentForm, create: create}, function (response) {
                    self.hideLoading();
                    if (response) {
                        $(".s-actions-block").html(response);
                        !create && $.storage.set("multiform-lastaction-" + $.multiform.domainHash, ['edit', actionId]);
                        create && $.storage.del("multiform-lastaction-" + $.multiform.domainHash);
                        self.afterActionLoad();
                    } else {
                        $.multiform.showError($__("Page cannot be loaded"));
                        $(".s-actions-block").html($__("Page cannot be loaded"));
                    }
                });
            }
        },
        /* Изменение полей у действия */
        editActionFieldAction: function (btn) {
            btn.hide().next().show().siblings('span').hide();
        },
        /* После загрузки страницы с настройками действия */
        afterActionLoad: function () {
            this.initColorpicker();
            this.initSwitcher();
        },
        /* Сохранение измененных полей у действия */
        saveActionFieldAction: function (btn, id) {
            var i = btn.find('i');
            if (!i.hasClass("loading")) {
                i.addClass("loading");
                var elem = btn.siblings("textarea, input");
                var oldName = btn.closest("div").siblings("span").text();
                var form = btn.closest(".content").find(".fields:first > .hidden-settings");
                $.post("?action=handler", {data: 'saveActionField', name: elem.attr("name"), value: elem.val(), action_id: id, form_id: $.form.currentForm}, function (response) {
                    i.removeClass("loading").addClass("disk");
                    if (response.status == 'ok' && response.data) {
                        elem.val(response.data).closest("div").siblings("span").text(response.data).show();
                        $.form_actions.updateSidebar(id);
                        form.find("input[name='" + elem.attr("name") + "']").val(response.data);
                    } else {
                        btn.closest("div").siblings("span").text(oldName).show();
                        elem.val(oldName);
                        form.find("input[name='" + elem.attr("name") + "']").val(oldName);
                    }
                    btn.closest("div").hide().siblings("a").show();
                });
            }
        },
        editAutoxecuteAction: function (btn, id) {
            var oldTitle = btn.attr('title');
            var newTitle = btn.attr('data-title');
            var i = btn.find("i");
            if (!i.length) {
                btn.html("<i class='icon16 loading'></i>");
                var value = parseInt(btn.attr("data-value")) ? 0 : 1;
                var form = btn.closest(".content").find(".fields:first > .hidden-settings");
                $.post("?action=handler", {data: 'saveActionField', name: 'auto_execute', value: value, action_id: id, form_id: $.form.currentForm}, function (response) {
                    btn.html('AUTO');
                    if (response.status == 'ok') {
                        if (parseInt(response.data)) {
                            btn.removeClass("not");
                        } else {
                            btn.addClass("not");
                        }
                        btn.attr("data-value", response.data);
                        btn.attr("title", newTitle).attr('data-title', oldTitle);
                        $.form_actions.updateSidebar(id);
                        form.find("input[name='auto_execute']").val(response.data);
                    }
                });
            }
        },
        /* Изменение статуса у действия */
        changeActionStatus: function (btn) {
            if (!btn.hasClass("loading")) {
                btn.removeClass('lightbulb lightbulb-off').addClass('loading');
                var li = btn.closest('li'),
                    actionId = li.data('id');
                $.post("?action=handler", {data: 'actionStatus', value: parseInt(btn.attr("data-value")) ? 0 : 1, action_id: actionId, form_id: $.form.currentForm}, function (response) {
                    btn.removeClass('loading').addClass(response.data ? 'lightbulb' : 'lightbulb-off');
                    if ($(".action-content-"+actionId).length) {
                        $(".action-content-"+actionId).siblings('.s-ibutton-checkbox').find(".switcher").data('iButton').toggle();
                    } 
                    if (response.data) {
                        btn.attr("data-value", 1);
                        li.removeClass('not-visible');
                    } else {
                        btn.attr("data-value", 0);
                        li.addClass('not-visible');
                    }
                });
            }
            return false;
        },
        /* Удаление загруженных файлов */
        deleteFileAction: function (btn) {
            btn.closest('div').fadeOut(function () {
                $(this).remove();
            });
        },
        /* Обновление данных в сайдбаре */
        updateSidebar: function (actionId, forceSidebar) {
            var self = this,
                sidebar = forceSidebar === 'multiple' ? $(".s-inner-sidebar.act-multiple > ul") : ($(".s-inner-sidebar > ul li[data-id='" + actionId + "']").length ? $(".s-inner-sidebar > ul li[data-id='" + actionId + "']").parent() : $(".s-inner-sidebar.main > ul")),
                innerBlock = forceSidebar === 'multiple' ? $(".s-inner-sidebar.act-multiple") : sidebar.closest('.s-inner-sidebar'),
                isMultipleAction = innerBlock.hasClass("act-multiple"),
                url = isMultipleAction ? "?module=actions" : "?module=form&action=pageActions&id=" + $.form.currentForm,
                params = isMultipleAction ? {action_id: $.action_multiple.parentId, form_id: $.form.currentForm} : {};
            $.post(url, params, function (response) {
                sidebar.html($(response).find(".s-inner-sidebar." + (isMultipleAction ? 'act-multiple' : 'main') + ' > ul').html());
                self.highlightSidebar(actionId);
            });
        },
        /* Удаление действия */
        deleteActionAction: function (btn, actionId) {
            var dialogParams = {
                loading_header: $__("Wait, please..."),
                buttons: '<div class="align-center">'
                    + '<input type="submit" value="' + $__("Delete") + '" class="button red">'
                    + '<span class="errormsg" style="margin-left: 10px; display: inline-block"></span> '
                    + $__('or')
                    + ' <a href="#" class="cancel">' + $__('cancel') + '</a></div>',
                title: $__("Do you really want to delete action?"),
                height: '150px',
                'min-height': '150px',
                onClose: function () {
                    $("#delete-action-dialog").remove();
                },
                onSubmit: function (form) {
                    if (!form.hasClass("is-loading")) {
                        form.addClass("is-loading");
                        $.post("?action=handler", {data: 'deleteAction', action_id: actionId, id: $.form.currentForm}, function (response) {
                            if (response.status == 'ok') {
                                $.form.setHash(btn.closest('.s-actions-block-multiple').length ? "page/actions/edit/" + $.action_multiple.parentId : "#/page/actions/");
                            }
                            form.trigger('close');
                        });
                    }
                    return false;
                }
            };
            $("body").append("<div id='delete-action-dialog'></div>");
            var dialog = $("#delete-action-dialog");
            dialog.waDialog(dialogParams);
        },
        /* Сохранение настроек действий */
        saveActions: function (form, callback) {
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
                /* Валидация обязательных полей формы */
                if (this.validate.requiredFields.execute(form)) {
                    showError($__('Fill in the required fields'));
                    return false;
                }

                /* Валидация файлов */
                if (form.find(".multiform-temp-file.error").length) {
                    showError($__('Added files are not valid'));
                    return false;
                }

                /* Проведение оставшихся проверок */
                var stop = 0;
                $.each(this.validate.others, function (i, validateField) {
                    if (typeof validateField === 'function') {
                        var result = validateField(form);
                        if (!result) {
                            stop = true;
                            return false;
                        }
                    }
                });
                if (stop) {
                    showError($__('Fix the errors above'));
                    return false;
                }

                showLoading($__('Please, wait, while data is saving'));
                form.addClass("is-loading");
                $.ajax({
                    url: "?module=actions&action=save",
                    data: form.find(":input, textarea, select").serializeArray(),
                    dataType: "json",
                    type: "post",
                    crossDomain: false,
                    success: function (response) {
                        btn.show().next(".multiform-temp-loading").remove();
                        form.removeClass("is-loading");
                        if (response.status == 'ok' && response.data) {
                            /* Если имеются файлы, запускаем их загрузку */
                            var fileFields = form.find(".field.type-file").not(".multiform-hide");
                            if (fileFields.length) {
                                var first = "";
                                var totalFiles = 0;
                                fileFields.each(function () {
                                    var ffield = $(this);
                                    if (ffield.closest('.multiform-hide').length) {
                                        return true;
                                    }
                                    var tempFiles = ffield.find(".multiform-temp-file:not('.done')");
                                    totalFiles += tempFiles.length;
                                    if (!first && tempFiles.length) {
                                        first = tempFiles.first();
                                    }
                                });

                                if (first.length) {
                                    /* Общее количество файлов */
                                    form.attr('data-files', totalFiles).attr('data-files-done', 0);
                                    var file = first.data('file');
                                    if (file !== undefined) {
                                        showLoading($__('Files uploading'));
                                        file.formData['action_id'] = response.data.id;
                                        file.formData['param'] = $(file.fileInputClone[0]).attr('data-name');
                                        file.formData['form_id'] = response.data.form_id;
                                        file.submit();
                                        return false;
                                    }
                                }
                            }

                            if (response.data.has_files !== undefined) {
                                showLoading($__('Saving'));
                                $.post("?module=actions&action=save", {action_id: response.data.id, "isLast": 1, "form_id": response.data.form_id, "_csrf": form.find("input[name='_csrf']").val()}, function (response2) {
                                    $.form_actions.postExecute(form, response2, callback);
                                }, "json");
                            }
                            if (response.postExecute !== undefined) {
                                $.form_actions.postExecute(form, response, callback);
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
                                $.each(response.errors.fields, function (i, v) {
                                    $.form_actions.validate.addError(form.find(".field.action-field-" + v), "", false);
                                });
                            }
                        } else {
                            showError($__('Something wrong. Please, reload the page'));
                        }
                    },
                    error: function () {
                        showError($__('Something wrong. Please, reload the page'));
                        btn.show().next(".multiform-temp-loading").remove();
                        form.removeClass("is-loading");
                    }
                });
                return false;
            }
        },
        postExecute: function (form, response, callback) {
            var errormsg = form.find(".errorfld .errormsg");
            var btn = form.find("input[type='submit']");

            form.removeClass("is-loading");
            btn.show().next(".multiform-temp-loading").remove();

            if (typeof response === 'undefined') {
                errormsg.html($__('Data saved with errors')).parent().css('display', 'table');
                return false;
            }
            if (response.status == 'ok' && response.data) {
                if (typeof callback == 'function') {
                    callback.call($.multiform, response);
                    return false;
                }

                $.form_actions.updateSidebar(response.data.id);
                /* Перезагрузка страницы */
                if (typeof response.data.reload !== 'undefined') {
                    $.form.setHash('#/page/actions/edit/' + response.data.id);
                }
                btn.after('<span class="temp"><i class="icon16 yes"></i><span>' + $__('Saved') + '</span></span>');
                setTimeout(function () {
                    btn.next('.temp').remove();
                }, 4000);
                /* JS callback */
                if (typeof response.data.js_callback !== 'undefined') {
                    form.find(".fields").append(response.data.js_callback);
                }
            } else if (typeof response.errors !== 'undefined') {

                if (typeof response.errors.messages !== 'undefined') {
                    var errors = "";
                    $.each(response.errors.messages, function (i, v) {
                        errors += v + "<br>";
                    });
                    errormsg.html(errors).parent().css('display', 'table');
                }
                if (typeof response.errors.fields !== 'undefined') {
                    $.each(response.errors.fields, function (i, v) {
                        $.form_actions.validate.addError(form.find(".field.action-field-" + v), "", false);
                    });
                }
            } else {
                errormsg.html($__('Something wrong. Please, reload the page')).parent().css('display', 'table');
            }
        },
        /* Перезагрузка действия */
        reloadAction: function (form, actionId) {
            $.form.setHash(form.closest('.s-actions-block-multiple').length ? "/page/actions/edit/" + $.action_multiple.parentId + "/edit/" + actionId : "#/page/actions/edit/" + actionId);
            $.form.cleanup();
        },
        validate: {
            fieldIsEmpty: function (field) {
                this.addError(field, $__('Field is required'));
            },
            addError: function (field, error, onlyMessage) {
                var errormsg = field.hasClass("field") ? field.find(".errormsg") : field.closest(".field").find(".errormsg");
                var errorBlock = field.hasClass("field") ? field.find(".value") : field.closest(".value");
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
            requiredFields: {
                input: function (field) {
                    field = field.find("input, textarea, select");
                    if ($.trim(field.val()) == '') {
                        $.form_actions.validate.fieldIsEmpty(field);
                        return false;
                    }
                    return true;
                },
                textarea: function (field) {
                    return this.input(field);
                },
                checkbox: function (field) {
                    var checked = field.find(":checked");
                    if (!checked.length) {
                        $.form_actions.validate.fieldIsEmpty(field.find(".value"));
                        return false;
                    }
                    return true;
                },
                file: function (field) {
                    if (!field.find(".multiform-temp-file").length && !field.find(".multiform-loaded-files > div").length) {
                        $.form_actions.validate.fieldIsEmpty(field.find("input"));
                        return false;
                    }
                    return true;
                },
                execute: function (form) {
                    var that = this;
                    var required = false;
                    $.each(form.find(".s-required").not(".multiform-hide"), function (i, elem) {
                        elem = $(elem);
                        var type = 'input';
                        if (elem.find(":checkbox, :radio").length) {
                            type = 'checkbox';
                        } else if (elem.find("select").length) {
                            type = 'input';
                        } else if (elem.find("input").length) {
                            type = elem.find("input").attr('type');
                        }
                        if (typeof that[type] === 'undefined') {
                            type = 'input';
                        }
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
            others: {

            }
        },
        initFileupload: function (field, params) {
            var block = field.closest(".field"),
                form = field.closest("form");
            field.val("");

            /* Исключаем двойную инициализацию */
            if (field.data("blueimpFileupload") !== undefined) {
                return false;
            }

            field.fileupload({
                autoUpload: false,
                dataType: "json",
                url: "?module=actions&action=save"
            }).on("fileuploadadd", function (e, data) {
                var fieldBlock = $(this);
                var file = data.files[0];
                var error = false;
                var errormsg = "";

                /* Проверка на допустимое количество */
                if (params.maxCount == 1) {
                    block.find(".multiform-temp-file").remove();
                } else if (params.maxCount && params.maxCount <= block.find(".multiform-temp-file").length) {
                    block.find(".multiform-temp-file:first").remove();
                }

                if (!block.find(".multiform-files").length) {
                    block.find(".multiform-fileupload").closest("div").append("<div class=\"multiform-files\"></div>");
                }
                var filesBlock = block.find(".multiform-files");
                var size = parseFloat(file.size / 1000);
                var html = $("<div class=\"multiform-temp-file\">" +
                    "<div class=\"multiform-cell multiform-temp-file-name\">" + file.name + "</div>" +
                    "<div class=\"multiform-cell multiform-temp-file-size\">" + (size < 1000 ? size.toFixed(2) + " KB" : (size / 1000).toFixed(2) + " MB") + "</div>" +
                    "<div class=\"multiform-cell multiform-temp-file-icons\"></div>" +
                    "<div class=\"multiform-cell multiform-progress-field\" style=\"display: none;\"></div>" +
                    "</div>");
                $("<a href=\"javascript:void(0)\">" + $__("Delete") + "</a>").click(function () {
                    $(this).closest(".multiform-temp-file").remove();
                }).appendTo(html.find(".multiform-temp-file-icons"));

                /* Проверка на размер файла */
                if (params.maxSize && params.maxSize < file.size) {
                    html.addClass("error").find(".multiform-temp-file-size").addClass("error");
                    error = 1;
                    errormsg = $__("Check file size");
                }

                /* Проверка расширения */
                if (params.extensions !== undefined && params.extensions && params.extensions.length) {
                    var exts = params.extensions;
                    if (exts.length) {
                        var ext = file.name.substr(file.name.lastIndexOf(".") + 1).toLowerCase();
                        if ($.inArray(ext, exts) == -1) {
                            var lastIndex = file.name.lastIndexOf(".") + 1;
                            var name = file.name.substr(0, lastIndex) + "<span style=\"color: #ff0000\">" + file.name.substr(lastIndex) + "</span>";
                            html.addClass("error").find(".multiform-temp-file-name").html(name);
                            error = 1;
                            errormsg = (errormsg !== "" ? errormsg + ", " : "") + $__("Check file extension");
                        }
                    }
                }

                if (error) {
                    $.form_actions.validate.addError(fieldBlock.closest("div"), errormsg, true);
                } else {
                    /* Сохраняем данные о файлах, если нет ошибок */
                    html.data("file", data);
                    data.formData = {param: fieldBlock.data('name')};
                    data.fieldProcess = $(html);
                }

                filesBlock.append(html);
            }).on("fileuploadsubmit", function (e, data) {
                data.formData["_csrf"] = $(this).closest("form").find("input[name=\"_csrf\"]").val();
                if ((parseInt(form.attr("data-files-done")) + 1) === parseInt(form.attr("data-files"))) {
                    data.formData["isLast"] = 1;
                }
                data.fieldProcess.removeClass("error").find(".multiform-progress-field").html("<div class=\"progressbar green small\"><div class=\"progressbar-outer\"><div class=\"progressbar-inner\" style=\"width: 0%;\"></div></div></div><i class='icon16 loading'></i>").show().siblings().hide();
            }).on("fileuploadprogress", function (e, data) {
                var progress = parseInt(data.loaded / data.total * 90, 10);
                data.fieldProcess.find(".progressbar-inner").css("width", progress + "%");
            }).on("fileuploaddone", function (e, data) {
                var response = data._response.result;
                if (response && response.status == "ok") {
                    form.attr("data-files-done", parseInt(form.attr("data-files-done")) + 1);
                    if (parseInt(form.attr("data-files-done")) === parseInt(form.attr("data-files")) || (typeof response.postExecute !== "undefined")) {
                        if (form.closest('.s-actions-block-multiple').length) {
                            $.form_actions.postExecute(form, response, function (response) {
                                $.action_multiple.editAction(response.data.id);
                            });
                        } else {
                            $.form_actions.postExecute(form, response);
                        }
                    }
                    data.fieldProcess.addClass("done").data('uploaded', response.data.saved).find(".progressbar-inner").css("width", "100%");
                    setTimeout(function () {
                        data.fieldProcess.find(".multiform-progress-field").html($__("File uploaded"));
                    }, 500);

                    /* Следующий файл для загрузки */
                    var next = form.find(".multiform-temp-file").not(".done");
                    if (next.length) {
                        var file = next.first().data("file");
                        if (file !== undefined) {
                            file.formData["action_id"] = response.data.id;
                            file.formData["form_id"] = response.data.form_id;
                            file.submit();
                        }
                    }

                    /* Преобразуем временные файлы в активные */
                    if (typeof response.data.has_files !== "undefined") {
                        $.each(form.find(".multiform-temp-file.done"), function () {
                            var that = $(this);
                            var field = that.closest(".field");
                            var uploaded = that.data('uploaded');
                            that.remove();
                            if (uploaded !== undefined) {
                                $.each(uploaded, function (i, v) {
                                    var html = "";
                                    html += "<div>"
                                        + "<a href='#/delete/file/' title='" + $__('Delete') + "' class='s-delete js-action'>"
                                        + $__('Delete') + " <i class='icon10 no'></i>"
                                        + "</a>"
                                        + (v.isImage ? "<img src='" + v.url + "'>" : "<a href='?module=actions&action=downloadFile&hash=" + v.hash + "' title='" + $__("Download") + "'>" + v.filename + "</a>")
                                        + "<input type='hidden'  name='data[" + v.param + "][" + v.hash + "]' value='" + v.filename + "'>"
                                        + "</div>";
                                    field.find('.multiform-loaded-files').append(html);
                                });
                            }
                        });
                    }
                } else {
                    $(this).data("blueimpFileupload")._trigger("fail", null, data);
                }
            }).on("fileuploadfail", function (e, data) {
                form.attr("data-files-done", 0);
                form.find(".multiform-temp-file").removeClass("done").find(".multiform-progress-field").hide().siblings().show();
                data.fieldProcess.addClass("error");
                form.find(".multiform-submit input").show().siblings(".multiform-temp-loading").remove();
                var response = data._response.result;
                var errors = "";
                if (typeof response === "undefined") {
                    errors = $__("Data saved with errors");
                } else if (typeof response.errors !== "undefined" && typeof response.errors.messages !== "undefined") {
                    $.each(response.errors.messages, function (i, v) {
                        errors += v + "<br>";
                    });
                } else {
                    errors += $__("Cannot save files");
                }
                form.removeClass("is-loading").find('input[type="submit"]').show().next(".multiform-temp-loading").remove();
                form.find(".errorfld .errormsg").html(errors).parent().css('display', 'table');
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
        /* Инициализируем переключатель (switcher) */
        initSwitcher: function () {
            $('.switcher:not(.inited)').iButton({labelOn: "", labelOff: "", className: 'mini', init: function (input) {
                    input.addClass('inited');
                }}).change(function () {
                var onLabelSelector = '#' + this.id + '-on-label',
                    offLabelSelector = '#' + this.id + '-off-label',
                    that = $(this),
                    ibuttonBlock = that.closest(".s-ibutton-checkbox");
                if (ibuttonBlock.hasClass("is-loading")) {
                    return false;
                }
                if (!this.checked) {
                    $(onLabelSelector).addClass('unselected');
                    $(offLabelSelector).removeClass('unselected');
                } else {
                    $(onLabelSelector).removeClass('unselected');
                    $(offLabelSelector).addClass('unselected');
                }
                if (that.hasClass("dynamic")) {
                    ibuttonBlock.addClass("is-loading");
                    that.data('iButton').disable(true);
                    var form = that.closest(".content").find(".fields:first > .hidden-settings");
                    $.post("?action=handler", {data: 'actionStatus', value: this.checked ? 1 : 0, action_id: that.data('id'), form_id: $.form.currentForm}, function (response) {
                        ibuttonBlock.removeClass("is-loading");
                        that.data('iButton').disable(false);
                        $.form_actions.updateSidebar(that.data('id'));
                        form.find("input[name='custom_status']").val(response.data);
                    });
                }
            })
        },
        /* Подсвечиваем активное действие */
        highlightSidebar: function (actionId) {
            var sidebar = $(".actions-tab .s-inner-sidebar li[data-id='" + actionId + "']").parent();
            sidebar.find('li').removeClass('selected').siblings("[data-id='" + actionId + "']").addClass('selected');
        },
        isLoading: function () {
            return $(".s-actions-block").hasClass("is-loading");
        },
        addLoading: function () {
            if (!this.isLoading()) {
                $(".s-actions-block").addClass("is-loading");
                $(".s-actions-block").html($__("Loading") + "... <i class='icon16 loading'></i>");
            }
        },
        hideLoading: function () {
            $(".s-actions-block").removeClass("is-loading").html('');
        }
    };
})(jQuery);