(function ($) {
    $.form_records = {
        init: function () {
            $.storage = new $.store();
        },
        initPageRecords: function () {

            this.changeFieldsVisibility();

            // Проставление отметок для формы выбора полей
            $(".records-tab .sort-field").each(function () {
                var that = $(this);
                if (!that.hasClass("hidden")) {
                    $(".records-tab .more-fields .column-" + that.data("column")).append("<i class='icon16 yes'></i>");
                }
            });

            // Постраничная навигация
            $(document).on('click', ".records-tab .pagination > ul a", function () {
                var that = $(this);
                that.parent().siblings().removeClass("selected");
                that.closest("ul").find("a[data-page='" + that.data("page") + "']").parent().addClass("selected");
                var params = $.form_records.getOrderParams("order-" + $.form.currentForm) || {};
                if (typeof params === 'string') {
                    params = {};
                }
                params['page'] = parseInt(that.data("page"));
                $.form_records.setOrderParams(params);
                $.form_records.orderByAction();
                return false;
            });

            /* Выбор фильтров в поиске */
            $(".search-block :checkbox").change(function () {
                var len = $(".search-block :checked").length;
                if (len) {
                    $(".search-block .s-name span").text($__('Selected') + ' ' + len);
                } else {
                    $(".search-block .s-name span").text($__('Everywhere'));
                }
                $.form_records.bindSearchChange();
            });

            // Сохранение комментариев
            $(document).on('click', ".record-comment-form input[type='submit']", function () {
                var btn = $(this);
                var form = btn.closest("form");
                var errormsg = form.find(".errormsg");
                var name = $.trim(form.find("input[name='name']").val());
                var comment = $.trim(form.find("textarea[name='comment']").val());

                if (!name) {
                    errormsg.text($__('Specify name'));
                    return false;
                }
                if (!comment) {
                    errormsg.text($__('Specify comment'));
                    return false;
                }

                if (!btn.next(".loading").length) {
                    errormsg.text('');
                    btn.after("<i class='icon16 loading'></i>");
                    $.post("?module=backend&action=handler", {data: 'saveRecordComment', record_id: form.find("input[name='record_id']").val(), name: name, comment: comment}, function (response) {
                        btn.next(".loading").remove();
                        if (response.status == 'ok') {
                            form.closest(".record-comment-block").find(".record-comments").append(response.data);
                            form.find("input[name='name']").val('');
                            form.find("textarea[name='comment']").val('');
                        } else {
                            errormsg.html(response.errors);
                        }
                    });
                }

                return false;
            });

            // Выделение строк таблицы
            $(document).on("click", ".f-checkbox-delete.all", function () {
                var that = $(this);
                if (that.prop("checked")) {
                    $(".records-tab td .f-checkbox-delete").prop("checked", true).closest("tr").addClass("highlight");
                } else {
                    $(".records-tab td .f-checkbox-delete").prop("checked", false).closest("tr").removeClass("highlight");
                }
                $.form_records.updateIndicator();
            });

            $(document).on('multiform-form-submitted', ".multiform-wrap", function () {
                $(this).closest(".content").find(".f-edit").click();
            });
        },
        // Выводим только те поля, которые были уже отобраны
        changeFieldsVisibility: function (data) {
            data = data || $(".records-tab");
            try {
                var fields = $.storage.get("form-" + $.form.currentForm);
            } catch (e) {
                fields = null;
            }
            if (fields) {
                data.find("table.black td").add(data.find("table.black .sort-field")).addClass("hidden");
                $.each(fields, function (i, field) {
                    data.find(".column-" + field).removeClass("hidden");
                    data.find("table.black td.n").removeClass("hidden");
                });
            }
            return data;
        },
        // Появление / исчезание формы для выбора полей 
        recordsChangeColumnsAction: function (btn) {
            btn.next().toggle();
        },
        // Добавление / удаление полей 
        recordsAddColumnAction: function (btn) {
            if (btn.find("i").length) {
                btn.find("i").remove();
                $(".records-tab table.black .column-" + btn.data("column")).addClass("hidden");
            } else {
                btn.append("<i class='icon16 yes'></i>");
                $(".records-tab table.black .column-" + btn.data("column")).removeClass("hidden");
            }
            var fields = $(".records-tab .sort-field").not(".hidden").map(function () {
                return $(this).data("column");
            });
            // Меняем атрибут объединения колонок для открытой записи
            $(".records-tab .record-page > td").attr("colspan", fields.length + 1);
            // Запоминаем видимые поля в хранилище
            $.storage.set("form-" + $.form.currentForm, fields);
        },
        // Сортировка  
        orderByAction: function (btn, field, ext) {
            if (!$.multiform.isLoading()) {
                var params = this.getOrderParams("order-" + $.form.currentForm) || {};
                if (typeof params === 'string') {
                    params = {};
                }
                if (field) {
                    params['field'] = field;
                }
                if (typeof ext !== 'undefined') {
                    params['ext'] = ext;
                }
                if (btn) {
                    params['direction'] = $(btn).find("i").hasClass("darr") ? 1 : 0;
                }
                $.multiform.showLoading();
                $.post("?module=form&action=pageRecords&id=" + $.form.currentForm, params, function (response) {
                    var data = $.form_records.changeFieldsVisibility($(response));
                    $("table.black").html(data.find("table.black").length ? data.find("table.black").html() : '');
                    $(".search-block .top-right").html($(response).find(".search-block .top-right").length ? $(response).find(".search-block .top-right").html() : '');
                    $(".records-tab .pagination ul.menu-h").remove();
                    $(".records-tab .pagination").prepend($(response).find(".pagination ul.menu-h"));
                    $.form_records.setOrderParams(params);
                    $.form_records.clean();
                    $.multiform.hideLoading();
                    $.form_records.bindSearchChange();
                }, "html");
            }
        },
        setOrderParams: function (params) {
            $.storage.set("order-" + $.form.currentForm, params);
        },
        getOrderParams: function () {
            let params = {};
            try {
                params = $.storage.get("order-" + $.form.currentForm);
            } catch (e) { }
            return params;
        },
        // Вывод информации о записи
        selectRecord: function (that, record_id) {
            if (that.closest("table.black").hasClass("edited")) {
                var checkbox = that.find(".f-checkbox-delete");
                if (checkbox.prop("checked")) {
                    checkbox.prop("checked", false);
                    that.removeClass("highlight");
                } else {
                    checkbox.prop("checked", true);
                    that.addClass("highlight");
                }
                this.updateIndicator();
                return false;
            }
            if (!$.multiform.isLoading()) {
                this.clean();
                if (that.hasClass("highlight")) {
                    $(".records-tab .record-page").remove();
                    that.removeClass("highlight");
                    return false;
                }
                $(".records-tab .record-page").remove();
                that.addClass("highlight").siblings().removeClass("highlight");
                that.after("<tr class='record-page'><td colspan='" + that.find("td").not(".hidden").length + "'><div class='content relative clearfix'>" + $__("Loading") + "... <i class='icon16 loading'></i></div></td></tr>");
                $.multiform.showLoading();
                $.post("?module=form&action=pageRecord&id=" + $.form.currentForm, {record_id: record_id}, function (response) {
                    that.next(".record-page").find(".content").html($(response).html());
                    $.multiform.hideLoading();
                }, "html");
            }
        },
        // Редактирование записи
        recordEditAction: function (btn, record_id) {
            if (!$.multiform.isLoading()) {
                this.clean();
                btn.find("i").removeClass().addClass("icon16 loading");
                $.multiform.showLoading($__("Loading form..."));
                if (!btn.hasClass("edited")) {
                    $.post("?module=backend&action=handler", {data: 'editRecord', record_id: record_id, id: $.form.currentForm}, function (response) {
                        $(".records-tab .record-page .multiform-wrap").html(response.data);
                        btn.addClass("edited").find("i").removeClass().addClass("icon16 cheatsheet");
                        btn.find("span").text($__("View"));
                        $.multiform.hideLoading();
                    }, "json");
                } else {
                    $.post("?module=form&action=pageRecord&id=" + $.form.currentForm, {record_id: record_id}, function (response) {
                        $(".records-tab .record-page .multiform-wrap").html($(response).find(".multiform-wrap").html());
                        btn.removeClass("edited").find("i").removeClass().addClass("icon16 edit");
                        btn.find("span").text($__("Edit"));
                        $.multiform.hideLoading();
                    }, "html");
                }
            }
            return false;
        },
        // Удаляем мусор
        clean: function () {
            $(".Zebra_DatePicker").remove();
        },
        // Удаление файлов
        recordDeleteFileAction: function (btn, hash) {
            if (!confirm($__("Do you really want to delete this file?"))) {
                return false;
            }
            btn.find("i").removeClass("no").addClass("loading");
            $.post("?module=backend&action=handler", {data: 'deleteFile', hash: btn.data("hash")}, function (response) {
                btn.closest("li").fadeOut(function () {
                    $(this).remove();
                });
            }, "json");
        },
        // Отправка записи на email
        recordEmailAction: function (btn, record_id) {
            $("#record-email-dialog").waDialog({
                'height': '150px',
                'width': '550px',
                'class': 'email-dialog',
                'content': '<h1 class="align-center">' + $__('Send record to email list') + '</h1><div class="margin-block align-center" style="margin-top: 30px"><input style="width: 100%; height: 30px" name="emails" type="text"><div class="small">' + $__('Use commas to separate emails. 10 emails max') + '</div></div>',
                'buttons': '<div class="align-center"><input type="submit" value="' + $__('Send') + '" class="button green"> ' + $__('or') + ' <a class="button red cancel" href="javascript:void(0)">' + $__('cancel') + '</a></div>',
                disableButtonsOnSubmit: false,
                onSubmit: function () {
                    var form = $(this);
                    if (!form.find(".temp").length) {
                        form.find("input[type='submit']").after("<i class='icon16 loading temp'></i>");
                        $.post("?action=handler", {data: 'recordSendEmail', record_id: record_id, emails: form.find("input[type='text']").val()}, function (response) {
                            form.find(".temp").remove();
                            var message = response.status == 'ok' ? "<span style='color: green;'>" + $__("Sended successfully!") + "</span>" : "<span style='color: #ff0000;'>" + $__("Failed!") + "</span>";
                            form.find(".margin-block input").val('').hide().after(message);
                            setTimeout(function () {
                                form.find(".margin-block input").show().next("span").remove();
                            }, 3000);
                        }, "json");
                    }
                    return false;
                }
            });
        },
        // Удаление записи
        recordDeleteAction: function (btn, record_id) {
            if (!confirm($__("Do you really want to delete this record? All files will be removed also."))) {
                return false;
            }
            if (!$.multiform.isLoading()) {
                btn.find("i").removeClass().addClass("icon16 loading");
                $.multiform.showLoading($__("Removing the record..."));
                $.post("?module=backend&action=handler", {data: 'deleteRecord', record_id: record_id}, function (response) {
                    btn.closest("tr").siblings(".highlight").remove();
                    $(".records-tab .record-page").remove();
                    $.form_records.clean();
                    $.multiform.hideLoading();
                }, "json");
            }
            return false;
        },
        // Удаление комментария
        recordDeleteCommentAction: function (btn, comment_id) {
            if (!confirm($__("Do you really want to delete this comment?"))) {
                return false;
            }
            btn.find("i").removeClass("no").addClass("loading");
            $.post("?module=backend&action=handler", {data: 'deleteRecordComment', comment_id: comment_id}, function (response) {
                btn.closest(".record-comment").fadeOut(function () {
                    $(this).remove();
                });
            });
        },
        // Экспорт записей
        recordsExportAction: function (btn) {
            var pull = [];
            var stopExport = false;
            $("#records-export-dialog").waDialog({
                'height': '200px',
                'width': '550px',
                'buttons': '<div class="align-center"><input type="submit" value="' + $__('Export') + '" class="button green"> ' + $__('or') + ' <a class="button red cancel" href="javascript:void(0)">' + $__('cancel') + '</a></div>',
                disableButtonsOnSubmit: false,
                onSubmit: function (d) {
                    d.find('.dialog-buttons').hide();

                    var form = $("#s-export-progressbar");

                    var showError = function (error) {
                        form.find('.errormsg').show().find("span").text(error);
                    };

                    $("#s-export-report").html('').hide();
                    $("#records-export-dialog form :submit").prop("disabled", true);

                    $('#s-scanning-progressbar').hide();

                    d.find("p").hide();

                    form.show();
                    form.find('.progressbar .progressbar-inner').css('width', '0%');
                    form.find('.progressbar-description').text('0.000%');
                    form.find('.progressbar').show();

                    var url = $(this).attr('action');
                    var processId;

                    var cleanup = function () {
                        $.post(url, {processId: processId, cleanup: 1}, function (r) {
                            form.hide();
                            if (r.report) {
                                $("#s-export-report").append(r.report).show();
                            }
                        }, 'json');
                    };

                    var step = function (delay) {
                        delay = delay || 2000;
                        if (stopExport) {
                            return false;
                        }
                        var timer_id = setTimeout(function () {
                            $.post(url, {processId: processId}, function (r) {
                                if (!r) {
                                    step(3000);
                                } else {
                                    if (r && r.error_message) {
                                        showError(r.error_message);
                                        return false;
                                    }
                                    if (r && r.ready) {
                                        form.find('.progressbar .progressbar-inner').css({
                                            width: '100%'
                                        });
                                        form.find('.progressbar-description').text('100%');
                                        cleanup();
                                    } else if (r && r.error) {
                                        showError(r.error);
                                    } else {
                                        if (r && r.progress) {
                                            var progress = parseFloat(r.progress.replace(/,/, '.'));
                                            form.find('.progressbar .progressbar-inner').animate({
                                                'width': progress + '%'
                                            });
                                            form.find('.progressbar-description').text(r.progress);
                                        }
                                        if (r && r.warning) {
                                            form.find('.progressbar-description').append('<i class="icon16 exclamation"></i><p>' + r.warning + '</p>');
                                        }
                                        step();
                                    }
                                }
                            }, 'json').error(function () {
                                step(3000);
                            });
                        }, delay);
                        pull.push(timer_id);
                    };
                    $.post(url, {form_id: $.form.currentForm}, function (r) {
                        if (r && r.processId) {
                            if (r && r.error_message) {
                                showError(r.error_message);
                                return false;
                            }
                            processId = r.processId;
                            step(1000);   // invoke Runner
                            step();         // invoke Messenger
                        } else if (r && r.error) {
                            showError(r.error);
                        } else {
                            showError($__('Server error'));
                        }
                    }, "json").error(function () {
                        showError($__('Server error'));
                    });

                    return false;
                },
                onClose: function () {
                    var timer_id = pull.pop();
                    while (timer_id) {
                        clearTimeout(timer_id);
                        timer_id = pull.pop();
                    }
                    $('#s-export-progressbar').hide();
                    $("#s-export-report").hide();

                    $("#records-export-dialog").find('.dialog-buttons').show();
                    $("#records-export-dialog p").show();
                    $("#s-export-progressbar .errormsg").hide();
                    stopExport = true;
                }
            });
        },
        // Удаление записей
        recordsDeleteAllAction: function (btn) {
            if (btn.hasClass("f-selected") || btn.hasClass("f-all")) {
                var params = {data: 'deleteRecord'};
                var confirmMessage = "";
                if (btn.hasClass("f-selected")) {
                    var ids = $.makeArray($(".records-tab td .f-checkbox-delete:checked").map(function () {
                        return $(this).val();
                    }));
                    if (!ids.length) {
                        alert($__("Select record"));
                        return false;
                    }
                    params['record_id'] = ids;
                    confirmMessage = $__("Do you really want to delete selected records?");
                } else {
                    params['all'] = $.form.currentForm;
                    confirmMessage = $__("Do you really want to delete all records?");
                }
                if (!confirm(confirmMessage)) {
                    return false;
                }
                if (!btn.find("i").hasClass("loading")) {
                    btn.find("i").addClass("loading").removeClass("delete");
                    $.post("?action=handler", params, function () {
                        if (btn.hasClass("f-selected")) {
                            var query = "1";
                            for (var i in ids) {
                                query += ", .records-tab tr[data-id='" + ids[i] + "']";
                            }
                            $(query).remove();
                            btn.find("i").removeClass("loading").addClass("delete");
                        } else {
                            $(".records-tab").html("<p class='align-center'>" + $__("You don't have any records") + "</p>");
                        }
                        $.form_records.updateIndicator();
                        $.form_records.clean();
                    });
                }
            } else if (btn.hasClass("f-cancel")) {
                btn.parent().hide().siblings(".f-button-delete").hide().siblings(".f-choise").show();
                $(".records-tab table.black th.n span, .records-tab table.black td.n span").show();
                $(".records-tab table.black th.n input, .records-tab table.black td.n input").hide();
                $(".records-tab table.black").removeClass("edited");
                $(".records-tab tr").removeClass("highlight");
                this.updateIndicator();
            } else {
                btn.parent().hide().siblings(".f-button-delete").show();
                $(".records-tab table.black th.n span, .records-tab table.black td.n span").hide();
                $(".records-tab table.black th.n input, .records-tab table.black td.n input").show();
                $(".records-tab table.black").addClass("edited");
            }
        },
        // Обновление индикатора выделенных записей
        updateIndicator: function (number) {
            number = number || $(".records-tab td .f-checkbox-delete:checked").length;
            $(".records-tab .f-button-delete .f-selected .indicator").text(number);
        },
        // Печать записи
        recordPrintAction: function (btn) {
            var mywindow = window.open('', 'my div', 'height=400,width=600');
            mywindow.document.write('<html><head><title>my div</title>');
            mywindow.document.write('</head><body >');
            mywindow.document.write(btn.closest(".record-page").find(".multiform-gap-fields").html());
            mywindow.document.write('</body></html>');

            mywindow.document.close(); // necessary for IE >= 10
            mywindow.focus(); // necessary for IE >= 10

            mywindow.print();
            mywindow.close();

            return true;
        },
        /* При редактировании записи отменить обязательное поле */
        skipRequiredAction: function (btn) {
            var field = btn.closest('.multiform-gap-field');
            if (!field.find('.s-skip-required').length) {
                field.find('.multiform-gap-value').append("<input type='checkbox' class='s-skip-required' style='display: none' name='skip_required[" + field.data('field-id') + "]' value='1' cehcked>");
            }
            var skipField = field.find('.s-skip-required'),
                oldTitle = btn.attr('title'),
                newTitle = btn.attr('data-title');
            btn.text(newTitle).attr('data-title', oldTitle).attr('title', newTitle);
            field.toggleClass('s-required');
            skipField.prop('checked', !skipField.prop('checked'));
        },
        /* Поиск записей */
        submitSearchAction: function (form) {
            var params = $.form_records.getOrderParams("order-" + $.form.currentForm) || {};
            if (typeof params === 'string') {
                params = {};
            }
            var searchField = $(form).find("input[type='search']");
            params['query'] = searchField.val();
            if (searchField.attr('placeholder') == params['query']) {
                params['query'] = '';
            }
            params['query-filters'] = $.makeArray($(form).find(":checked").map(function () {
                return $(this).val();
            }));

            if (params['query'] == '' && !params['query-filters'].length) {
                delete params['query'];
                delete params['query-filters'];
            }

            $.form_records.setOrderParams(params);
            $.form_records.orderByAction();
            return false;
        },
        /* Сброс результатов поиска */
        resetRecordsSearchAction() {
            var checked = $(".search-block :checked"),
                first = checked.first();
            checked.prop('checked', false);
            first.change();
            $(".search-block input[type='search']").val('');
            $('.search-block .reset-search').hide();
            $(".search-block form").submit();
        },
        /* Проверить, изменялись ли данные поиска, чтобы скрыть или показать сслыку сброса */
        bindSearchChange: function () {
            if (!$(".search-block :checked").length && $(".search-block input[type='search']").val() == '') {
                $('.search-block .reset-search').hide();
            } else {
                $('.search-block .reset-search').show();
            }
        }
    };
    $(function () {
        $.form_records.init();
    });
})(jQuery);