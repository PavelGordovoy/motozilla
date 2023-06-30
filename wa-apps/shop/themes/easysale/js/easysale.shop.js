// Shop :: Color scheme custom

( function($) {

    var html = $('html');

    if (html.data('custom')) {

        function hex2rgb(hexStr){
            // note: hexStr should be #rrggbb
            var hex = parseInt(hexStr.substring(1), 16);
            var r = (hex & 0xff0000) >> 16;
            var g = (hex & 0x00ff00) >> 8;
            var b = hex & 0x0000ff;
            return [r, g, b];
        }

        var color = 'rgba('+hex2rgb(html.data('custom'))+',1)';
        var color2 = 'rgba('+hex2rgb(html.data('custom'))+',0.3)';
        $('head').append("<style>html.color_scheme_custom .s-homepage-products #products-sets .nav-link.active,html.color_scheme_pink ul.s-products-list li .product-item .s-preview-button i:hover{color:"+color+"}html.color_scheme_custom #s-mainslider.owl-theme .owl-dots .owl-dot.active span,html.color_scheme_custom #storequickorder .submit-button,html.color_scheme_custom .s-category-filters .ui-widget-content .ui-state-default,html.color_scheme_custom .s-compare-page .s-compare-filter .s-button.is-selected,html.color_scheme_custom .s-home-brands #s-home-brands-carousel.owl-theme .owl-nav div:hover,html.color_scheme_custom .s-home-shops #s-home-shops .owl-nav div:hover,html.color_scheme_custom .s-product-page .s-product-info .s-tabs-wrapper .s-tab-item.is-active,html.color_scheme_custom .s-range-slider .r-bar-wrapper .r-point,html.color_scheme_custom ul.s-products-list .owl-nav div:hover,html.color_scheme_custom ul.s-sidebar-categories>li>a:hover>span,html.color_scheme_custom ul.s-sidebar-subcategories>li>a:hover,html.color_scheme_custom .s-sidebar-subpages li:hover,html.color_scheme_custom .s-checkout-page .s-step-content .s-shipping-item.is-selected .s-shipping-label,html.color_scheme_custom .s-checkout-page .s-steps-wrapper .s-step-wrapper .s-step-content .s-payment-options .s-payment-item.is-selected .s-payment-header label,html.color_scheme_custom .c-input>input:checked~.c-indicator,html.color_scheme_custom .s-product-cart .s-options-wrapper .inline-select a.selected{background-color:"+color+"}html.color_scheme_custom .s-category-filters .ui-slider-horizontal .ui-widget-header,html.color_scheme_custom .c-input>input:active~.c-indicator{background-color:"+color2+"}html.color_scheme_custom .s-product-page .s-product-main-wrapper .s-photos-list .s-photo-thumb a.is-selected,html.color_scheme_custom .s-checkout-page .s-step-content .s-shipping-item.is-selected,html.color_scheme_custom .s-product-cart .s-options-wrapper .inline-select a.selected{border-color:"+color+"}html.color_scheme_custom .c-input > input:focus~.c-indicator{-webkit-box-shadow: 0 0 0 .075rem #fff,0 0 0 .2rem "+color+";box-shadow: 0 0 0 .075rem #fff,0 0 0 .2rem "+color+";}</style>");
        // mobile css
        $('head').append("<style>html.color_scheme_custom #s-mobile-nav>ul>li a:hover,html.color_scheme_custom #s-mobile-nav>ul>li.active a{background-color:"+color+"}</style>");
    }

})(jQuery);

// Mobile :: Fixed header

( function($) {

    var fixedHeader = function() {

        var mobileCatalog = $('#s-mobile-catalog');

        if (mobileCatalog.length && MatchMedia("(max-width: 1199px)")) {
            $(window).scroll(function() {
                if ($(this).scrollTop() > 500) {
                    mobileCatalog.css({
                        'position': 'fixed',
                        'top': '0'
                    });
                } else {
                    mobileCatalog.css({
                        'position': 'absolute',
                        'top': 'calc(100% - 41px)'
                    });
                }
            });
        }

    };

    $(document).ready( function() {
        fixedHeader();
    });

})(jQuery);

