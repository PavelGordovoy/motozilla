$(function () {
    $.waDialog({
        html: $('.dialog-template').clone().html(),
        onOpen: function ($dialog, dialog) {
            var $form = $dialog.find('form');

            $form.find('[name="password"]:first').trigger('focus');

            $form.on('submit', function (event) {
                event.preventDefault();

                var $error_message = $dialog.find('.state-error');
                var $spinner = $('<span><i class="fas fa-spinner fa-spin"></i></span>');
                var $submit = $dialog.find('.js-submit');

                $error_message.css({visibility: 'hidden'});
                $dialog.find('.dialog-footer').append($spinner);

                $.when(
                    $.ajax({
                        type: 'POST',
                        url: location.href,
                        data: $form.serialize(),
                        dataType: 'json'
                    }),
                    (function () {
                        var deferred = $.Deferred();
                        var timeout = 500;

                        setTimeout(function() {
                            deferred.resolve();
                        }, timeout);

                        return deferred;
                    })()
                ).then(function (post_response) {
                    var response = post_response[0];

                    $spinner.remove();

                    if (response.status == 'fail') {
                        if (response.errors !== undefined) {
                            $error_message.html(response.errors.join(' ')).css({visibility: 'visible'});
                        }

                        $submit.removeAttr('disabled');
                        $dialog.find('[name="password"]').focus();
                    } else {
                        dialog.close();
                        location.reload();
                    }
                });
            });

            $dialog.find('.js-submit').on('click', function () {
                $form.trigger('submit');
            });
        }
    });
});
