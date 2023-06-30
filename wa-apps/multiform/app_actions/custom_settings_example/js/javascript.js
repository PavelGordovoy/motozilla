$(function () {
    $.custom_settings_example = {
        init: function () {
            var self = this;
            /* 
             * Данный класс создается автоматически исходя из ID вашего действия
             * This class generated automatically based on your action ID 
             * */
            $(".actions-tab .s-custom-settings-example").submit(function () {
                self.saveHandler($(this));
                return false;
            });
        },
        /* 
         * Сохранение настроек действий 
         * Saving process
         * */
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

                /* 
                 * Валидация обязательных полей формы 
                 * Required fields validate
                 * */
                if ($.form_actions.validate.requiredFields.execute(form)) {
                    showError($__('Fill in the required fields'));
                    return false;
                }

                showLoading($__('Please, wait, while data is saving'));
                form.addClass("is-loading");
                $.ajax({
                    /* 
                     * Укажите свой путь для обработки данных 
                     * Specify your own path for saving date
                     * */
                    url: "?module=customSettingsExampleAction&action=save",
                    data: form.find(":input, textarea, select").serializeArray(),
                    dataType: "json",
                    type: "post",
                    crossDomain: false,
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
                    error: function () {
                        showError($__('Something wrong. Please, reload the page'));
                        btn.show().next(".multiform-temp-loading").remove();
                        form.removeClass("is-loading");
                    }
                });
                return false;
            }
        }
    };
    $.custom_settings_example.init();
});

