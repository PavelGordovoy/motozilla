/*global $, $_*/
/*global localStorage, backend_url, wa_lang*/
'use strict';
$.mylang = {
    locales: null,
    selected: null,
    pages: null,
    data: null,
    translatable: ['name', 'summary', 'meta_title', 'meta_keywords', 'meta_description', 'description'],
    features: null,
    binder: null,
    init: function (type) {
        this.selected = localStorage.getItem('mylang/locale');
        if (type == 'category') {
            $.mylang.translateCategory();
        } else {
            $.mylang.binder = $('#s-product-edit-forms').find('.field :first');
            $(document).ajaxComplete(function (event, xhr, settings) {
                if (settings.url.indexOf('action=count')>0) {
                    return;
                }
                if ($.product.path.id != 'new' && $.product.path.id != null) {
                    $.mylang.initClone();
                    $.mylang.fill('product');
                    setTimeout(function () {
                        $('select.s-mylang-select').val($.mylang.selected).change();
                    }, 1000);
                }
            });
            $.mylang.initSelect();
        }
        $("select.s-mylang-select").val(localStorage.getItem('mylang/locale')).change();
    },
    initClone: function () {
        $.mylang.debindSaveSku();
        $.mylang.translatable.forEach(function (el) {
            $('[name^="product[' + el + ']"]').each(function () {
                var that = $(this);
                for (var i in $.mylang.locales) {
                    if ($.mylang.locales.hasOwnProperty(i)) {
                        $.mylang.clone(that, el, i);
                    }
                }
            });
        });
        $('[name^="skus"][name$="[name]"]').each(function () {
            var that = $(this);
            for (var i in $.mylang.locales) {
                if ($.mylang.locales.hasOwnProperty(i)) {
                    $.mylang.cloneSku(that, i);
                }
            }
        });
    },
    initSelect: function () {
        var selected = localStorage.getItem('mylang/locale');
        var sel = $('select.s-mylang-select');
        var options = '<option value="ALL">'+$_('All')+'</option>';
        for (var i in $.mylang.locales) {
            if ($.mylang.locales.hasOwnProperty(i)) {
                options += '<option value="' + i + '"';
                options += (selected == i) ? ' selected ' : '';
                options = options + '">' + $.mylang.locales[i] + '</option>';
            }
        }
        options = options + '<option value="NONE">'+$.mylang.main.name+'</option>';
        sel.append(options);
        if ($.products.hash.indexOf('category_id') > -1) {
            sel.detach().insertAfter('.hint.float-right');
        }
        sel.change();
        if ($.mylang.provider) {
            sel.after("<button onclick='$.mylang.translate(); return false;'>"+$_('Translate')+"</button>");
        }
    },
    translate: function () {
        var provider = $.mylang.provider;
        $.each(Object.keys($.mylang.locales), function (key, locale) {
            var lang = locale.substr(0, 2);
            $.mylang.translatable.forEach(function (el) {
                $('[name^="product[' + el + ']"]').each(function () {
                    var text = $(this).val();
                    var input = $('[name^="product[mylang][product][' + locale + ']"][name$="[' + el + ']"]');
                    var trtext = input.val();
                    if (text != '' && trtext.trim() === '') {
                        $.post(backend_url+'mylang/?module=backend&action=translate', {text: text, target:lang, provider:provider}, function (response) {
                            if (el !='description') {
                                input.val(response.data);
                            } else {
                                input.waEditor('insert',response.data)
                                // let resp = input.redactor('code.set', response.data);
                                // console.log(input, response.data, resp);
                                // input.val(response.data);
                            }
                        });
                    }
                });
                if ($.product_list.collection_hash[0] === 'category') {
                    $('[name^="' + el + '"]').each(function () {
                        var text = $(this).val();
                        var input = $('[name^="category[mylang][category][' + locale + ']"][name$="[' + el + ']"]');
                        var trtext = input.val();
                        if (text != '' && trtext.trim() === '') {
                            $.post(backend_url+'mylang/?module=backend&action=translate', {text: text, target:lang, provider:provider}, function (response) {
                                if (el !='description') {
                                    input.val(response.data);
                                } else {
                                    input.waEditor('insert',response.data)
                                    // input.redactor('code.set', response.data);
                                    // input.waEditor('sync');
                                    // input.val(response.data);
                                }
                            });
                        }
                    });
                }
            });
        });
    },
    fill: function (type) {
        $.mylang.data.forEach(function (string) {
            var $el = $("[name='" + type + '[mylang][' + type + '][' + string.locale + '][' + string.type_id + '][' + string.subtype + "]']");
            if ($el.data('filled') != 1) {
                $el.val(string.text);
                $el.data('filled', 1);
            }
        });
        $.mylang.initEditors();
    },
    clone: function (el, id, locale) {
        if (typeof $.mylang.binder.data(id + locale) == 'undefined') {
            if (id == 'sku_name') {
                var sku_id = el.parent().parent().data('id');
                id = 'sku_name_' + sku_id;
            }
            if (id == 'description') {
                var description = $('.description').parent();
                var html = '<div class="field-group" data-mylang="' + locale + '"><div class="field"><div class="name">' + $_('Description')+'  '+ locale + '</div><div class="value" id="s-product-description-' + locale + '"><div class="wa-editor-core-wrapper s-editor-core-wrapper"><ul class="wa-editor-wysiwyg-html-toggle s-wysiwyg-html-toggle"><li class="selected"><a class="wysiwyg" href="#">WYSIWYG</a></li> <li><a class="html" href="#">HTML</a></li></ul><div><textarea id="s-product-description-content-' + locale + '" name="product[mylang][product][' + locale + '][' + $.product.path.id + '][description]"></textarea></div></div></div></div></div>';
                description.after(html);
            } else if (id == 'meta_title') {
                var parent_div = el.parent().parent();
                var el_title = el.clone().attr('name', 'product[mylang][product][' + locale + '][' + $.product.path.id + '][' + id + ']').attr('data-mylang', locale).attr('id', null).attr('placeholder', $.mylang.locales[locale]).attr('title', locale).attr('value', '');
                parent_div.after('<div class="field-group" data-mylang="' + locale + '"><div class="field"><div class="name align-right">' + $.mylang.locales[locale] + '</div><div class="value">'+ $(el_title).prop('outerHTML') +'</div></div></div>');
            } else {
                el.clone().attr('name', 'product[mylang][product][' + locale + '][' + $.product.path.id + '][' + id + ']').attr('data-mylang', locale).attr('id', null).val('').attr('placeholder', $.mylang.locales[locale]).attr('title', locale).insertAfter(el).before('<br>');
            }
            $.mylang.binder.data(id + locale, '1');
        }
    },
    cloneSku: function (el, locale) {
        let sku_id;
        if (el.parent().parent().data('id') > 0) {
            sku_id = el.parent().parent().data('id'); //ss6
        } else {
            sku_id = el.parent().parent().parent().data('id'); //ss7
        }
        var id = 'skuname_' + sku_id;
        if (typeof $.mylang.binder.data(id + locale) == 'undefined' && sku_id > 0) {
            el.clone().attr('name', 'product[mylang][product][' + locale + '][' + $.product.path.id + '][' + id + ']').attr('data-mylang', locale).attr('id', null).val('').attr('placeholder', $.mylang.locales[locale]).attr('title', locale).insertAfter(el);
            $.mylang.binder.data(id + locale, '1');
        }
    },
    debindSaveSku: function () {
        if ($('[name*="[skuname"]').length < 1) {
            $.each($.mylang.binder.data(), function (value) {
                if (value.indexOf('skuname') > -1) {
                    $.mylang.binder.removeData(value);
                }
            });
        }
        $('#s-product-save-button').click(function () {
            var valueObject = {};
            $("[name*='skuname']").each(function () {
                valueObject[$(this).attr('name')] = $(this).val();
            });
            $(document).one('ajaxComplete', function () {
                $.each(valueObject, function (name, value) {
                    $("[name='" + name + "']").val(value);
                });
            });
        });
    },
    translateCategory: function () {
        var c_id = $('.hint.float-right').text().replace('id: ', '');
        $.mylang.translatable.forEach(function (id) {
            Object.keys($.mylang.locales).forEach(function (key) {
                var locale = key;
                if (id == 'description') {
                    var description = $('.description').parent();
                    var html = '<div class="field-group" data-mylang="' + locale + '"><div class="field"><div class="name">' + $_('Description')+'  ' + locale + '</div><div class="value" id="s-category-description-' + locale + '"><div class="wa-editor-core-wrapper s-editor-core-wrapper"><ul class="wa-editor-wysiwyg-html-toggle s-wysiwyg-html-toggle"><li class="selected"><a class="wysiwyg" href="#">WYSIWYG</a></li> <li><a class="html" href="#">HTML</a></li></ul><div><textarea id="s-category-description-content-' + locale + '" name="category[mylang][category][' + locale + '][' + c_id + '][description]"></textarea></div></div></div></div></div>';
                    description.after(html);
                } else if (id == 'meta_title') {
                    var el = $('[name="' + id + '"]');
                    var parent_div = el.parent().parent();
                    var el_title = el.clone().attr('name', 'category[mylang][category][' + locale + '][' + c_id + '][' + id + ']').attr('data-mylang', locale).attr('id', null).attr('placeholder', $.mylang.locales[locale]).attr('title', locale).attr('value', '');
                    parent_div.after('<div class="field-group" data-mylang="' + locale + '"><div class="field"><div class="name align-right">' + $.mylang.locales[locale] + '</div><div class="value">'+ $(el_title).prop('outerHTML') +'</div></div></div>');
                } else {
                    var elem = $('[name="' + id + '"]:last');
                    elem.clone().attr('name', 'category[mylang][category][' + locale + '][' + c_id + '][' + id + ']').attr('data-mylang', locale).attr('id', null).attr('value', '').attr('placeholder', $.mylang.locales[locale]).insertAfter(elem);
                }
            });
        });
        var timerId = setInterval(function () {
            if (typeof wa_lang != 'undefined') {
                $.mylang.fill('category');
                $.mylang.initSelect();
                clearInterval(timerId);
            }
        }, 50);
    },
    initEditors: function () {
        let type = 'product';
        if ($('#s-product-save-button').length < 1) {
            type = 'category';
        }
        for (var i in $.mylang.locales) {
            if ($.mylang.locales.hasOwnProperty(i)) {
                let $description = $('#s-'+type+'-description-content-' + i);
                if ($description.length && !$description.data('redactor')) {
                    let $container = $description.parents('div.s-product-form');
                    let saveButton = $('#s-product-save-button');
                    if ($('#s-product-save-button').length < 1) {
                        saveButton = $('input[type=submit]','#s-product-list-create-form');
                    }
                    $description.waEditor({
                        lang: wa_lang,
                        modification_wysiwyg_msg: $description.data('modification-wysiwyg-msg'),
                        toolbarFixedTopOffset: $('#mainmenu').length ? $('#mainmenu').height() : 0,
                        imageUploadFields: $description.data('uploadFields'),
                        saveButton: saveButton,
                        toolbarFixed: false,
                        callbacks: {
                            change: function () {
                                    $description.waEditor('sync');
                                    if (type == 'product') {
                                        $.product.helper.onChange($container);
                                    }
                            },
                            keydown: function (e) {
                                //Ctrl+S
                                if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey)) {
                                    e.preventDefault();
                                    if ($('#s-product-save-button').length > 0) {
                                        $('#s-product-save-button').click();
                                    }
                                    if ($('input[type=submit]','#s-product-list-create-form').length > 0) {
                                        $('input[type=submit]','#s-product-list-create-form').click()
                                    }
                                    return false;
                                } else {
                                    if (type == 'product') {
                                        $.product.helper.onChange($container);
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }
        $('.s-'+type+'-form.descriptions').on('focusout', function () {
            for (var locale in $.mylang.locales) {
                $('#s-'+type+'-description-content-' + locale).waEditor('sync');
            }
        });
    },
};

$(document).on('change', '.s-mylang-select', function () {
    let data_mylang=$('[data-mylang]');
    data_mylang.hide();
    var selected = $(this).val();
    localStorage.setItem('mylang/locale', selected);
    if ($(this).val() == 'ALL') {
        data_mylang.show();
    } else if ($(this).val() != 'NONE') {
        $('[data-mylang=' + selected + ']').show();
        if ($(this).prev().data('mylang') == selected) {
            $(this).show();
        }
    }
    $('.s-mylang-select').each(function () {
        $(this).val(localStorage.getItem('mylang/locale'));
    });
    $.mylang.selected = selected;
});
//Shop-Script 7
$("#s-product-list-settings-dialog").on('mousedown', "input[type=submit]","#s-product-list-settings-form", function(){
    $('[id^=s-category-description-content-]').each(function(){this.waEditor('sync')});
});
//Shop-Script 8 Needs checking
/*$("#s-product-list-create-form").on('mousedown', "input[type=submit]","#s-product-list-settings-form", function(){
    $('[id^=s-category-description-content-]').each(function(){this.waEditor('sync')});
});*/


$('#s-content').on('mousedown', '#s-page-settings-toggle, #s-page-advanced-params-link', function () {
    if ($('select[name*="mylang_locale"]', '#s-page-settings').length > 0) {
        return false;
    }
    var sel = '<div class="name bold">'+$_('Locale')+'</div><div class="value"><select name="info[mylang_locale]"><option value="">'+$_('Everyone')+'</option>';
    Object.keys($.mylang.localesAll).forEach(function (key) {
        sel += '<option value="' + key + '">' + $.mylang.localesAll[key] + '</option>';
    });
    $('.s-page-url:first', '#s-page-settings').append(sel + '</select></div>');
    $('select[name*="mylang_locale"]', '#s-page-settings').val($.mylang.pages[$.product_pages.page_id]);
    $('#s-page-advanced-params').show();
    $('#s-page-advanced-params-link').remove();
    return true;
});

$.extend($.product.helper = $.product.helper, {
    updateInput: function (name, value, html, id) {
        if (name && name.indexOf('mylang') > 0) {
            var selector = '.s-' + name.replace(/\[(.+?)\]/g, '-$1') + '-input';
            $.shop.trace('update field: ' + name + ' ' + selector, value);
            var el = $(selector);
            if (el.is('input,textarea')) {
                el.val(value);
            } else if (html) {
                if (name === 'product[summary]') {
                    el.text(value); // escaped
                } else {
                    el.html(value); // not escaped
                }
            } else {
                el.text(value);
            }
            if (id) {
                var input_name = $(selector).attr('name');
                input_name = input_name.replaceAll(/\[-[\d]+\]/, '[' + id + ']');
                $.shop.trace('$.product.helper.updateInput name', [input_name]);
            }
        }
    }
});