// Captcha :: Refresh
( function($) {

    var bindEvents = function() {

        // Click on refresh button or image
        $(".wa-captcha").on("click", ".wa-captcha-refresh, .wa-captcha-img", function() {
            refreshCaptcha( $(this) );
            return false;
        });

    };

    // Refresh Captcha
    var refreshCaptcha = function( $target ) {
        var $wrapper = $target.closest(".wa-captcha"),
            captcha = $wrapper.find(".wa-captcha-img");

        if (captcha.length) {
            var newCaptchaHref = captcha.attr("src").replace( /\?.*$/,'?rid='+Math.random() );

            captcha.attr("src", newCaptchaHref);

            captcha.one("load", function() {
                $wrapper.find('.wa-captcha-input').focus();
            });
        }

        $wrapper.find("input").val("");
    };

    $(document).ready( function() {
        bindEvents();
    });

})(jQuery);

// Checkout Marking Active Options
( function($) {

    var storage = {
        activeStepClass: "is-selected",
        getCheckoutOptions: function() {
            return $(".checkout-options li");
        }
    };

    var initialize = function() {
        var $checkoutOptions = storage.getCheckoutOptions();

        $checkoutOptions.find("input[type=\"radio\"]").each( function() {
            var $input = $(this),
                is_active = ( $(this).attr("checked") === "checked" );

            if (is_active) {
                markOption( $input );
            }
        });
    };

    var bindEvents = function() {
        var $checkoutOptions = storage.getCheckoutOptions();

        $checkoutOptions.find("input[type=\"radio\"]").on("click", function() {
            markOption( $(this) );
        });

    };

    var markOption = function( $input ) {
        var $wrapper = $input.closest("li"),
            $checkoutOptions = storage.getCheckoutOptions();

        // Clear class for all
        $checkoutOptions.removeClass(storage.activeStepClass);

        // Mark this
        $wrapper.addClass(storage.activeStepClass);
    };

    $(document).ready( function() {
        //
        initialize();
        //
        bindEvents();
    });

})(jQuery);

// Shop :: Product Page :: Tabs
( function($) {
    var storage = {
        shown_class: "is-shown",
        active_class: "is-active",
        getWrapper: function() {
            return $("#s-product-info");
        },
        getTabWrapper: function() {
            return this.getWrapper().find(".s-tabs-wrapper");
        },
        getTabListItems: function() {
            return this.getTabWrapper().find(".s-tab-item");
        },
        getTabContentItems: function() {
            return this.getWrapper().find(".s-tabs-content .s-tab-content");
        }
    };

    var bindEvents = function() {
        var $tabItems = storage.getTabListItems();

        $tabItems.on("click", function() {
            var $tab = $(this),
                content_id = $tab.data("content-id");

            if (content_id) {
                showTabContent( $tab, content_id );
                return false;
            }
        });
    };

    var showTabContent = function($tab_link, content_id ) {
        var $content = $("#tab-content-"+content_id),
            $tabs = storage.getTabListItems(),
            $content_item = storage.getTabContentItems();

        // hide old content
        $content_item.removeClass(storage.shown_class);

        // show current content
        $content.addClass(storage.shown_class);

        // unmark old tab
        $tabs.removeClass(storage.active_class);

        // mark current tab
        $tab_link.addClass(storage.active_class);
    };

    $(document).ready( function() {
        bindEvents();
    });

})(jQuery);

// Shop :: Compare Counter
( function($) {
    $(document).ready( function() {

        var $wrapper = $("#s-compare-counter"),
            empty_class = "is-empty",
            default_class = "default-bg";

        $wrapper.on("checkCompare", function() {
            checkCompare();
        });

        function checkCompare() {
            var compare = $.cookie('shop_compare'),
                count;

            compare = (compare) ? compare.split(',') : [];
            count = compare.length;

            if (count > 0) {
                $wrapper.find(".s-compare-count").addClass(default_class).text(count);
                $wrapper.removeClass(empty_class);
            } else {
                $wrapper.addClass(empty_class);
                $wrapper.find(".s-compare-count").removeClass(default_class).text('0');
            }
        }

        checkCompare();
    });
})(jQuery);

// Shop :: Favorite Counter
( function($) {
    $(document).ready( function() {

        var $wrapper = $("#s-favorite-counter"),
            empty_class = "is-empty",
            default_class = "default-bg";

        $wrapper.on("checkFavorite", function() {
            checkFavorite();
        });

        function checkFavorite() {
            var favorite = $.cookie('shop_favorite'),
                count;

            favorite = (favorite) ? favorite.split(',') : [];
            count = favorite.length;

            if (count > 0) {
                $wrapper.find(".s-favorite-count").addClass(default_class).text(count);
                $wrapper.removeClass(empty_class);
            } else {
                $wrapper.addClass(empty_class);
                $wrapper.find(".s-favorite-count").removeClass(default_class).text('0');
            }
        }

        checkFavorite();
    });
})(jQuery);

