// Shop :: Products Class
var Products = ( function($) {

    Products = function(options) {
        var that = this;


        // DOM
        that.$wrapper = options["$wrapper"];
        that.$product_resize = options["product_resize"];
        that.$products = that.$wrapper.find(".s-product-wrapper");
        that.$sorting = that.$wrapper.find(".s-sorting-wrapper");
        that.$productList = that.$wrapper.find(".s-products-list");

        // VARS
        that.added_class = "is-added";
        that.compare = ( options['compare'] || false );

        // DYNAMIC VARS

        // INIT
        that.bindEvents();
        that.productsSlider();

    };

    Products.prototype.productsResize = function() {
        var that = this,
            $wrapper = that.$wrapper,
            $productList = $wrapper.find(".s-products-list.s-not-slider");
            $elements = ['.s-image-wrapper', '.s-info-wrapper', '.s-pricing-height', 'li.s-product-wrapper'];

        $.fn.matchHeight._maintainScroll = true;

        $.each($elements, function(index,value) {

            var $element = $productList.find(value.toString());

            $element.matchHeight();

        });

    };

    Products.prototype.bindEvents = function() {
        var that = this,
            $wrapper = that.$wrapper,
            $product_resize = that.$product_resize,
            $window = $(window);

        $window.on("resize", function(event) {
            event.preventDefault();

            if ($product_resize == 'jquery') {
                that.productsResize();
            }
        });

        $wrapper.on("submit", "form.add-to-cart", function(event) {
            event.preventDefault();
            that.onSubmitProduct( $(this) );
        });

        $wrapper.on("click", ".s-add-button", function(event) {
            event.preventDefault();
            that.onAddProduct( $(this) );
        });

        $wrapper.on("click", ".s-preview-button", function(event) {
            event.preventDefault();
            that.onPreviewProduct( $(this) );
        });

        $wrapper.on("click", ".s-compare-button", function(event) {
            event.preventDefault();
            that.onCompareProduct( $(this) );
        });

        $wrapper.on("click", ".s-favorite-button", function(event) {
            event.preventDefault();
            that.onFavoriteProduct( $(this) );
        });

        $wrapper.on("click", ".set-table-view", function() {
            that.onChangeView( $(this), 'table' );
            that.productsResize();
            return false;
        });

        $wrapper.on("click", ".set-thumbs-view", function() {
            that.onChangeView( $(this), 'thumbs' );
            that.productsResize();
            return false;
        });

        $wrapper.on("click", ".set-list-view", function() {
            that.onChangeView( $(this), 'list' );
            that.productsResize();
            return false;
        });

        $wrapper.on("click", ".increase-volume", function () {
            that.increaseQuantity( true, $(this) );
        });

        $wrapper.on("click", ".decrease-volume", function() {
            that.increaseQuantity( false, $(this) );
        });

        $wrapper.find(".product-quantity-field").on("change", function() {
            that.changeQuantity( $(this) );
        });

    };

    // SORTING

    Products.prototype.onChangeView = function( $link, view ) {
        var that = this,
            $list = that.$productList,
            active_class = "is-active",
            thumbs_class = "thumbs-view",
            list_class = "list-view",
            table_class = "table-view",
            is_active = $link.hasClass(active_class);

        if (!is_active) {

            if (view == 'thumbs') {
                $list
                    .removeClass(table_class)
                    .removeClass(list_class)
                    .addClass(thumbs_class);
            }

            if (view == 'list') {
                $list
                    .removeClass(table_class)
                    .removeClass(thumbs_class)
                    .addClass(list_class);
            }

            if (view == 'table') {
                $list
                    .removeClass(thumbs_class)
                    .removeClass(list_class)
                    .addClass(table_class);
            }

            that.$sorting.find(".view-filters ." + active_class).removeClass(active_class);
            $link.addClass(active_class)
        }
    };

    // ADD PRODUCT

    Products.prototype.onSubmitProduct = function($form) {
        var that = this,
            $product = $form.closest(".s-product-wrapper"),
            product_href = $form.data("url");

        if (product_href) {
            $.post(product_href, function( html ) {
                var dialog = new Dialog({
                    html: html
                });

                var $dialog = dialog.$dialog;

                $dialog.on("addedToCart", function() {
                    that.maskProductAsAdded( $product );
                });
            });

        } else {
            $.post($form.attr('action') + '?html=1', $form.serialize(), function (response) {
                that.maskProductAsAdded( $product );

                // Update Cart at Header
                if (response["data"]) {
                    var count = response["data"]["count"],
                        text = response["data"]["total"];

                    if (text && count >= 0) {
                        updateHeaderCart({
                            text: text,
                            count: count
                        });
                    }

                    // flying product
                    var flying_product = $('<div></div>').append($product.html()),
                        cart_wrapper = $('#s-cart-wrapper');

                    flying_product.css({
                        'z-index': 100500,
                        background: '#fff',
                        top: $product.offset().top,
                        left: $product.offset().left,
                        width: $product.width()+'px',
                        height: $product.height()+'px',
                        position: 'absolute',
                        overflow: 'hidden'
                    }).appendTo('body').css({'border':'2px solid #eee','padding':'20px','background':'#fff'}).animate({
                        top: cart_wrapper.offset().top,
                        left: cart_wrapper.offset().left,
                        width: '10px',
                        height: '10px',
                        opacity: 0.7
                    }, 700, function() {
                        $(this).remove();
                    });
                }

            }, "json");
        }
    };

    Products.prototype.onPreviewProduct = function($form) {
        var that = this,
            $product = $form.closest(".s-product-wrapper"),
            product_href = $form.data("url");

            $.post(product_href, function( html ) {
                var dialog = new Dialog({
                    html: html
                });

                var $dialog = dialog.$dialog;

                $dialog.on("addedToCart", function() {
                    that.maskProductAsAdded( $product );
                });
            });

    };

    Products.prototype.onAddProduct = function( $button ) {
        var that = this,
            is_active = $button.hasClass(that.added_class);

        if (!is_active) {
            var $form = $button.closest(".s-product-wrapper").find("form.add-to-cart");
            $form.submit();
        }
    };

    Products.prototype.maskProductAsAdded = function( $product ) {
        var that = this,
            $button = $product.find(".s-add-button"),
            $block = $product.find(".s-product-added"),
            added_text = $button.data("added-text");

        $block.css('display','block');
        $button
            .addClass(that.added_class)
            .val(added_text);
    };

    Products.prototype.increaseQuantity = function( increase, $button ) {
        var that = this,
            $wrapper = $button.closest(".s-quantity-wrapper"),
            $quantity = $wrapper.find(".product-quantity-field"),
            value = parseInt( $quantity.val() ),
            new_value = 1;

        if (value && value > 0) {
            new_value = (increase) ? value + 1 : value - 1;
        }

        $quantity
            .val(new_value)
            .trigger("change");

    };

    Products.prototype.changeQuantity = function( $quantity ) {
        var that = this,
            input_max_data =  parseInt( $quantity.data("max-quantity") ),
            max_val = ( isNaN(input_max_data) || input_max_data === 0 ) ? Infinity : input_max_data,
            value = parseInt( $quantity.val() ),
            new_value = 1;

        if (value && value > 0) {
            new_value = (value >= max_val) ? max_val : value;
        }

        $quantity.val(new_value);
    };

    // COMPARE

    Products.prototype.onCompareProduct = function( $button ) {
        var that = this,
            active_class = "active",
            $icon = $button.find(".compare"),
            $product = $button.closest(".s-product-wrapper"),
            product_id = $product.data("product-id"),
            is_active = $icon.hasClass(active_class),
            is_disabled = ( $button.data("is_disabled") || false );

        var compare = $.cookie('shop_compare'); // Product Id's Array @string
        compare = (compare) ? compare.split(',') : [];

        if (is_active) {
            //
            $icon.removeClass(active_class);

            //
            var index = $.inArray( product_id + '', compare);
            if (index != -1) {
                compare.splice(index, 1)
            }
            if (compare.length > 0) {
                $.cookie('shop_compare', compare.join(','), { expires: 30, path: '/'});
            } else {
                $.cookie('shop_compare', null, {path: '/'});
            }

        } else {
            //
            $icon.addClass(active_class);

            //
            compare.push(product_id);
            $.cookie('shop_compare', compare.join(','), { expires: 30, path: '/'});
        }

        var compare_url = that.compare["url"];
        if (compare.length > 0) {
            compare_url = compare_url.replace(/compare\/.*$/, 'compare/' + compare.join(',') + '/');
        }

        //compare_url

        $("#s-compare-counter").trigger("checkCompare");
    };


    // FAVORITE

    Products.prototype.onFavoriteProduct = function( $button ) {
        var that = this,
            active_class = "active",
            $icon = $button.find(".favorite"),
            $product = $button.closest(".s-product-wrapper"),
            product_id = $product.data("product-id"),
            is_active = $icon.hasClass(active_class),
            is_disabled = ( $button.data("is_disabled") || false );

        var favorite = $.cookie('shop_favorite'); // Product Id's Array @string
        favorite = (favorite) ? favorite.split(',') : [];

        if (is_active) {
            //
            $icon.removeClass(active_class);

            //
            var index = $.inArray( product_id + '', favorite);
            if (index != -1) {
                favorite.splice(index, 1)
            }
            if (favorite.length > 0) {
                $.cookie('shop_favorite', favorite.join(','), { expires: 30, path: '/'});
            } else {
                $.cookie('shop_favorite', null, {path: '/'});
            }

        } else {
            //
            $icon.addClass(active_class);

            //
            favorite.push(product_id);
            $.cookie('shop_favorite', favorite.join(','), { expires: 30, path: '/'});
        }

        $("#s-favorite-counter").trigger("checkFavorite");
    };

    /* Products slider */

    Products.prototype.productsSlider = function() {

        var that = this,
            $wrapper = that.$wrapper,
            $productList = $wrapper.find(".s-products-list.slider"),
            $elements = ['.s-image-wrapper', '.s-info-wrapper', '.s-pricing-height', 'li'],
            loaderIntro = '<div class="landing landing-slider"><div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>';

        if ($productList.length) {

            $productList.each(function () {

                var $this = $(this);

                $this.append(loaderIntro);

                $this.waitForImages({

                    finished: function () {

                        $this.find('.landing-slider').remove();

                            var autoplay = $this.data('autoplay'),
                                autoplaySpeed = $this.data('autoplayspeed'),
                                autoplayHoverPause = $this.data('autoplayhoverpause'),
                                loop = $this.data('loop'),
                                navigation = $this.data('navigation'),
                                pagination = $this.data('pagination'),
                                animateOut = $this.data('animout');

                            $this.owlCarousel({
                                margin:14,
                                autoplay: autoplay || false,
                                autoplaySpeed: autoplaySpeed || 0,
                                autoplayHoverPause: autoplayHoverPause || false,
                                loop: loop || false,
                                nav: navigation || false,
                                dots: pagination || false,
                                animateOut: animateOut || 'fadeOut',
                                navText: [
                                    "<i class='fa fa-angle-left fa-2x'></i>",
                                    "<i class='fa fa-angle-right fa-2x'></i>"
                                ],
                                responsive:{
                                    0:{
                                        items:1
                                    },
                                    600:{
                                        items:2
                                    },
                                    1024:{
                                        items:3
                                    },
                                    1025:{
                                        items:4
                                    }
                                }
                            });

                        $this.removeClass('preload');

                        if ($wrapper.parents('.tab-pane') && !$wrapper.parents('.tab-pane').hasClass('active')) {
                            $wrapper.parents('.tab-pane').addClass('active s-to-hide');
                        }

                        // Resize products
                        for ( var i=0; i < $elements.length; i++ ) {

                            var $element = $productList.find($elements[i]),
                                height = 0;

                            $element.each(function () {
                              if ($(this).height() > height) {
                                height = $(this).height();
                              }
                            });
                            $element.height(height);

                        }

                        if ($wrapper.parents('.tab-pane') && $wrapper.parents('.tab-pane').hasClass('s-to-hide')) {
                            $wrapper.parents('.tab-pane').removeClass('active s-to-hide');
                        }

                    },
                    waitForAll: true
                });
            });
        }

    };

    return Products;

})(jQuery);
