(function ($) {
    $.form_notifications = {
        initPageNotifications: function () {

            // Редактор
            $('.redactor-editor').redactor({
                deniedTags: false,
                plugins: ['source', 'fontcolor', 'fontsize', 'fontfamily'],
                lang: $.multiform.lang
            });

            // Сохранение формы
            $("#s-form-notifications-save").submit(function () {
                $.form_notifications.saveHandler($(this));
                return false;
            });
        },
        // Сохранение формы
        saveHandler: function (form) {
            var btn = form.find("input[type='submit']");
            if (!btn.next(".loading").length) {
                btn.after("<i class='icon16 loading'></i>");
                var i = btn.next("i");
                form.find('.errormsg').html('');

                $.ajax({
                    url: "?module=form&action=notificationsSave",
                    dataType: "json",
                    type: "post",
                    data: form.serializeArray(),
                    success: function (response) {
                        if (response.status == 'ok') {
                            i.removeClass("loading").addClass("yes");
                        }
                        if (response.status == 'fail' && response.errors) {
                            i.removeClass("loading").addClass("cross");
                            if (typeof response.errors.messages !== 'undefined') {
                                for (var error in response.errors.messages) {
                                    form.find('.errormsg').append(response.errors.messages[error] + '<br>');
                                }
                            }
                            if (typeof response.errors.fields !== 'undefined') {
                                for (var field in response.errors.fields) {
                                    form.find(response.errors.fields[field]).addClass("error");
                                }
                            }
                        }
                        setTimeout(function () {
                            i.remove();
                        }, 3000);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (console) {
                            console.log(jqXHR, textStatus, errorThrown);
                        }
                        i.remove();
                        $.multiform.showError($__("Error"));
                    }
                });

            }
            return false;
        }
    };
})(jQuery);