// Shop :: Change Currency
( function($) {
    $(document).ready( function () {
        $("#currency").change( function () {
            var href = location.href,
                url = (href.indexOf('?') == -1) ? href += '?' : href += '&';

            location.href = url + 'currency=' + $(this).val();
        });
    });
})(jQuery);

// Shop :: Change Language
( function($) {
    $(document).ready( function () {
        $("#language").change(function () {
            var href = location.href,
                url = (href.indexOf('?') == -1) ? href += '?' : href += '&';
            location.href = url + 'locale=' + $(this).val();
        });
    });
})(jQuery);

// Shop :: Header Catalog List
var CatalogList = ( function($) {

    CatalogList = function(options) {
        var that = this;

        // DOM
        that.$wrapper = options["$wrapper"];
        that.$button = that.$wrapper.find(".s-catalog-button");
        that.$list = that.$wrapper.find(".s-catalog-list");

        // VARS
        that.open_class = "is-opened";

        // DYNAMIC VARS
        that.is_opened = false;
        that.is_mobile = false;

        // INIT
        that.initClass();
    };

    CatalogList.prototype.initClass = function() {
        var that = this,
            button = that.$button[0];

        that.$button.on("mouseenter", onMouseEnter);
        that.$wrapper.on("mouseleave", onMouseLeave);

        button.addEventListener("touchstart", function () {
            if (!that.is_mobile) {
                that.$button.off("mouseenter", onMouseEnter);
                that.$wrapper.off("mouseleave", onMouseLeave);
                that.is_mobile = true;
            }
            that.toggleView();
        });

        function onMouseEnter() {
            that.toggleView("show");
        }

        function onMouseLeave() {
            that.toggleView("hide");
        }
    };

    CatalogList.prototype.toggleView = function( show ) {
        var that = this,
            show_list;

        if (show) {
            if (show == "show") {
                show_list = true;
            } else if (show == "hide") {
                show_list = false;
            }
        } else {
            show_list = !that.is_opened;
        }

        if (show_list) {
            that.$wrapper.addClass(that.open_class);
            that.is_opened = true;
        } else {
            that.$wrapper.removeClass(that.open_class);
            that.is_opened = false;
        }
    };

    return CatalogList;

})(jQuery);

// Shop :: Header Cart Counter
var updateHeaderCart = ( function($) {

    $(document).ready( function() {
        var $cart = $("#s-cart-wrapper"),
            $counter = $cart.find(".s-cart-count"),
            $price = $cart.find(".s-text-wrapper .s-text"),
            empty_class = "is-empty";

        updateHeaderCart = function(options) {
            var text = options["text"],
                count = options["count"];

            if (count > 0) {
                $cart.removeClass(empty_class);
                $price.html(text);
                $counter.html(count);

            } else {
                $cart.addClass(empty_class);
            }
        };

        return updateHeaderCart;

    });

})(jQuery);

