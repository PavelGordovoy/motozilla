var MatchMedia = function( media_query ) {
    var matchMedia = window.matchMedia,
        is_supported = (typeof matchMedia === "function");
    if (is_supported && media_query) {
        return matchMedia(media_query).matches
    } else {
        return false;
    }
};

// Site :: Color scheme custom

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
        var color2 = 'rgba('+hex2rgb(html.data('custom'))+',0.9)';
        $('head').append("<style>html.color_scheme_custom #s-sale-header i:hover,html.color_scheme_custom .default-color,html.color_scheme_custom .footer .footer-menu ul li a:hover,html.color_scheme_custom .s-footer-wrapper .s-callback-wrapper .s-callback-dialog,html.color_scheme_custom .s-footer-wrapper .s-footer-menu ul li a:hover,html.color_scheme_custom a,html.color_scheme_custom header.s-header-wrapper nav.s-bottom-header ul.s-bottom-menu>li>a:hover,html.color_scheme_custom header.s-header-wrapper nav.s-bottom-header ul.s-bottom-menu>li>ul.s-menu-style-2>li>a:hover,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-middle-header-menu .s-mainmenu-catalog .s-mainmenu-catalog-wrapper .s-mainmenu-catalog-menu>ul>li>a:hover,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-middle-header-menu .s-mainmenu-catalog .s-mainmenu-catalog-wrapper .s-mainmenu-catalog-menu>ul>li>ul>li a,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-middle-header-menu .s-mainmenu-catalog-dropdown>ul>li>ul.s-menu-style-2>li>a:hover,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-middle-header-menu>ul>li>a:hover,html.color_scheme_custom header.s-header-wrapper nav.s-top-header .s-time-wrapper ul li.s-callback-wrapper span,html.color_scheme_custom header.s-header-wrapper nav.s-top-header .s-top-header-user-account ul li a:hover{color:"+color+"}html.color_scheme_custom a:hover{color:"+color2+"}html.color_scheme_custom .btn-default,html.color_scheme_custom .default-bg,html.color_scheme_custom .landing-slider .spinner>div,html.color_scheme_custom .s-navbar-toggler i.nav-toggle,html.color_scheme_custom .s-navbar-toggler i.nav-toggle::after,html.color_scheme_custom .s-navbar-toggler i.nav-toggle::before,html.color_scheme_custom .s-paging-wrapper ul.s-paging-list li.selected a,html.color_scheme_custom header.s-header-wrapper nav.s-bottom-header ul.s-bottom-menu>li>ul.s-menu-style-1 li a:hover,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-column-right .s-info-wrapper .s-cart-count,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-column-right .s-info-wrapper .s-compare-count,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-column-right .s-info-wrapper .s-favorite-count,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-middle-header-menu .s-mainmenu-catalog-dropdown>ul>li>a:hover,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-middle-header-menu .s-mainmenu-catalog-dropdown>ul>li>ul.s-menu-style-1>li>a:hover,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-middle-header-menu .s-mainmenu-catalog-dropdown>ul>li>ul.s-menu-style-1>li>ul>li>a:hover,html.color_scheme_custom ul.s-sidebar-profile-pages li a:hover,html.color_scheme_custom ul.s-sidebar-profile-pages li.is-selected a{background-color:"+color+"}html.color_scheme_custom .btn-default:hover{background-color:"+color2+"}html.color_scheme_custom header.s-header-wrapper nav.s-bottom-header ul.s-bottom-menu>li>ul.s-menu-style-2,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-middle-header-menu .s-mainmenu-catalog .s-mainmenu-catalog-wrapper .s-mainmenu-catalog-menu{border-top-color:"+color+"}html.color_scheme_custom .btn-transparent,html.color_scheme_custom .dialog-window .dialog-body,html.color_scheme_custom header.s-header-wrapper nav.s-middle-header .s-column-right .s-search-wrapper .s-text-input:focus,html.color_scheme_custom input:focus,html.color_scheme_custom textarea:focus{border-color:"+color+"}</style>");
    }

})(jQuery);

