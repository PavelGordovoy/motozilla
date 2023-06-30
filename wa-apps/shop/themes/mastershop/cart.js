$(function () {

    function updateCart(data)
    {
        $(".cart-total").html(data.total);
        $('.js-cart-preview-count').html(data.count);
        $('.js-cart-preview-total').html(data.total);

        if (data.discount_numeric) {
            $(".cart-discount").closest('div.row').show();
        }
        $(".cart-discount").html('&minus; ' + data.discount);

        if (data.add_affiliate_bonus) {
            $(".affiliate").show().html(data.add_affiliate_bonus);
        } else {
            $(".affiliate").hide();
        }

        if (data.affiliate_discount) {
            $('.affiliate-discount-available').html(data.affiliate_discount);
            if ($('.affiliate-discount').data('use')) {
                $('.affiliate-discount').html('&minus; ' + data.affiliate_discount);
            }
        }

        updateTotalsaved(data);
    }

    function updateTotalsaved(data){
        var totalSavedWrap = $('.js-cart-saved-total'),
            totalSavedWrapOuter = $('.js-cart-saved-total-outer'),
            cartItems =  $(".js-cart-item, .js-cart-product");

        if(totalSavedWrap.length){
            var totalSaved = 0;

            totalSavedWrapOuter.addClass("display-none");

            if(cartItems.length){
                cartItems.each(function (){
                    var item  = $(this),
                        quantity = parseInt(item.find(".js-qty .qty").val()),
                        itemPriceWrap = item.find(".js-item-total"),
                        itemComparePriceWrap = item.find(".js-item-compare-price");

                    if(itemPriceWrap.length && itemComparePriceWrap.length){
                        var price = parseFloat(itemPriceWrap.data("price-item")),
                            priceCompare = parseFloat(itemComparePriceWrap.data("compare-price"));

                        if(price > 0 && priceCompare > price){
                            totalSaved += ((priceCompare - price)*quantity);
                        }
                    }
                });
            }


            if(data.discount_numeric){
                totalSaved += parseFloat(data.discount_numeric);
            }

            if(data.affiliate_discount){
                var affiliateWrap = $('.js-affiliate-discount'),
                    affiliateDiscount = affiliateWrap.data("affiliate");

                if(affiliateDiscount){
                    totalSaved += parseFloat(affiliateDiscount);
                }

            }

            if(totalSaved > 0){
                totalSavedWrap.html(currencyFormat(totalSaved));
                totalSavedWrapOuter.removeClass("display-none");
            }
        }
    }

    function changeQty(productWrap, quantityInput, url){
        var quantity = quantityInput.val(),
            id = productWrap.data('id');

        if (parseInt(quantity) > 0) {
            $.post(url, {html: 1, id: id, quantity: quantity}, function (response) {
                var itemTotalWrap = productWrap.find('.js-item-total'),
                    itemCompareTotalWrap = productWrap.find('.js-item-compare-price'),
                    itemSavedTotalWrap = productWrap.find('.js-item-saved-price');

                if(response.data.item_total.charAt(0) == '0' && itemTotalWrap.data('text')){
                    itemTotalWrap.html(itemTotalWrap.data('text'));
                }else{
                    itemTotalWrap.html(response.data.item_total);
                }
                if (response.data.q) {
                    quantityInput.val(response.data.q);
                    quantity = response.data.q;
                }
                if(itemCompareTotalWrap.length && quantity){
                    var itemComparePrice = itemCompareTotalWrap.data("compare-price"),
                        itemTotalComparePrice = parseFloat(itemComparePrice)*parseFloat(quantity);

                    itemCompareTotalWrap.html(currencyFormat(itemTotalComparePrice));

                    if(itemSavedTotalWrap.length){
                        var itemPrice = itemTotalWrap.data("price-item"),
                            itemTotalPrice = parseFloat(itemPrice)*parseFloat(quantity),
                            itemSavedTotal =   itemTotalComparePrice - itemTotalPrice;

                        itemSavedTotalWrap.html("-" + currencyFormat(itemSavedTotal));
                    }
                }

                if (response.data.error) {
                    alert(response.data.error);
                } else {
                    quantityInput.removeClass('error');
                }
                updateCart(response.data);
            }, "json");

        } else {
            quantityInput.val(1);
        }
    }

    $(".cart .delete").click(function () {
        var row = $(this).closest('div.row');
        $.post('delete/', {html: 1, id: row.data('id')}, function (response) {
            if (response.data.count == 0) {
                location.reload();
            }
            row.remove();
            updateCart(response.data);
            sidebarCart.clearItem(row.data('product-id'), row.data('data-id'));
            $('input[name="product_id"][value="'+ row.data('product-id') +'"]').closest(".js-add-to-cart").find(".js-submit-form, .js-product-card-dialog").removeClass("added");
        }, "json");
    });

    $(".js-cart-preview-product-delete").on("click", function(){
        var row = $(this).closest('.js-cart-product'),
            cartWrap = $(this).closest('.js-cart-ajax'),
            url = cartWrap.data("url");

        $.post(url+'delete/', {html: 1, id: row.data('id')}, function (response) {
            sidebarCart.clearItem(row.data('product-id'), row.data('data-id'));
            $('input[name="product_id"][value="'+ row.data('product-id') +'"]').closest(".js-add-to-cart").find(".js-submit-form, .js-product-card-dialog").removeClass("added");
            row.fadeOut("500", function() {
                row.remove();
                updateCart(response.data);

                $('.js-cart-preview-count').html(response.data.count);
                $('.js-cart-preview-total').html(response.data.total);

                if (response.data.count == 0) {
                    $('.js-cart-popup').html("");
                    setTimeout(function (){
                        $('.js-fixed-cart-outer').html("");
                    }, 300);

                    $('.js-cart-preview').addClass('empty');
                }
            });
        }, "json");
    });

    $(".cart input.qty").change(function () {
        var quantityInput = $(this),
            productWrap = quantityInput.closest('div.row');

        changeQty(productWrap, quantityInput, 'save/');
    });

    $(".js-cart-ajax input.qty").change(function () {
        var quantityInput = $(this),
            productWrap = quantityInput.closest('.js-cart-product');

        changeQty(productWrap, quantityInput, $('.js-fixed-cart-outer').data("cart-url") + 'save/');
    });

    $(".js-cart-services input:checkbox").change(function () {
        var obj = $('select[name="service_variant[' + $(this).closest('div.row').data('id') + '][' + $(this).val() + ']"]');
        if (obj.length) {
            if ($(this).is(':checked')) {
                obj.removeAttr('disabled').trigger('refresh');;

            } else {
                obj.attr('disabled', 'disabled').trigger('refresh');;
            }
        }

        var div = $(this).closest('.js-cart-service');
        var row = $(this).closest('div.row');
        if ($(this).is(':checked')) {
           var parent_id = row.data('id')
           var data = {html: 1, parent_id: parent_id, service_id: $(this).val()};
           var $variants = $('[name="service_variant[' + parent_id + '][' + $(this).val() + ']"]');
           if ($variants.length) {
               data['service_variant_id'] = $variants.val();
           }
           $.post('add/', data, function(response) {
               div.data('id', response.data.id);
               row.find('.js-item-total').html(response.data.item_total);
               updateCart(response.data);
           }, "json");
        } else {
           $.post('delete/', {html: 1, id: div.data('id')}, function (response) {
               div.data('id', null);
               row.find('.js-item-total').html(response.data.item_total);
               updateCart(response.data);
           }, "json");
        }
    });

    $(".js-cart-services select").change(function () {
        var row = $(this).closest('div.row');
        $.post('save/', {html: 1, id: $(this).closest('.js-cart-service').data('id'), 'service_variant_id': $(this).val()}, function (response) {
            row.find('.js-item-total').html(response.data.item_total);
            updateCart(response.data);
        }, "json");
    });

    $("#cancel-affiliate").click(function () {
        $(this).closest('form').append('<input type="hidden" name="use_affiliate" value="0">').submit();
        return false;
    });


    $("#use-affiliate").click(function () {
        $(this).closest('form').append('<input type="hidden" name="use_affiliate" value="1">').submit();
    });

    $("#use-coupon").click(function () {
        $('#discount-row:hidden').slideToggle(200);
        $('#apply-coupon-code:hidden').show();
        $('#apply-coupon-code input[type="text"]').focus();
        $(this).hide();
        return false;
    });

    // listen coupon input value for change and hide its error message if changing have been happened

    var onInputChange = function ($input, onChange) {
        var prev_input_val = $input.val() || '';
        var timer_id, timeout = 500;
        $input.keydown(function () {
            if (timer_id) {
                clearTimeout(timer_id);
            }
            timer_id = setTimeout(function () {
                if (prev_input_val !== $input.val()) {
                    onChange.apply($input, arguments);
                }
                prev_input_val = $input.val();
            }, timeout);
        }).change(onChange);
    };

    onInputChange($('#apply-coupon-code input[type="text"]'), function () {
        $('#apply-coupon-code .errormsg').hide();
    });

    $("div.addtocart input:button").click(function () {
        var f = $(this).closest('div.addtocart');
        if (f.data('url')) {
            var d = $('#dialog');
            var c = d.find('.cart');
            c.load(f.data('url'), function () {
                c.prepend('<a href="#" class="dialog-close">&times;</a>');
                c.find('form').data('cart', 1);
                d.show();
                if ((c.height() > c.find('form').height())) {
                    c.css('bottom', 'auto');
                } else {
                    c.css('bottom', '15%');
                }
            });
            return false;
        }
        $.post($(this).data('url'), {html: 1, product_id: $(this).data('product_id')}, function (response) {
            if (response.status == 'ok') {
                var cart_total = $(".cart-total");
                $("#page-content").load(location.href, function () {
                    cart_total.closest('#cart').removeClass('empty');
                    cart_total.html(response.data.total);
                    $('#cart').addClass('fixed');

                    var $addedText = $('<div class="cart-just-added"></div>').html(f.find('span.added2cart').text());
                    $('#cart-content').append($addedText);
                    setTimeout( function() {
                        $addedText.remove();
                    }, 2000);

                    $('.cart-to-checkout').slideDown(200);
                });
            }
        }, 'json');
       return false;
    });

});