// Shop :: Product Page :: Product Photos Class
var ProductPhotos = ( function($) {

    function getImageArray( $links ) {
        var imageArray = [];

        $links.each( function() {
            var image_src = $(this).attr("href");
            if (image_src) {
                imageArray.push(image_src);
            }
        });

        return imageArray;
    }

    ProductPhotos = function(options) {
        var that = this;

        that.$wrapper = options["$wrapper"];
        that.$mainLink = that.$wrapper.find("#s-photo-main");
        that.$video = that.$wrapper.find(".s-video-wrapper");
        that.$thumbs = that.$wrapper.find(".s-photos-list");
        that.$thumbsLinks = that.$thumbs.find("a");

        // VARS
        that.active_class = "is-selected";
        that.hidden_class = "is-hidden";
        that.imageArray = getImageArray( that.$thumbsLinks.length ? that.$thumbsLinks : that.$mainLink );

        // DYNAMIC VARS
        that.active_index = 0;
        that.$activeLink = ( that.$thumbs.find("a." + that.active_class) || false );

        // INIT
        that.bindEvents();
    };

    ProductPhotos.prototype.bindEvents = function() {
        var that = this;

        // EVENTS
        that.$thumbs.on("click", "a", function() {
            that.setPhoto( $(this) );
            return false;
        });

        that.$mainLink.on("click", function() {
            that.showSwipeBox();
            return false;
        });
    };

    ProductPhotos.prototype.setPhoto = function( $link ) {
        var that = this,
            is_video = $link.hasClass("is-video"),
            big_photo_src = $link.attr("href"),
            index = $link.data("index"),
            active_class = that.active_class;

        // Marking
        if (that.$activeLink) {
            that.$activeLink.removeClass(active_class)
        }
        $link.addClass(active_class);

        if (is_video) {

            that.$mainLink.addClass(that.hidden_class);
            that.$video.removeClass(that.hidden_class);

        } else {

            that.$mainLink.removeClass(that.hidden_class);
            that.$video.addClass(that.hidden_class);

            // Change main photo
            that.$mainLink.attr("href", big_photo_src)
                .find("img").attr("src", big_photo_src);
        }

        // Save data
        that.active_index = index;
        that.$activeLink = $link;
    };

    ProductPhotos.prototype.showSwipeBox = function() {
        var that = this,
            before = [],
            after = [],
            images;

        $.each(that.imageArray, function(index, image_src) {
            var image = {
                href: image_src,
                index: index
            };

            if (index >= that.active_index) {
                before.push(image);
            } else {
                after.push(image);
            }
        });

        images = before.concat(after);

        $.swipebox( images, {
            hideBarsDelay: false,
            useSVG: false
        });

        // Close on scroll
        $(window).one("scroll", function() {
            var $swipebox = $.swipebox;
            if ($swipebox.isOpen) {
                $swipebox.close();
            }
        });
    };

    return ProductPhotos;

})(jQuery);

// Shop :: Dialog Class
var Dialog = ( function($) { "use strict";

    Dialog = function(options) {
        var that = this;

        // LOAD
        that.html = ( options['html'] || false );
        that.$body = $("body");
        that.loadContent();

        // DOM
        that.$dialog = $("#s-dialog-wrapper");
        that.$content = $("#s-dialog-content");
        that.$closeLink = that.$dialog.find(".s-close-dialog");

        // VARS
        that.onClose = ( options['onClose'] || false );
        that.onCancel = ( options['onClose'] || false );
        that.dialog_shown = "dialog-is-show";

        // DYNAMIC VARS

        // INIT
        that.initDialog();

        return that;
    };

    Dialog.prototype.initDialog = function() {
        var that = this;

        that.showDialog();

        that.bindEvents();
    };

    Dialog.prototype.bindEvents = function() {
        var that = this,
            $dialog = that.$dialog,
            $content = that.$content,
            $closeLink = that.$closeLink

        $dialog.on("click", function() {
            $dialog.trigger("closeDialog");
            $dialog.trigger("onCancel");
        });

        $closeLink.on("click", function() {
            $dialog.trigger("closeDialog");
            return false;
        });

        $content.on("click", function(event) {
            event.stopPropagation();
        });

        // Custom events

        $dialog.on("closeDialog", function() {
            that.closeDialog();
        });

        $dialog.on("onClose", function() {
            if (that.onClose && (typeof that.onClose == "function") ) {
                that.onClose();
            }
        });

        $dialog.on("onCancel", function() {
            if (that.onCancel && (typeof that.onCancel == "function") ) {
                that.onCancel();
            }
        });
    };

    Dialog.prototype.loadContent = function() {
        var that = this,
            html = that.html,
            $dialog;

        $dialog = $('<div class="s-dialog-wrapper" id="s-dialog-wrapper"><div class="s-dialog-content" id="s-dialog-content">' + html +'</div></div>');

        $("#s-dialog-wrapper").remove();

        that.$body.append($dialog);
    };

    Dialog.prototype.showDialog = function() {
        var that = this,
            main = $('.s-main-wrapper');

        that.$body
            .addClass(that.dialog_shown)
            .append( that.$dialog );

        main.addClass('blur');

    };

    Dialog.prototype.closeDialog = function() {
        var that = this,
            $main = $('.s-main-wrapper');

        that.$dialog.trigger("onClose");

        that.$dialog.remove();

        that.$body.removeClass(that.dialog_shown);

        $main.removeClass('blur');
    };

    return Dialog;

})(jQuery);

