$(function () {

    var self = {
        init: function () {
            self.initViewModesDropdown();
            self.initEvents();
        },

        delayedRequest: function (deferred, callback) {
            $.when(deferred, self.getDelayDeferred()).then(callback);
        },

        initPublishedStatusSwitch: function ($switch, $icon, action, callback) {
            $switch.waSwitch({
                ready: function (wa_switch) {
                    var $label = wa_switch.$wrapper.siblings('label');

                    wa_switch.$label = $label;
                    wa_switch.active_text = $label.data('active-text');
                    wa_switch.inactive_text = $label.data('inactive-text');
                },

                change: function(active, wa_switch) {
                    var $checkbox = $switch.find('input:checkbox');
                    var $dialog = $checkbox.closest('.dialog');
                    var $hidden_fields = $dialog.find('.hidden-field');
                    var $published_status_selector = $switch.closest('.published-status-selector');
                    var $url_container = $dialog.find('.published-url-container');
                    var $spinner = $('<i class="fas fa-spinner fa-spin custom-ml-8 logs-spinner"></i>');
                    var $error_message = $dialog.find('.dialog-error-message .state-error');

                    wa_switch.$label.text(active ? wa_switch.active_text : wa_switch.inactive_text);

                    $dialog.find('.fa-spinner').remove();
                    $error_message.empty();
                    $published_status_selector.append($spinner);

                    self.fixElementDataPath($checkbox);

                    self.delayedRequest($.post('?module=dialog&action=' + action, {
                        path: self.getElementDataPath($checkbox),
                        status: active ? 1 : 0
                    }), function (post_response) {
                        var response = post_response[0];

                        $('.logs-spinner').remove();

                        if (response.status == 'ok') {
                            var $icon_parent = $icon.parent();

                            if (active) {
                                $icon_parent.addClass('enabled-action-container');

                                if ($icon_parent.data('title-enabled')) {
                                    $icon_parent.attr('title', $icon_parent.data('title-enabled'));
                                }
                            } else {
                                $icon_parent.removeClass('enabled-action-container');

                                if ($icon_parent.data('title-simple')) {
                                    $icon_parent.attr('title', $icon_parent.data('title-simple'));
                                }
                            }

                            $dialog.find('.published-url').val(response.data.url);
                            $dialog.find('.published-password-value').val(response.data.password);

                            $dialog.find('.published-password-empty').hide();
                            $dialog.find('.published-password-exists').show();

                            if (active) {
                                $url_container.slideDown(200, callback);
                                $hidden_fields.slideDown(200);
                            } else {
                                $url_container.slideUp(callback);
                                $hidden_fields.slideUp();
                            }
                        } else {
                            $error_message.html(response.errors.join(' '));
                        }
                    });
                }
            });
        },

        initTrackedStatusSwitch: function ($switch, $icon) {
            $switch.waSwitch({
                ready: function (wa_switch) {
                    var $label = wa_switch.$wrapper.siblings('label');

                    wa_switch.$label = $label;
                    wa_switch.active_text = $label.data('active-text');
                    wa_switch.inactive_text = $label.data('inactive-text');
                },

                change: function(active, wa_switch) {
                    var $checkbox = $switch.find('input:checkbox');
                    var $dialog = $checkbox.closest('.dialog');
                    var $tracked_status_selector = $switch.closest('.tracked-status-selector');
                    var $spinner = $('<i class="fas fa-spinner fa-spin custom-ml-8 logs-spinner"></i>');
                    var $error_message = $dialog.find('.dialog-error-message .state-error');

                    wa_switch.$label.text(active ? wa_switch.active_text : wa_switch.inactive_text);

                    $dialog.find('.fa-spinner').remove();
                    $error_message.empty();
                    $tracked_status_selector.append($spinner);

                    self.fixElementDataPath($checkbox);

                    self.delayedRequest($.post('?module=dialog&action=trackedFileUpdateStatus', {
                        path: self.getElementDataPath($checkbox),
                        status: active ? 1 : 0
                    }), function (post_response) {
                        var response = post_response[0];

                        $('.logs-spinner').remove();

                        if (response.status == 'fail') {
                            $error_message.html(response.errors.join(' '));
                        } else {
                            var $icon_parent = $icon.parent();
                            var $hint_field = $switch.closest('.dialog').find('.hint-field');

                            if (active) {
                                $icon_parent.addClass('enabled-action-container');

                                if ($icon_parent.data('title-enabled')) {
                                    $icon_parent.attr('title', $icon_parent.data('title-enabled'));
                                }

                                $hint_field.slideDown();
                            } else {
                                $icon_parent.removeClass('enabled-action-container');

                                if ($icon_parent.data('title-simple')) {
                                    $icon_parent.attr('title', $icon_parent.data('title-simple'));
                                }

                                $hint_field.slideUp();
                            }
                        }
                    });
                }
            });
        },

        initViewModesDropdown: function () {
            var $view_modes_dropdown = $('#view-modes-dropdown');

            if ($view_modes_dropdown.length) {
                $view_modes_dropdown.waDropdown();
            }
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

        getUrlParamValue: function (param_name) {
            var url_params = location.search.replace(/^[^a-z]+/, '').split('&');

            return url_params.reduce(function (result, item) {
                var parts = item.split('=');

                if (parts.length == 2) {
                    var name = parts[0];
                    var value = parts[1];

                    if (name == param_name) {
                        result = value;
                    }
                }

                return result;
            }, '');
        },

        isFilePage: function () {
            return self.getUrlParamValue('action') == 'file';
        },

        updateDialogSubmitStatus: function ($dialog, $submit) {
            var $error = $dialog.find('.state-error');

            if ($error.length && $error.text().trim().length) {
                $submit.attr('disabled', false);
            }
        },

        fixElementDataPath: function ($element) {
            //hack to work around a bug in waHtmlControl
            $element.data('path', self.getElementDataPath($element).split('\\/').join('/'));
        },

        getElementDataPath: function ($element) {
            // do not use .data('path') to support JSON-like file names
            return $element.attr('data-path');
        },

        reloadCallback: function (html) {
            $('#wa-app').replaceWith(html);
            self.initViewModesDropdown();
        },

        initEvents: function () {
            // delete
            $(document).on('click', '.logs-action-delete', function () {
                var $icon = $(this).find('svg');
                var path = self.getElementDataPath($icon);

                $.waDialog({
                    html: self.getTemplateHtml($('.dialog-template-delete')),
                    onOpen: function ($dialog, dialog) {
                        var $submit = $dialog.find('.dialog-action-submit');

                        $submit.attr('disabled', true);

                        $.get('?module=dialog&action=delete&path=' + path, function (html) {
                            var $response = $(html);
                            var $header = $response.find('.dialog-header');
                            var $content = $response.find('.dialog-content');

                            $dialog.find('.dialog-header').replaceWith($header);
                            $dialog.find('.dialog-content').replaceWith($content);

                            self.updateDialogSubmitStatus($dialog, $submit);
                            dialog.resize();
                        });

                        $dialog.on('click', '.js-submit', function () {
                            var $form = $dialog.find('form');
                            var $spinner = $('<span><i class="fas fa-spinner fa-spin custom-ml-8"></i></span>');
                            var update_size_param = $('.total-size').length > 0 ? '&update_size=1' : '';

                            $dialog.find('.state-error').empty();
                            $dialog.find('.dialog-footer').append($spinner);

                            self.delayedRequest(
                                $.ajax({
                                    type: 'POST',
                                    url: '?action=delete' + update_size_param,
                                    data: $form.serialize(),
                                    dataType: 'json'
                                }),
                                function (post_response) {
                                    var response = post_response[0];

                                    $spinner.remove();

                                    if (response.status == 'fail') {
                                        $dialog.find('.state-error').html(response.errors.join(' '));
                                        dialog.resize();
                                    } else {
                                        if ($icon.data('return-url')) {
                                            location.href = $icon.data('return-url');
                                        } else {
                                            $icon.closest('li').remove();

                                            if ($('.item-list li').length) {
                                                if (response.data.total_size !== undefined) {
                                                    $('.total-size').show().text(response.data.total_size);
                                                }

                                                if (response.data.total_size_class !== undefined) {
                                                    $('.total-size').attr('class', response.data.total_size_class);
                                                }

                                                if (response.data.is_large !== undefined && !response.data.is_large) {
                                                    $('#wa-app-logs .badge').remove();
                                                }
                                            } else {
                                                $('.item-list').hide();
                                                $('.total-size').hide().attr('class', 'total-size');
                                                $('#wa-app-logs .badge').remove();
                                                $('.no-logs-message').show();
                                            }

                                            dialog.close();
                                        }
                                    }
                                }
                            );
                        });
                    }
                });
            });

            // rename
            $(document).on('click', '.logs-action-rename', function () {
                var $icon = $(this).find('svg');
                var path = self.getElementDataPath($icon);

                $.waDialog({
                    html: self.getTemplateHtml($('.dialog-template-rename')),
                    onOpen: function ($dialog, dialog) {
                        var $submit = $dialog.find('.js-submit');

                        $submit.attr('disabled', true);

                        $.get('?module=dialog&action=rename&path=' + path).then(function (html) {
                            var $response = $(html);
                            var $response_header = $response.find('.dialog-header');
                            var $response_content = $response.find('.dialog-content');

                            if ($response_header.length) {
                                $dialog.find('.dialog-header').replaceWith($response_header);
                            }

                            $dialog.find('.dialog-content').replaceWith($response_content);
                            $dialog.find('[name="name"]').focus();
                            $submit.attr('disabled', false);
                            dialog.resize();
                        });

                        $dialog.on('submit', 'form', function (event) {
                            event.preventDefault();

                            var $error = $dialog.find('.state-error');
                            var $spinner = $('<span><i class="fas fa-spinner fa-spin"></i></span>');
                            var $dialog_footer = $dialog.find('.dialog-footer');
                            var $form = $dialog.find('form');

                            $error.empty();
                            $dialog_footer.append($spinner);

                            self.delayedRequest(
                                $.ajax({
                                    type: 'POST',
                                    url: '?module=backend&action=rename',
                                    data: $form.serialize(),
                                    dataType: 'json'
                                }),
                                function (post_response) {
                                    var response = post_response[0];

                                    if (response.status == 'fail') {
                                        $spinner.remove();
                                        $error.html(response.errors.join(' '));
                                        dialog.resize();
                                    } else {
                                        if (!self.isFilePage()) {
                                            $.get(location.href).then(function (html) {
                                                self.reloadCallback(html);
                                                dialog.close();
                                            });
                                        } else {
                                            location.href = response.data.redirect_url;
                                        }
                                    }
                                }
                            );
                        });

                        $dialog.on('click', '.js-submit', function () {
                            $dialog.find('form').submit();
                        });
                    }
                });
            });

            // reload item list
            $(document).on('click', '.list-update-action', function () {
                var $icon = $(this).find('svg');
                var $spinner = $('<i class="fas fa-spinner fa-spin"></i>');

                $icon.replaceWith($spinner);

                self.delayedRequest(
                    $.get(location.href),
                    function (get_result) {
                        var html = get_result[0];
                        self.reloadCallback(html);
                    }
                );
            });

            // publishing dialog
            $(document).on('click', '.logs-action-published', function () {
                var $icon = $(this).find('svg');
                var path = self.getElementDataPath($icon);

                $.waDialog({
                    html: $('.dialog-template-file-publish').clone().html(),
                    onOpen: function ($dialog, dialog) {
                        $.get('?module=dialog&action=publishedFile&path=' + path).then(function (html) {
                            $dialog.find('.dialog-content').html(html);
                            dialog.resize();
                            self.initPublishedStatusSwitch(
                                $dialog.find('.published-status-switch'),
                                $icon,
                                'publishedFileUpdateStatus',
                                dialog.resize.bind(dialog)
                            );
                        });
                    }
                });
            });

            // phpinfo
            $(document).on('click', '.logs-action-phpinfo', function () {
                var $icon = $(this).find('svg');

                $.waDialog({
                    html: $('.dialog-template-phpinfo').clone().html(),
                    onOpen: function ($dialog, dialog) {
                        $.get('?module=dialog&action=phpinfo').then(function (html) {
                            $dialog.find('.dialog-content').html(html);
                            dialog.resize();
                            self.initPublishedStatusSwitch(
                                $dialog.find('.published-status-switch'),
                                $icon,
                                'publishedPhpinfoUpdateStatus',
                                dialog.resize.bind(dialog)
                            );
                        });
                    }
                });
            });

            // track
            $(document).on('click', '.logs-action-track', function () {
                var $icon = $(this).find('svg');
                var path = self.getElementDataPath($icon);

                $.waDialog({
                    html: self.getTemplateHtml($('.dialog-template-tracked-file')),
                    onOpen: function ($dialog, dialog) {
                        $.get('?module=dialog&action=trackedFile&path=' + path).then(function (html) {
                            $dialog.find('.dialog-content').html(html);
                            dialog.resize();
                            self.initTrackedStatusSwitch(
                                $dialog.find('.tracked-status-switch'),
                                $icon
                            );
                        });
                    }
                });
            });

            // auto-select text in input
            $(document).on('click', '.auto-select', function () {
                $(this).trigger('select');
            })

            // prevent users from submitting the form manually
            $(document).on('submit', '.published-password-protection', function (event) {
                event.preventDefault();
            });

            // set password
            $(document).on('click', '.published-set-password-link', function (event) {
                event.preventDefault();

                var $link = $(this);
                var $spinner = $('<span><i class="fas fa-spinner fa-spin custom-ml-8"></i></span>');
                var $dialog = $link.closest('.dialog');
                var $form = $link.closest('form');
                var $error_message = $dialog.find('.dialog-error-message .state-error');
                var $empty_password_container = $form.find('.published-password-empty');
                var $existing_password_container = $form.find('.published-password-exists');

                $error_message.empty();
                $spinner.insertAfter($link);

                self.delayedRequest(
                    $.post('?module=dialog&action=' + $form.data('setPasswordAction'), $form.serialize()),
                    function (post_response) {
                        var response = post_response[0];

                        $spinner.remove();

                        if (response.status == 'fail') {
                            $error_message.html(response.errors.join(' '));
                            $dialog.trigger('resize');
                        } else {
                            $form.find('.published-password-value').val(response.data.password);
                            $empty_password_container.slideUp();
                            $existing_password_container.slideDown();
                        }
                    }
                );
            });

            // reset password
            $(document).on('click', '.published-reset-password-link', function (event) {
                event.preventDefault();

                var $link = $(this);
                var $spinner = $('<i class="fas fa-spinner fa-spin custom-ml-8"></i>');
                var $dialog = $link.closest('.dialog');
                var $form = $link.closest('form');
                var $error_message = $dialog.find('.dialog-error-message .state-error');
                var $password = $form.find('.published-password-value');

                $error_message.empty();
                $spinner.insertAfter($password);

                self.delayedRequest(
                    $.post('?module=dialog&action=' + $form.data('setPasswordAction'), $form.serialize()),
                    function (post_response) {
                        var response = post_response[0];

                        $dialog.find('.fa-spinner').remove();

                        if (response.status == 'fail') {
                            $error_message.html(response.errors.join(' '));
                            $dialog.trigger('resize');
                        } else {
                            $form.find('.published-password-value').val(response.data.password);

                            $('<span class="logs-spinner"><i class="fas fa-check custom-ml-8"></i></span>').insertAfter($password);
                            $dialog.find('svg.fa-check').remove();
                            $dialog.find('.logs-spinner').animate({opacity: 0}, 700, function () {
                                $(this).remove();
                            });
                        }
                    }
                );
            });

            // remove password
            $(document).on('click', '.published-remove-password-link', function (event) {
                event.preventDefault();

                var $link = $(this);
                var $spinner = $('<i class="fas fa-spinner fa-spin custom-ml-8"></i>');
                var $dialog = $link.closest('.dialog');
                var $form = $link.closest('form');
                var $error_message = $dialog.find('.dialog-error-message .state-error');
                var $empty_password_container = $form.find('.published-password-empty');
                var $existing_password_container = $form.find('.published-password-exists');
                var $password = $form.find('.published-password-value');

                $error_message.empty();
                $spinner.insertAfter($password);

                self.delayedRequest(
                    $.post('?module=dialog&action=publishedRemovePassword', $form.serialize()),
                    function (post_response) {
                        var response = post_response[0];

                        $dialog.find('.fa-spinner').remove();

                        if (response.status == 'fail') {
                            $error_message.html(response.errors.join(' '));
                            $dialog.trigger('resize');
                        } else {
                            $form.find('.published-password-value').val('');
                            $empty_password_container.slideDown();
                            $existing_password_container.slideUp();
                        }
                    }
                );
            });

            // settings
            $(document).on('click', '.logs-action-settings', function () {
                var $icon = $(this).find('svg');

                $.waDialog({
                    html: self.getTemplateHtml($('.dialog-template-settings')),
                    onOpen: function ($dialog, dialog) {
                        var $submit = $dialog.find('.js-submit');
                        var maximum_dialog_width = 800;

                        $dialog.find('.dialog-body').css({
                            width: Math.max(maximum_dialog_width, $(window).width() * 0.5) + 'px'
                        });

                        $submit.attr('disabled', true);

                        $.get('?module=dialog&action=settings').then(function (html) {
                            $dialog.find('.dialog-content').html(html);
                            $submit.attr('disabled', false);
                            dialog.resize();
                        });

                        $dialog.on('click', '.js-submit', function () {
                            var $error = $dialog.find('.state-error');
                            var $form = $dialog.find('form');
                            var $spinner = $('<span><i class="fas fa-spinner fa-spin custom-ml-8"></i></span>');

                            $dialog.find('.js-close-dialog').after($spinner);
                            $error.empty();

                            self.delayedRequest(
                                $.post('?module=dialog&action=settingsSave', $form.serialize()),
                                function (post_response) {
                                    var response = post_response[0];

                                    if (response.status == 'fail') {
                                        $spinner.remove();
                                        $error.html(response.errors.join(' '));
                                        dialog.resize();
                                    } else {
                                        var do_reload = settingsSaveUpdateFilePage($icon, $dialog);

                                        if (do_reload === false) {
                                            dialog.close();
                                        }
                                    }
                                }
                            );
                        });
                    }
                });

                function settingsSaveUpdateFilePage ($icon, $dialog) {
                    if (self.isFilePage()) {
                        var old_hide_data = $icon.data('hide-data');

                        if (old_hide_data !== undefined) {
                            var new_hide_data = [];

                            $dialog.find('[name^="settings[hide]"]').each(function () {
                                var $checkbox = $(this);
                                var matches = $checkbox.attr('name').match(/settings\[[^\]]+\]\[([^\]]+)\]\[([^\]]+)\]/);

                                if (matches[2] == 'backend' && $checkbox.is(':checked')) {
                                    new_hide_data.push(matches[1]);
                                }
                            });

                            old_hide_data.sort();
                            new_hide_data.sort();

                            $icon.data('hide-data', new_hide_data);

                            if (JSON.stringify(old_hide_data) !== JSON.stringify(new_hide_data)) {
                                location.reload();
                            } else {
                                return false;
                            }
                        }
                    }

                    return false;
                }
            });

            // show/hide PHP errors selection setting
            $(document).on('change', '.php_log_setting', function () {
                var $checkbox = $(this);
                var $php_errors_field = $checkbox.closest('.fields').find('.field-php-log-errors');
                var enabled = $checkbox.is(':checked');

                if (enabled) {
                    $php_errors_field.slideDown(200);
                } else {
                    $php_errors_field.slideUp();
                }
            });

            // update PHP errors setting checkboxes
            $(document).on('change', '.field-php-log-errors input[type="checkbox"]', function () {
                var $checkbox = $(this);
                var $field_value_container = $checkbox.closest('.value');
                var $e_all = $field_value_container.find('[value="E_ALL"]');
                var $e_other = $field_value_container.find('[type="checkbox"]').not('[value="E_ALL"]');

                var is_checked = $checkbox.is(':checked');

                if ($checkbox.is($e_all)) {
                    $e_other.attr('checked', !is_checked);
                } else {
                    $e_all.attr('checked', !$e_other.filter(':checked').length);
                }
            });
        }
    };

    self.init();

});