// Site :: More Button
( function($) {

    $(document).ready( function() {

      var $wrapper = $('.s-bottom-header'),
          $menu = $wrapper.find('ul.s-bottom-menu'),
          $menu_item = $menu.children('li'),
          $f_width = $wrapper.children('.container').width(),
          $m_width = 0,
          $i_width = 0;

      function createMore() {

          // Get menu width
          $($menu_item).each(function(){
              var $this = $(this);
              $m_width = $m_width + $this.outerWidth(true);
          });


          // Create menu
          if ($f_width < $m_width) {

              $menu.append('<li id="s-bottom-menu-more" class="s-mainmenu-sub s-menu-style-1"><a href="#s-bottom-menu-more" style="color:#fff;"><i class="icon-options"></a><ul class="s-menu-style-1"></ul></li>');

              $($menu_item.get().reverse()).each(function(){
                  var $this = $(this),
                      $more = $('li#s-bottom-menu-more'),
                      $i_width = $this.outerWidth(true);

                  if ($f_width - $more.outerWidth(true) < $m_width) {
                      $m_width = $m_width - $i_width;
                      $this.attr('class','');
                      $this.find('i').remove();
                      if ( $this.children('ul').length > 0 ) {
                          $this.addClass('s-mainmenu-sub');
                      }
                      $this.find('a').attr('style','');
                      $this.prependTo($more.children('ul'));
                  }

              });
          }
      }

      createMore();

    });

})(jQuery);

// Site :: Sale header
( function($) {

    $(document).ready( function() {

        var $sale = $("#s-sale-header"),
            $close = $sale.find('i');

        $close.on("click", function() {

            var sale_header = $.cookie('sale_header');
            $.cookie('sale_header', 'false', { expires: 1, path: '/'});
            $sale.remove();

            return false;

        });

    });
})(jQuery);

// Site :: Min space before footer
( function($) {

    function init() {
        var $window = $(window),
            $main = $("#s-content-block"),
            $footer = $(".s-footer-wrapper");

        setSpace();

        $window.on("resize", setSpace);

        window.addEventListener("orientationchange", setSpace);

        function setSpace() {
            var display_height = $window.height(),
                main_height = $main.closest(".s-content-wrapper").height(),
                footer_top = $footer.offset().top,
                footer_height = $footer.outerHeight(true);

            var delta = ( display_height - (footer_top + footer_height) );

            if ( delta > 0 ) {
                $main.css({
                    "min-height": main_height + delta + "px"
                });
            } else {
                $main.removeAttr("style");
            }
        }
    }

    $(document).ready( function () {
        init();
    });

})(jQuery);

// Site :: General Layout
( function($) {

    var bindEvents = function() {

        $(".auth-type-wrapper a").on("click", function() {
            onProviderClick( $(this) );
            return false;
        });

        $("#show-sidebar-link").on("click", function() {
            toggleSidebar();
        })

    };

    var onProviderClick = function( $link ) {
        var $li = $link.closest("li"),
            provider = $li.data("provider");

        if (provider != 'guest' && provider != 'signup') {
            var left = (screen.width-600)/ 2,
                top = (screen.height-400)/ 2,
                href = $link.attr("href");

            if ( ( typeof require_authorization !== "undefined" ) && !require_authorization) {
                href = href + "&guest=1";
            }

            openWindow(href, "oauth", "width=600,height=400,left="+left+",top="+top+",status=no,toolbar=no,menubar=no");
        }

    };

    var toggleSidebar = function() {
        var $wrapper = $(".site-wrapper"),
            activeClass = "sidebar-is-shown";

        $wrapper.toggleClass(activeClass);
    };

    var openWindow = function( href, win_name, params) {
        window.open(href, win_name, params);
    };

    $(document).ready( function() {
        bindEvents();
    });

})(jQuery);

