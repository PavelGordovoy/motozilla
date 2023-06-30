(function ($)
{
    $.Redactor.prototype.imagemanager = function ()
    {
        return {
            langs: {
                en: {
                    "Upload": "Upload",
                    "Choose": "Choose"
                },
                ru: {
                    "Upload": "Загрузить",
                    "Choose": "Выбрать из загруженных"
                }
            },
            init: function ()
            {
                if (!this.opts.imageManagerJson)
                {
                    return;
                }

                this.modal.addCallback('image', this.imagemanager.load);
            },
            load: function ()
            {
                var $box = $('<div style="overflow: auto; height: 300px; display: none;" class="redactor-modal-tab" data-title="' + this.lang.get('Choose') + ' (' + this.opts.imageManagerJson.length + ')' + '">');
                this.modal.getModal().append($box);

                $.each(this.opts.imageManagerJson, $.proxy(function (key, val)
                {
                    // title
                    var thumbtitle = '';
                    if (typeof val.title !== 'undefined')
                    {
                        thumbtitle = val.title;
                    }

                    var img = $('<div style="margin-right:10px;display:inline-block;pos"><img src="' + val.thumb + '"  data-params="' + encodeURI(JSON.stringify(val)) + '" style="max-width: 100px; max-height: 75px; cursor: pointer;" /></div>');
                    $box.append(img);
                    $(img).click($.proxy(this.imagemanager.insert, this));

                }, this));

            },
            insert: function (e)
            {
                var $el = $(e.target);
                var json = $.parseJSON(decodeURI($el.attr('data-params')));

                this.image.insert(json, null);
            }
        };
    };
})(jQuery);