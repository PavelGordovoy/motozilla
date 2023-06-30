(function ($) {
    $.settings = {
        init: function () {
            var that = this;
            // Сохранение масок
            $("#settings-form input[type='submit']").click(function () {
                that.onSave($(this));
                return false;
            });
        },
        onSave: function (btn) {
            var form = btn.closest("form");
            form.find(".errormsg").text('');
            btn.next("i").remove();
            btn.prop('disabled', true).after("<i class='icon16 loading temp-loader'></i>");
            $.ajax({
                url: "?module=settings&action=save",
                data: form.serializeArray(),
                dataType: "json",
                type: "post",
                success: function (response) {
                    btn.prop('disabled', false).next(".temp-loader").remove();
                    if (response.status == 'ok' && response.data) {
                        btn.after("<i class='icon16 yes'></i>");
                    } else {
                        btn.after("<i class='icon16 no'></i>");
                    }
                },
                error: function () {
                    form.find(".errormsg").text($__('Something wrong'));
                    btn.prop('disabled', false).next(".temp-loader").remove();
                    btn.after("<i class='icon16 no'></i>");
                },
            });
        }
    };
})(jQuery);