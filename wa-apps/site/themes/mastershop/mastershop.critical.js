var responsiveMenu = {
    init: function (){
        var _this = this;

        _this.responsive();
        _this.resize();
    },
    responsive: function(){
        var _this = this;

        jQuery.each($(".js-resp-nav"), function(){
            _this.responsived($(".js-resp-nav"), '.js-resp-nav-el', '.js-header-nav-sub');
        });
        _this.categoriesResponsived($('.js-h-categories'), '.js-h-categories-item', '.js-categories-sub', $('.js-categories-h-else-items'));
    },
    resize: function(){
        var _this = this;

        $(window).resize(function() {
            _this.responsive();
        });
    },
    responsived: function(menu, itemSelector, subwrapSelector){
        var _this = this,
            menuWidth = menu.width(),
            ItemElse = menu.find(itemSelector + '[data-type="else"]'),
            ItemElseSubwrap = ItemElse.find(subwrapSelector),
            ItemElseSubwrapItems = ItemElse.find(itemSelector),
            ItemElseWidth = parseFloat(ItemElse.removeClass('hide').outerWidth(true)),
            classElement = menu.data("class-el"),
            classSubElement = menu.data("class-sub-el");

        if(ItemElseSubwrapItems.length){
            if(classElement){
                ItemElseSubwrapItems.removeClass(classSubElement);
            }
            if(classSubElement){
                ItemElseSubwrapItems.addClass(classElement);
            }
            var clone = ItemElseSubwrapItems.clone();
            clone.insertBefore(ItemElse);
        }

        ItemElse.addClass('hide');
        ItemElseSubwrap.html("");

        var items = menu.children(itemSelector + ':not([data-type="else"])'),
            allItemsWidth = 0;

        jQuery.each(items, function(){
            var $this = $(this),
                elWidth = parseFloat($this.outerWidth(true));


            if((allItemsWidth + elWidth + ItemElseWidth) > menuWidth){
                ItemElse.removeClass('hide');
                var clone = $this.clone();

                if(classElement){
                    clone.removeClass(classElement);
                }
                if(classSubElement){
                    clone.addClass(classSubElement);
                }
                clone.appendTo(ItemElseSubwrap);
                elWidth += $this.outerWidth(true);
                $this.remove();
            }
            allItemsWidth += elWidth;
        });

        menu.css("overflow", "visible");
    },
    categoriesResponsived: function(menu, itemSelector, subwrapSelector, menuElse){
        var _this = this,
            menuWidth = menu.width(),
            ItemElse = menu.find(itemSelector + '[data-type="else"]'),
            ItemElseWidth = parseFloat(ItemElse.removeClass('hide').outerWidth(true));

        var items = menu.children(itemSelector + ':not([data-type="else"])'),
            allItemsWidth = 0;

        items.removeClass("hide");
        ItemElse.addClass('hide');
        ItemElse.find(subwrapSelector).remove();
        ItemElse.append(menuElse.html());
        ItemElse.find(".js-subcategories-item").addClass('hide');

        jQuery.each(items, function(){
            var $this = $(this),
                elWidth = parseFloat($this.outerWidth(true)),
                id = $this.data("id");

            if((allItemsWidth + elWidth + ItemElseWidth) > menuWidth){
                ItemElse.removeClass('hide');
                ItemElse.find(".js-subcategories-item[data-id='" + id + "']").removeClass('hide');

                elWidth += $this.outerWidth(true);
                $this.addClass("hide");
            }
            allItemsWidth += elWidth;
        });

        menu.css("overflow", "visible");
    },
    positionSubmenu: function (itemsSelector, outerSelector, subwrapSelector){
        $('body').on("hover", itemsSelector, function (){
            var $this = $(this),
                wrapMenu = $this.closest(outerSelector),
                subMenu = $this.find(subwrapSelector).first();
            if(subMenu.length){
                var wrapMenuOffset = wrapMenu.offset(),
                    wrapMenuOffsetLeft = wrapMenuOffset.left,
                    wrapMenuOffsetRight = parseFloat(wrapMenuOffsetLeft) + parseFloat(wrapMenu.outerWidth(true));

                subMenu.removeClass('to-left').removeClass('to-right').removeAttr('style');
                subMenu.css('visibility', 'hidden').css('display', 'block');
                var subMenuOffset = subMenu.offset(),
                    subMenuOffsetLeft = subMenuOffset.left,
                    subMenuWidth = parseFloat(subMenu.outerWidth(true)),
                    subMenuOffsetRight = (parseFloat(subMenuOffsetLeft) + subMenuWidth);
                if(wrapMenuOffsetRight < subMenuOffsetRight){
                    subMenu.addClass('to-left');
                }
                subMenu.removeAttr('style');
            }
        });
    }
};