// Site :: Profile :: Edit
var renderProfilePage = function(options) {

    var storage = {
        hiddenClass: "is-hidden",
        changeLink: false,          // Dynamic var
        deletePhotoLink: false,     // Dynamic var
        getWrapper: function () {
            return $("#wa-my-info-wrapper");
        },
        getForm: function () {
            return $("#wa-my-info-edit-wrapper");
        },
        getInfoBlock: function () {
            return $("#wa-my-info-read-wrapper");
        },
        getPassword: function () {
            return this.getWrapper().find(".wa-field-password");
        },
        getPhoto: function () {
            return this.getWrapper().find(".wa-field-photo");
        }
    };

    var initialize = function () {
        var $password = storage.getPassword(),
            $photo = storage.getPhoto();

        if ($password.length) {
            renderChangePasswordLink();
        }

        if ($photo.length) {
            renderPhoto();
        }

        // initialize bindEvents after Render
        bindEvents();
    };

    var bindEvents = function () {

        // show Edit Form
        $("#wa-my-info-edit").on("click", function () {
            showEditForm();
            return false;
        });

        // hide edit Form
        $("#wa-my-info-cancel").on("click", function () {
            hideEditForm();
            return false;
        });

        var $change_link = storage.changeLink;
        if ($change_link.length) {
            $change_link.on("click", function () {
                onChangePasswordClick( $(this) );
                return false;
            });
        }

        var $delete_photo_link = storage.deletePhotoLink;
        if ($delete_photo_link.length) {
            $delete_photo_link.on("click", function () {
                onDeletePhotoClick($(this));
                return false;
            });
        }
    };

    var renderPhoto = function () {
        var $photo = storage.getPhoto(),
            $delete_photo_link = $("<a class=\"general-button\" href=\"javascript:void(0);\">" + options["deletePhotoText"] + "</a>"),
            $user_photo = $photo.find('img:first'),
            $default_photo = $photo.find('img:last');

        if ($user_photo[0] != $default_photo[0]) {
            //
            $default_photo.hide();

            //
            $default_photo.after($delete_photo_link);

            // Save to storage
            storage.deletePhotoLink = $delete_photo_link;

        } else {
            $default_photo.show();
        }
    };

    var renderChangePasswordLink = function () {
        var $change_link = $("<a class=\"general-button\" href=\"javascript:void(0);\">" + options["changePasswordText"] + "</a>"),
            $password = storage.getPassword();

        // Hide Password Fields
        $password.find("p").addClass(storage.hiddenClass);

        // Render
        $password.find('.wa-value').prepend($change_link);

        // Save to storage
        storage.changeLink = $change_link;
    };

    var showEditForm = function () {
        var $form = storage.getForm(),
            $info = storage.getInfoBlock();

        $form.removeClass(storage.hiddenClass);
        $info.addClass(storage.hiddenClass);
    };

    var hideEditForm = function () {
        var $form = storage.getForm(),
            $info = storage.getInfoBlock();

        $form.addClass(storage.hiddenClass);
        $info.removeClass(storage.hiddenClass);
    };

    var onDeletePhotoClick = function ($delete_photo_link) {
        var $photo = storage.getPhoto(),
            $photo_input = $photo.find('[name="profile[photo]"]'),
            $user_photo = $photo.find('img:first'),
            $default_photo = $photo.find('img:last');

        // Show default photo
        $default_photo.show();

        // Show user photo
        $user_photo.hide();

        // Hide delete link
        $delete_photo_link.hide();

        // Clear input value
        $photo_input.val('');
    };

    var onChangePasswordClick = function($change_link) {
        // hide link
        $change_link.hide();

        // Show fields
        storage.getPassword().find("p").removeClass(storage.hiddenClass);
    };

    $(document).ready(function () {
        initialize();
    });
};

// Site :: Callback dialog
( function($) {

    $(document).ready( function () {

        var main = $('.s-main-wrapper');

        $('.s-callback-dialog').click(function() {
            $("#callback-dialog").waDialog({
                'onLoad': function (d) {
                    $("#callback-dialog").addClass('fadeIn animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend',
                    function(){
                        $("#callback-dialog").removeClass('fadeIn animated');
                    });
                    main.addClass('blur');
                },
                'onClose': function (d) {
                    $("#callback-dialog").addClass('fedeOut animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend',
                    function(){
                        $("#callback-dialog").removeClass('fedeOut animated');
                    });
                    main.removeClass('blur');
                }
            });
        });

        $('#callback-dialog .dialog-background.cancel').click(function() {
            $('#callback-dialog .dialog-close').click();
        });

    });
})(jQuery);