// Shop :: Lazy Loading
var LazyLoading = ( function($) {

    var onScroll;

    LazyLoading = function(options) {
        var that = this;

        // VARS
        that.list_name = options["names"]["list"];
        that.items_name = options["names"]["items"];
        that.pagind_name = options["names"]["paging"];
        that.product_resize = options["names"]["product_resize"];
        that.load_class = "is-loading";

        // DOM
        that.$wrapper = ( options["$wrapper"] || false );
        that.$list = that.$wrapper.find(that.list_name);
        that.$window = $(window);

        // DYNAMIC VARS
        that.$paging = that.$wrapper.find(that.pagind_name);

        // INIT
        that.initLazyLoading();
    };

    LazyLoading.prototype.productsResize = function() {
        var that = this,
            $wrapper = that.$wrapper,
            $productList = $wrapper.find(".s-products-list.s-not-slider"),
            $elements = ['.s-image-wrapper', '.s-info-wrapper', '.s-pricing-height', 'li.s-product-wrapper'];

        $.fn.matchHeight._maintainScroll = true;

        $.each($elements, function(index,value) {

            var $element = $productList.find(value.toString());

            $element.matchHeight();

        });

    };

    LazyLoading.prototype.initLazyLoading = function() {
        var that = this;

        that.addWatcher();
    };

    LazyLoading.prototype.addWatcher = function() {
        var that = this,
            $product_resize = that.product_resize;

        onScroll = function() {
            that.onScroll();
        };

        that.$window.on("scroll", onScroll);

        if ($product_resize == 'jquery') {
            that.productsResize();
        }

    };

    LazyLoading.prototype.stopWatcher = function() {
        var that = this;

        if (typeof onScroll == "function") {
            that.$window.off("scroll", onScroll);
        }
    };

    LazyLoading.prototype.onScroll = function() {
        var that = this,
            is_paging_exist = ( $.contains(document, that.$paging[0]) );

        if (is_paging_exist) {

            var $window = that.$window,
                scroll_top = $window.scrollTop(),
                display_height = $window.height(),
                paging_top = that.$paging.offset().top;

            // If we see paging, stop watcher and run load
            if (scroll_top + display_height >= paging_top) {
                that.stopWatcher();
                that.loadNextPage();
            }

        } else {
            that.stopWatcher();
        }

    };

    LazyLoading.prototype.loadNextPage = function() {
        var that = this,
            next_page_url = getNextUrl(),
            $paging = that.$paging;

        function getNextUrl() {
            var $nextPage = that.$paging.find(".selected").next(),
                result = false;

            if ($nextPage.length) {
                result = $nextPage.find("a").attr("href");
            }

            return result;
        }

        function showLoad() {
            var $loading = '<div class="s-loading-wrapper"><i class="icon16 loading"></i>&nbsp;' + $paging.data("loading-text") + '</div>';

            $paging
                .addClass(that.load_class)
                .append($loading);
        }

        if (next_page_url) {

            showLoad();

            $.get(next_page_url, function(response) {
                var $category = $(response),
                    $newItems = $category.find(that.list_name + " " + that.items_name),
                    $newPaging = $category.find(that.pagind_name);

                that.$list.append($newItems);

                $paging.after($newPaging);

                $paging.remove();

                that.$paging = $newPaging;

                that.addWatcher();

            });

        }

    };

    return LazyLoading;

})(jQuery);

