// Click on "+","-" good volume buttons
( function($) {

    var bindEvents = function() {
        $(document).on("click", ".increase-volume", function() {
            var $product = $(this).closest(".cart-product-item");

            changeVolume("positive",$product);
            return false;
        });

        $(document).on("click", ".decrease-volume", function() {
            var $product = $(this).closest(".cart-product-item");

            changeVolume("negative",$product);
            return false;
        });
    };

    var changeVolume = function(type, $product) {
        var $volumeInput = $product.find(".item-qty input.qty"),
            current_val = parseFloat($volumeInput.val()),
            is_disabled = ($volumeInput.attr("disabled") === "disabled"),
            disable_time = 800,
            new_val,
            count_step = parseFloat( $product.data('count-step') );

        if (type === "positive") {
            new_val = current_val + count_step;
        } else if (type === "negative") {
            new_val = current_val - count_step;
        }

        // Set new value
        if (!is_disabled) {

            if ( new_val > 0 ) {
                $volumeInput.attr("disabled","disabled");

                $volumeInput.val(new_val);

                // Recalculate Price
                $volumeInput.change();

                setTimeout( function() {
                    $volumeInput.attr("disabled", false)
                }, disable_time);

            // If volume is zero => remove item from basket
            } else {
                deleteItemFromBasket( $product );
            }
        }
    };

    var deleteItemFromBasket = function( $product ) {
        $product.find("a.delete").click();
    };

    $(document).ready( function() {
        bindEvents();
    });

})(jQuery);

// Old JS
$(function () {

    function updateCart(data) {
        $(".cart-total").html(data.total);
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
    }

    $(".cart a.delete").click(function () {
        var row = $(this).closest('div.row');
        $.post('delete/', {html: 1, id: row.data('id')}, function (response) {
            if (response.data.count == 0) {
                location.reload();
            }
            row.remove();
            updateCart(response.data);

            // Update Cart Counter at Header Place
            if (typeof updateHeaderCartCount === "function") {
                updateHeaderCartCount( response );
            }

        }, "json");
        return false;
    });

    $(".cart input.qty").change(function () {
        var that = $(this),
            product_quantity = parseFloat( that.val() ),
            row = that.closest('div.row'),
            count_step = parseFloat( row.data('count-step') ),
            count_min = parseFloat( row.data('count-min') );

        if ( isNaN( product_quantity ) || product_quantity < count_min) {
            product_quantity = count_min;
        }
        if ((product_quantity - count_min) % count_step !== 0) {
            var round_up = Math.ceil((product_quantity - count_min) / count_step);
            product_quantity = count_min + round_up * count_step;
        }
        that.val(product_quantity);

        if (that.val() >= count_min) {
            $.post('save/', {html: 1, id: row.data('id'), quantity: product_quantity}, function (response) {
                row.find('.item-total').html(response.data.item_total);
                if (response.data.q) {
                    that.val(response.data.q);
                }
                if (response.data.error) {
                    alert(response.data.error);
                } else {
                    that.removeClass('error');
                }

                updateCart(response.data);

                // Update Cart Counter at Header Place
                if (typeof updateHeaderCartCount === "function") {
                    updateHeaderCartCount( response );
                }
            }, "json");

        }
    });

    $(".cart .services input:checkbox").change(function () {
        var obj = $('select[name="service_variant[' + $(this).closest('div.row').data('id') + '][' + $(this).val() + ']"]');
        if (obj.length) {
            if ($(this).is(':checked')) {
                obj.removeAttr('disabled');
            } else {
                obj.attr('disabled', 'disabled');
            }
        }

        var div = $(this).closest('div');
        var row = $(this).closest('div.row');
        if ($(this).is(':checked')) {
           var parent_id = row.data('id')
           var data = {html: 1, parent_id: parent_id, service_id: $(this).val()};
           var variants = $('select[name="service_variant[' + parent_id + '][' + $(this).val() + ']"]');
           if (variants.length) {
               data['service_variant_id'] = variants.val();
           }
           $.post('add/', data, function(response) {
               div.data('id', response.data.id);
               row.find('.item-total').html(response.data.item_total);
               updateCart(response.data);
           }, "json");
        } else {
           $.post('delete/', {html: 1, id: div.data('id')}, function (response) {
               div.data('id', null);
               row.find('.item-total').html(response.data.item_total);
               updateCart(response.data);
           }, "json");
        }
    });

    $(".cart .services select").change(function () {
        var row = $(this).closest('div.row');
        $.post('save/', {html: 1, id: $(this).closest('div').data('id'), 'service_variant_id': $(this).val()}, function (response) {
            row.find('.item-total').html(response.data.item_total);
            updateCart(response.data);
        }, "json");
    });

    $("#cancel-affiliate").click(function () {
        $(this).closest('form').append('<input type="hidden" name="use_affiliate" value="0">').submit();
        return false;
    });
    
    $("#use-coupon").click(function () {
        $(this).hide();
        $('#discount-row:hidden').show();
        $('#discount-row').addClass('highlighted');
        $('#apply-coupon-code:hidden').show();        
        $('#apply-coupon-code input[type="text"]').focus();
        return false;
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
                    $('#cart-content').append($('<div class="cart-just-added"></div>').html(f.find('span.added2cart').text()));
                    $('.cart-to-checkout').slideDown(200);
                });
            }
        }, 'json');
       return false;
    });

});