// Shop :: Cart popover
( function($) {

    $(document).ready( function () {

        var $cart = $('#s-cart-wrapper');
            $url = $cart.find('.s-link').attr('href'),
            $page = $('#s-cart-page'),
            $popover = $('.s-cart-popover'),
            $product_wrapper = $popover.find('.s-cart-products-wrapper'),
            $total_wrapper = $popover.find('.s-cart-total-wrapper'),
            $discount_wrapper = $popover.find('.s-discount-wrapper'),
            $discount = $popover.find('#s-cart-discount'),
            $total = $popover.find('#cart-total'),
            $quantity = $popover.find('.s-product-quantity'),
            $delete = $popover.find('.delete-product'),
            $button = $popover.find('#cart-button'),
            $button_close = $popover.find('#cart-close-button'),
            $counter = $cart.find(".s-cart-count"),
            empty_class = "is-empty",
            active_class = "is-active";

        function cartInit() {
            $.ajax({
                url: $url,
                type: "GET",
                dataType: "html",
                success: function(data){
                    var $data = $(data),
                        items = $data.find('.s-cart-products'),
                        discount = $data.find('#s-cart-discount'),
                        total = $data.find('#cart-total');

                    if (items.length) {
                        $total_wrapper.show();
                        $product_wrapper.html(items);
                        $discount.html(discount);
                        $total.html(total);
                        $button.attr('href',$url);
                    } else {
                        $total_wrapper.hide();
                        $product_wrapper.html('<p style="padding: 1rem 1.5rem;">Ваша корзина пуста.</p>')
                    }
                }
            });
        }

        function changeCounter($count) {

            if (count > 0) {
                $cart.removeClass(empty_class);
                $counter.html(count);
            } else {
                $cart.addClass(empty_class);
            }
        }

        function changeProductQuantity(type, $product) {
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

                    deleteProduct($product );
                }
            }
        }

        function onChangeProductQuantity($product, $quantity) {

            var $product_id = $product.data('id');

            $.post($url + 'save/', {html: 1, id: $product_id, quantity: $quantity }, function(response) {

                count = response.data.count;
                discount = response.data.discount;
                item_total = response.data.item_total;
                total = response.data.total;

                changeCounter(count);
                $product.find('.s-product-total').html(item_total);
                $discount.html(discount);
                $total.html(total);

            }, "json");

        }

        function deleteProduct($product) {

            var $product_id = $product.data('id');

            $.post($url+'delete/', {html: 1, id: $product_id}, function (response) {

                $product.remove();

                count = response.data.count;
                discount = response.data.discount;
                total = response.data.total;

                changeCounter(count);
                $total.html(total);
                $discount.html(discount);

            }, "json");
        }

        if ($cart.length && $page.length == 0 && !MatchMedia("(max-width: 991px)")) {
            cartInit();
        }

        $cart.on("click", function() {

            if ($page.length == 0 && !MatchMedia("(max-width: 991px)")) {
                cartInit();

                if ( !$popover.hasClass(active_class) ) {
                    $cart.addClass(active_class);
                    $popover.addClass(active_class);
                    if (!$cart.hasClass('s-cart-fixed')) {
                        $popover.css('top', $('.s-middle-header').innerHeight() + 1);
                    }
                } else {
                    $cart.removeClass(active_class);
                    $popover.removeClass(active_class);
                    if (!$cart.hasClass('s-cart-fixed')) {
                        $popover.css('top', '-800px');
                    }
                }

                return false;
            }
        });

        $quantity.live('change', function(){
            var $currentProduct = $(this).closest(".s-cart-product"),
                $quantity = $(this).val();
            onChangeProductQuantity($currentProduct, $quantity);
            return false;
        });

        $popover.on("click", ".increase-volume", function() {
            var $currentProduct = $(this).closest(".s-cart-product");
            changeProductQuantity("positive", $currentProduct);
            return false;
        });

        $popover.on("click", ".decrease-volume", function() {
            var $currentProduct = $(this).closest(".s-cart-product");
            changeProductQuantity("negative", $currentProduct);
            return false;
        });

        $button_close.live('click', function(){
            $cart.click();
            return false;
        });

        $delete.live('click', function(){
            var $currentProduct = $(this).parents('.s-cart-product');
            deleteProduct($currentProduct);
            return false;
        });

    });

})(jQuery);