// Shop :: Promo CountDown
var CountDown = ( function($) {

    CountDown = function(options) {
        var that = this;

        // DOM
        that.$wrapper = options["$wrapper"];

        // VARS
        that.start = options["start"];
        that.end = options["end"];
        that.format = "%days%:%hours%:%minutes%:%seconds%";

        // DYNAMIC VARS
        that.period = that.getPeriod();
        that.time_period = null;
        that.timer = 0;

        // INIT
        that.run();
    };

    CountDown.prototype.getPeriod = function() {
        var that = this,
            start_date = new Date( that.start ),
            end_date = new Date( that.end );

        return (end_date > start_date) ? (end_date - start_date) : 0;
    };

    CountDown.prototype.getData = function() {
        var that = this,
            period = that.period;

        var second = 1000,
            minute = second * 60,
            hour = minute * 60,
            day = hour * 24,
            residue;

        var days = Math.floor(period/day);
        residue = ( period - days * day );

        var hours = Math.floor(residue/hour);
        residue = ( residue - hours * hour );

        var minutes = Math.floor(residue/minute);
        residue = ( residue - minutes * minute );

        var seconds = Math.floor(residue/second);

        return {
            days: days,
            hours: hours,
            minutes: minutes,
            seconds: seconds
        }
    };

    CountDown.prototype.getTime = function() {
        var that = this,
            data = that.getData(),
            result = that.format;

        return result
            .replace("%days%", (data.days < 10) ? "0" + data.days : data.days)
            .replace("%hours%", (data.hours < 10) ? "0" + data.hours : data.hours)
            .replace("%minutes%", (data.minutes < 10) ? "0" + data.minutes : data.minutes)
            .replace("%seconds%", (data.seconds < 10) ? "0" + data.seconds : data.seconds);
    };

    CountDown.prototype.run = function() {
        var that = this,
            timer = 1000;

        if (that.period > 0) {
            var time = that.getTime();

            that.$wrapper.html(time);

            that.period -= timer;

            if (that.period > 0) {
                that.timer = setTimeout( function () {
                    that.run();
                }, timer);
            }

        } else {
            that.destroy();
        }
    };

    CountDown.prototype.destroy = function() {
        var that = this;

        that.$wrapper.prev('.s-mainslider-counter-text').remove();
        that.$wrapper.remove();
    };

    return CountDown;

})(jQuery);

// Shop :: AJAX Products Filtering
var ProductsFilter = ( function($) {

    ProductsFilter = function(options) {
        var that = this;

        // DOM
        that.$wrapper = options["$wrapper"];
        that.$form = that.$wrapper.find("form");

        // VARS

        // DYNAMIC VARS
        that.is_locked = false;

        // INIT
        that.initClass();
    };

    ProductsFilter.prototype.initClass = function() {
        var that = this;
        //
        //that.initRangeSlider();
        //
        that.bindEvents();
    };

    ProductsFilter.prototype.initRangeSlider = function() {
        var that = this,
            $ranges = that.$wrapper.find(".s-range-item");

        $ranges.each( function() {
            var $range = $(this),
                $inputMin = $range.find(".min"),
                $inputMax = $range.find(".max"),
                min = +$range.data("min"),
                max = +$range.data("max"),
                left = +$inputMin.val(),
                right = +$inputMax.val();

            if ($inputMin.length && $inputMax.length && min >= 0 && max > 0) {
                new RangeSlider({
                    min: min,
                    max: max,
                    left_value: left,
                    right_value: right,
                    $wrapper: $range,
                    $inputMin: $inputMin,
                    $inputMax: $inputMax
                });
            }
        });
    };

    ProductsFilter.prototype.bindEvents = function() {
        var that = this;

        // On submit form
        that.$form.on("submit", function(event) {
            event.preventDefault();
            if (!that.is_locked) {
                that.onSubmit( $(this) );
            }
            return false;
        });

        // On change
        that.$form.find('.s-filter-item input').on("change", function(event) {
            event.preventDefault();
            if (!that.is_locked) {
                that.onChange( that.$form );
            }
            return false;
        });

        that.is_locked = false;
    };

    ProductsFilter.prototype.onSubmit = function( $form ) {
        var that = this,
            href = $form.attr("action"),
            data = $form.serialize(),
            $category = $("#s-category-wrapper");

        // Lock
        that.is_locked = true;

        // Animation
        $category.addClass("is-loading");

        $.get(href, data, function(html) {
            // Insert new html
            if ($category.length) {
                $category.replaceWith(html);
            }
            // Filter history
            if (!!(history.pushState && history.state !== undefined)) {
                window.history.pushState({}, '', '?'+data+'&filter');
                //console.log(data);
            }
            // Scroll to Top
            $("html, body").animate({
                scrollTop: $('.s-products-wrapper').offset().top
            }, 200);
            // Unclock
            that.is_locked = false;
        });
    };

    ProductsFilter.prototype.onChange = function( $form ) {
        var that = this,
            href = $form.attr("action"),
            data = $form.serialize(),
            $category = $("#s-category-wrapper");

            console.log(data);

        // Lock
        that.is_locked = true;

        // Animation
        $category.addClass("is-loading");

        $.get(href, data, function(html) {
            // Insert new html
            if ($category.length) {
                $category.replaceWith(html);
            }
            // Filter history
            if (!!(history.pushState && history.state !== undefined)) {
                window.history.pushState({}, '', '?'+data+'&filter');
            }
            // Unclock
            that.is_locked = false;
        });
    };

    return ProductsFilter;

})(jQuery);

