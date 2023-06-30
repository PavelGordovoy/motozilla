/*global $, $_*/
/*global localStorage, backend_url*/
'use strict';
$.mylang = {
    locales: null,
    selected: null,
    data: null,
    binder: null,
    translatable: ['name', 'description'],
    album: null,
    init: function() {
        this.selected = localStorage.getItem('mylang/locale');
        $.mylang.binder = $('#album-settings-dialog').find('.field :first');
    },
    cloneAlbum: function (el, id, locale) {
        if (typeof $.mylang.binder.data(id + locale) == 'undefined') {
            el.clone().attr('name', 'mylang[album][' + locale + '][' + this.album + '][' + id + ']').attr('data-mylang', locale).attr('id', null).val('').attr('placeholder', $.mylang.locales[locale]).attr('title', locale).insertAfter(el).before('<br>');
            $.mylang.binder.data(id + locale, '1');
        }
    },
    albumInit: function() {
        var album = $.photos.getAlbum();
        this.album = album ? album.id : 0;
        $.mylang.translatable.forEach(function (el) {
            $('[name^="' + el + '"]').each(function () {
                var that = $(this);
                for (var i in $.mylang.locales) {
                    if ($.mylang.locales.hasOwnProperty(i)) {
                        $.mylang.cloneAlbum(that, el, i);
                    }
                }
            });
            
        });
        $.mylang.fill('album');
    },
    fill: function (type) {
        $.mylang.data.forEach(function (string) {
            var $el = $('[name="mylang[' + type + '][' + string.locale + '][' + string.type_id + '][' + string.subtype + ']"');
            if ($el.data('filled') != 1) {
                $el.val(string.text);
                $el.data('filled', 1);
            }
        });
    },
};

$('#mylang_photo_form').on('submit', function(e){
     e.preventDefault();
     var fields = $(this).serializeArray();
     fields.push({'name':'type_id', 'value': $.photos.getPhotoId()});
     fields.push({'name':'app_id', 'value':'photos'});
     $.post(backend_url+'mylang/?module=backend&action=save', fields).done(function(response){
         if(response.status == 'ok') {
             $('#mylang-photosave-message').show().fadeOut(1500);
         }
     });
});