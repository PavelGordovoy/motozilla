var helpdeskProRequests = (function ($) {
    helpdeskProRequests = function (options) {
        var that = this;

        // DOM
        that.$wrapper = $("#ticket");
        that.favourite = options.favourite || {};

        // DYNAMIC VARS
        that.is_locked = false;
        that.crmUrl = options.crmUrl || '';
        that.crmTagsName = options.crmTagsName || '';
        that.clientContactEmail = options.clientContactEmail || '';
        that.deleteSpamContacts = options.deleteSpamContacts || 0;
        that.clientContactId = options.clientContactId || '';
        that.spam = options.spam || 0;
        that.spam = parseInt(that.spam);
        that.customRequestFields = options.customRequestFields;

        that.requestId = that.$wrapper.find('.h-request-item').data('id');

        // INIT
        that.initClass();
    };

    helpdeskProRequests.prototype.initClass = function () {
        var that = this;

        that.initFavouriteMessages();
        that.initNewRequestLink();
        that.initGallery();
        that.initCRMTags();
        that.initSpam();
        that.initCustomRequestFields();
        that.initTagsRemoving();
        that.initAttachments();

        that.bindEvents();
    };

    helpdeskProRequests.prototype.bindEvents = function () {
        var that = this;

        /* Всплывающее окно для проверки домена */
        that.$wrapper.on('click', '.js-check-domain', function () {
            new helpdeskProDialog({
                url: '?plugin=pro&module=dialog&action=domainCheck',
                saveUrl: "?plugin=pro&module=developer&action=licenseCheck",
                onOpen: function () {
                    var d = this;
                    // Открываем заказ на странице проверки домена 
                    d.$form.on("click", ".js-order-check", function (event) {
                        event.preventDefault();
                        var btn = $(this),
                            orderId = btn.data('order-id'),
                            domain = btn.data('domain'),
                            product = btn.data('product');
                        d.save({ order: orderId, type: 'order' }, function () {
                            $("<a href='javascript:void(0)' class='back' title='" + $__('Back') + "'>&larr;Назад</a>").click(function () {
                                d.save({ domain: domain, product: product });
                            }).prependTo(d.$wrapper.find(".w-dialog-header h1"));
                        });
                    });
                },
                onSuccess: function (d, response) {
                    d.$form.find('.w-dynamic-content').html(response.data);
                },
                onSuccessCallback: function () {
                    this.$wrapper.find("h1 .back").remove();
                }
            });
            return false;
        });

        /* Всплывающее окно для проверки номера заказа */
        that.$wrapper.on('click', '.js-check-order', function () {
            new helpdeskProDialog({
                url: '?plugin=pro&module=dialog&action=orderCheck',
                saveUrl: "?plugin=pro&module=developer&action=licenseCheck",
                onSuccess: function (d, response) {
                    d.$form.find('.w-dynamic-content').html(response.data);
                }
            });
            return false;
        });

    };

    helpdeskProRequests.prototype.initTagsRemoving = function () {
        var that = this;

        $('#h-all-tags-dialog li label').each(function () {
            var label = $(this);
            var $removeBtn = $("<a href='javascript:void(0)' class='hp-tag-delete' title='" + $__('Remove tag') + "'><i class='icon10 no'></i></a>").click(function () {
                var btn = $(this);
                if (!btn.find('.loading').length) {
                    btn.find('i').removeClass('icon10 no').addClass('icon16 loading');
                    $.post('?plugin=pro&action=removeTag', { name: label.find(':checkbox').val() }, function (response) {
                        if (response.status == 'ok') {
                            label.closest('li').fadeOut(function () {
                                $(this).remove();
                            });
                        }
                    }).always(function () {
                        btn.find('i').removeClass('icon16 loading').addClass('icon10 no');
                    });
                }
            });
            label.after($removeBtn);
        });
    };

    helpdeskProRequests.prototype.initAttachments = function () {
        var that = this;

        $('.s-request .attachment .hint').each(function () {
            var hint = $(this);
            var attachmentBlock = hint.closest('.attachment');
            var link = hint.prev('a');
            var $removeBtn = $("<a href='javascript:void(0)' class='hp-attachment-delete' title='" + $__('Delete attachment') + "'><i class='icon10 no'></i></a>").click(function () {
                var btn = $(this);
                if (!btn.find('.loading').length && confirm($__('Are you sure you want to delete attachment?'))) {
                    btn.find('i').removeClass('icon10 no').addClass('icon16 loading');
                    $.post('?plugin=pro&action=removeAttachment', { params: link.attr('href') }, function (response) {
                        if (response.status == 'ok') {
                            link.closest('.h-log-item, .h-request-item').find('.h-request-image a[href="'+link.attr('href')+'"]').remove();
                            link.add(hint).add(btn).fadeOut(function () {
                                $(this).remove();
                                if (!attachmentBlock.find('a').length) {
                                    attachmentBlock.remove();
                                }
                            });
                        }
                    }).always(function () {
                        btn.find('i').removeClass('icon16 loading').addClass('icon10 no');
                    });
                }
            });
            hint.after($removeBtn);
        });
    };

    helpdeskProRequests.prototype.initCustomRequestFields = function () {
        var that = this;

        /* Находим кастомные поля и присваиваем им значения */
        that.$wrapper.find('.h-request-left-fields, .h-request-right-field').find('p').each(function () {
            var fld = $(this).find('span:first');
            var name = fld.text().trim();
            if (name.substr(-1) === ':') {
                name = name.substring(0, name.length - 1);
            }
            /* Поле найдено по совпадению имени.. Печально */
            if (void 0 !== that.customRequestFields[name]) {

                fld.data('id', that.customRequestFields[name]['id']).append($(' <i class="icon10 edit" style="cursor: pointer;margin-left: 5px;" title="' + $__('edit') + '"></i>').click(function () {
                    var btn = $(this);
                    new helpdeskProDialog({
                        title: $__('Editing field'),
                        content: getFieldConstructor(that.customRequestFields[name], fld.next('span').text()),
                        onSubmit: function (form, d) {
                            if (form.find('.js-value').length) {
                                d.$submitBtn.next('i').remove();
                                d.$submitBtn.after('<i class="icon16 loading"></i>');
                                var value = that.customRequestFields[name].type === 'Checkbox' ? (form.find('.js-value').prop('checked') ? $__('Yes') : 0) : form.find('.js-value').val();
                                $.post('?plugin=pro&action=saveCustomRequestField', {
                                    request_id: that.requestId,
                                    field_id: that.customRequestFields[name].id,
                                    value: value
                                }, function (response) {
                                    if (response.status == 'ok') {
                                        d.close();
                                        var parent = btn.closest('span');
                                        if (!parent.next('span').length) {
                                            parent.after('<span class="bold">' + response.data + '</span>');
                                        } else {
                                            parent.next().html(response.data);
                                        }

                                        /* Проверяем наличие дополнительного поля в списке запросов */
                                        var sidebarAdditionalField = $(".requests-table tr[rel='" + that.requestId + "'] .description-col .hp-contact-row[data-id='" + that.customRequestFields[name]['id'] + "']");
                                        if (sidebarAdditionalField.length) {
                                            if (!$.trim(value)) {
                                                sidebarAdditionalField.hide();
                                            } else {
                                                sidebarAdditionalField.show().find('.hp-contact-value div').text(value);
                                            }
                                        }
                                    } else {
                                        d.$submitBtn.next('i').removeClass('loading').addClass('no');
                                        setTimeout(function () {
                                            d.$submitBtn.next('i').remove();
                                        }, 3000);
                                    }
                                });
                            } else {
                                d.close();
                            }
                        },
                        onBgClick: function (e, d) {
                            d.close();
                        },
                    });
                }));
            }

            function getFieldConstructor(field, defaultValue) {
                var html = '<div class="hp-custom-field-value">';
                html += field.name + ': ';
                switch (field.type) {
                    case 'Select':
                        html += "<select class='js-value'><option></option>";

                        if (field.options) {
                            for (var i in field.options) {
                                html += '<option value="' + i + '"' + (defaultValue == i ? ' selected' : '') + '>' + field.options[i] + '</option>';
                            }
                        }
                        html += "</select>";
                        break;
                    case 'String':
                        html += '<input type="text" class="js-value" value="' + defaultValue + '">';
                        break;
                    case 'Number':
                        html += '<input type="number" class="js-value" value="' + defaultValue + '">';
                        break;
                    case 'Checkbox':
                        html += '<input type="checkbox" class="js-value" value="Да"' + (defaultValue === 'Да' ? ' checked' : '') + '>';
                        break;
                    default:
                        html += $__("Field not found");
                }
                html += '</div>';
                return html;
            }

        });
    };

    helpdeskProRequests.prototype.initFavouriteMessages = function () {
        var that = this;
        /* Избранные сообщение */
        that.$wrapper.find(".s-comments > li").each(function () {
            var msg = $(this),
                id = (msg.hasClass("h-log-item") ? 'l' : '') + msg.data('id'),
                favourite = that.favourite[id] === undefined ? '-empty' : '';
            $("<a href='#' class='hp-favourite-link' title='" + (favourite !== '-empty' ? $__('Message added to favourites') : $__('Message is not favourite')) + "'><i class='icon16 star" + favourite + "'></i></a>").click(function () {
                var btn = $(this),
                    i = btn.find('i');
                if (!i.hasClass("loading")) {
                    var state = that.favourite[id] !== undefined ? 0 : 1;
                    i.removeClass('star star-empty').addClass("loading");
                    $.post("?plugin=pro&action=favourite", {
                        message_id: id,
                        state: state,
                        request_id: that.requestId
                    }, function (response) {
                        i.removeClass('loading');
                        if (response.status == 'ok') {
                            i.addClass(response.data ? 'star' : 'star-empty');
                            if (response.data) {
                                that.favourite[id] = id;
                                btn.attr('title', $__('Message added to favourites'));
                                msg.addClass("hp-favourite");
                            } else {
                                btn.attr('title', $__('Message is not favourite'));
                                msg.removeClass("hp-favourite");
                                delete that.favourite[id];
                            }
                        }
                    });
                }
                return false;
            }).appendTo(msg);
            if (that.favourite[id] !== undefined) {
                msg.addClass("hp-favourite");
            }
        });
    };

    helpdeskProRequests.prototype.initGallery = function () {
        $('#ticket .text img').colorbox({
            rel: function () {
                return $(this).closest("li").data("id");
            },
            href: function () {
                return $(this).attr('src');
            },
            photo: true,
            current: $__("image %c of %t").replace('%c', '{current}').replace('%t', '{total}'),
            previous: $__("previous"),
            next: $__("next"),
            close: $__("close"),
            xhrError: $__("This content failed to load."),
            imgError: $__("This image failed to load."),
            maxWidth: '100%'
        });
    };

    helpdeskProRequests.prototype.initNewRequestLink = function () {
        var that = this;

        if ($.helpdesk_pro.createNewRequest) {
            /* Добавляем возможность создания новых запросов на основе сообщений */
            that.$wrapper.find(".s-comments li.h-log-item").each(function () {
                var msg = $(this);
                /* Если сообщение пустое, то не даем возможность создавать новый запрос */
                if ($.trim(msg.find('.text').html()) !== '') {
                    $("<a href='#' class='hp-newrequest-link' title='" + $__('create new request') + "'><i class='icon10 add'></i> " + $__('create new request') + "</a>").click(function () {
                        new helpdeskProDialog({
                            url: '?plugin=pro&module=dialog&action=newRequest&request_id=' + that.requestId + '&message_id=' + msg.data('id'),
                            onSubmit: function (form) {
                                var d = this;
                                d.$form.off('submit');
                                form.submit(function () {
                                    that.saveNewRequest(d, form);
                                    return true;
                                }).submit();
                            },
                            onOpen: function () {
                                var d = this;
                                $("#c-core-content").off('helpdesk_after_load').on('helpdesk_after_load', function () {
                                    d.close();
                                });
                            }
                        });
                        return false;
                    }).appendTo(msg);
                }
            });
        }

    };

    helpdeskProRequests.prototype.initCRMTags = function () {
        var that = this;

        var unmakeFixableRequestHeader = function () {
            if ($.wa.helpdesk_controller.lastView) {
                var hblock = $('.hd-last-view').closest('.block');
                hblock.length && hblock.fixedBlock('destroy');
            } else {
                var block = $('#h-request-top-block .request-header');
                block && block.fixedBlock('destroy');
            }
        };
        setTimeout(function () {
            unmakeFixableRequestHeader();
        }, 1000);
        $(function () {
            $.crm = {
                app_url: that.crmUrl,
                content: {
                    reload: function () {
                        var tags = $('.crm-tags-input').val().split(',');
                        var tagsHtml = '';
                        if (tags.length > 1 || (tags.length === 1 && tags[0] !== '')) {
                            for (var i in tags) {
                                tagsHtml += '<li data-id=' + i + '><span class=hu-tag>' + tags[i] + '</span></li>';
                            }
                        }
                        $('.crm-dialog-wrapper').trigger('close');
                        $('.hu-tags-list li[data-id]').remove();
                        $('.hu-tags-list').prepend(tagsHtml);
                    }
                }
            };
            that.$wrapper.on('click', '.crm-contact-assign-tags', function (e) {
                var link = $(this),
                    url = that.crmUrl + "?module=contactOperation&action=assignTags&is_assign=1";
                e.preventDefault();
                if (!link.next('i.loading').length) {
                    link.after($('<i class=icon16></i>').addClass('loading'));
                    $.get(url, { contact_ids: [that.clientContactId] }, function (html) {
                        new CRMDialog({
                            html: html,
                            onOpen: function (d) {
                                d.find('h1').text(that.crmTagsName);
                                d.find('.crm-popular-tag-item-link').click(function () {
                                    var poplink = $(this),
                                        input = d.find('.crm-tags-input'),
                                        val = $.trim(poplink.text());
                                    input.removeTag(val);
                                    input.addTag(val);
                                });
                                link.next('i').remove();
                            }
                        });
                    });
                }
            });
        });
    };

    helpdeskProRequests.prototype.initSpam = function () {
        var that = this;

        that.spam && that.$wrapper.addClass('hp-spam');
        if (that.clientContactEmail !== '') {
            $("<a href='#' title='" + (that.spam ? $__("Remove from spam") : $__('Add to spam')) + "'><i class='icon16 spam' style='vertical-align: text-top;display:inline-block'></i> " + (that.spam ? $__("Remove from spam") : $__("Add to spam")) + "</a>").click(function () {
                new helpdeskProDialog({
                    title: (!that.spam ? $__('Add email "%s" to spam') : $__('Remove email "%s" from spam')).replace('%s', that.clientContactEmail),
                    content: !that.spam ? ($__("Do you really want to add this email to spam list? The request will be deleted automatically.") + (parseInt(that.deleteSpamContacts) ? $__('Contact will also be deleted.') : '')) : $__("Do you really want to remove this email from spam list?"),
                    buttons: '<input type="submit" class="button red t-button" value="' + (!that.spam ? $__('Add to spam') : $__("Remove from spam")) + (!that.spam && parseInt(that.deleteSpamContacts) ? ' ' + $__('and delete the contact') : '') + '"> ' + $__('or') + ' <a href="javascript:void(0)" class="js-close-dialog">' + $__('cancel') + '</a>',
                    saveUrl: !that.spam ? '?plugin=pro&action=addEmailToSpam' : '?plugin=pro&action=removeSpam',
                    onOpen: function () {
                        var d = this;
                        d.$form.append("<input type='hidden' name='email' value='" + that.clientContactEmail + "'><input type='hidden' name='request_id' value='" + that.requestId + "'>");
                        $(window).on('helpdesk.list.element', function () {
                            d.close();
                        });
                    },
                    onBgClick: function (e, d) {
                        d.close();
                    },
                    onSuccessCallback: function () {
                        var d = this;
                        d.$form.find("footer").remove().end().find('.w-dialog-content').html($__('Wait, please. Redirecting...') + ' <i class="icon16 loading"></i>');
                        d.$submitBtn.after("<i class='icon16 loading'></i>");
                        if (!that.spam) {
                            $.wa.helpdesk_controller.stopDispatch(0);
                            /* Перенаправляем на существующую страницу */
                            $.wa.helpdesk_controller.lastHash = null;
                            var redirectUrl = $.wa.helpdesk_controller.hashBase;
                            if (redirectUrl && redirectUrl !== undefined && redirectUrl !== '') {
                                if (redirectUrl == window.location.hash) {
                                    window.location.hash = '#';
                                    $.wa.helpdesk_controller.redispatch();
                                } else {
                                    window.location.hash = redirectUrl;
                                }
                            } else {
                                window.location.hash = '#';
                                $.wa.helpdesk_controller.redispatch();
                            }
                        } else {
                            $.wa.helpdesk_controller.redispatch();
                        }
                    }
                });
                return false;
            }).insertAfter(that.$wrapper.find('.request-header h1'));
        }
    };

    helpdeskProRequests.prototype.saveNewRequest = function (that, form) {
        if (!that.is_locked) {
            that.is_locked = true;

            addLoading();
            errorText();

            clearErrors();

            var r = that.rand = Math.random();
            $('iframe[name="' + form.attr('id') + '-target"]').one('load', function () {
                if (r != that.rand) {
                    return;
                }

                // Make sure we'll show something even when bad things happen and we don't have any JSON.
                var timeout = setTimeout(function () {
                    that.is_locked = false;
                    removeLoading();
                    errorText($__('An error occurred while processing your request. Please try again later.'));
                }, 200);

                var iframe = $(this);
                setTimeout(function () {
                    if (r != that.rand) {
                        return;
                    }
                    var json = iframe.contents().find("body").html();
                    if (json) {
                        if (JSON && JSON.parse) {
                            json = JSON.parse(json);
                        } else {
                            eval('json = (' + json + ')');
                        }
                        if (json && json.errors) {
                            that.is_locked = false;
                            removeLoading();
                            for (var fld_name in json.errors) {
                                if (json.errors.hasOwnProperty(fld_name)) {
                                    if (fld_name) {
                                        var el = form.find('[name="' + fld_name + '"]');
                                        if (el.length) {
                                            el.addClass('error').parent().append($('<em class="errormsg f"></em>').html(json.errors[fld_name]));
                                            continue;
                                        }
                                    }
                                    errorText(json.errors[fld_name]);
                                }
                            }
                        } else if (json && json.data) {
                            var hash = '#/request/' + json.data;
                            $.wa.helpdesk_controller.stopDispatch(0);
                            $.wa.helpdesk_controller.lastView = null;
                            if (hash == window.location.hash) {
                                window.location.hash = hash;
                                $.wa.helpdesk_controller.redispatch();
                            } else {
                                window.location.hash = hash;
                            }
                        }
                        clearTimeout(timeout);
                    }
                }, 100);
            });

            // allow form to submit via its target iframe
            return true;
        }

        function addLoading() {
            removeLoading();
            that.$submitBtn.after("<i class='icon16 loading'></i>");
        }

        function clearErrors() {
            that.$form.find('.errormsg.f').remove();
            that.$form.find('.error').removeClass('error');
        }

        function removeLoading() {
            that.$submitBtn.next("i").remove();
        }

        function errorText(text) {
            text = text || '';
            that.$form.find('.errormsg').html(text);
        }
    };

    return helpdeskProRequests;
})(jQuery);
