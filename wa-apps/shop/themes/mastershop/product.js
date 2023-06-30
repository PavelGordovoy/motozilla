function Product(form, options) {
    this.form = $(form);
    this.add2cart = this.form.find(".add2cart");
    this.skFastButton = this.form.find(".js-sk-button-fastorder");
    this.fastButton = this.form.find(".js-button-fastorder");
    this.discount = this.form.closest('.js-product').find(".js-product-discount");
    this.button = this.add2cart.find("input[type=submit], .js-submit-form");

    for (var k in options) {
        this[k] = options[k];
    }
    var self = this;
    // add to cart block: services
    this.form.find(".services input[type=checkbox]").change(function () {
        var obj = $('select[name="service_variant[' + $(this).val() + ']"]');
        if (obj.length) {
            if ($(this).is(':checked')) {
                obj.removeAttr('disabled');
            } else {
                obj.attr('disabled', 'disabled');
            }
        }
        self.cartButtonVisibility(true);
        self.updatePrice();

        if(obj.hasClass('js-select')){
            obj.trigger("refresh");
        }
    });

    this.form.find(".services .service-variants").on('change', function () {
        self.cartButtonVisibility(true);
        self.updatePrice();
    });

    this.form.find('.inline-select a').click(function () {
        var d = $(this).closest('.inline-select');
        d.find('a.selected').removeClass('selected');
        $(this).addClass('selected');
        d.find('.sku-feature').val($(this).data('value')).change();
        return false;
    });

    this.form.find(".skus input[type=radio], .skus select").change(function () {
        var image_id = null,
            is_disabled = false,
            sku_id = $(this).val();

        if($(this).attr("type") == "radio"){
            image_id = $(this).data('image-id');
            is_disabled = $(this).data('disabled');
        }else{
            image_id = $(this).find('option:selected').data('image-id');
            is_disabled = $(this).find('option:selected').data('disabled');
        }
        if (image_id && $("#product-image-" + image_id).length) {
            gallery.selectLargePhoto($("#product-image-" + image_id));
        }
        if (is_disabled) {
            self.button.attr('disabled', 'disabled').addClass('disabled');
            self.skFastButton.addClass('disabled');
            self.fastButton.addClass('disabled');
        } else {
            self.button.removeAttr('disabled').removeClass('disabled');
            self.skFastButton.removeClass('disabled');
            self.fastButton.removeClass('disabled');
        }

        self.updateSkuServices(sku_id);
        self.cartButtonVisibility(true);
        self.updatePrice();
    });

    if($('.skus input[type=radio]').length) {
        var $initial_cb = this.form.find(".skus input[type=radio]:checked:not(:disabled)");
        if (!$initial_cb.length) {
            $initial_cb = this.form.find(".skus input[type=radio]:not(:disabled):first").prop('checked', true).click();
        }
    }else if($('.skus option').length){
        var $initial_cb = this.form.find(".skus option:selected:not(:disabled)");
        if (!$initial_cb.length) {
            $initial_cb = this.form.find(".skus option:not(:disabled):first").prop('selected', true).click();
        }
    }

    if(typeof $initial_cb !== 'undefined' &&  $initial_cb.length && $initial_cb.data('image-id')) {
        $initial_cb.change();

        if ($initial_cb.length && $initial_cb.data('image-id') && $("#product-image-" + $initial_cb.data("image-id")).length) {
            gallery.selectLargePhoto($("#product-image-" + $initial_cb.data("image-id")));
        }
    }

    this.form.find(".sku-feature").change(function () {
        var key = "";
        self.form.find(".sku-feature").each(function () {
            key += $(this).data('feature-id') + ':' + $(this).val() + ';';
        });
        var sku = self.features[key];
        if (sku) {
            if (sku.image_id && $("#product-image-" + sku.image_id).length) {
                gallery.selectLargePhoto($("#product-image-" + sku.image_id));
            }
            self.updateSkuServices(sku.id);
            if (sku.available) {
                self.button.removeAttr('disabled').removeClass('disabled');
                self.skFastButton.removeClass('disabled');
                self.fastButton.removeClass('disabled');
            } else {
                self.form.find("div.stocks div").hide();
                self.form.find(".sku-no-stock").show();
                self.button.attr('disabled', 'disabled').addClass('disabled');
                self.skFastButton.addClass('disabled');
                self.fastButton.addClass('disabled');
            }
            self.add2cart.find(".price").data('price', sku.price);
            self.updatePrice(sku.price, sku.compare_price);
        } else {
            self.form.find("div.stocks div").hide();
            self.form.find(".sku-no-stock").show();
            self.button.attr('disabled', 'disabled').addClass('disabled');
            self.skFastButton.addClass('disabled');
            self.fastButton.addClass('disabled');
            self.form.find(".compare-at-price").hide();
            self.form.find(".price").empty();
        }
        self.cartButtonVisibility(true);
    });
    this.form.find(".sku-feature:first").change();

    if (!this.form.find(".skus input:radio:checked").length) {
        this.form.find(".skus input:radio:enabled:first").attr('checked', 'checked');
    }
    if (!this.form.find(".skus option:selected").length) {
        this.form.find(".skus option:enabled:first").attr('selected', 'selected');
    }

    self.showAllSkus();
    self.selectColor();
}
Product.prototype.getEscapedText = function( bad_string ) {
    return $("<div>").text( bad_string ).html();
};