// Shop :: Range Filter
var RangeSlider = ( function($) {

    var template =
    '<div class="s-range-slider">' +
        '<div class="r-bar-wrapper">' +
            '<span class="r-bar"></span>' +
            '<span class="r-point left"></span>' +
            '<span class="r-point right"></span>' +
        '</div>' +
    '</div>';

    RangeSlider = function(options) {
        var that = this;

        // DOM
        that.$wrapper = options["$wrapper"];
        that.$inputMin = options["$inputMin"];
        that.$inputMax = options["$inputMax"];

        // VARS
        that.min = options["min"];
        that.max = options["max"];
        that.left_value = options["left_value"];
        that.right_value = options["right_value"];
        that.move_class = "is-move";
        that.active_class = "is-active";

        // DYNAMIC DOM
        that.$rangeWrapper = false;
        that.$leftPoint = false;
        that.$rightPoint = false;
        that.$bar = false;
        that.$activePoint = false;

        // DYNAMIC VARS
        that.left = 0;
        that.right = 100;
        that.range_left = false;
        that.range_width = false;

        // INIT
        that.initClass();
    };

    RangeSlider.prototype.initClass = function() {
        var that = this;

        that.$rangeWrapper = $(template);
        that.$leftPoint = that.$rangeWrapper.find(".r-point.left");
        that.$rightPoint = that.$rangeWrapper.find(".r-point.right");
        that.$barWrapper = that.$rangeWrapper.find(".r-bar-wrapper");
        that.$bar = that.$rangeWrapper.find(".r-bar");
        that.$wrapper.after( that.$rangeWrapper );

        that.changeRange();

        that.bindEvents();
    };

    RangeSlider.prototype.bindEvents = function() {
        var that = this,
            move_class = that.move_class,
            $document = $(document),
            $body = $("body"),
            no_select = "no-user-select";

        that.$rangeWrapper.on("mousedown", ".r-point", function() {
            that.$activePoint = $(this);
            that.range_left = that.$barWrapper.offset().left;
            that.range_width = that.$barWrapper.outerWidth();

            // No selection then moving
            $body.addClass(no_select);

            // Add sub events
            $document.on("mousemove", onMouseMove);
            $document.on("mouseup", onMouseUp);
        });

        that.$inputMin.on("keyup", function() {
            var $input = $(this),
                val = parseInt( $input.val() );

            if ( !(val >= 0) ) {
                val = 0;
            }

            if (val < that.min) {
                val = that.min;
            }

            if (val >= that.max) {
                val = that.max * .99;
            }

            that.left_value = val;
            that.changeRange();
        });

        that.$inputMax.on("keyup", function() {
            var $input = $(this),
                val = parseInt( $input.val() );

            if ( !(val >= 0) ) {
                val = that.max;
            }

            if (val > that.max) {
                val = that.max;
            }

            if (val <= that.left_value) {
                val = that.left_value * 1.01;
            }

            that.right_value = val;
            that.changeRange();
        });

        function onMouseMove(event) {
            var $point = that.$activePoint;
            if (that.$activePoint) {
                // Add move Class
                var is_move = $point.hasClass(that.move_class);
                if (!is_move) {
                    $point.addClass(that.move_class);
                }
                // Do moving
                that.prepareSetRange(event, $point);
            }
        }

        function onMouseUp() {
            $document.off("mousemove", onMouseMove);
            $document.off("mouseup", onMouseUp);
            $body.removeClass(no_select);
            if (that.$activePoint) {
                that.$activePoint.removeClass(move_class);
                that.$activePoint = false;
            }
        }
    };

    RangeSlider.prototype.prepareSetRange = function(event, $point) {
        var that = this,
            is_left = ( $point[0] == that.$leftPoint[0] ),
            delta, percent, min, max;
        //
        delta = ( event.pageX || event.clientX ) - that.range_left;
        if (delta < 0) {
            delta = 0;
        } else if (delta > that.range_width) {
            delta = that.range_width;
        }
        //
        percent = (delta/that.range_width) * 100;

        // Min Max
        var min_points_place = 7; // 7%
        if (is_left) {
            min = 0;
            max = that.right - min_points_place;
        } else {
            min = that.left + min_points_place;
            max = 100;
        }
        if (percent < min) {
            percent = min;
        } else if (percent > max) {
            percent = max;
        }

        // Set Range
        if (is_left) {
            that.setRange(percent, that.right);
        } else {
            that.setRange(that.left, percent);
        }
    };

    RangeSlider.prototype.setRange = function( left, right, not_change_input ) {
        var that = this,
            result_left = 0,
            result_right = 100;

        if ( left && left >= 0 && left < result_right ) {
            result_left = left;
        }

        if ( right && right > 0 && right <= result_right && right > result_left ) {
            result_right = right;
        }

        // Set data
        that.left = result_left;
        that.right = result_right;

        // Render
        that.$leftPoint.css("left", result_left + "%");
        that.$rightPoint.css("left", result_right + "%");

        if (!not_change_input) {
            var delta_value = that.max - that.min,
                min_val = that.min + that.left * ( delta_value / 100 ),
                max_val = that.min + that.right * ( delta_value / 100 );

            that.$inputMin.val( parseInt(min_val * 10)/10 );
            that.$inputMax.val( parseInt(max_val * 10)/10 );
        }

        // Bar
        that.setBar();

    };

    RangeSlider.prototype.setBar = function() {
        var that = this;
        that.$bar.css({
            width: Math.floor(that.right - that.left) + "%",
            left: that.left + "%"
        });
    };

    RangeSlider.prototype.changeRange = function() {
        var that = this;

        // Set Range at Start
        var left_value = that.left,
            right_value = that.right,
            delta_value = that.max - that.min;

        if (that.left_value && that.left_value >= that.min && that.left_value < that.max) {
            left_value = ( (that.left_value - that.min)/delta_value) * 100;
        }

        if (that.right_value && that.right_value > left_value && that.right_value <= that.max) {
            right_value = ( (that.right_value - that.min)/delta_value) * 100;
        }

        that.setRange(left_value,right_value, true);
    };

    return RangeSlider;

})(jQuery);