// Mobile :: Sidebar nav
( function($) {

    var cartPosition = function() {

        var cart = $('.s-cart-wrapper'),
            compare = $('.s-compare-wrapper'),
            fixed = cart.data('fixed');

        if (MatchMedia("(max-width: 991px)")) {
            cart.removeClass('s-cart-fixed').addClass('s-cart-not-fixed');
            compare.removeClass('s-compare-fixed').addClass('s-compare-not-fixed');
        } else {
            if (fixed == 'true') {
                cart.addClass('s-cart-fixed').removeClass('s-cart-not-fixed');
                compare.addClass('s-compare-fixed').removeClass('s-compare-not-fixed');
            }
        }

    };


    $(document).ready( function () {

        var $window = $(window),
            html = $('html'),
            header = $('header'),
            navSidebar = $('#s-mobile-nav'),
            navSidebarToggler = $('.s-navbar-toggler'),
            mainContent = $('.s-main-wrapper');

        if ($('html').data('mobile') == true) { // && html.data('touch')) {

            $(mainContent).swipe( {
              swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                if (direction == "right"){
                   var swipeStart = fingerData[0].start.x;
                   if (swipeStart < 70) {
                     navSidebarToggler.click();
                   }
                }
              },
              allowPageScroll: "auto",
              threshold:20,
              excludedElements:''
            });

            $(navSidebar).swipe( {
              swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                if (direction == "left"){
                     navSidebarToggler.click();
                }
              },
              allowPageScroll: "auto",
              threshold:20,
              excludedElements:''
            });
        }

        navSidebar.on('show.bs.collapse', function () {
           mainContent.removeClass('closed').addClass('open');
           header.removeClass('closed').addClass('open');
           html.css('overflow-y','hidden');
        });

        navSidebar.on('hide.bs.collapse', function () {
           mainContent.removeClass('open').addClass('closed');
           header.removeClass('open').addClass('closed');
           html.css('overflow-y','visible');
        });

        cartPosition();
        $window.on("resize", cartPosition);

    });

})(jQuery);

// Swipebox
( function($) {

    $(document).ready( function () {

        $('.swipebox' ).swipebox({
      		useCSS : true,
      		useSVG : false,
      		initialIndexOnArray : 0,
      		hideCloseButtonOnMobile : false,
      		hideBarsDelay : 0,
      		videoMaxWidth : 1140,
      		loopAtEnd: true
      	});

    });

})(jQuery);

// Scroll to top button
( function($) {

    $(document).ready( function () {

      $(window).scroll(function() {
        if ($(this).scrollTop()) {
            $('#s-top-button').fadeIn();
        } else {
            $('#s-top-button').fadeOut();
        }
      });

      $("#s-top-button").click(function() {
        $("html, body").animate({scrollTop: 0}, 1000);
      });

    });

})(jQuery);

// MAILER app email subscribe form
( function($) {

    $(document).ready( function () {

      $('#mailer-subscribe-form input.charset').val(document.charset || document.characterSet);
      $('#mailer-subscribe-form').submit(function() {
          var form = $(this);

          var email_input = form.find('input[name="email"]');
          var email_submit = form.find('input[type="submit"]');
          if (!email_input.val()) {
              email_input.addClass('error');
              return false;
          } else {
              email_input.removeClass('error');
          }

          email_submit.hide();
          email_input.after('<i class="icon16 loading"></i>');

          $('#mailer-subscribe-iframe').load(function() {
              $('#mailer-subscribe-form').hide();
              $('#mailer-subscribe-iframe').hide();
              $('#mailer-subscribe-thankyou').show();
          });
      });

    });

})(jQuery);