Product.prototype.currencyFormat = function (number, no_html) {
    // Format a number with grouped thousands
    //
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +	 bugfix by: Michael White (http://crestidg.com)

    var i, j, kw, kd, km;
    var decimals = this.currency.frac_digits;
    var dec_point = this.currency.decimal_point;
    var thousands_sep = this.currency.thousands_sep;

    // input sanitation & defaults
    if( isNaN(decimals = Math.abs(decimals)) ){
        decimals = 2;
    }
    if( dec_point == undefined ){
        dec_point = ",";
    }
    if( thousands_sep == undefined ){
        thousands_sep = ".";
    }

    i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

    if( (j = i.length) > 3 ){
        j = j % 3;
    } else{
        j = 0;
    }

    km = (j ? i.substr(0, j) + thousands_sep : "");
    kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
    //kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).slice(2) : "");
    kd = (decimals && (number - i) ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");


    var number = km + kw + kd;
    var s = no_html ? this.currency.sign : this.currency.sign_html;
    if (!this.currency.sign_position) {
        return s + this.currency.sign_delim + number;
    } else {
        return number + this.currency.sign_delim + s;
    }
};


Product.prototype.serviceVariantHtml= function (id, name, price) {
    return $('<option data-price="' + price + '" value="' + id + '"></option>').text(name + ' (+' + this.currencyFormat(price, 1) + ')');
};

Product.prototype.updateSkuServices = function (sku_id) {
    if(this.form.find(".js-product-code").length > 0){
        this.form.find(".js-product-code").hide();
        this.form.find(".sku-" + sku_id + "-product-code").show();
    }

    this.form.find("div.stocks div").hide();
    this.form.find(".sku-" + sku_id + "-stock").show();
    for (var service_id in this.services[sku_id]) {
        var v = this.services[sku_id][service_id];
        if (v === false) {
            this.form.find(".service-" + service_id).hide().find('input,select').attr('disabled', 'disabled').removeAttr('checked');
        } else {
            this.form.find(".service-" + service_id).show().find('input').removeAttr('disabled');
            if (typeof (v) == 'string' || typeof (v) == 'number') {
                this.form.find(".service-" + service_id + ' .service-price').html(this.currencyFormat(v));
                this.form.find(".service-" + service_id + ' input').data('price', v);
            } else {
                var select = this.form.find(".service-" + service_id + ' .service-variants');
                var selected_variant_id = select.val();
                for (var variant_id in v) {
                    var obj = select.find('option[value=' + variant_id + ']');
                    if (v[variant_id] === false) {
                        obj.hide();
                        obj.attr('disabled', 'disabled');
                        if (obj.attr('value') == selected_variant_id) {
                            selected_variant_id = false;
                        }
                    } else {
                        if (!selected_variant_id) {
                            selected_variant_id = variant_id;
                        }
                        obj.removeAttr('disabled');
                        obj.replaceWith(this.serviceVariantHtml(variant_id, v[variant_id][0], v[variant_id][1]));
                    }
                }
                if(!selected_variant_id){
                    selected_variant_id = this.form.find(".service-" + service_id + ' .service-variants').find("option:not(.disable):first").attr("value");
                }
                this.form.find(".service-" + service_id + ' .service-variants').val(selected_variant_id).trigger('refresh');
            }
        }
    }
};
Product.prototype.updatePrice = function (price, compare_price) {
    if (price === undefined) {
        var input_checked = this.form.find(".skus input:radio:checked, .skus option:selected");
        if (input_checked.length) {
            var price = parseFloat(input_checked.data('price'));
            var compare_price = parseFloat(input_checked.data('compare-price'));
        } else {
            var price = parseFloat(this.add2cart.find(".price").data('price'));
        }
    }
    var self = this;
    this.form.find(".services input:checked").each(function () {
        var s = $(this).val();
        if (self.form.find('.service-' + s + '  .service-variants').length) {
            price += parseFloat(self.form.find('.service-' + s + '  .service-variants :selected').data('price'));
        } else {
            price += parseFloat($(this).data('price'));
        }
    });
    if (compare_price && price > 0) {
        this.form.find(".compare-at-price").html(this.currencyFormat(compare_price)).show();
    } else {
        this.form.find(".compare-at-price").hide();
    }

    var $priceWrap = this.add2cart.find(".price");
    var priceFormat = this.currencyFormat(price);
    var textZeroPrice = $priceWrap.data("text");
    if (price == 0 && textZeroPrice){
        $priceWrap.html('<span class="product-card__zero-prices">' + textZeroPrice + '</span>');
    }else{
        $priceWrap.html(priceFormat);
    }
    self.updateDiscount(price,compare_price);
}

Product.prototype.updateDiscount = function (price, compare_price) {
    if (this.discount.length){
        var discount = 0,
            typeRound = this.discount.data('round'),
            minimal = parseInt(this.discount.data('minimal'));

        this.discount.addClass('display-none');
        if(compare_price > price && price > 0){
            discount = ((compare_price- price)/compare_price)*100;
            if (typeRound == "ceil"){
                discount = Math.ceil(discount);
            }else if (typeRound == "floor"){
                discount = Math.floor(discount);
            }else{
                discount = Math.round(discount);
            }
            if(discount >= minimal){
                this.discount.html('-' + discount + '%').removeClass('display-none');
            }
        }
    }
}

Product.prototype.cartButtonVisibility = function (visible) {
    //toggles "Add to cart" / "%s is now in your shopping cart" visibility status
    if (visible) {
        this.add2cart.find('.js-compare-at-price').show();
        this.add2cart.find('input[type="submit"], .js-submit-form').show();
        this.add2cart.find('.js-sk-button-fastorder').show();
        this.add2cart.find('.price').show();
    }
}

Product.prototype.showAllSkus = function () {
    $(".js-product-skus-show").on("click", function (){
        var $this = $(this), outerWrap = $this.closest(".js-product-skus");

        outerWrap.find(".js-product-skus-item").toggleClass("hide");
        $this.toggleClass("open");
    });
};

Product.prototype.selectColor = function (){
    var self = this, colorOption = $('.js-product-color');

    colorOption.on("click", function (){
        self.writeTextColor($(this));
    });

    var currentColorOption = $('.js-product-color');
    if(currentColorOption.length){
        self.writeTextColor(currentColorOption);
    }
};

Product.prototype.writeTextColor = function (colorOption){
    var wrapColorTitle = colorOption.closest('form').find('.js-product-selected-color-title'),
        colorTitle = colorOption.data('color-title');

    if(wrapColorTitle.length && colorTitle){
        wrapColorTitle.html('<i class="fal fa-minus icon"></i> ' + colorTitle);
    }else{
        wrapColorTitle.html('');
    }
};




var gallery = {
    init: function(){
        var _this = this;

        _this.productMainImageCarousel();
        _this.productPreviewsCarousel();
        _this.selectedPreviewImage();
        _this.popup();
        _this.popupVideo();
    },
    productMainImageCarousel: function(){
        var _this = this,
            mainGallery = $('.js-product-gallery-main'),
            position = 0;

        if(mainGallery.length){
            var currenrPreview = mainGallery.closest('.js-product').find('.js-gallery-preview.selected');
            if(currenrPreview.length){
                if(currenrPreview.data("position") != "0"){
                    position = parseInt(currenrPreview.data("position"));
                }
            }
            mainGallery.owlCarousel({
                loop: false,
                nav: false,
                margin: 0,
                items: 1,
                lazyLoad: true,
                autoHeight: false,
                dots: false,
                startPosition: position,
                mouseDrag: false,
                onLoadedLazy: function(event){
                    var gallery = $(event.currentTarget);
                    if (gallery.length && $.Retina) {
                        gallery.find(".owl-item.active .owl-lazy").retina();
                    }
                },
                onInitialized: function(){
                    if(!is_touch_device()){
                        _this.productImageZoom(mainGallery);
                    }
                    _this.displayImageTitle(mainGallery);
                },
                onDragged: function(event){
                    var gallery = $(event.currentTarget);

                    if(gallery.length){
                        var previews = gallery.closest('.js-product').find('.js-gallery-preview'),
                            image_id = $(gallery).find(".owl-item.active .js-product-gallery-main-el").data("id");

                        if(image_id){
                            previews.removeClass("selected");
                            var currentPreview = previews.filter("[data-id='" + image_id + "']");
                            currentPreview.addClass("selected");
                        }
                        _this.displayImageTitle(gallery);
                        _this.productPreviewsCarouselGoTo(currentPreview.data("position"));
                    }
                }
            });
        }
    },
    productPreviewsCarousel: function (){
        var previewsCarousel = $('.js-gallery-previews-carousel'),
            contentCountCols = contentCols.getCount(previewsCarousel.closest('.js-content-cols')),
            is_dialog = (!previewsCarousel.closest('.js-content-cols').length),
            position = 0,
            countGalleyPreviews = previewsCarousel.find('.js-gallery-preview').length;

        var responsive = { 500: { items: 4 }, 600: { items: 5 }, 700: { items: 6 }, 800: { items: 3 }, 900: { items: 4 }, 1180: { items: 5 }, 1350: { items: 6 } };
        if(contentCountCols == 2){
            responsive = { 500: { items: 4 }, 600: { items: 5 }, 700: { items: 6 }, 800: { items: 3 }, 1300: { items: 4 }, 1450: { items: 5 } };
        }else if (contentCountCols == 3){
            responsive = { 500: { items: 4 }, 600: { items: 5 }, 700: { items: 6 },  800: { items: 3 }, 1400: { items: 4 } };
        }
        var params = {
            loop: false,
            nav: true,
            lazyLoad: true,
            autoHeight: false,
            dots: false,
            startPosition: position,
            responsive: responsive,
            navText: ['<span class="carousel-prev carousel-prev--mini bs-color"></span>','<span class="carousel-next carousel-next--mini bs-color"></span>'],
            navElement: "div",
            mouseDrag: false,
            onInitialized: function(event){
                var Carousel = $(event.currentTarget);

                if(Carousel.find('.owl-nav').hasClass('disabled')){
                    Carousel.closest(".js-outer").addClass("none-nav");
                }else{
                    Carousel.closest(".js-outer").removeClass("none-nav");
                }
            },
            onResized: function(event){
                var Carousel = $(event.currentTarget);

                if(Carousel.find('.owl-nav').hasClass('disabled')){
                    Carousel.closest(".js-outer").addClass("none-nav");
                }else{
                    Carousel.closest(".js-outer").removeClass("none-nav");
                }
            }
        };
        if(previewsCarousel.length){
            previewsCarousel.owlCarousel(params);
        }
        var isLoop = (previewsCarousel.data("loop"));
        if(isLoop && previewsCarousel.find('.owl-item:not(.active)').length){
            previewsCarousel.trigger('destroy.owl.carousel');
            params["loop"] = true;
            previewsCarousel.owlCarousel(params);
        }
    },
    productPreviewsCarouselGoTo: function(position) {
        var previewsCarousel = $('.js-gallery-previews-carousel');

        if(previewsCarousel.length){
            var preview = previewsCarousel.find(".js-gallery-preview").filter("[data-position='" + position + "']");

            if(!preview.parent().is(".active")){
                previewsCarousel.trigger("to.owl.carousel", [(parseInt(position)), 300]);
            }
        }
    },
    selectedPreviewImage: function (){
        var _this = this;

        $("body").on("click", ".js-gallery-preview a", function (event) {
            event.preventDefault();

            _this.selectLargePhoto($(this), false);
        });
    },
    popup : function (){
        var _this = this,
            $ = jQuery,
            mainGallery = $(".js-product-gallery-main");

        if (mainGallery.data('photoswipe')){
            _this.popupPhotoswipe(mainGallery);
        }else{
            _this.popupSwipebox(mainGallery);
        }
    },
    popupPhotoswipe: function(mainGallery){
        var mainGalleryImages =  mainGallery.find(".js-product-image-popup");

        $('body').on("click", ".pswp button", function (event){
            event.preventDefault();
        });

        if (mainGalleryImages.length) {
            mainGalleryImages.add('.js-gallery-preview-else').on("click", function (e) {
                e.preventDefault();

                var previews = $(this).closest('.js-product').find('.js-gallery-preview'),
                    mainPhoto = $(this).closest('.js-product').find('.js-product-gallery-main-el'),
                    pswpElement = document.querySelectorAll('.pswp')[0],
                    position = $(this).data('position'),
                    items = [];

                if (previews.length){
                    previews.each(function () {
                        var image = $(this);
                        if (!image.data("video")) {
                            items.push({src: image.find('a').attr("href"), w: 0, h: 0});
                        }
                    });
                }else if(mainPhoto.length){
                    items.push({src: mainPhoto.attr("href"), w: 0, h: 0});
                }

                var options = {
                    index: position,
                    shareEl: false,
                    history: false
                };

                var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);

                gallery.listen('gettingData', function (index, item) {
                    if (item.w < 1 || item.h < 1) { // unknown size
                        var img = new Image();
                        img.onload = function () {
                            item.w = this.width;
                            item.h = this.height;
                            gallery.invalidateCurrItems();
                            gallery.updateSize(true);
                        };
                        img.src = item.src;
                    }
                });
                gallery.init();
            });
        }
    },
    popupSwipebox: function(mainGallery){
        var $ = jQuery,
            mainGalleryImages = mainGallery.find(".js-product-image-popup");

        if (mainGalleryImages.length) {
            mainGalleryImages.add('.js-gallery-preview-else').on("click", function(e) {
                e.preventDefault();

                var previews = $(this).closest('.js-product').find('.owl-item:not(.cloned) .js-gallery-preview'),
                    images = [],
                    position = $(this).data('position');

                if(!previews.length){
                    /* список превью слева */
                    previews = $(this).closest('.js-product').find('.js-gallery-preview');
                }

                if (previews.length) {
                    previews.each(function () {
                        if(!$(this).data('video')){
                            images.push({href: $(this).find('a').attr('href'), src: $(this).find('img').attr('src')});
                        }
                    });
                } else {
                    images.push({href: $(this).attr('href'), src: $(this).find('img').attr('src')});
                }

                $.swipebox(images, {
                    useSVG : false,
                    hideBarsDelay: false,
                    thumbs: true,
                    initialIndexOnArray: position,
                    afterOpen: function() {
                        if (images.length > 1){
                            var imagesThumbsHtml = '', currentIndex = parseInt($('#swipebox-slider .slide.current').data("index"));
                            images.forEach(function(element, index) {
                                var addClass = "swipebox-thumbs_el js-swipebox-thumbs-el";

                                if (currentIndex === index) {
                                    addClass += " active";
                                }
                                if (element.is_video == true) {
                                    imagesThumbsHtml += '<a class="' + addClass + '" data-index="' + index + '" href="' + element.href + '"><i class="swipebox-preview-video fas fa-video bs-color"></i></a>';
                                } else {
                                    imagesThumbsHtml += '<a class="' + addClass + '" data-index="' + index + '" href="' + element.href + '"><img src="' + element.src + '" /></a>';
                                }
                            });

                            $('#swipebox-container').append('<div id="swipebox-thumbs" class="swipebox-thumbs">'+imagesThumbsHtml+'</div>');
                            $('#swipebox-slider').css("padding-bottom", (parseInt($('#swipebox-thumbs').outerHeight()) + 30) + 'px');

                            $('#swipebox-bottom-bar').addClass("swipebox-bottom-bar--pos-center");
                            $('#swipebox-arrows').addClass("swipebox-arrows--pos-center");
                        }


                        var closeSwipe = function() {
                            var $closeButton = $("#swipebox-close");
                            if ($closeButton.length) {
                                $closeButton.trigger("click");
                            }
                            $(document).off("scroll", closeSwipe);
                        };

                        $(document).on("scroll", closeSwipe);
                    }
                });
                return false;
            });
        }
    },
    selectLargePhoto: function (previewImage, isPreviewsCarouselGoTo){
        var _this = this,
            preview =  previewImage.parent(),
            image_id = preview.data("id"),
            mainGallery = previewImage.closest('.js-product').find('.js-product-gallery-main'),
            mainGalleryItems = mainGallery.find('.js-product-gallery-main-el');

        if(isPreviewsCarouselGoTo == undefined){
            isPreviewsCarouselGoTo = true
        }

        preview.closest('.js-product').find('.js-gallery-preview').removeClass('selected');
        preview.addClass('selected');

        if(image_id){
            var position = mainGalleryItems.filter("[data-id='" + image_id + "']").data("position");
            if(typeof position !== "undefined"){
                mainGallery.trigger("to.owl.carousel", [position, 300]);
                _this.displayImageTitle(mainGallery);
            }
            if(isPreviewsCarouselGoTo){
                _this.productPreviewsCarouselGoTo(position);
            }
        }
    },
    productImageZoom: function(mainGallery){
        if(mainGallery.length && mainGallery.data("zoom")){
            var currentImage = mainGallery.find(".js-product-gallery-main-el");

            currentImage.each(function(){
                $(this).zoom({
                    url: $(this).attr('href'),
                    onZoomIn: function (){
                        $(this).parent().addClass("zooming");
                    },
                    onZoomOut: function (){
                        $(this).parent().removeClass("zooming");
                    }
                });
            });
        }
    },
    displayImageTitle: function(gallery){
        var titleWrap = gallery.closest('.js-product').find('.js-product-gallery-title'),
            currentImage = gallery.find(".owl-item.active");

        titleWrap.text("");
        if(titleWrap.length && currentImage.length){

            var title = currentImage.find('img').attr('alt');
            if(title){
                titleWrap.text(title);
            }
        }
    },
    popupVideo: function (){
        $('body').on('touchstart touchend touchup', '.js-mfp-fade-product-video', function(event) {
            event.stopPropagation();
        });

        $("body").on("click", '.js-gallery-popup-video a', function(event){
            event.preventDefault();
            var href = $(this).attr("href");

            if(href){
                $.magnificPopup.open({
                    items: {
                        src: href
                    },
                    type: 'iframe',
                    mainClass: 'mfp-fade js-mfp-fade-product-video',
                    removalDelay: 160,
                    preloader: false,
                    fixedContentPos: false,
                    iframe: {
                        patterns: {
                            youtube_short: {
                                index: 'youtu.be/',
                                id: 'youtu.be/',
                                src: '//www.youtube.com/embed/%id%?autoplay=1'
                            }
                        }
                    }
                }, 0);
            }
        });
    }
};


$(function () {
    gallery.init();
});

function is_touch_device() {
    return 'ontouchstart' in window || navigator.maxTouchPoints;
};

var contentCols = {
    getCount: function (contentColsWrap){
        var contentCountCols = 1;

        if(contentColsWrap.length && parseInt(contentColsWrap.data('count')) > 0){
            contentCountCols = contentColsWrap.data('count');
        }

        return contentCountCols;
    }
};