// Shop :: Search autocomplete
( function($) {

    $(document).ready( function() {

        var $search = $('#s-shop-search'),
            $autocomlete = $search.data('autocomlete'),
            $shop_url = $search.data('url'),
            $input = $search.find('.s-text-input');

        if ($autocomlete) {

            $input.autocomplete({
                delay: 500,
                minLength: 3,
                search: function(event, ui) {
                    if($(this).val().replace(/^\s+|\s+$/g, '').length < 3){
                        $(this).autocomplete("close");
                        return false;
                    }

                },
                source: function(request, response){
                    request.term = request.term.replace(/^\s+|\s+$/g, '');
                    var query = request.term.replace(/\s+/g, '+');
                    $.ajax({
                        url: $shop_url + '?query='+encodeURIComponent(query),
                        type: "GET",
                        dataType: "html",
                        success: function(data){
                                var items = $.map($(data).find('.s-products-list li:lt('+5+')'), function(item){
                                var regexp = new RegExp("(" + request.term.replace(/\s+/, "|", 'g') +")", "ig");
                                var name = $(item).find('.s-product-header a').text();
                                return {
                                    label: name,
                                    value: name,
                                    url: $(item).find('a').attr('href'),
                                    text: '<img width="40" height="40" src="'+$(item).find('img').attr('src').replace(/^(.*\/[0-9]+\.)(.*)(\..*)$/, '$1' + '96x96' + '$3')+'"><span class="autofit-name">'+name.replace(regexp, '<span class="match">$1</span>')+'</span><span class="autofit-price">'+$(item).find('.s-price').html()+'</span>'
                                }
                            });
                            if($(data).find('.s-products-list li').length > 5) items[items.length] = {
        						label: ''+query,
        						value: ''+query,
        						url: $shop_url + '?query='+encodeURIComponent(query),
        					    text: 'Показать все'
        					}
        					response(items);
                        }
                    });
                },
                select: function( event, ui ) {
                    location.href = ui.item.url;
                }
            }).data("autocomplete")._renderMenu = function( ul, items ) {
                var _width = $input.innerWidth();
                ul.addClass('autofit-product table table-hover');

                $.each( items, function( index, item ) {
        			$('<li style="width: '+_width+'px;"></li>')
                        .data('item.autocomplete', item)
                        .append('<a href="'+item.url+'">'+item.text+'</a>')
                        .appendTo(ul);
        		});
            };

            $(window).bind('resize', function(){ $input.autocomplete("close"); });

        }

    });

})(jQuery);
