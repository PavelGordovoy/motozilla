var helpdeskProSettings = (function ($) {
    helpdeskProSettings = function (options) {
        var that = this;

        // DOM
        that.$form = options.$form;
        that.$submitBtn = that.$form.find("input[type='submit']");

        // DYNAMIC VARS
        that.is_locked = false;

        // INIT
        that.initClass();
    };

    helpdeskProSettings.prototype.initClass = function () {
        var that = this;

        that.bindEvents();
    };

    helpdeskProSettings.prototype.bindEvents = function () {
        var that = this;

        that.$form.on("submit", function (event) {
            event.preventDefault();
            if (!that.is_locked) {
                that.save();
            }
            return false;
        });

        /* Открытие настроек для разработчиков */
        that.$form.find(".js-developer-settings").click(function () {
            new helpdeskProDialog({
                onSuccess: function () {
                    this.refresh({url: "?plugin=pro&module=dialog&action=developerSettings", html: ''});
                    return false;
                },
                onOpen: function () {
                    var d = this;
                    /* Открытие продуктов */
                    d.$form.on("click", ".js-show-products", function (event) {
                        event.preventDefault();
                        new helpdeskProDialog({
                            onSuccess: function () {
                                this.refresh({url: "?plugin=pro&module=dialog&action=developerProducts", html: ''});
                                return false;
                            },
                            onOpen: function () {
                                var dd = this;
                                $("<a href='javascript:void(0)' class='back' title='" + $__('Back') + "'>&larr;Назад</a>").click(function () {
                                    d.refresh({url: "?plugin=pro&module=dialog&action=developerSettings", html: ''});
                                    dd.close();
                                }).prependTo(dd.$wrapper.find(".w-dialog-header h1"));
                            },
                            url: "?plugin=pro&module=dialog&action=developerProducts",
                            saveUrl: "?plugin=pro&module=developer&action=saveProducts"
                        });
                        d.close();
                    });
                },
                url: "?plugin=pro&module=dialog&action=developerSettings",
                saveUrl: "?plugin=pro&module=developer&action=saveSettings"
            });
            return false;
        });
    };

    helpdeskProSettings.prototype.save = function () {
        var that = this,
            successTimeout = null;
        if (!that.is_locked) {
            that.is_locked = true;

            addLoading();
            errorText();
            successTimeout && clearTimeout(successTimeout);

            $.ajax({
                url: "?plugin=pro&module=settings&action=save",
                dataType: "json",
                type: "post",
                data: that.$form.serializeArray(),
                success: function (response) {
                    that.is_locked = false;

                    removeLoading();

                    if (typeof response !== 'undefined' && response.status == 'fail' && response.errors) {
                        errorText(response.errors);
                    } else {
                        successMessage();
                    }
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
            removeLoading();
            that.$submitBtn.after("<i class='icon16 loading'></i>");
        }

        function successMessage() {
            that.$submitBtn.after("<i class='icon16 yes'></i>");
            successTimeout = setTimeout(function () {
                removeLoading();
            }, 3000);
        }

        function removeLoading() {
            that.$submitBtn.next("i").remove();
        }

        function errorText(text) {
            text = text || '';
            that.$form.find('.errormsg').html(text);
        }
    };

    return helpdeskProSettings;
})(jQuery);
