$(function () {
    var self = {
        init: function () {
            self.fixFirstPageLink();
            self.events();
            prettyPrint();
        },

        fixFirstPageLink: function () {
            $('.pagination a').each(function () {
                var link = $(this);
                var href = link.attr('href');

                if (href.indexOf('page=') < 0) {
                    if (href.indexOf('?') < 0) {
                        href = href + '?page=1';
                    } else {
                        href = href + '&page=1';
                    }

                    link.attr('href', href);
                }
            });
        },

        getDelayDeferred: function () {
            var deferred = $.Deferred();
            var timeout = 500;

            setTimeout(function() {
                deferred.resolve();
            }, timeout);

            return deferred;
        },

        getTemplateHtml: function ($template) {
            var $clone = $template.clone();
            var html = $clone.html();

            $clone.remove();
            return html;
        },

        events: function () {
            $(document).on('click', '.item-lines', function getMoreLines () {
                var $button = $(this);

                if ($button.hasClass('disabled')) {
                    return;
                }

                $button.addClass('disabled');

                var $arrow = $button.find('.arrow');
                $arrow.addClass('hidden');

                var $spinner = $('<span><i class="fas fa-spinner fa-spin"></i></span>');
                $button.append($spinner);

                var $form = $('.item-lines-form:first');
                var direction = $(this).hasClass('previous') ? 'previous' : 'next';

                $form.find('[name="direction"]').val(direction);

                $.when(
                    $.ajax({
                        type: 'POST',
                        url: location.href,
                        data: $form.serialize(),
                        dataType: 'json'
                    }),
                    self.getDelayDeferred()
                ).then(function (post_response) {
                    var response = post_response[0];

                    $spinner.remove();

                    if (response.status == 'fail') {
                        $arrow.removeClass('hidden');

                        var errors_occurred = response.errors !== undefined && response.errors.length;

                        if (errors_occurred) {
                            $.waDialog({
                                html: self.getTemplateHtml($('.dialog-template-error')),
                                esc: false,
                                onOpen: function ($dialog, dialog) {
                                    $dialog.find('.state-error').html(response.errors.join(' '));
                                    dialog.resize();

                                    $dialog.on('click', '.js-submit', function errorDialogSubmit () {
                                        var $dialog_footer = $dialog.find('.dialog-footer');
                                        var $spinner = $('<i class="fas fa-spinner fa-spin"></i>')

                                        $dialog_footer.append($spinner);
                                        location.href = '?action=files&mode=updatetime';
                                    });
                                }
                            });
                        } else {
                            (function loadMoreLinesFailedReload () {
                                if (response.data.return_url !== undefined && response.data.return_url.length) {
                                    location.href = response.data.return_url;
                                } else {
                                    location.reload();
                                }
                            })();
                        }
                    } else {
                        //status = ok
                        var $contents = $('.item-contents');

                        if (response.data.contents) {
                            if (direction == 'previous' && response.data.first_line == 0) {
                                $button.attr('title', '');
                            } else {
                                $button.removeClass('disabled');
                            }

                            $arrow.removeClass('hidden');

                            if (direction == 'previous') {
                                $contents.prepend(response.data.contents);
                                $('[name="first_line"]').val(response.data.first_line);
                            } else {
                                $contents.append(response.data.contents);
                                $('[name="last_line"]').val(response.data.last_line);

                                if (response.data.last_eol !== undefined) {
                                    $form.find('[name="last_eol"]').val(response.data.last_eol);
                                }

                                if (response.data.file_end_eol !== undefined) {
                                    $form.find('[name="file_end_eol"]').val(response.data.file_end_eol);
                                }
                            }

                            if (response.data.file_size !== undefined) {
                                $('.total-size-file').text(response.data.file_size);
                            }

                            // remove previous prettyprint tags and preserve line breaks
                            $contents.html($contents.html().split('<br>').join("\n"));
                            $contents.text($contents.text());

                            prettyPrint();

                            if (direction == 'next') {
                                $('html').animate({scrollTop: $(document).height()}, 'slow');
                            }
                        } else {
                            $('<span class="hint message">' + $('.item-data').data('item-lines-empty-message') + '</span>')
                                .appendTo($button)
                                .animate({opacity: 0}, 700, function () {
                                    $(this).remove();
                                    $arrow.removeClass('hidden');
                                    $button.removeClass('disabled');
                                });
                        }
                    }
                });
            });
        }
    };

    self.init();
});
