$.mylang.editor = {
    init:function(options){
        this.locales = options.locales;
        this.slug = options.slug;
        this.page = options.page;
    },
    'locales': '',
    'locale': { id:"", name:""},
    'slug': '',
    'page': '',
    'pages':0,
    'total':0,
    escape: function (text) {
        text = this.ifset(text);
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    },
    ifset: function (variable) {
        if (typeof variable !== "undefined" && variable) {
            return variable;
        } else return '';
    },
    show_tip: function (el) {
        el.style.display = "block";
    },
    hide_tip: function (el) {
        el.style.display = "";
    },
    'pagination': {
        set_per_page: function(items){
            document.cookie ="mylangentries_per_page=" + items + "; expires=Tue, 24 Nov 2026 18:18:45 GMT; path=/";
        },
        change_page: function(page){
            $.mylang.editor.page = page;
            $.mylang.editor.getstrings($.mylang.editor.locale.id);
        },
        init:function(){
            $.mylang.editor.page;
            $.mylang.editor.pages;
            $.mylang.editor.total;

            let paging_next=$('#paging-next');
            paging_next.hide();
            let paging_prev = $('#paging-prev');
            paging_prev.hide();
            $('.paging').show();
            if ($.mylang.editor.page > 1 && $.mylang.editor.page <= $.mylang.editor.pages) {
                paging_prev.show();
            }
            if ($.mylang.editor.page > 0 && $.mylang.editor.page <= $.mylang.editor.pages-1) {
                paging_next.show();
            }
        },
    },
    addstrings: function(strings) {
        if (strings.length < 1) return false;
        strings.push({ name:"slug", value:$.mylang.editor.slug });
        $.post('?module=editorjs&action=addStrings', strings)
            .done(function(response){
                if (response.status == 'ok') {
                    $.mylang.editor.getstrings();
                    $('#add-dialog').trigger('close');
                }
            })
    },
    deletestrings: function (strings) {
        if (strings.length < 1) return false;
        strings = ({slug: $.mylang.editor.slug, strings: strings, locale: $.mylang.editor.locale.id});
        $.post('?module=editorjs&action=deleteStrings', strings)
            .done(function (response) {
                if (response.status == 'ok') {
                    $.mylang.editor.getstrings();
                }
            })
    },
    getstrings: function(value) {
        if (typeof(value) == 'undefined' || value=='') value = $.mylang.editor.locale.id;
        if (value == '') return;
        $('.loading').show();
        $.getJSON('?module=editorjs', {
            action : "getstrings",
            locale : value,
            slug : this.slug,
            page : this.page,
            query: $('input[name="query"]').val(),
        }).done(function(data){
            if (data.status =='ok') {
                $.mylang.editor.pages = data.data.mylang_pagecount;
                delete data.data.mylang_pagecount;
                $.mylang.editor.total = data.data.mylang_entries;
                delete data.data.mylang_entries;
                $.mylang.editor.data = data.data;
                $.mylang.editor.pagination.init();
                let html ='<table class="zebra" ><tr><th>'+$_('System keys')+'</th><th>'+$.mylang.editor.locale.name+'</th><th></th></tr>';
                $.each(data.data, function(i, item){
                    if (typeof(item.msgid_plural) !=='undefined') { //plural
                        html += '<tr data-id="'+item.id+'"><td>'+ $.mylang.editor.escape(item.msgid) + '</td><td><textarea name="'+item.id+'[0] placeholder="1">' + $.mylang.editor.escape(item['msgstr[0]']) + '</textarea></td><td rowspan="2"><a href="#" class="delete_strings"><i class="icon16 delete"></i></a></td></tr>';
                        html += '<tr data-id="'+item.id+'"><td>'+ $.mylang.editor.escape(item.msgid_plural) + '</td>';
                        html += '<td>'+$_('Plural form')+'<br><textarea placeholder="2..3" name="'+item.id+'[1]">' + $.mylang.editor.escape(item['msgstr[1]']) + '</textarea><br>';
                        html += '<textarea placeholder="5..10" name="'+item.id+'[2]">'+$.mylang.editor.escape(item['msgstr[2]'])+'</textarea></td></tr>';
                    } else {
                        html += '<tr data-id="'+item.id+'"><td class="original">'+ $.mylang.editor.escape(item.msgid) + '</td><td><textarea name="'+item.id+'" class="translated">' + $.mylang.editor.escape(item.msgstr) + '</textarea></td><td class="delete_strings"><a href="#" class="delete_strings"><i class="icon16 delete"></i></a></td></tr>';
                    }
                });
                html += '</table>';
                $('#transtable').html(html);
                $('.loading').hide();
                $('#wa-save-button').addClass('green').removeClass('yellow');
                $('textarea').each(function () {
                    this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
                }).on('input', function () {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
                if($('input[name="query"]').val().length) $('#clearQuery').removeClass('stealth');
            } else {
                $('.loading').hide();
                $('#transtable').html(data.errors);
            }
        });
    },
    translate: function (provider) {
        var lang = $.mylang.editor.locale.id.substr(0, 2);
        var errors = false;
        $('.loading').show();
        $.each($('.translated:empty'), function () {
            if (errors) return false;
            var self = this;
            var text = $(this).parent().siblings().text();
            if (text != '') {
                $.post(backend_url + 'mylang/?module=backend&action=translate', {
                    text: text,
                    target: lang,
                    source: 'en_US',
                    provider: provider
                }, function (response) {
                    if (response.status == 'ok') {
                        $(self).text(response.data);
                        $(self).addClass('changed');
                        $('#wa-save-button').addClass('yellow').removeClass('green');
                    } else {
                        $('#wa-form-error').text(response.errors).removeClass('stealth');
                        errors = true;
                    }
                });
            }
        });
        $('.loading').hide();
    },
    getJSON: function(url, data) {
        return $.getJSON(url, data).done(function (data) {
            if (data.status == 'ok') {
                $('#editor_locale').trigger('change');
                $('#compile-form-status').show();
                setTimeout(function () {
                    $('#compile-form-status').hide();
                }, 1500);
            } else {
                $('#compile-form-error').text(data.errors).show();
                setTimeout(function () {
                    $('#compile-form-error').hide();
                }, 3000)
            }
        }).fail(function () {
            $('#compile-form-error').show();
            setTimeout(function () {
                $('#compile-form-error').hide();
            }, 1500);
        });
    }
};

$('#locale-sync').on('click', function() {
    $.mylang.editor.getJSON('?module=editorjs', {
        action : "sync",
        slug : $.mylang.editor.slug,
    });
});

$('#editor-repair').on('click', function() {
    $.mylang.editor.getJSON('?module=editorjs', {
        action : "repair",
        slug : $.mylang.editor.slug,
        locale: $.mylang.editor.locale.id,
    })
});

$('#editor-repair-keys').on('click', function() {
    $.mylang.editor.getJSON('?module=editorjs', {
        action : "repairkeys",
        slug : $.mylang.editor.slug,
        locale: $.mylang.editor.locale.id,
    })
});

$('#paging-next').on('click', function() {
    $.mylang.editor.page++;
    $.mylang.editor.getstrings();
});

$('#paging-prev').on('click', function() {
    $.mylang.editor.page--;
    $.mylang.editor.getstrings();
});

$(".locale-download").on('click', function () {
    $("#wa-locale-download-dialog").waDialog();
    return false;
});

$(".locale-upload").on('click', function () {
    $('#pofile-upload').trigger('reset');
    $('.wa-upload-dialog-error').text('');
    $("#wa-locale-upload-dialog").waDialog();
    return false;
});

$("#locale-compile-dialog").on('click', function () {
    $("#wa-locale-compile-dialog").waDialog();
    return false;
});

$('#editor_locale').on('change', function(){
    if (this.value == "") {
        $('.loading').hide();
        return;
    }
    $.mylang.editor.locale = { id:this.value, name:$.mylang.editor.locales[this.value]};
    $.mylang.editor.getstrings(this.value);
});

$("#editor_reload").on('click', function () {
    $('#editor_locale').trigger('change');
    return false;
});

$('.add_custom').click(function() {
    $('#add-form')[0].reset();
    $('#add-dialog').waDialog({
        onSubmit:function() {
            var data = $('#add-form').serializeArray();
            $.mylang.editor.addstrings(data);
            return false;
        },
    });
});

$("#add-dialog input[type='text']").each(function(){
    $.mylang.makeFlexibleInput('input[name="'+this.name+'"]', 30);
});

$('#editor_strings').on('click', '.delete_strings',function(e){
    e.preventDefault();
    e.stopImmediatePropagation();
    if(confirm($_("Delete string?"))) {
        $.mylang.editor.deletestrings($(this).closest('tr').data('id'));
    }
});

//Search by enter
$('input[name="query"]').keyup(function (e) {
    if (e.keyCode === 13) {
        $.mylang.editor.getstrings();
    }
});

$('#clearQuery').click(function(){
    $(this).addClass('stealth');
    $('input[name="query"]').val('');
    $.mylang.editor.getstrings();
});