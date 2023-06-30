var helpdeskProConstructor = (function ($) {
    helpdeskProConstructor = function (options) {
        var that = this;

        // DOM
        that.$form = options.$form;

        // DYNAMIC VARS
        that.is_locked = false;
        that.type = options.type || '';

        // INIT
        that.initClass();
    };

    helpdeskProConstructor.prototype.initClass = function () {
        var that = this;

        that.initSortable();
        that.bindEvents();
    };

    helpdeskProConstructor.prototype.bindEvents = function () {
        var that = this;

        /* Изменение активности поля */
        that.$form.find(':checkbox').change(function () {
            that.updateSortable();
        });

        /* Открытие окна с информацией о поле контакта */
        that.$form.find(".js-edit-field").click(function () {
            new helpdeskProDialog({
                url: '?plugin=pro&module=dialog&action=' + (that.type == 'Additional' ? 'additionalField' : 'contactField') + '&field_id=' + $(this).data('id'),
                saveUrl: '?plugin=pro&module=constructor&action=save&field_id=' + $(this).data('id'),
                onOpen: function () {
                    var d = this;

                    $.helpdesk_pro.initColorpicker();

                    /* Изменение видимости альтернативного название товара */
                    d.$form.find('.js-show-name').change(function () {
                        var checkbox = $(this),
                            name = checkbox.closest("label").next();
                        if (checkbox.prop('checked')) {
                            name.slideDown();
                        } else {
                            name.slideUp();
                        }
                    });
                },
                onSuccessCallback: function () {
                    $.get('?plugin=pro&module=constructor' + that.type, function (response) {
                        that.$form.find('.fields').replaceWith($(response).find('.fields'));
                        that.initClass();
                    });
                }
            });
        });

    };

    helpdeskProConstructor.prototype.initSortable = function () {
        var that = this;

        that.$form.find('.fields').sortable({
            helper: 'clone',
            items: '.field.sortable',
            opacity: 0.75,
            handle: '.sort',
            tolerance: 'pointer',
            update: function () {
                that.updateSortable();
            }
        });
    };

    helpdeskProConstructor.prototype.updateSortable = function () {
        var that = this,
            ids = [];
        that.$form.find('.fields .field.sortable').each(function () {
            var field = $(this);
            ids.push({id: field.data('id'), enable: field.find(":checkbox").prop('checked') ? 1 : 0});
        });

        $.post("?plugin=pro&module=backend&action=" + (that.type == 'Additional' ? 'additionalFieldsEnable' : 'contactFieldsSort'), {ids: ids});
    };

    return helpdeskProConstructor;
})(jQuery);
