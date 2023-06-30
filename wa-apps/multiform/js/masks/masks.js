(function ($) {
    $.masks = {
        initMasks: function () {
            // Добавление новой маски
            $(document).on('click', '.f-add-mask', function () {
                var btn = $(this).closest(".add-button");
                var template = btn.prev(".template").clone();
                var id = 'new';
                var count = 0;
                while ($("#mask-form tr[rel='" + id + "']").length) {
                    id += count;
                    count++;
                }
                template.find(".f-name").attr('name', 'mask[' + id + '][name]').prop('disabled', false);
                template.find(".f-mask").attr('name', 'mask[' + id + '][mask]').prop('disabled', false);
                template.find(".f-error").attr('name', 'mask[' + id + '][error]').prop('disabled', false);
                template.removeClass('template').attr('rel', id);
                btn.prev().before(template);
            });
            // Сохранение масок
            $("#mask-form input[type='submit']").click(function () {
                var btn = $(this);
                var form = btn.parents("form");
                $(".errormsg").text('');
                btn.next("i").remove();
                // Валидация полей
                var error = false;
                $.each(form.find("tr:not(.template) .f-required"), function (i, v) {
                    $(this).removeClass('s-empty');
                    if ($(this).val() == '') {
                        $(this).addClass('s-empty');
                        error = true;
                    }
                });
                if (error) {
                    form.find(".errormsg").text($__('Fill in the required fields'));
                    return false;
                }
                btn.attr('disabled', 'disabled').after("<i class='icon16 loading temp-loader'></i>");
                $.ajax({
                    url: "?module=masks&action=save",
                    data: form.serializeArray(),
                    dataType: "json",
                    type: "post",
                    success: function (response) {
                        btn.removeAttr('disabled').next(".temp-loader").remove();
                        if (typeof response.errors !== 'undefined') {
                            if (typeof response.errors.messages !== 'undefined') {
                                $.each(response.errors.messages, function (i, v) {
                                    form.find(".errormsg").append(v + "<br />");
                                });
                            }
                        } else if (response.status == 'ok' && response.data) {
                            btn.after("<i class='icon16 yes'></i>");
                        } else {
                            btn.after("<i class='icon16 no'></i>");
                        }
                    },
                    error: function () {
                        $(".errormsg").text($__('Something wrong'));
                        btn.removeAttr('disabled').next(".temp-loader").remove();
                        btn.after("<i class='icon16 no'></i>");
                    }
                });
                return false;
            });
        }
    };
})(jQuery);