var Cart = ( function($) {

    Cart = function(options) {
        var that = this;

        // DOM
        that.$wrapper = ( options["$wrapper"] || false );
        that.$products = that.$wrapper.find(".s-cart-product");
        that.$services = that.$products.find(".s-product-services");
        that.$cartAffiliateHint = $("#affiliate-hint-wrapper");
        that.$cartDiscount = $("#s-cart-discount");
        that.$cartDiscountWrapper = $("#cart-discount-wrapper");
        that.$cartTotal = $("#cart-total");

        // VARS
        that.error_class = "has-error";
        that.currency = options["currency"];

        that.currency['sign_delim'] = " ";
        that.currency['sign_position'] = 1;

        // INIT
        this.bindEvents();
        this.productSaving();
    };

    // Events
    Cart.prototype.bindEvents = function() {
        var that = this,
            $wrapper = that.$wrapper,
            $product = that.$products,
            $productServices = that.$services;

        $product.on("click", ".increase-volume", function() {
            var $currentProduct = $(this).closest(".s-cart-product");
            that.changeProductQuantity("positive", $currentProduct);
            return false;
        });

        $product.on("click", ".decrease-volume", function() {
            var $currentProduct = $(this).closest(".s-cart-product");
            that.changeProductQuantity("negative", $currentProduct);
            return false;
        });

        $product.on("change", ".s-product-quantity", function() {
            that.onChangeProductQuantity( $(this) );
            return false;
        });

        $product.on("click", ".delete-product", function() {
            that.deleteProduct( $(this).closest(".s-cart-product") );
            return false;
        });

        $productServices.on("change", "select", function() {
            that.onServiceChange( $(this) );
            return false;
        });

        $productServices.on("change", "input[type=\"checkbox\"]", function() {
            that.onServiceCheck( $(this) );
            return false;
        });

        $("#cancel-affiliate").on("click", function () {
            that.cancelAffiliate( $(this) );
            return false;
        });

        $("#use-coupon").on("click", function () {
            that.useCoupon( $(this) );
            return false;
        });

        $wrapper.on("click", ".add-to-cart-link", function() {
            that.onAddToCart( $(this) );
            return false;
        });
    };

    Cart.prototype.onAddToCart = function( $target ) {
        var that = this,
            $deferred = $.Deferred(),
            request_href = $target.data("request-href"),
            request_data = {
                html: 1,
                product_id: $target.data("product_id")
            };

        $.post(request_href, request_data, function (response) {
            $deferred.resolve(response);
        }, 'json');

        $deferred.done( function(response) {
            if (response.status == 'ok') {
                location.reload();
            }
        });
    };

    Cart.prototype.onServiceCheck = function($input) {
        var that = this,
            $product = $input.closest(".s-cart-product"),
            $deferred = $.Deferred(),
            product_id = $product.data("id"),
            is_checked = $input.is(":checked"),
            input_val = $input.val(),
            $field = $('[name="service_variant[' + product_id + '][' + input_val + ']"]'),
            $service = $input.closest(".s-service"),
            request_data = {};

        // Toggle service <select>
        if ($field.length) {
            if (is_checked) {
                $field.removeAttr('disabled');
            } else {
                $field.attr('disabled', 'disabled');
            }
        }

        if (is_checked) {
            request_data = {
                html: 1,
                parent_id: product_id,
                service_id: input_val
            };

            // If variants exits, adding to request_data
            if ($field.length) {
                request_data["service_variant_id"] = $field.val();
            }

            $.post('add/', request_data, function(response) {
                $deferred.resolve(response);
            }, "json");

            $deferred.done( function(response) {
                // Set ID
                $service.data("id", response.data.id);

                // Set Product Total
                var $productTotal = $product.find(".s-product-total");
                $productTotal.html(response.data.item_total);

                // Update Cart Total
                that.updateCart($product, response.data);
            });

        } else {

            request_data = {
                html: 1,
                id: $service.data('id')
            };

            $.post('delete/', request_data, function (response) {
                $deferred.resolve(response);
            }, "json");

            $deferred.done( function(response) {
                // Set ID
                $service.data('id', null);

                // Set Product Total
                var $productTotal = $product.find(".s-product-total");
                $productTotal.html(response.data.item_total);

                // Update Cart Total
                that.updateCart($product, response.data);
            });
        }

    };

    Cart.prototype.useCoupon = function($target ) {
        var that = this,
            $discountWrapper = that.$cartDiscountWrapper;

        // Hide link
        $target.hide();

        // Render Discount
        $discountWrapper.show();

        // Render Coupon Field
        $("#apply-coupon-code").show();
    };

    Cart.prototype.cancelAffiliate = function($link) {
        var that = this,
            $form = $link.closest('form');

        // Adding Affiliate Field
        $form.append("<input type=\"hidden\" name=\"use_affiliate\" value=\"0\">");

        // Submit
        $form.submit();
    };

    Cart.prototype.onServiceChange = function($select) {
        var that = this,
            $product = $select.closest(".s-cart-product"),
            $deferred = $.Deferred(),
            $service = $select.closest(".s-service"),
            request_data = {
                html: 1,
                id: $service.data("id"),
                service_variant_id: $select.val()
            };

        $.post("save/", request_data, function (response) {
            $deferred.resolve(response);
        }, "json");

        $deferred.done( function(response) {

            // Render Product Total
            $product.find('.s-product-total').html(response.data.item_total);

            // Render Cart Total
            that.updateCart($product, response.data);
        });
    };

    Cart.prototype.updateCart = function($product, data ) {
        var that = this,
            $cartTotal = that.$cartTotal,
            $cartDiscountWrapper = that.$cartDiscountWrapper,
            $cartAffiliateBonus = that.$cartAffiliateHint,
            $cartDiscount = that.$cartDiscount,
            text = data["total"],
            count = data["count"];

        // Render Total
        $cartTotal.html(text);

        // Update Cart at Header
        if (text && count >= 0) {
            updateHeaderCart({
                text: text,
                count: count
            });
        }

        // Render Discount
        if (data.discount_numeric) {
            $cartDiscountWrapper.show();
            $cartDiscount.html('&minus; ' + data.discount);
        } else {
            //$cartDiscountWrapper.hide();
            $cartDiscount.html('&minus; ' + data.discount);
        }

        // Render Affiliate Bonus
        if (data.add_affiliate_bonus) {
            $cartAffiliateBonus
                .show()
                .html(data.add_affiliate_bonus);
        } else {
            $cartAffiliateBonus
                .hide();
        }
    };

    Cart.prototype.changeProductQuantity = function(type, $product) {
        var that = this,
            $quantityInput = $product.find(".s-product-quantity"),
            current_val = parseInt( $quantityInput.val() ),
            is_disabled = ( $quantityInput.attr("disabled") === "disabled"),
            disable_time = 800,
            new_val;

        if (type === "positive") {
            new_val = current_val + 1;
        } else if (type === "negative") {
            new_val = current_val - 1;
        }

        // Set new value
        if (!is_disabled) {

            if ( new_val > 0 ) {
                $quantityInput.attr("disabled","disabled");

                $quantityInput.val(new_val);

                // Recalculate Price
                $quantityInput.change();

                setTimeout( function() {
                    $quantityInput.attr("disabled", false)
                }, disable_time);

                // If volume is zero => remove item from basket
            } else {

                this.deleteProduct($product );
            }
        }
    };

    Cart.prototype.productSaving = function() {

        var that = this,
            $product = $(".s-cart-product");

        $product.each(function() {

            var $this = $(this),
                $price = $this.find(".s-price"),
                $comparePrice = $this.find(".s-compare"),
                $saving = $this.find('.s-saving-price'),
                $quantity = $this.find('.s-product-quantity').val(),
                saving_sum,
                price,
                compare_price;

            if ($comparePrice.length) {

                price = parseFloat( $price.data("price") );
                compare_price = parseFloat( $comparePrice.data("compare-price") );

                saving_sum = (compare_price - price) * $quantity;
                $saving.html( that.currencyFormat(saving_sum) );

            }


        });
    }

    Cart.prototype.onChangeProductQuantity = function( $input ) {
        var that = this,
            $product = $input.closest(".s-cart-product"),
            $deferred = $.Deferred(),
            $price = $product.find(".s-price"),
            $comparePrice = $product.find(".s-compare"),
            $saving = $product.find('.s-saving-price'),
            $sum_wrapper = $product.find(".s-product-total"),
            product_quantity = parseInt( $input.val() ),
            request_data,
            saving_sum;

        // DYNAMIC VARS
        var price = parseFloat( $price.data("price") );
        var compare_price = parseFloat( $comparePrice.data("compare-price") );

        // Check for STRING Data at Quantity Field
        if ( isNaN( product_quantity ) ) {
            product_quantity = 1;
        }
        $input.val( product_quantity );

        // Data for Request
        request_data  = {
            html: 1,
            id: $product.data('id'),
            quantity: product_quantity
        };

        // If Quantity 1 or more
        if (product_quantity > 0) {

            $.post("save/", request_data, function (response) {
                $deferred.resolve(response);
            }, "json");

            $deferred.done( function(response) {

                saving_sum = (compare_price - price) * product_quantity;
                $sum_wrapper.html( response.data.item_total );
                $saving.html( that.currencyFormat(saving_sum) );              

                if (response.data.q) {
                    $input.val( response.data.q );
                }

                if (response.data.error) {
                    $input.addClass(that.error_class);

                    // at Future make it better ( renderErrors(errors) )
                    alert(response.data.error);

                } else {

                    $input.removeClass(that.error_class);

                }

                that.updateCart($product, response.data);
            });

        // Delete Product
        } else if (product_quantity == 0) {
            that.deleteProduct($product);
        }
    };

     // Replace price to site format
    Cart.prototype.currencyFormat = function (number, no_html) {
        // Format a number with grouped thousands
        //
        // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +	 bugfix by: Michael White (http://crestidg.com)

        var that = this;
        var i, j, kw, kd, km;
        var decimals = that.currency.frac_digits;
        var dec_point = that.currency.decimal_point;
        var thousands_sep = that.currency.thousands_sep;

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

        number = km + kw + kd;
        var s = no_html ? that.currency.sign : that.currency.sign_html;
        if (!that.currency.sign_position) {
            return s + that.currency.sign_delim + number;
        } else {
            return number + that.currency.sign_delim + s;
        }
    };

    Cart.prototype.deleteProduct = function($product ) {
        var that = this,
            $deferred = $.Deferred(),
            request_data = {
                html: 1,
                id: $product.data('id')
            };

        $.post("delete/", request_data, function (response) {
            $deferred.resolve(response);
        }, "json");

        $deferred.done( function(response) {
            if (response.data.count == 0) {
                location.reload();
            } else {
                $product.remove();
                that.updateCart($product, response.data);
            }
        });

    };

    // Initialize
    return Cart;

})(jQuery);
