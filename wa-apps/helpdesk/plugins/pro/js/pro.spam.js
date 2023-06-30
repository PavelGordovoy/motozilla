var helpdeskProSpam = (function ($) {
    helpdeskProSpam = function (options) {
        var that = this;

        // DOM
        that.$wrapper = options.$wrapper;
        that.$form = options.$form;

        // DYNAMIC VARS
        that.is_locked = false;

        // INIT
        that.initClass();
    };

    helpdeskProSpam.prototype.initClass = function () {
        var that = this;

        that.bindEvents();
    };

    helpdeskProSpam.prototype.bindEvents = function () {
        var that = this;

        /* Добавление email адреса в спам */
        that.$form.submit(function () {
            var form = $(this),
                email = form.find(":text");
            if ($.trim(email.val()) !== '' && !form.find(".loading").length) {
                form.find(".loading").remove();
                form.find(":submit").after('<i class="icon16 loading"></i>');
                $.post('?plugin=pro&action=addSpam', {email: email.val()}, function (response) {
                    var i = form.find(".loading");
                    i.removeClass('loading');
                    email.val('');
                    if (response.status == 'ok') {
                        if (response.data) {
                            that.$wrapper.find('.hp-spam-table tbody').append('<tr><td class="item"></td><td><a href="mailto:' + response.data + '" class="js-spam-email" title="' + $__('Write to email') + '">' + response.data + '</a></td><td><a href="#" class="js-delete-spam" title="' + $__('delete from spam') + '"><i class="icon16 delete"></i></a></td></tr>');
                        }
                        i.addClass('yes');
                    } else {
                        i.addClass('no');
                    }
                    setTimeout(function () {
                        i.remove();
                    }, 1000);
                });
            }
            return false;
        });

        /* Удаление email адреса из спама */
        that.$wrapper.on('click', '.js-delete-spam', function () {
            var link = $(this),
                i = link.find('i'),
                tr = link.closest('tr');
            if (!i.hasClass('loading')) {
                i.removeClass('delete').addClass('loading');
                $.post('?plugin=pro&action=removeSpam', {email: tr.find('.js-spam-email').text()}, function () {
                    tr.fadeOut(function () {
                        $(this).remove();
                    });
                });
            }
            return false;
        });

    };

    return helpdeskProSpam;
})(jQuery);
