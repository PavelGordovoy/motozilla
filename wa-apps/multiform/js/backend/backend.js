(function ($) {
    $.backend = {
        init: function () {
            this.fixRightToolbar();
        },
        // Изменение статуса формы, поля 
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
                        if (btn.data("type") == 'fieldStatus') {
                            if (response.data) {
                                btn.closest(".form-builder-row").removeClass("not-visible");
                            } else {
                                btn.closest(".form-builder-row").addClass("not-visible");
                            }
                        }
                    } else {
                        i.addClass(oldClass);
                    }
                });
            }
        },
        // Сортировка  
        orderByAction: function (btn, field, ext) {
            ext = ext || '';
            if (!$.multiform.isLoading()) {
                $.multiform.showLoading();
                $.post(location.href, {field: field, direction: $(btn).find("i").hasClass("darr") ? 1 : 0, ext: ext}, function (response) {
                    $(".zebra.black").replaceWith($(response).find(".zebra.black"));
                    $.multiform.hideLoading();
                }, "html");
            }
        },
        // Копирование формы 
        copyFormAction: function (btn, id) {
            var i = btn.find("i");
            if (!i.hasClass("loading")) {
                i.removeClass().addClass("icon16 loading");
                $.post("?action=handler", {data: "copyForm", id: id}, function (response) {
                    i.removeClass();
                    if (response.status == 'ok') {
                        btn.closest("tr").after(response.data);
                    }
                    i.addClass("icon16 icon-custom copy");
                });
            }
        },
        // Удаление формы 
        deleteFormAction: function (btn, id) {
            $("#backend-delete-form-dialog").waDialog({
                'height': '180px',
                'width': '600px',
                'class': 'dialog-red',
                'content': '<h1 class="align-center">' + $__('Attention') + '</h1><div class="margin-block align-center" style="margin-top: 30px">' + $__('If you delete this form, <b>all records</b> with files and data will be <b>removed also</b>. Are you sure?') + '</div>',
                'buttons': '<div class="align-center"><input type="submit" value="' + $__('Delete anyway') + '" class="button red"> ' + $__('or') + ' <a class="button yellow cancel" href="javascript:void(0)">' + $__('cancel') + '</a></div>',
                disableButtonsOnSubmit: false,
                onSubmit: function () {
                    var form = $(this);
                    if (!form.find(".temp").length) {
                        form.find("input[type='submit']").after("<i class='icon16 loading temp'></i>");
                        $.post("?action=handler", {data: "deleteForm", id: id}, function (response) {
                            $(".dialog").remove();
                            if (response.status == 'ok') {
                                btn.closest("tr").fadeOut(function () {
                                    $(this).remove();
                                });
                                $.multiform.showError($__("Form removed"));
                            } else {
                                $.multiform.showError($__("Error. Check log files"));
                                i.addClass("icon16 delete");
                            }
                        });
                    }
                    return false;
                }
            });
        },
        // make right toolbar fixed for available even in scrolling
        fixRightToolbar: function () {
            $.backend.fixRightToolbar._handler = function handler() {
                var toolbar = $('#p-toolbar');
                if (!toolbar.length) {
                    return;
                }
                if (toolbar.hasClass('rendered')) {
                    toolbar.removeClass('p-fixed');
                    return;
                }
                if (!handler.top) {
                    handler.top = toolbar.children(':first').offset()['top'];
                }
                if ($(this).scrollTop() > handler.top) {
                    toolbar.addClass('p-fixed');
                } else {
                    toolbar.removeClass('p-fixed');
                }
            };
            // check right now (maybe document is scrolled)
            $.backend.fixRightToolbar._handler.call(document);
            // bind handler
            $(document).bind('scroll', $.backend.fixRightToolbar._handler);
        },
        // Изменение темы дизайна для формы
        changeTheme: function (select) {
            if (!$.multiform.isLoading()) {
                $.multiform.showLoading($__('Saving'));
                $.post("?action=handler", {data: 'useTheme', id: $(select).closest("tr").data('id'), theme_id: select.value}, function (response) {
                    $.multiform.hideLoading();
                }, "html");
            }
        }
    };
})(jQuery);