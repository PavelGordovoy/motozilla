"use strict";

/**
 * object to work with pocket
 */
$.pocketlists.Pocket = function ($pocket_wrapper, options) {
    var $sidebarPocketsWrapper = $('[data-pl2-sidebar-wrapper="pockets"]'),
        $loading = $('<i class="icon16 loading"></i>'),
        pocket_id = 0,
        request_in_action = false,
        o = $.extend({}, {
            current_user_id: 0,
            listsWrapper: null
        }, options);

    var _settingDialog = function () {
        var pocketId = $(this).data('pl2-pocket-id') || 0;

        $.get('?module=pocket&action=settingsDialog&id=' + pocketId)
        .done(function (data) {
            {
                $.waDialog({
                    content: $(data).find('.dialog-content').html(),
                    footer: $(data).find('.dialog-buttons').html(),
                    onOpen: function ($dialog, dialogIstance) {
                        var $dialogWrapper = $dialog;
        
                        $dialogWrapper
                            .on('click', '#pl-pocket-color a', function (e) {
                                e.preventDefault();
        
                                $dialogWrapper.find('#pl-pocket-color input').val($(this).data('pl-pocket-color'));
                                $(this).addClass('selected')
                                    .siblings().removeClass('selected')
                            })
                            .on('click', '[data-pl2-action="delete-pocket"]', function (e) {
                                e.preventDefault();
        
                                _deletePocket.call(dialogIstance, pocketId);
                            })
                            .on('click', '.cancel', function (e) {
                                e.preventDefault();

                                dialogIstance.close();
                            })
                            .on('click', '.dialog-footer input[type="submit"]', function (e) {
                                e.preventDefault();
  
                                $(this).after($loading);
                                $.post('?module=pocket&action=save', $dialogWrapper.find('.form :input').serialize(), function (r) {
                                    $loading.remove();
                                    if (r.status === 'ok') {
                                        dialogIstance.close();
                                        if (!pocketId) {
                                            window.location.hash = '#/pocket/' + r.data.id;
                                        }
                                        $.pocketlists_routing.redispatch();
                                    } else {
                
                                    }
                                }, 'json');
                            })
                        ;
        
                        setTimeout(function () {
                            if (!$dialogWrapper.find('[name="pocket[id]"]').val() == 0) {
                                $dialogWrapper.find('[name="pocket[name]"]').trigger('focus');
                            }
                        }, 10);
                    }
                });  
            }
        }) 
    };

    var _sidebarHandlers = function () {
        $sidebarPocketsWrapper.on('click', '[data-pl2-action="add-pocket"]', function (e) {
            e.preventDefault();

            _settingDialog();
        });
    };

    var _initSortList = function ($lists_wrapper) {
        if (!o.isAdmin) {
            return;
        }

        $lists_wrapper.sortable({
            draggable: '[data-pl-list-id]',
            delay: 200,
            delayOnTouchOnly: true,
            animation: 150,
            forceFallback: true,
            ghostClass:'pl-list-placeholder',
            // chosenClass:'album-list-chosen',
            // dragClass:'album-list-drag',
            onEnd: function(event) {
                let $item = $(event.item);
                /* хак для предотвращения срабатывания клика по элементу после его перетаскивания*/
                let $link = $item.find('[onclick]'),
                    href = $link.attr('onclick');
                $link.attr('onclick', 'javascript:void(0);');
                setTimeout(() => $link.attr('onclick', href),500)

                var getLists = function () {
                    var data = [];
                    $lists_wrapper.find('[data-pl-list-id]').each(function (i) {
                        var $this = $(this);
                        // color = $this.attr('class').match(/pl-(.*)/);
                        data.push({
                            id: $this.data('pl-list-id'),
                            sort: i
                            // color: color[1]
                        });
                    });
                    return data;
                };

                var updateSort = function () {
                    $.post(
                        '?module=list&action=sort',
                        {
                            data: getLists(),
                            pocket_id: pocket_id
                        },
                        function (r) {
                            if (r.status === 'ok') {
                            } else {
                                alert(r.errors);
                            }
                        },
                        'json'
                    );
                };

                updateSort();
            }
        });
    };

    var _initDropList = function ($lists_wrapper) {
        $('[data-pl-list-id]', $lists_wrapper).droppable({
            accept: '[data-parent-id]',
            disabled: false,
            greedy: true,
            tolerance: 'pointer',
            classes: {
                'ui-droppable': 'pl-droppable'
            },
            over: function (event, ui) {
                $(this).addClass('highlighted-background');
            },
            out: function (event, ui) {
                $(this).removeClass('highlighted-background');
            },
            drop: function (event, ui) {
                var $item = ui.draggable,
                    $list = $(event.target),
                    list_id = $list.data('pl-list-id');

                $item.trigger('moveToList.pl2', {
                    id: list_id,
                    drop: this
                });
                $(this).removeClass('highlighted-background');
                $item.addClass('pl-dropped');
            }
        });
    };

    function init() {
        pocket_id = $pocket_wrapper.data('pl2-pocket-wrapper');
        _addHandlers();
    }

    var _deletePocket = function (id) {
        id = id || pocket_id;
        var d = this;

        if (!confirm($_('DANGER! Pocket will be deleted with all its content including lists and to-dos. There will be no way to restore this data. Are you sure?'))) {
            return false;
        }

        if (request_in_action) {
            return;
        }
        request_in_action = true;

        $.post('?module=pocket&action=delete', {id: id}, function (r) {
            if (r.status === 'ok') {
                d.close();
                $.wa.setHash('#/todo/');
                $.pocketlists_routing.redispatch();
            }
        }, 'json').always(function () {
            request_in_action = false;
        });
    };

    function _addHandlers() {
        $pocket_wrapper.on('click', '[data-pl2-action="edit-pocket"]', function (e) {
            e.preventDefault();

            _settingDialog.call(this);
        });

        var $lists_wrapper = $('#pl-lists').find('.pl-lists');

        _initSortList(o.listsWrapper);
        _initDropList(o.listsWrapper);

        o.listsWrapper.on('moveTo.pl2', '[data-pl-list-id]', function (e, data) {
            // if (request_in_action) {
            //     return;
            // }
            // request_in_action = true;

            var $this = $(this),
                pocket_id = data['id'],
                drop = data['drop'];

            return $.post(
                '?module=list&action=moveTo',
                {
                    id: parseInt($this.data('pl-list-id')),
                    pocket_id: pocket_id
                },
                function (r) {
                    // $.pocketlists.$loading.removeAttr('style').remove();
                    if (r.status === 'ok') {
                        $this.remove();
                        // if ($this.find('.pl-item').data('pl-assigned-contact') === undefined) {
                        //     $this.find('.pl-item-name').after('<strong class="hint"><br>' + $_('Assigned to') + ' ' + r.data + '</strong>');
                        // } else {
                        //     $this.find('.pl-item-name + .hint').html('<br>' + $_('Assigned to') + ' ' + r.data);
                        // }
                        // $this.find('.pl-item').data('pl-assigned-contact', team_id);
                    }
                    $(drop).trigger('dropActionDone.pl2', {
                        $obj: $this,
                        result: r.status === 'ok'
                    });
                    // request_in_action = false;
                },
                'json'
            );
        });
    }

    if ($pocket_wrapper) {
        init();
    }

    return {
        showSettingsDoalog: _settingDialog
    }
};
