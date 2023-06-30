var main = {
    init: function () {
        var _this = this;

        _this.addTouchClass();
        _this.retinaLogo();
        _this.lazyloadImage();
        _this.retinaImage();
        _this.retinaProductImage();
        _this.searchAuto();
        _this.moveTop();
        if(globalThemeSettings.isLocationSelect){
            _this.locationChange();
        }
        if(globalThemeSettings.isCurrencySelect){
            _this.currencyChange();
        }
        _this.expandText();
        _this.expandAccordionText();
        _this.breadcrumbsScroll();
        if(!globalThemeSettings.isMobile){
            _this.expandSidebarPluginsNav();
            _this.expandSidebarTags();
        }
    },
    addTouchClass: function(){
        if(is_touch_device()){
            $('body').removeClass('no-touch').addClass('touch');
        }
    },
    retinaLogo: function(){
        var logo = $('.js-logo-retina');

        if(logo.length){
            logo.retina({ force_original_dimensions: false });
        }
    },
    retinaImage: function(){
        var image = $('.js-img-retina');

        if(image.length){
            image.retina({ force_original_dimensions: false });
        }
    },
    lazyloadImage: function(){
        var image = $('.js-image-lazy');

        if(image.length){
            image.lazy({bind: "event"});
        }
    },
    retinaProductImage: function(){
        var image = $('.js-product-img-retina img');

        if(image.length){
            image.retina({ force_original_dimensions: false });
        }
    },
    searchAuto: function (){
        var input = $('.js-search-auto');

        if(!input.length){
            return false;
        }

        input.on("keyup", function(){
            var $this = $(this),
                value = $this.val(),
                form = $this.closest('form'),
                url = form.attr("action"),
                resultWrap = form.find('.js-search-auto-result');

            if(value.length >= 3){
                $.get(url + '?query='+value+'&ajax=1', function(data) {
                    var content = $(data).find('.js-auto-search');

                    if(content.length){
                        resultWrap.show();
                        resultWrap.html(content);
                    }else{
                        resultWrap.html("");
                    }
                });
            }else{
                resultWrap.html("");
                resultWrap.hide();
            }
        });

        $('body').click(function (e) {
            var popup = $(".js-search-auto-result");
            if (!$('.js-search-auto').is(e.target) && !popup.is(e.target) && popup.has(e.target).length == 0) {
                popup.hide();
            }
        });
    },
    moveTop: function () {
        var linkMove = $('#move-to-top'), contentTop = $('.js-content-move');

        if(contentTop.length && linkMove.length){

            $(window).scroll(function() {
                var t = $(document).scrollTop();
                var contentTopX = contentTop.offset().top;
                if (t >= contentTopX) {
                    linkMove.fadeIn();
                } else {
                    linkMove.fadeOut();
                }
            });


            linkMove.click(function(event) {
                event.preventDefault();

                $('html,body').animate({
                    scrollTop: 0
                }, 500);
            });
        }

    },
    locationChange: function (){
        if(!$(".js-language").length){
            return false;
        }

        $(".js-language").on("click", function (event) {
            event.preventDefault();
            var url = location.href;

            if(url.indexOf('#') > -1){
                url = url.substr(0, url.indexOf('#'));
            }

            if (url.indexOf('?') == -1) {
                url += '?';
            } else {
                url += '&';
            }

            location.href = url + 'locale=' + $(this).data("value");
        });
    },
    currencyChange: function (){
        if(!$(".js-currency").length){
            return false;
        }

        $(".js-currency").on("click", function (e) {
            e.preventDefault();
            var url = location.href;

            if(url.indexOf('#') > -1){
                url = url.substr(0, url.indexOf('#'));
            }

            if (url.indexOf('?') == -1) {
                url += '?';
            } else {
                url += '&';
            }

            location.href = url + 'currency=' + $(this).data("value");
        });
    },
    expandText: function (){
        $('body').on("click", ".js-open-expand-text", function (){
            var wrap = $(this).closest(".js-expand-text-item"),
                text = wrap.find(".js-expand-text");

            if(!text.is(":visible")){
                text.slideDown(250, function() {
                    wrap.addClass("open").removeClass("close");
                });
            }else{
                text.slideUp(250, function() {
                    wrap.addClass("close").removeClass("open");
                });
            }
        });
    },
    expandAccordionText: function (){
        $('body').on("click", ".js-extend-accordion-title", function (){
            var outerList = $(this).closest('.extend-accordion__list'),
                openItem = outerList.find(".js-extend-accordion-item.open"),
                currentItem = $(this).closest(".js-extend-accordion-item"),
                currentItemText = currentItem.find(".js-extend-accordion-text");

            if(openItem.length){
                var openItemText = openItem.find(".js-extend-accordion-text");

                openItemText.slideUp(250, function() {
                    openItem.addClass("close").removeClass("open");
                });
            }
            if(!currentItemText.is(":visible")){
                currentItemText.slideDown(250, function() {
                    currentItem.addClass("open").removeClass("close");
                });
            }else{
                currentItemText.slideUp(250, function() {
                    currentItem.addClass("close").removeClass("open");
                });
            }
        });
    },
    breadcrumbsScroll: function (){
        var breadcrumbsWrap = $('.js-breadcrumbs-scroll');

        if(breadcrumbsWrap.length){
            var breadcrumbsWrapWidth = breadcrumbsWrap[0].scrollWidth;


            breadcrumbsWrap.scrollLeft(breadcrumbsWrapWidth);
        }
    },
    expandSidebarPluginsNav: function (){
        var sidebarPluginsNavWrap = $('.js-sidebar-plugins-nav');

        sidebarPluginsNavWrap.each(function(){
            var plugin = $(this),
                hideElements = plugin.find(".menu-v li:hidden");

            if(hideElements.length){
                var btn = plugin.find('.js-sidebar-plugins-nav-more');
                    btn.removeClass('hide');

                btn.on("click", function(){
                    var hideElements = $(this).closest('.js-sidebar-plugins-nav').find(".menu-v li:hidden, .menu-v li.show");

                    hideElements.toggleClass("show");
                    $(this).toggleClass('open');
                });
            }
        });
    },
    expandSidebarTags: function (){
        var _this = this,
            btn = $('.js-show-tags');

        btn.on("click", function (){
            var $this = $(this),
                tags = $this.closest('.js-tags').find('.js-tag');

            if($this.hasClass('open')){
                $this.removeClass('open');
                tags.addClass("hide");
            }else{
                $this.addClass('open');
                tags.removeClass("hide");
            }
        });
    }
};

var headerFixed = {
    init: function (){
        var _this = this;

        var headerFixedWrap = $('.js-header-fixed');

        if(!headerFixedWrap.length){
            return false;
        }

        _this.showHide();
        _this.categoriesVerticalMenu();
        $(window).scroll(function() {
            _this.showHide();
        });
    },
    showHide: function (){
        var _this = this,
            wrap = $('.js-header-fixed'),
            contentTop = $('.js-content-move');

        if(contentTop.length && wrap.length){
            var t = $(document).scrollTop();
            var contentTopX = contentTop.offset().top;
            if (t >= (contentTopX + 60)) {
                wrap.show();
            } else {
                if(wrap.is(":visible")){
                    wrap.hide();
                    _this.closeMenu();
                }
            }
        }
    },
    closeMenu: function (){
        var menu = $(".js-header-fixed .js-categories-v"),
            bg = $('.js-categories-menu-bg');

        if(!menu.hasClass('close')){
            menu.addClass("close");
            menu.removeAttr("style");

            var allVerticalMenu = $('.js-categories-v'),
                isOpenOtherVerticalMenu = false;

            allVerticalMenu.each(function (){
                var innerMenu = $(this).find('.js-categories-menu');

                if(innerMenu.is(':visible') && innerMenu.css("position") === 'absolute'){
                    isOpenOtherVerticalMenu = true;
                }
            });
            if(!isOpenOtherVerticalMenu){
                bg.hide();
            }
        }
    },
    categoriesVerticalMenu: function (){
        var bg = $(".js-header-fixed .js-categories-menu-inner");

        bg.on("click", function(e){
            var menu = bg.find(".js-categories-menu");

            if (!menu.is(e.target) && menu.has(e.target).length == 0) {
                categoriesVerticalMenu.closeAll();
            }
        });

        $('body').click(function (e) {
            var popup = $(".js-search-auto-result");
            if (!$('.js-search-auto').is(e.target) && !popup.is(e.target) && popup.has(e.target).length == 0) {
                popup.hide();
            }
        });
    },
    setHeightToMainMenu: function (menu){
        if(menu.closest(".js-header-fixed").length){
            var openSubcategories = menu.find(".js-categories-sub, .js-submenu").filter(":visible"),
                mainMenuWrap = menu.closest('.js-categories-menu-inner'),
                mainMenuWrapTop = mainMenuWrap.offset().top,
                mainMenuMinHeight = menu.outerHeight();

            if(openSubcategories.size()){
                openSubcategories.each(function (){
                    var submenu = $(this),
                        submenuTop = submenu.offset().top,
                        submenuHeight = submenu.outerHeight(),
                        submenuBottom = submenuTop + submenuHeight;

                    if(mainMenuMinHeight < submenuBottom){
                        mainMenuMinHeight = submenuBottom
                    }
                });
                mainMenuMinHeight = mainMenuMinHeight - mainMenuWrapTop;
                if(mainMenuMinHeight > 0){
                    mainMenuWrap.css("min-height", mainMenuMinHeight + "px");
                }
            }
        }
    }
};

var headerMobileFixed = {
    init: function (){
        var _this = this;

        var headerFixedWrap = $('.js-m-header-top');

        if(!headerFixedWrap.length && headerFixedWrap.data('fixed')){
            return false;
        }

        _this.showHide();
        $(window).scroll(function() {
            _this.showHide();
        });
    },
    showHide: function (){
        var _this = this,
            headerWrap = $('.js-m-header-top'),
            contentTopWrap = $('.js-content-move');

        if(headerWrap.length && contentTopWrap.length){
            var scrollTop = $(document).scrollTop(),
                contentTop = contentTopWrap.offset().top;

            if (scrollTop > parseInt(contentTop + 60)) {
                if(!headerWrap.hasClass("fixed")){
                    headerWrap.css("top", "-60px");
                    headerWrap.addClass('fixed');
                    headerWrap.animate({"top": "0"}, 300, function () {
                        headerWrap.removeAttr("style");
                    });
                }
            } else if (scrollTop == 0) {
                headerWrap.removeClass('fixed');
            }
        }
    },
};

var mobilePopupBlocks = {
    init: function (){
        var _this = this;

        _this.open();

        $('.js-close-m-popup').on("click", function (){
            _this.close($(this).closest('.js-m-popup'));
        });
    },
    open: function (){
        var _this = this,
            button = $('.js-open-m-popup');

        button.on("click", function (){
            var $this = $(this),
                blockId = $this.data('id');

            if(blockId){
                var block = $("#" + blockId);
                if(block.length){
                    if(block.is(":visible")){
                        _this.close(block);
                    }else{
                        block.css("top", "100%");
                        block.addClass("show");
                         block.animate({"top": "0"}, 300, function (){
                            block.removeAttr("style");
                        });
                        $("body").addClass("overflow-hidden");
                        if(blockId = 'popup-contacts'){
                            _this.createMap(block);
                        }
                    }
                }
            }
        });
    },
    close: function (block){
        var _this = this;

        if(block.length){
            block.animate({"top": "100%"}, 300, function (){
                block.removeAttr("style");
                block.removeClass("show");
            });
            $("body").removeClass("overflow-hidden");
        }
    },
    createMap: function (popup){
        var mapWrap = popup.find('.js-popup-map');
        if(mapWrap.length && !mapWrap.find('iframe').length && mapWrap.data("link-map")){
            mapWrap.html('<iframe width="100%" height="400px" src="'+mapWrap.data("link-map")+'" frameborder="0" allowfullscreen=""></iframe>');
        }
    }
};

var mobileMenu = {
    init: function (){
        var _this = this;

        _this.open();
        _this.closeByButton();
        _this.closeByBg();
        _this.showSubmenu();
    },
    open: function (){
        var _this = this,
            button = $('.js-open-mobile-menu');

            button.on("click", function (){
                var menuId = $(this).data('id');
                if(!menuId){
                    return false;
                }
                var menu = $("#" + menuId);
                if(menu.length) {
                    var isLazyImages = menu.data("lazy"),
                        isRetinaImages = menu.data("retina");

                    if(isLazyImages){
                        categoriesImages.lazyImages(menu.find('.js-menu-m-image'), isRetinaImages);
                    }else if(isRetinaImages){
                        categoriesImages.retinaImages(menu.find('.js-menu-m-image'));
                    }

                    if (menu.is(":visible")) {
                        _this.close(menu);
                    } else {
                        var isOPenSelectedItem = menu.data("open-selected-item"),
                            itemsMenuSelected = menu.find('.js-m-menu-item.selected');
                        if(isOPenSelectedItem && itemsMenuSelected.length){
                            itemsMenuSelected.parents(".js-m-menu-item").addClass('open');
                        }

                        menu.css("left", "100%");
                        menu.addClass("show");
                        menu.animate({"left": "0"}, 300, function () {
                            menu.removeAttr("style");
                        });
                        $("body").addClass("overflow-hidden").append("<div class='js-m-bg m-bg'></div>");
                    }
                }

            });

    },
    closeByButton: function (){
        var _this = this,
            button = $('.js-mobile-menu-close');

        button.on("click", function (){
            var menu = $(this).closest('.js-mobile-menu');

            _this.close(menu);
        });
    },
    closeByBg: function (){
        var _this = this;

        $('body').on("click", '.js-m-bg', function (){
            _this.close($('.js-mobile-menu'));
        });
    },
    close: function (menu){
        if(menu.length){
            menu.animate({"left": "-100%"}, 300, function (){
                menu.removeAttr("style");
                menu.removeClass("show");
                $("body").removeClass("overflow-hidden");
                $(".js-m-bg").first().remove();
            });
        }
    },
    showSubmenu: function (){
        var _this = this,
            button = $('.js-m-submenu-open');

        button.on("click", function(){
            var item = $(this).parent();

            if(item.hasClass('open')){
                item.removeClass('open');
            }else{
                item.addClass('open');
            }
        });
    }
};

var anchorLink = {
    init: function (){
        var anchorLink = $('.js-move-to-tab');

        anchorLink.on("click", function(event){
            event.preventDefault();

            var $this = $(this),
                idWrapContent = $this.data('tab-content');

            var contentTabWrap = $('.js-tab-content#' + idWrapContent),
                contentAccordionTabWrap = $('.js-accordion-tab-content#' + idWrapContent),
                content = $('#' + idWrapContent);

            if(contentTabWrap.length){
                tabs.moveToTabContent($this, contentTabWrap);
            }else if(contentAccordionTabWrap.length){
                accordionTabs.moveToTabContent($this, contentAccordionTabWrap);
            }else{
                var top = content.offset().top - 40;
                if($('.js-header-fixed').length){
                    top = top - 70;
                }
                $('html,body').animate({
                    scrollTop: top
                }, 500);
            }
        });
    }
}

var tabs = {
    init: function (){
        var _this = this;

        _this.initSelectTab();
        _this.selectTab();
        _this.scrollMobile();
    },
    selectTab: function (){
        $('body').on("click", ".js-tab", function(){
            var $this = $(this),
                contentWrapId = $this.data('tab-content'),
                content = $this.closest('.js-tabs-outer').find('.js-tab-content'),
                tabs = $this.closest('.js-tabs-outer').find('.js-tab'),
                currentContent = $this.closest('.js-tabs-outer').find('#' + contentWrapId);

            tabs.removeClass('selected');
            content.removeClass('selected');

            $this.addClass('selected');
            currentContent.addClass('selected');
        });
    },
    moveToTabContent: function (anchorLink, currentTabContent){
        var tabsOuterWrap = currentTabContent.closest(".js-tabs-outer"),
            allTabsContent = tabsOuterWrap.find('.js-tab-content'),
            tab = tabsOuterWrap.find('.js-tab'),
            thisTfb = tabsOuterWrap.find('.js-tab[data-tab-content="'+anchorLink.data('tab-content')+'"]');

        tab.removeClass('selected');
        thisTfb.addClass('selected');
        allTabsContent.removeClass('selected');
        currentTabContent.addClass('selected');

        var top = currentTabContent.offset().top - 115;
        if($('.js-header-fixed').length){
            top = top - 75;
        }
        $('html,body').animate({
            scrollTop: top
        }, 500);
    },
    initSelectTab: function (){
        var tabsWrapOuter = $('.js-tabs-outer');

        if(tabsWrapOuter.length){
            tabsWrapOuter.each(function (){
                var $this = $(this),
                    selectedTab = $this.find('.js-tab.selected');

                if(!selectedTab.length){
                    selectedTab = $this.find('.js-tab:first');

                    if(selectedTab.length){
                        var tabContent = tabsWrapOuter.find('#' + selectedTab.data('tab-content'));

                        if(tabContent.length){
                            selectedTab.addClass('selected');
                            tabContent.addClass('selected');
                        }
                    }
                }
            });
        }
    },
    scrollMobile: function (){
        var tabs = $('.js-tabs'),
            isMobile = $('body').hasClass('touch');

        if(tabs.length && isMobile){
            tabs.each(function (){
                var oneTabs = $(this);

                if(oneTabs[0].offsetWidth < oneTabs[0].scrollWidth){
                    oneTabs.addClass("tabs--shadow");
                }
            });
        }

        $(".js-tabs").on( 'scroll', function(){
            $(this).removeClass("tabs--shadow");
        });

    }
};

var accordionTabs = {
    init: function (){
        var _this = this;

        _this.initSelectTab();
        _this.selectTab();
    },
    selectTab: function (){
        $('body').on("click", '.js-accordion-tab', function(){
            var $this = $(this),
                contentWrapId = $this.data('tab-content'),
                currentContent = $this.closest('.js-accordion-tabs-outer').find('#' + contentWrapId);

            if(currentContent.is(':visible')){
                $this.removeClass('selected');
                currentContent.removeClass('selected');
            }else{
                $this.addClass('selected');
                currentContent.addClass('selected');
            }
        });
    },
    moveToTabContent: function (anchorLink, currentTabContent){
        var tabsOuterWrap = currentTabContent.closest(".js-accordion-tabs-outer"),
            thisTfb = tabsOuterWrap.find('.js-accordion-tab[data-tab-content="'+anchorLink.data('tab-content')+'"]');

        thisTfb.addClass('selected');
        currentTabContent.addClass('selected');

        var top = currentTabContent.offset().top - 40;
        if($('.js-header-fixed').length){
            top = top - 70;
        }
        $('html,body').animate({
            scrollTop: top
        }, 500);
    },
    initSelectTab: function (){
        var tabsWrapOuter = $('.js-accordion-tabs-outer');

        if(tabsWrapOuter.length){
            tabsWrapOuter.each(function (){
                var $this = $(this),
                    selectedTab = $this.find('.js-accordion-tab.selected');

                if(!selectedTab.length){
                    selectedTab = $this.find('.js-accordion-tab:first');

                    if(selectedTab.length){
                        var tabContent = tabsWrapOuter.find('#' + selectedTab.data('tab-content'));

                        if(tabContent.length){
                            selectedTab.addClass('selected');
                            tabContent.addClass('selected');
                        }
                    }
                }
            });
        }
    }
};

var slider = {
    init: function (){
        var _this = this,
            sliders = $('.js-main-slider');

        if(sliders.length){
            sliders.each(function (){
                var slider = $(this);

                _this.sliderAuto(slider);
                _this.sliderInteraction(slider);
            });
        }

        var firstSlideImage = $('.js-product-item:first-child').find('.js-slider-image');
        if(firstSlideImage.length && $.Retina) {
            firstSlideImage.retina();
        }
    },
    sliderAuto: function (slider){
        var _this = this;

        var pause = parseInt(slider.data("pause")) * 1000;

        if(pause > 0){
            setTimeout(function (){
                _this.sliderInit(slider,null, pause);
            }, pause);
        }
    },
    sliderInteraction: function (slider){
        var _this = this;

        var sliderNav = $('.js-slider-init-interaction');
        sliderNav.on("click", function (){
            var thisSlider = $(this).closest('.js-slider-wrap').find('.js-main-slider');

            _this.sliderInit(thisSlider, $(this).data("index"), false);
            thisSlider.trigger('stop.owl.autoplay');
        });

        if(is_touch_device() || slider.data("swipe")){
            slider.swipe( {
                allowPageScroll:"auto",
                threshold: 20,
                swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                    if(direction == 'left'){
                        _this.sliderInit(slider,"next", false);
                    }
                    else if(direction == 'right') {
                        _this.sliderInit(slider,"prev", false);
                    }
                    if(slider.hasClass("owl-loaded")){
                        slider.trigger('stop.owl.autoplay');
                    }
                }
            });
        }
    },
    sliderInit: function (slider, goToSlid, pause){
        var _this = this;

        if(slider.hasClass("owl-loaded")){
            return false;
        }

        var auto = (pause) ? true : false,
            isMouseDrag = slider.data("swipe"),
            isLazy = slider.data("lazy"),
            navWrap = slider.closest(".js-slider-wrap").find('.js-slider-nav'),
            dotsWrap = slider.closest(".js-slider-wrap").find('.js-slider-dots');

        var params = {
            loop: true,
            margin: 0,
            nav: true,
            navElement: "div",
            navContainer: navWrap,
            dotsContainer: dotsWrap,
            navText: ['<span class="carousel-prev"></span>', '<span class="carousel-next"></span>'],
            lazyLoad: isLazy,
            autoHeight: true,
            items: 1,
            mouseDrag: isMouseDrag,
            onInitialize: function (){
                navWrap.html("");
                dotsWrap.html("");
            },
            onInitialized: function (event){
                if (typeof $.autobadgeFrontend !== 'undefined') {
                    $.autobadgeFrontend.reinit();
                }

                var thisSlider = $(event.currentTarget);
                if(isLazy) {
                    _this.setHeightNextSlide(thisSlider);
                }
            },
            onTranslate: function(){
                slider.addClass("_switch");
            },
            onDragged: function(event){
                if (typeof $.autobadgeFrontend !== 'undefined') {
                    $.autobadgeFrontend.reinit();
                }
            },
            onTranslated: function(event){
                if (typeof $.autobadgeFrontend !== 'undefined') {
                    $.autobadgeFrontend.reinit();
                }
            },
            onLoadedLazy: function(event){
                var thisSlider = $(event.currentTarget),
                    activeSlide = thisSlider.find(".owl-item.active");

                thisSlider.find('.js-slider-banner-inner').removeAttr("style");

                if (activeSlide.length && $.Retina) {
                    activeSlide.find(".js-product-item .js-slider-image").retina();
                }

                var activeImage = activeSlide.find(".owl-lazy");
                activeImage.removeClass("owl-lazy");

                var clonedImage = thisSlider.find(".owl-item img[src='" + activeImage.attr("src") + "']");
                clonedImage.removeClass("owl-lazy");

                thisSlider.trigger('refresh.owl.carousel');
                _this.setHeightNextSlide(thisSlider);
            }
        };

        if(auto){
            params["autoplay"] = true;
            params["autoplayTimeout"] = pause;
            params["autoplayHoverPause"] = true;
        }

        slider.owlCarousel(params);

        $('.js-slider-wrap .owl-prev, .js-slider-wrap .owl-next, .js-slider-wrap .owl-dot').on('click', function(e) {
            slider.trigger('stop.owl.autoplay');
        });

        if (goToSlid) {
            if (goToSlid == "prev") {
                slider.trigger("prev.owl.carousel");
            } else if (goToSlid == "next") {
                slider.trigger("next.owl.carousel");
            } else {
                slider.trigger("to.owl.carousel", [parseInt(goToSlid)]);
            }
        }

        if(!is_touch_device() || isMouseDrag){
            slider.swipe( {
                allowPageScroll:"auto",
                threshold: 20,
                swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                    if(direction == 'left'){
                        slider.trigger("next.owl.carousel");
                    }
                    else if(direction == 'right') {
                        slider.trigger("prev.owl.carousel");
                    }
                }
            });
        }
    },
    setHeightNextSlide: function (slider){
        var isLazySlider = slider.find("img.owl-lazy").length;

        if(isLazySlider){
            var heightSlider = slider.height(),
                activeSlide = slider.find(".owl-item.active"),
                nextSlide = activeSlide.next(),
                prevSlide = activeSlide.prev();

            if(nextSlide.length && nextSlide.find("img.owl-lazy").length){
                var nextSlideInner = nextSlide.find(".js-slider-banner-inner");

                if(nextSlideInner.length){
                    nextSlideInner.css("height", heightSlider + "px");
                }
            }

            if(prevSlide.length && prevSlide.find("img.owl-lazy").length){
                var prevSlideInner = prevSlide.find(".js-slider-banner-inner");

                if(prevSlideInner.length){
                    prevSlideInner.css("height", heightSlider + "px");
                }
            }
        }
    },
};

var countdown = {
    init: function (){
        $(".js-promo-countdown").each(function(){
            var $this = $(this),
                dateEnd = $this.data("end"),
                dayText = $this.data("day-text"),
                isWrap = $this.data("wrap");

            $this.countdown(dateEnd, function(event) {
                if(isWrap){
                    $this.find('.js-countdown-days').html(event.strftime('%D'));
                    $this.find('.js-countdown-hours').html(event.strftime('%H'));
                    $this.find('.js-countdown-minutes').html(event.strftime('%M'));
                    $this.find('.js-countdown-seconds').html(event.strftime('%S'));
                }else{
                    $this.text(event.strftime('%D ' + dayText + ' %H:%M:%S'));
                }
            });
        });
    }
};

var form = {
    init: function (){
        var _this = this;

        _this.formStyler();
        _this.submit();
        _this.numberType();
    },
    formStyler: function (){
        $('body').on('change', 'input[type="checkbox"]', function() {
            if ($(this).is(':checked')) {
                $(this).closest('.jq-checkbox, .js-checkbox-styler').addClass('checked');
                $(this).closest('label').addClass('checked');
            } else {
                $(this).closest('.jq-checkbox, label, .js-checkbox-styler').removeClass('checked');
                $(this).closest('label').removeClass('checked');
            }
        });

        $('body').on('change', 'input[type="radio"]', function() {
            var inputs = $('input[type="radio"][name="'+$(this).attr('name')+'"]');
            inputs.each(function(){
                var input = $(this);
                if (input.is(':checked')) {
                    input.closest('.jq-radio, .js-radio-styler').addClass('checked');
                    input.closest('label').addClass('checked');
                }else{
                    input.closest('.jq-radio, .js-radio-styler').removeClass('checked');
                    input.closest('label').removeClass('checked');
                }
            });

        });

        if(!globalThemeSettings.isFormStylerInit){
            return false;
        }

        var inputStyler = $('input[type="checkbox"]:not(.js-checkbox-styler-input):not(.js-none-styler):not(.shop-sk-callback__checkbox), input[type="radio"]:not(.js-radio-styler-input):not(.buy1step-auth__variant):not(.js-none-styler), .js-select');
        if(!inputStyler.length){
            return false;
        }
        inputStyler.styler();
        $('.searchpro__page-filters input[type="checkbox"], .searchpro__page-filters input[type="radio"]').styler('destroy');
        $('.onestep-cart input[type="checkbox"], .onestep-cart input[type="radio"]').styler('destroy');
        $('.js-addgifts__cart-item input[type="radio"]').styler('destroy');
    },
    submit: function (){
        $('body').on("click", ".js-submit-form", function (){
            var $this = $(this),
                form = $this.closest("form");

            if(!$this.hasClass('disabled')){
                form.submit();
            }
        });
    },
    numberType: function (){
        $('body').on("keyup", ".js-number", function(){
            var reg_number = /[^0-9]/g;

            $(this).val($(this).val().replace(reg_number, ''));
        });
    }
};

var headerMenu = {
    init: function (){
        var _this = this;

        _this.hoverByItem();
    },
    hoverByItem: function (){
        var _this = this,
            menu = $('.js-header-menu-outer');

        /*touch for mobile*/
        menu.find('.js-header-menu-item, .js-header-submenu-item a').click(function (event){
            var isMobile = $('body').hasClass('touch');
            if(isMobile){
                var submenu = $(this).closest('.js-header-menu-item, .js-header-submenu-item').find('.js-header-submenu-outer').first();
                if(submenu.length && submenu.is(':hidden')){
                    event.preventDefault();
                }
            }
        });

        $('body').on("mouseover", '.js-header-menu-item, .js-header-submenu-item', function () {
            $(this).find('.js-header-submenu-outer').first().stop(true).delay(150).fadeIn(1, function (){
                $(this).closest('.js-header-menu-item').addClass('hover');
                $('.js-header-menu-bg').show();
                $('.js-header-top').css("z-index", "11");
                _this.positionSubmenu(menu, $(this).closest('.js-header-submenu-outer'));

            });
        });

        $('body').on("mouseout", '.js-header-menu-item, .js-header-submenu-item', function () {
            $(this).find('.js-header-submenu-outer').first().stop(true).delay(150).fadeOut(1, function(){
                $(this).closest('.js-header-menu-item').removeClass('hover');
            });
        });

        $('.js-header-top-inner, .js-header-menu-outer').mouseleave(function (){
            $('.js-header-menu-bg').hide();
            $('.js-header-top').removeAttr("style");
        });
    },
    positionSubmenu: function (wrapMenu, subMenu){
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
            subMenu.css('display', 'block');
        }
    }
};

var categoriesVerticalMenu = {
    init: function (){
        var _this = this;

        if(!$('.js-categories-v').length){
            return false;
        }

        _this.open();
        _this.closeByBg();
        _this.openSubcategories();
        _this.retinaImagesFirstlvl($('.js-categories-v:not(.close) .js-categories-menu'));
    },
    open: function () {
        var _this = this,
            btn = $('.js-categories-btn'),
            bg = $('.js-categories-menu-bg'),
            allVerticalMenu = $('.js-categories-menu');

        btn.on("click", function(){
            var $this = $(this),
                wrap = $this.closest('.js-categories-v'),
                thisMenu = wrap.find('.js-categories-menu');

            if(thisMenu.is(':visible')){
                _this.close($this);
            }else{
                _this.closeAll();

                wrap.removeClass("close");
                if(thisMenu.css("position") === "absolute" || thisMenu.closest(".js-header-fixed").length){
                    bg.show();
                    wrap.css("z-index", 11);
                }
                _this.retinaImagesFirstlvl(thisMenu);
            }
        });
    },
    close: function (button){
        var _this = this,
            bg = $('.js-categories-menu-bg'),
            wrap = $('.js-categories-v');

        if(button){
            wrap = button.closest('.js-categories-v');
        }

        wrap.addClass("close");
        bg.hide();
        wrap.removeAttr("style");
    },
    closeAll: function (){
        var _this = this,
            allMenu = $('.js-categories-menu');

        allMenu.each(function (){
            var menu = $(this),
                button = menu.closest('.js-categories-v').find('.js-categories-btn');

            if(menu.css("position") === "absolute" || menu.closest(".js-header-fixed").length){
                _this.close(button);
            }
        });
    },
    closeByBg: function (){
        var _this = this,
            bg = $('.js-categories-menu-bg');

        bg.on("click", function(){
            _this.closeAll();
        });
    },
    retinaImagesFirstlvl: function (menu){
        if(menu.length){
            var isLazyImages = menu.data("lazy"),
                isRetinaImages = menu.data("retina");

            if(isLazyImages){
                categoriesImages.lazyImages(menu.find('.js-categories-v-image'), isRetinaImages);
            }else if(isRetinaImages){
                categoriesImages.retinaImages(menu.find('.js-categories-v-image'));
            }
        }
    },
    openSubcategories: function () {
        var menu = $('.js-categories-menu'),
            isMobile = $('body').hasClass('touch');

        menu.menuAim({
            rowSelector: "> .js-categories-v-item",
            activate: function(current){
                var isMobile = $('body').hasClass('touch'),
                    currentMenu = $(current).closest('.js-categories-menu'),
                    menuIsAbsolute = (currentMenu.css("position") === "absolute"),
                    menuIsInFixedHeader = (currentMenu.closest(".js-header-fixed").length > 0),
                    menuOuter = currentMenu.closest('.js-categories-v'),
                    isLazyImages = currentMenu.data("lazy"),
                    isRetinaImages = currentMenu.data("retina"),
                    link = $(current).find('a').first(),
                    subcats = $(current).find(".js-categories-sub");

                if(isMobile && link.size() && subcats.size() && $(current).data("type") != "else"){
                    link.click(function (event){
                        if(!$(current).hasClass('hover')) {
                            event.preventDefault();
                            $(current).addClass('hover');
                        }
                    });
                }else{
                    $(current).addClass('hover');
                }
                if(isLazyImages){
                    categoriesImages.lazyImages($(current).find('.js-subcat-image'), isRetinaImages);
                    categoriesImages.lazyImages($(current).find('.js-brand-image'), false);
                }else if(isRetinaImages){
                    categoriesImages.retinaImages($(current).find('.js-subcat-image'));
                }
                $(current).find('.js-submenu-image-lazy').lazy();

                if(!menuIsAbsolute && !menuIsInFixedHeader && subcats.size()){
                    $('.js-categories-menu-bg').show();
                    menuOuter.css("z-index", 11);
                }
                headerFixed.setHeightToMainMenu(currentMenu);
            },
            deactivate: function(current){
                $(current).removeClass('hover');
            },
            enter: function(current) {
                if(!menu.find(".js-categories-sub").filter(":visible").size()){
                    var isMobile = $('body').hasClass('touch'),
                        currentMenu = $(current).closest('.js-categories-menu'),
                        menuIsAbsolute = (currentMenu.css("position") === "absolute"),
                        menuIsInFixedHeader = (currentMenu.closest(".js-header-fixed").length > 0),
                        menuOuter = currentMenu.closest('.js-categories-v'),
                        link = $(current).find('a').first(),
                        subcats = $(current).find(".js-categories-sub");

                    if(isMobile && link.size() && subcats.size()){
                        link.click(function (event){
                            if(!$(current).hasClass('hover')) {
                                event.preventDefault();
                            }
                        });
                    }else{
                        $(current).addClass('hover');
                    }
                    if(!menuIsAbsolute && !menuIsInFixedHeader && subcats.size()){
                        $('.js-categories-menu-bg').show();
                        menuOuter.css("z-index", 11);
                    }
                }
            },
            exitMenu: function(currentMenu){
                var menuIsAbsolute = ($(currentMenu).css("position") === "absolute"),
                    menuIsInFixedHeader = ($(currentMenu).closest(".js-header-fixed").length > 0),
                    menuOuter = $(currentMenu).closest('.js-categories-v');

                $(currentMenu).find(".js-categories-v-item").removeClass('hover');
                if(!menuIsAbsolute && !menuIsInFixedHeader) {
                    $('.js-categories-menu-bg').hide();
                    menuOuter.removeAttr("style");
                }
            }
        });

        menu.find(".js-subcategories-menu").each(function(){
            var $submenu = $(this);
            $submenu.menuAim({
                rowSelector: "> .js-categories-sub-item",
                activate: function(current){
                    var isMobile = $('body').hasClass('touch'),
                        link = $(current).find('a').first(),
                        subcats = $(current).find(".js-submenu");

                    if(isMobile && link.size() && subcats.size()){
                        link.click(function (event){
                            if(!$(current).hasClass('hover')) {
                                event.preventDefault();
                                $(current).addClass('hover');
                            }
                        });
                    }else{
                        $(current).addClass('hover');
                    }
                    headerFixed.setHeightToMainMenu($(current).closest('.js-categories-menu'));
                },
                deactivate: function(current){
                    $(current).removeClass('hover');
                },
                enter: function(current) {
                    if(!$submenu.find(".js-submenu").filter(":visible").size()){
                        var isMobile = $('body').hasClass('touch'),
                            link = $(current).find('a').first(),
                            subcats = $(current).find(".js-submenu");

                        if(isMobile && link.size() && subcats.size()){
                            link.click(function (event){
                                if(!$(current).hasClass('hover')) {
                                    event.preventDefault();
                                }
                            });
                        }else{
                            $(current).addClass('hover');
                        }
                    }
                },
                exitMenu: function(){
                    $(".js-categories-sub-item").removeClass('hover');
                }
            })
        })
    }
};

var categoriesHorizontalMenu = {
    init: function (){
        var _this = this;

        if(!$('.js-categories-h-outer').length){
            return false;
        }

        _this.openSubcategories();
        _this.hideBg();
        _this.retinaImagesFirstlvl($('.js-categories-h-items'));
    },
    openSubcategories: function () {
        var _this = this,
            menu = $('.js-categories-h-items'),
            isLazyImages = menu.data("lazy"),
            isRetinaImages = menu.data("retina");

        menu.find('.js-h-categories-item a').click(function (event){
            var isMobile = $('body').hasClass('touch');
            if(isMobile){
                var submenu = $(this).closest('.js-h-categories-item').find('.js-categories-sub').first();
                if(submenu.length && submenu.is(':hidden')){
                    event.preventDefault();
                }
            }
        });

        menu.find('.js-h-categories-item').hover(function () {
            $(this).find('.js-categories-sub').first().stop(true).delay(150).fadeIn(1, function (){
                $(this).closest('.js-h-categories-item').addClass('hover');

                if(isLazyImages){
                    categoriesImages.lazyImages($(this).find('.js-subcat-image'), isRetinaImages);
                    categoriesImages.lazyImages($(this).find('.js-brand-image'), false);
                }else if(isRetinaImages){
                    categoriesImages.retinaImages($(this).find('.js-subcat-image'));
                }

                _this.positionSubmenu($(this).closest('.js-categories-h-items'), $(this));

                _this.showBg();
            });
        }, function () {
            $(this).find('.js-categories-sub').first().stop(true).delay(150).fadeOut(1, function(){
                $(this).closest('.js-h-categories-item').removeClass('hover');
            });
        });

        menu.find('.js-subcategories-item a').click(function (event){
            var isMobile = $('body').hasClass('touch');
            if(isMobile){
                var submenu = $(this).closest('.js-subcategories-item').find('.js-categories-sub').first();
                if(submenu.length && submenu.is(':hidden')){
                    event.preventDefault();
                }
            }
        });

        menu.find('.js-subcategories-item').hover(function () {
            $(this).find('.js-categories-sub').first().stop(true).delay(150).fadeIn(1, function (){
                $(this).closest('.js-h-categories-item').addClass('hover');
                _this.positionSubmenu($(this).closest('.js-categories-h-items'), $(this));
            });
        }, function () {
            $(this).find('.js-categories-sub').first().stop(true).delay(150).fadeOut(1, function(){
                $(this).removeAttr("style");
            });
        });
    },
    positionSubmenu: function (wrapMenu, subMenu){
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
            subMenu.css('display', 'block');
        }
    },
    showBg: function (){
        var _this = this;

        $('.js-header-top, .js-categories-h-outer').css("z-index", 11);
        $('.js-header').css("z-index", 12);
        $('.js-categories-menu-bg').stop(true).delay(150).fadeIn(1);
    },

    hideBg: function (){
        var _this = this;

        var menuWrap = $('.js-categories-h-outer');

        menuWrap.on("mouseleave", function (){
            $('.js-categories-menu-bg').stop(true).delay(150).fadeOut(1, function (){
                $('.js-header, .js-header-top, .js-categories-h-outer').removeAttr("style");
            });
        });
    },
    retinaImagesFirstlvl: function (menu){
        if(menu.length){
            var isLazyImages = menu.data("lazy"),
                isRetinaImages = menu.data("retina");

            if(isLazyImages){
                categoriesImages.lazyImages(menu.find('.js-categories-h-image'), isRetinaImages);
            }else if(isRetinaImages){
                categoriesImages.retinaImages(menu.find('.js-categories-h-image'));
            }
        }

    }
};

var categoriesVerticalMenuUnfolding = {
    init: function (){
        var _this = this;

        if(!$('.js-sidebar-cats-tree').length){
            return false;
        }

        _this.sidebar();
        _this.categoriesImages();
    },
    sidebar: function (){
        var _this = this;

        _this.sidebarInit();

        var openBtn = $('.js-subcat-open');

        openBtn.click(function(){
            var $this = $(this), subs = $($this.parent().find('.js-subcat')[0]);

            if(!subs.is(':visible')){
                subs.slideDown();
                $this.addClass('selected');
            }else{
                subs.slideUp();
                $this.removeClass('selected');
            }
        });
    },
    sidebarInit: function (){
        var treeCats = $('.js-sidebar-cats-tree'),
            currentCat = treeCats.find('.selected'),
            outWrap = currentCat.parents(".js-subcat");

        outWrap.removeClass('hide');
        outWrap.each(function(){
            $(this).parent().find('.js-subcat-open').first().addClass('selected');
        });
    },
    categoriesImages: function (){
        var menu = $('.js-sidebar-cats-tree'),
            isLazyImages = menu.data("lazy"),
            isRetinaImages = menu.data("retina");

        if(isLazyImages){
            categoriesImages.lazyImages(menu.find('.js-sidebar-cat-image'), isRetinaImages);
        }else if(isRetinaImages){
            categoriesImages.retinaImages(menu.find('.js-sidebar-cat-image'));
        }
    }
};

var pagesTree = {
    init: function (){
        var _this = this,
            treeCats = $('.js-sidebar-pages-tree');

        if(!treeCats.length){
            return false;
        }

        _this.open();

        var currentCat = treeCats.find('.selected'),
            outWrap = currentCat.parents(".js-subpages");

        outWrap.removeClass('hide');
        outWrap.each(function(){
            $(this).parent().find('.js-subpages-open').first().addClass('selected');
        });
    },
    open: function (){
        var _this = this;

        var openBtn = $('.js-subpages-open');

        openBtn.click(function(){
            var $this = $(this), subs = $($this.parent().find('.js-subpages')[0]);

            if(!subs.is(':visible')){
                subs.slideDown();
                $this.addClass('selected');
            }else{
                subs.slideUp();
                $this.removeClass('selected');
            }
        });
    },

};

var dropDownList = {
    init: function (){
        var _this = this;

        _this.open('.js-drop-down-toggle', '.js-drop-down-list', '.js-drop-down-items');
    },
    open: function (linkSelector, wrapParentSelector, itemsWrapSelector){
        var _this = this;

        $('body').on("click", linkSelector, function(){
            var list = $(this).closest(wrapParentSelector);
            var items = list.find(itemsWrapSelector);

            list.siblings().removeClass('open');
            list.siblings().find(wrapParentSelector).removeClass('open');

            if(items.is(':visible')){
                list.removeClass('open');
            }else{
                list.addClass('open');
            }
        });

        $('body').on("click", linkSelector+ ' a', function(event){
            event.preventDefault();
        });

        $(document).click(function(event) {
            if(!$(event.target).closest(wrapParentSelector).length) {
                $(wrapParentSelector).each(function (){
                    $(this).removeClass('open');
                });
            }
        });
    }
};

var MatchMedia = function( media_query ) {
    var matchMedia = window.matchMedia,
        is_supported = (typeof matchMedia === "function");
    if (is_supported && media_query) {
        return matchMedia(media_query).matches
    } else {
        return false;
    }
};

var subscribeForm = {
    init: function (){
        var _this = this;

        _this.submitForm();
        _this.openCaptcha();
    },
    openCaptcha: function (){
        var emailField = $('.js-subscribe-input');

        emailField.on("focus", function (){
            var captcha = emailField.closest('form').find('.js-subscribe-image');

            if(!captcha.find('.wa-invisible-recaptcha').length){
                captcha.addClass("show");
            }
        });

        $(document).click(function(event) {
            if(!$(event.target).closest("#mailer-subscribe-form").length) {
                $("#mailer-subscribe-form .js-subscribe-image").removeClass('show');
            }
        });

    },
    submitForm: function (){
        // MAILER app email subscribe form
        $('#mailer-subscribe-form input.charset').val(document.charset || document.characterSet);
        $('#mailer-subscribe-form').submit(function() {
            var form = $(this);

            var email_input = form.find('.js-subscribe-input');
            var email_submit = form.find('.js-submit-form');
            var email_agree = form.find('input[name="agree"]');
            var loader = form.find('.js-subscribe-load');
            var error = false;
            var usualCaptcha = form.find('.js-subscribe-image');
            var usualCaptchaInput = usualCaptcha.find('.wa-captcha-input');
            var errorMessage = $('#mailer-subscribe-error');

            email_input.removeClass('error');
            email_agree.closest('.js-subscribe-agree').removeClass('error');

            if(email_agree.length > 0 && !email_agree.is(':checked')){
                email_agree.closest('.js-subscribe-agree').addClass('error');
                error = true;
            }
            if(!email_input.val()){
                email_input.addClass('error');
                error = true;
            }else{
                if(!validateEmail(email_input.val())){
                    email_input.addClass('error');
                    error = true;
                }else{
                    if (!usualCaptcha.length){
                        email_input.attr('name', 'email');
                    }
                }
            }
            if(error === false){
                email_submit.hide();
                loader.show();
                errorMessage.hide();
                errorMessage.find(".js-text-error").remove();
                usualCaptchaInput.removeClass("error");

                var url = form.attr("action");
                if (url.substr(0,4) === "http") {
                    url = url.replace("http:", "").replace("https:", "");
                }

                $.post(url, form.serialize(), function (response) {
                    if (!usualCaptcha.length){
                        email_input.attr('name', 'username');
                    }

                    if(response.status == "ok"){
                        $('.js-subscribe-inputs').hide();
                        if(response.data.html){
                            $('#mailer-subscribe-thankyou').html(response.data.html);
                        }
                        $('#mailer-subscribe-thankyou').show();
                        errorMessage.hide();
                        errorMessage.find(".js-text-error").remove();
                        usualCaptchaInput.removeClass("error");
                    }else{
                        if(response.errors){
                            var errorText = Object.entries(response.errors).toString();
                            errorText = errorText.replace(","," ");

                            if(errorText.indexOf("captcha") !== -1 && usualCaptchaInput.length){
                                usualCaptchaInput.addClass("error");
                                usualCaptcha.addClass("show");
                            }
                            errorMessage.append("<span class='js-text-error'>: "+errorText+"</span>");
                        }
                        errorMessage.show();
                    }
                    email_submit.show();
                    loader.hide();
                });
            }else{
                if (!usualCaptcha.length){
                    email_input.attr('name', 'username');
                }
            }

            return false;
        })
    }
};

var moreText = {
    init: function (){
        var _this = this;

        _this.addLink();
        _this.open();
    },
    addLink: function (){
        $('.js-moretext-outer').each(function (){
            var wrapOuter = $(this).find(".js-moretext-wrap"),
                maxHeight = wrapOuter.data("max-height"),
                wrapText = $(this).find(".js-moretext"),
                linkMore = $(this).find('.js-moretext-more-wrap');

            linkMore.remove();
            wrapOuter.removeClass("close");
            if (wrapOuter.length && maxHeight){
                wrapOuter.css("max-height", maxHeight + "px");
            }
            if(wrapOuter.length && wrapText.length && wrapOuter.outerHeight() < wrapText.outerHeight()){
                var textMore = wrapOuter.data("text-more");

                wrapOuter.addClass("close");
                wrapOuter.after("<div class='js-moretext-more-wrap linkmore-wrap'><span class='js-moretext-more bs-color read-more'>"+textMore+"</span></div>");
            }else{
                wrapOuter.removeAttr("style");
            }
        });
    },
    open: function (){
        $('body').on("click", ".js-moretext-more", function(){
            var $this = $(this),
                wrapOuter = $this.closest('.js-moretext-outer').find(".js-moretext-wrap"),
                wrapText = $this.closest('.js-moretext-outer').find(".js-moretext"),
                maxHeight = wrapOuter.data("max-height"),
                textMore = wrapOuter.data("text-more"),
                textHide = wrapOuter.data("text-hide");

            if($this.hasClass("open")){
                $this.removeClass("open");
                wrapOuter.addClass("close");
                $this.text(textMore);
                wrapOuter.animate({maxHeight: maxHeight}, 500);
            }else{
                wrapOuter.animate({maxHeight: wrapText.outerHeight() + "px"}, 500);
                $this.addClass("open");
                wrapOuter.removeClass("close");
                $this.text(textHide);
            }

        });
    }
};

var modalForm = {
    init: function (){
        var _this = this;

        _this.loadContentForm('a.js-form-popup');
        _this.loadContentForm('.js-ajax-form a[href="/login/"]');
        _this.loadContentForm('.js-ajax-form a[href="/forgotpassword/"]');
        _this.submitForm();
    },
    loadContentForm : function (selectorBtnElement){
        var _this = this;
        var selectorContent = '.js-ajax-form';

        $('body').on("click", selectorBtnElement, function (event){
            event.preventDefault();
            var url = $(this).attr('href')+"?ajax=1";

            $.magnificPopup.close();
            $('body').prepend("<div class='js-loading-bg mfp-bg mfp-ready'><div class='mfp-preloader'></div></div>");

            $.get(url, function(data) {
                $('.js-loading-bg').remove();

                var content = $(data).find(selectorContent);
                _this.openModal(content, url);
            });
        });
    },
    openModal: function (content, url){
        var _this = this;

        $(content).find('form').attr("action", url);
        $(content).find('input[type="checkbox"], input[type="radio"], .js-select').styler();

        $.magnificPopup.open({
            items: {
                src: "<div class='popup-content'>"+content.outerHTML()+"</div>"
            },
            type: 'inline'
        }, 0);
    },
    submitForm: function(){
        var _this = this;

        $('body').on("submit", '.js-ajax-form form', function(event){
            var wrapForm = $(this).closest('.js-ajax-form');
            if (!wrapForm.find('.wa-login-form-wrapper').length && !wrapForm.find('.wa-forgotpassword-form-wrapper').length) {
                event.preventDefault();

                var url = $(this).attr("action"),
                    data = $(this).serialize(),
                    btn = $(this).find('input[type="submit"]');

                btn.hide();
                btn.after($('<i class="icon16 loading js-loading"></i>'));

                $.post(url, data, function(data){
                    var content = $(data).find('.js-ajax-form');
                    if(content.length > 0){
                        _this.openModal(content, url);
                        btn.show();
                        $(this).find(".js-loading").remove();
                    }else{
                        window.location.reload();
                    }
                });
            }
        });
    }

};

var cartPreview = {
    init: function (){
        var _this = this;

        if(!$('.js-cart-popup').length){
            return false;
        }

        var preview = $('.js-cart-preview');

        preview.each(function(){
            var $this = $(this),
                popup = $this.find('.js-cart-popup');

            if(popup.length){
                $this.hover(function (){
                    var url = $this.data('url');

                    popup.html("");
                    $.get(url + '?ajax=1', function(html) {
                        var cartContentHtml = $(html).find('.js-cart-ajax');

                        if(cartContentHtml.length){
                            popup.html(html);
                        }
                    });
                });
            }
        });
    }
};

var productListUser = {
    init: function() {
        var _this = this;

        _this.compare();
        _this.favorites();
        _this.clear();
        _this.viewed();
    },
    viewed: function(){
        var _this = this,
            product = $('#product-cart');

        if(product.length && product.data('id')){
            _this.add('viewed_list', product.data('id'), globalThemeSettings.countViewed);
        }
    },
    compare: function(){
        var _this = this,
            wrapPreview;

        if($('.js-bar-fixed-bottom .js-preview-favorite').length){
            wrapPreview = $('.js-bar-fixed-bottom .js-preview-compare');
        }else if($('.js-bar-fixed-right .js-preview-compare').length){
            wrapPreview = $('.js-bar-fixed-right .js-preview-compare');
        }else if($('.js-header-fixed .js-preview-compare').length){
            wrapPreview = $('.js-header-fixed .js-preview-compare');
        }else{
            wrapPreview = $('.js-preview-compare').first();
        }

        var inCompare = productListUser.get('shop_compare'),
            url = '/compare/';

        if(inCompare && inCompare != 'null' && inCompare !== null){
            url =  '/compare/' + inCompare + '/';
        }

        var msgAddToCompare = "<a href='"+url+"'>"+globalThemeSettings.msgAddToCompare+"</a>";

        _this.list(
            'shop_compare',
            $(".js-compare-count"),
            wrapPreview,
            msgAddToCompare,
            '.js-add-to-compare',
            compareProduct.add,
            null
        );
    },
    favorites: function(){
        var _this = this,
            wrapPreview;

        if($('.js-bar-fixed-bottom .js-preview-favorite').length){
            wrapPreview = $('.js-bar-fixed-bottom .js-preview-favorite');
        }else if($('.js-bar-fixed-right .js-preview-favorite').length){
            wrapPreview = $('.js-bar-fixed-right .js-preview-favorite');
        }else if($('.js-header-fixed .js-preview-favorite').length){
            wrapPreview = $('.js-header-fixed .js-preview-favorite');
        }else{
            wrapPreview = $('.js-preview-favorite').first();
        }

        _this.list(
            'favorites_list',
            $(".js-favorite-count"),
            wrapPreview,
            globalThemeSettings.msgAddToFavorite,
            '.js-add-to-favorites',
            null,
            globalThemeSettings.countFavorites
        );
    },
    list: function (listName, wrapCount, wrapPreview, addMag, btn, callback, limit){
        var _this = this;

        $("body").on('click', btn, function (event) {
            event.preventDefault();
            var $this = $(this),
                countInList = 0,
                productId = $(this).data('product'),
                isAdd = !$this.hasClass('selected');

            $(btn + "[data-product='"+productId+"']").toggleClass('selected');
            if (isAdd) {
                countInList = _this.add(listName, productId, limit);
                _this.showAddedMsg(wrapPreview, addMag);
            } else {
                countInList = _this.remove(listName, productId);

            }
            wrapCount.html(countInList);
            if(countInList > 0){
                wrapCount.removeClass('empty');
            }else{
                wrapCount.addClass('empty');
            }
            if(callback){
                callback({that: $this, productId: productId, isAdded: isAdd});
            }
        });
    },
    add: function(listName, productId, limit){
        var _this = this, list = $.cookie(listName), listArr = [];

        if (list && list != "null" && list != "0" ) {
            list = list.replace(",null", "");
            list = list.replace(",0", "");
            var listArr = list.split(',');

            var i = $.inArray(productId + '', listArr);
            if (i != -1) {
                listArr.splice(i, 1);
            }
        }
        listArr.unshift(productId);

        if(limit){
            listArr.splice(limit);
        }

        _this.save(listArr, listName);

        return listArr.length;
    },
    remove: function(listName, productId){
        var _this = this, list = $.cookie(listName);

        if (list) {
            list = list.split(',');
        } else {
            list = [];
        }
        var i = $.inArray(productId + '', list);
        if (i != -1) {
            list.splice(i, 1);
        }

        _this.save(list, listName);

        return list.length;
    },
    get: function(listName){
        return $.cookie(listName);
    },
    save: function(list, listName){
        if (list.length > 0) {
            for(var i = 0; i < list.length; i ++){
                if (!parseInt(list[i])){
                    list.splice(i, 1);
                }
            }
        }
        if (list.length > 0) {
            $.cookie(listName, list.join(','), { expires: 90, path: '/'});
        } else {
            $.cookie(listName, null, {path: '/'});
        }
    },
    clear: function (){
        var _this = this, btn = $('.js-clear-user-list');

        btn.on("click", function (){
            var $this = $(this),
                list = [],
                listName = $this.data("list") + "_list";

            _this.save(list, listName);
            location.reload();

        });
    },
    showAddedMsg: function(listPreviewWrap, text){
        if(listPreviewWrap.is(':visible')){
            $('.js-preview-favorite, .js-preview-compare, .js-cart-preview').removeClass('active');
            listPreviewWrap.addClass('active');
            setTimeout(function(){
                listPreviewWrap.removeClass('active');
            }, 3000);
        }else{
            messages.notifySuccess(text, 0);
        }

    }
};

var compareProduct = {
    addToSidebar: function(params){
        var $this = params.that,
            productId = params.productId,
            isAdded = params.isAdded,
            productsFullWrap = $('.js-compare-products-full'),
            productsEmptyWrap = $('.js-compare-products-empty'),
            productsList = $('.js-compare-products-list'),
            template = $('.js-compare-template');

        if(!isAdded){
            var product = $('.js-compare-product[data-product="'+productId+'"]');
            product.remove();
        }else{
            if(productsList.length && $this.data('name') && template){
                var addedProduct = template.clone();
                addedProduct.removeClass('js-compare-template').addClass('js-compare-product');
                addedProduct.attr("data-product", productId);
                addedProduct.find('.js-add-to-compare').attr("data-product", productId).addClass('active');
                addedProduct.find('.js-compare-name').text($this.data('name'));
                addedProduct.find('.js-compare-name').attr("href", $this.data('url'));
                addedProduct.find('.js-compare-img').attr("href", $this.data('url'));
                if($this.data('img')){
                    addedProduct.find('.js-compare-img').html("<img src='"+$this.data('img')+"'>");
                }
                template.after(addedProduct);
                addedProduct.show();
            }
        }
        if($('.js-compare-product').length){
            productsEmptyWrap.hide();
            productsFullWrap.show();
        }else{
            productsEmptyWrap.show();
            productsFullWrap.hide();
        }
    },
    add: function (params){
        var _this = this,
            link = $('.js-link-compare'),
            inCompare = productListUser.get('shop_compare'),
            url = '/compare/';

        if(inCompare && inCompare != 'null' && inCompare !== null){
            url =  link.attr('href').replace(/compare\/.*$/, 'compare/' + inCompare + '/');
        }
        link.attr("href", url);
        compareProduct.addToSidebar(params);
    }
};

var cart = {
    init: function (){
        var _this = this;

        _this.addToCart();
        _this.quantityCart();
        _this.cartDialog();

        topMessageAddToCart.close();
    },
    cartDialog: function(){
        var _this = this;

        $('body').on( "click", ".js-product-card-dialog", function (){
            var $this = $(this),
                isReloadCart = $this.closest("[data-reload-cart='true']").length,
                form = $this.closest('.js-add-to-cart'),
                inputCount = form.find("input[name=quantity]"),
                quantity = null,
                href = $(this).data('href'),
                isOnlyOPtions = href.indexOf('select-options') !== -1;

            if(inputCount.length){
                quantity = parseInt(inputCount.val());
                if(quantity){
                    href = href + "&quantity=" + quantity;
                }
            }
            $.magnificPopup.open({
                items: {
                    src: href
                },
                type: 'ajax',
                callbacks: {
                    parseAjax: function(mfpResponse) {
                        if($(mfpResponse.data).find(".js-product-cart-quick-view").length){
                            mfpResponse.data = $(mfpResponse.data).find(".js-add-to-cart");

                            if(isOnlyOPtions){
                                $(mfpResponse.data).addClass("popup-content--dialog-options");
                                $(mfpResponse.data).find('.product-card__right').removeAttr('class');
                                $(mfpResponse.data).find('.product-card__popup').removeAttr('class');
                            }
                        }else{
                            if (typeof mfpResponse.data === 'string' || mfpResponse.data instanceof String){
                                var outer = "<div>" + mfpResponse.data + "</div>";
                                mfpResponse.data = $(outer).find(".js-add-to-cart");
                            }else{
                                mfpResponse.data = $(mfpResponse.data);
                            }
                        }
                    },
                    ajaxContentAdded: function() {
                        var inputStyler = this.content.find('input[type="checkbox"]:not(.js-checkbox-styler-input):not(.js-none-styler):not(.shop-sk-callback__checkbox), input[type="radio"]:not(.js-radio-styler-input):not(.buy1step-auth__variant):not(.js-none-styler), .js-select');
                        inputStyler.styler();

                        /* for cart page crossselling */
                        if(isReloadCart){
                            this.content.attr("data-reload-cart", true);
                        }

                        var productCard = this.content.find('#product-cart');
                        if(productCard.length && productCard.data('id')){
                            productListUser.viewed(productCard.data('id'));
                        }

                        if (typeof $.autobadgeFrontend !== 'undefined') {
                            $.autobadgeFrontend.reinit();
                        }

                        if(!is_touch_device()){
                            ProductCardGallery.productImageZoom(this.content.find(".js-product-gallery-main-el").first());
                        }
                        ProductCardGallery.swipeLargePhoto(this.content.find('.js-product-gallery-main'));
                        ProductCardGallery.productPreviewsCarousel(this.content.find('.js-gallery-previews-carousel'));
                    },
                    open: function() {

                        $.magnificPopup.instance._onFocusIn = function(e) {
                            if( $(e.target).closest( '#storequickorder' ) ) {
                                return true;
                            }
                            $.magnificPopup.proto._onFocusIn.call(this,e);
                        };


                    }
                }
            }, 0);
        });
    },
    addToCart: function (){
        var _this = this,
            form = $('.js-add-to-cart'),
            cartPreview = $('.js-cart-preview'),
            cartCount = $('.js-cart-preview-count'),
            cartTotal = $('.js-cart-preview-total');

        $('body').on('submit', '.js-add-to-cart', function (event){
            event.preventDefault();
            var $this = $(this),
                data = $this.serialize(),
                dataObject = $this.serializeObject(),
                action = $this.attr('action'),
                button = $this.find('.js-submit-form');

            button.addClass("loading");
            button.removeClass("added");
            $.post(action + '?html=1', data, function (response) {
                button.removeClass("loading");

                /* globalThemeSettings - init in index.html */
                if(globalThemeSettings.show_product_in_basket){
                    button.addClass('added');
                    if(typeof dataObject.product_id !== 'undefined'){
                        var addedProductAllButtons = $('input[name="product_id"][value="'+ dataObject.product_id +'"]').closest(".js-add-to-cart").find(".js-submit-form, .js-product-card-dialog");
                        addedProductAllButtons.addClass("added");
                    }
                }

                if (response.status == 'ok') {

                    /* update previews cart */
                    cartTotal.html(response.data.total);
                    cartCount.html(response.data.count);
                    cartCount.removeClass('empty');
                    cartPreview.removeClass('empty');

                    /* close dislog product */
                    var cartDialog = $('#cart-form-dialog');
                    if(cartDialog.length > 0){
                        $.magnificPopup.close();
                    }

                    /* action after add to cart */
                    if($this.data('after-action') == 'popup'){
                        _this.popupAddCart($this);
                    }else if($this.data('after-action') == 'top'){
                        topMessageAddToCart.show($this);
                    }else if($this.data('after-action') == 'move'){
                        _this.animationMoveToCart($this);
                        if(cartDialog.length > 0){
                            $.magnificPopup.close();
                        }
                        _this.showMsg();
                    }else if($this.data('after-action') == 'fixed'){
                        fixedCart.show();
                    }else{
                        _this.showMsg();
                    }

                    /* update sidebar cart */
                    sidebarCart.add($this);

                    /* for cart page crossselling */
                    if($this.closest("[data-reload-cart='true']").length){
                        location.reload();
                    }
                } else if (response.status == 'fail') {
                    button.removeClass("loading");

                    alert(response.errors);
                }
            }, "json");
        });
    },
    animationMoveToCart: function (form){
        var productBlock = form.closest('.js-product').find(".js-product-gallery-main"); /* карточка товара */
        if(productBlock.length == 0){
            productBlock = form.closest('.js-product-item');
        }
        if(productBlock.length){
            var productBlockCopy = $('<div></div>').append(productBlock.html());
            var cart_preview = $('.js-cart-preview[data-type="fixed"]');
            if(!cart_preview.length || !cart_preview.is(':visible')){
                cart_preview = $('.js-cart-preview[data-type="header"]');
            }
            productBlockCopy.css({
                'z-index': 100,
                top: productBlock.offset().top,
                left: productBlock.offset().left,
                width: productBlock.width()+'px',
                height: productBlock.height()+'px',
                position: 'absolute',
                overflow: 'hidden',
                background: "#FFF"
            }).insertAfter('#main-content').animate({
                top: cart_preview.offset().top,
                left: cart_preview.offset().left,
                width: 0,
                height: 0,
                opacity: 0.7
            }, 650, function() {
                productBlockCopy.remove();
            });
        }
    },
    popupAddCart: function (form){
        var popup = $('#popup-addcart'),
            productName = form.data("name"),
            price = form.data("price"),
            image = form.data("image"),
            quantity = 1,
            quantityFileld = form.find("input[name='quantity']");

        if(quantityFileld.length){
            quantity = quantityFileld.val()
        }

        popup.find(".js-popup-addcart-name").html(productName);
        popup.find(".js-popup-addcart-price").html(price);
        popup.find(".js-popup-addcart-count").html("(x" + quantity + ")");
        if(image){
            popup.find(".js-popup-addcart-image").html( "<img src='" + image + "' />");
        }else{
            popup.find(".js-popup-addcart-image").html("");
        }

        $.magnificPopup.open({
            items: {
                src: popup,
                type: 'inline'
            },
            callbacks: {
                afterClose: function (){
                    popup.find(".js-popup-addcart-name").html("");
                    popup.find(".js-popup-addcart-price").html("");
                    popup.find(".js-popup-addcart-count").html("");
                    popup.find(".js-popup-addcart-image").html("");
                }
            }

        });
        $('.js-close-popup-addcart').on("click", function (){
            $.magnificPopup.close();
        });
    },
    quantityCart: function (){
        $('body').on("click", ".js-qty-action", function(){
            var $this = $(this),
                wrapOut = $this.closest('.js-qty'),
                action = $this.data('type'),
                input = wrapOut.find('input'),
                currentQty = parseInt(input.val());

            if(action == "-"){
                if(currentQty > 1 ){
                    currentQty--;
                }else{
                    currentQty = 1;
                }
            }else{
                if(currentQty){
                    currentQty++;
                }else{
                    currentQty = 1;
                }
            }

            input.val(currentQty);
            input.change();
        });
    },
    showMsg: function (){
        var cartPreview;

        if($('.js-bar-fixed-bottom .js-cart-preview').length){
            cartPreview = $('.js-bar-fixed-bottom .js-cart-preview');
        }else if($('.js-bar-fixed-right .js-cart-preview').length){
            cartPreview = $('.js-bar-fixed-right .js-cart-preview');
        }else if($('.js-header-fixed .js-cart-preview').length && $('.js-header-fixed .js-cart-preview').is(":visible")){
            cartPreview = $('.js-header-fixed .js-cart-preview');
        }else if($('.js-header .js-cart-preview').length){
            cartPreview = $('.js-header .js-cart-preview');
        }else{
            cartPreview = $('.js-cart-preview').first();
        }

        $('.js-preview-compare, .js-preview-favorite, .js-cart-preview').removeClass('active');

        cartPreview.addClass('active');
        setTimeout(function(){cartPreview.removeClass('active');}, 2000);
    }
};

var topMessageAddToCart = {
    init: function (){
        var _this = this;

        _this.close();
    },
    show: function (form){
        var _this = this,
            popup = $('#top-msg-addcart'),
            productName = form.data("name"),
            price = form.data("price"),
            image = form.data("image"),
            quantity = 1,
            quantityFileld = form.find("input[name='quantity']");

        if(quantityFileld.length){
            quantity = quantityFileld.val()
        }

        popup.find(".js-top-msg-addcart-name").html(truncateText(productName, 40));
        popup.find(".js-top-msg-addcart-price").html(price);
        popup.find(".js-top-msg-addcart-count").html("(x" + quantity + ")");
        if(image){
            popup.find(".js-top-msg-addcart-image").html( "<img src='" + image + "' />");
        }else{
            popup.find(".js-top-msg-addcart-image").html("");
        }

        popup.slideDown(100);

        setTimeout(function (){
            _this.hide();
        }, 5000);
    },
    hide: function (){
        var _this = this,
            popup = $('#top-msg-addcart');

        if(popup.length){
            popup.slideUp(100);
            popup.find(".js-top-msg-addcart-name").html("");
            popup.find(".js-top-msg-addcart-price").html("");
            popup.find(".js-top-msg-addcart-count").html("");
            popup.find(".js-top-msg-addcart-image").html("");
        }
    },
    close: function (){
        var _this = this,
            btnClose = $('.js-top-msg-addcart-close');

        btnClose.on("click", function (){
            _this.hide()
        });
    }
};

var fixedCart = {
    init: function (){
        var _this = this;

        _this.close();
    },
    show: function (){
        var wrapFixedCart = $('.js-fixed-cart-outer');

        if(wrapFixedCart.length){
            var cartUrl = wrapFixedCart.data("cart-url") + "?fixed=1";

            wrapFixedCart.html("");
            $.get(cartUrl, function(html) {
                var cartContent = $("<div>"+html+"</div>");

                if(cartContent.find('.js-fixed-cart').length){
                    wrapFixedCart.html(html);
                }
            });
        }
    },
    close: function(){
        $('body').on("click", '.js-cart-fixed-close', function (){
            var wrapFixedCart = $('.js-fixed-cart-outer');

            wrapFixedCart.html("");
        });
    }
};

var sidebarCart = {
    add: function(addToCartForm){
        var sidebarCart = $('.js-sidebar-cart'),
            productAddId = addToCartForm.find('input[name="product_id"]').val(),
            productAddQuantity = parseInt(addToCartForm.find('input[name="quantity"]').val());
        if(!productAddQuantity) {
            productAddQuantity = 1;
        }

        if(sidebarCart.length){
            var sidebarCartProduct = sidebarCart.find('.js-sidebar-cart-item[data-product-id='+productAddId+']');

            if(sidebarCartProduct.length){
                var sidebarCartProductWrapQuantity = sidebarCartProduct.find('.js-sidebar-cart-quantity'),
                    sidebarCartProductQuantity = parseInt(sidebarCartProductWrapQuantity.text()) + productAddQuantity;

                sidebarCartProductWrapQuantity.text(sidebarCartProductQuantity);
            }else{
                var productAdded = sidebarCart.find('.js-sidebar-cart-template').clone(),
                    productTitle = addToCartForm.data('name'),
                    productLink = addToCartForm.data('link'),
                    productPrice = addToCartForm.data("price"),
                    productImage = addToCartForm.data("image");

                productAdded.find('.js-sidebar-cart-template-img').html('<a href="'+productLink+'"><img src="'+productImage+'"></a>');
                productAdded.find('.js-sidebar-cart-template-link').html('<a href="'+productLink+'">'+productTitle+'</a>');
                productAdded.find('.js-sidebar-cart-price').html(productPrice);
                productAdded.find('.js-sidebar-cart-quantity').html(productAddQuantity);
                productAdded.attr('data-product-id', productAddId);
                productAdded.removeClass('js-sidebar-cart-template').removeClass('display-none').addClass('js-sidebar-cart-item');

                $('.js-sidebar-cart-items').append(productAdded);
                sidebarCart.removeClass('hide');
            }
        }
    },
    clearItem: function (productId, cartProductId){
        var sidebarCart = $('.js-sidebar-cart');

        if(sidebarCart.length && (productId || cartProductId)){
            var product = $('.js-sidebar-cart-item[data-data-id='+cartProductId+']');
            if(product.length == 0){
                product = $('.js-sidebar-cart-item[data-product-id='+productId+']');
            }

            if(product.length){
                product.remove();
                if($('.js-sidebar-cart-item').length == 0){
                    sidebarCart.addClass('hide');
                }
            }
        }
    }
};

var sidebarMobileMenu = {
    init: function (){
        var _this = this;

        _this.openWrap();
        _this.openSubmenu();
    },
    openWrap: function (){
        var btn = $('.js-m-sidebar-menu-open');

        btn.on("click", function (){
            var wrap = $(this).closest('.js-m-sidebar-menu');

            wrap.toggleClass('open');
        });
    },
    openSubmenu: function (){
        var btn = $('.js-m-sidebar-submenu-open');

        btn.on("click", function (){
            var item = $(this).parent();

            item.toggleClass('open');
        });
    }
}

var sidebarCarousel = {
    init: function (){
        var _this = this;

        var carouselProducts = $('.js-carousel-products-sidebar');
        carouselProducts.each(function (){
            _this.carousel($(this));
        });
    },
    carousel: function (productsList){
        var carouselWrap = productsList.find('.js-carousel-items'),
            countVisible = parseInt(carouselWrap.data("count-visible")),
            isLazy = carouselWrap.data('image-lazy'),
            isRetina = carouselWrap.data('retina');

        carouselWrap.jCarouselLite({
            btnNext: productsList.find(".js-carousel-next"),
            btnPrev: productsList.find(".js-carousel-prev"),
            vertical: true,
            visible: countVisible ? countVisible : 3,
            afterEnd: function (item){
                if (isLazy){
                    var image = item.find('.js-product-preview-img');

                    if(image.length){
                        image.lazy({
                            onFinishedAll: function(element) {
                                if(isRetina){
                                    $(element).retina();
                                }
                            },
                        });
                    }

                }
            }
        });
    }
};

var openMap = {
    init: function (){
        var _this = this;

        $('.js-popup-map').on("click", function(){

            $.magnificPopup.open({
                disableOn: 700,
                type: 'iframe',
                mainClass: 'mfp-fade',
                removalDelay: 160,
                preloader: false,
                fixedContentPos: false,
                items: {
                    src: $(this).data('href'),
                    type: 'iframe'
                }
            }, 0);
        });
    }
};

var switchVersionSite = {
    init: function (){
        var _this = this;

        _this.switchVersion();
        _this.removeLink();
    },
    switchVersion: function (){
        $('.js-switch-version-link').on("click", function (event){
            event.preventDefault();

            if($.cookie("is_desktop_for_mobile")){
                $.cookie("is_desktop_for_mobile", "", {path: '/', expires: 5});
            }else{
                $.cookie("is_desktop_for_mobile", 1, {path: '/', expires: 5});
            }
            location.reload();
        })
    },
    removeLink: function (){
        $('.js-switch-version-remove').on("click", function (event){
            event.preventDefault();

            $.cookie("is_hide_link_version_site", 1, {path: '/', expires: 1});
            $('.js-switch-version').remove();
        })
    }
};

var demoTest = {
    init: function (){
        var _this = this;

        if(!$('#test-settings').length){
            return false;
        }

        $('#open-test-settings').on("click", function(event){
            event.preventDefault();

            $.magnificPopup.open({
                items: {
                    src: '#test-settings'
                },
                type: 'inline'
            });
        });

        $('#open-test-settings').one("click", function(event){
            event.preventDefault();

            _this.settings();
        });
    },
    settings: function (){
        var _this = this,
            settings = [
                "color_scheme",
                "button_type",
                "base_bg_color",
                "base_bg_font_color",
                "base_font_color",
                "ac_bg_color",
                "ac_bg_color",
                "ac_bg_color_font",
                "ac_font_color",
                "font",
                "header_fixed_desktop",
                "bar_fixed_bottom_desktop",
                "bar_fixed_right_desktop",
                "is_horizontal_menu",
                "horizontal_main_menu_brand_submenu_type",
                "vertical_main_menu_brand_submenu_type",
                "homepage_sidebar_left",
                "homepage_sidebar_right",
                "homepage_slider_over_content",
                "homepage_main_menu_left_of_slider",
                "categories_mainpage_design",
                "vertical_main_menu_brand_submenu_type",
                "horizontal_main_menu_brand_submenu_type",
                "product_gallery_previews",
                "product_card_type_main_content_desktop",
                "product_card_type_main_content_mobile",
                "product_tile_display_fastorder",
                "show_product_discount",
                "show_product_badge_saving",
                "cart_add_action_desktop"
            ];

        $('#test-settings .js-color').attr("type", "color");
        $('#test-settings .js-color').on("change", function (){
            $(this).data("empty", 0);
        });

        $('#test-settings .js-color-clear').on("click", function (){
            $("#"+$(this).data('id')).val("#ffffff").data("empty", 1);
        });

        $('#settings-form').submit(function(event){
            event.preventDefault();

            var $this = $(this),
                settingsForm = $this.serializeArray();

            _this.saveSettings(settingsForm, settings);
        });

        $('.js-select-setting-color').on("click", function (){
            var $this = $(this),
                value = $this.data("value"),
                input = $('.js-setting-color');

            input.val(value);
            $('.js-select-setting-color').removeClass("selected");
            $this.addClass("selected");
        });

        $('.js-clear-test-settings').on("click", function (){
            _this.clearTestSettings(settings);
        });
    },
    saveSettings: function (values, settings){
        settings.forEach(function(name){
            var setting = values.find(function (item){
                return item.name == name;
            });

            var input = $('#settings-form').find('input[name="' + name + '"]');

            if(input.attr("type")=='color' && input.data("empty") == 1){
                setting.value = null;
            }

            var date = new Date(), minutes = 30;
            date.setTime(date.getTime() + (minutes * 60 * 1000));
            $.cookie(name, setting.value, { expires: date, path: '/' });

            window.location.reload();
        });
    },
    clearTestSettings: function (settings){
        settings.forEach(function(name){
            $.removeCookie(name, { path: '/' });
        });
        window.location.reload();
    }
};

/*
 * Виджет социальные группы
 */
if (!window.SocialWidgets) {

    var SocialWidgets = (function ($) {

        'use strict';

        /**
         * Конструктор виджета "Социальные группы"
         *
         * @constructor SocialWidgets
         *
         * @param {Object} params параметры
         */
        var SocialWidgets = function (params) {
            this.init(params);
        };

        SocialWidgets.prototype = {
            _params: {
                container: ".js-social-widgets",
                timeAutoSwitch: 5000,
                autoSwitch: false
            },

            /**
             * Получение параметров
             *
             * @method getParams
             * @return {Array} Объект с настройками
             */
            getParams: function () {
                return this._params;
            },

            /**
             * Получение параметра
             *
             * @method getParam
             * @return {String} Объект с настройками
             */
            getParam: function (name) {
                return this._params[name];
            },
            /**
             * Установка параметра
             *
             * @method setParam
             *
             * @param {String} name Наименование параметров
             * @param {String} value Значение параметрра
             *
             * @return {String} Объект с настройками
             */
            setParam: function (name, value) {
                this._params[name] = value;
            },
            /**
             * Инициализируем плагин
             *
             * @method init
             *
             * @param {Object} params
             */
            init: function (params) {
                var that = this;

                that.autoTimer = null;

                that._params = $.extend({}, that._params, params);
                that._params.preload = "switch";

                that.initElements();

                if(typeof vivaSocialWidgetsData === "undefined") return false;

                that.data = vivaSocialWidgetsData;
                that.dataInst = vivaSocialWidgetsInst;

                if(!that.elements.container.size()) return false;

                that.active = Object.keys(that.elements.contentsArray)[0];
                that.autoSwitch = that.elements.container.data("auto");

                if(parseInt(that.elements.container.data("time"))){
                    that._params.timeAutoSwitch = parseInt(that.elements.container.data("time"));
                }
                if(that.elements.container.data("preload")){
                    that._params.preload = that.elements.container.data("preload");
                }
                that.initTabs();

                setTimeout(function(){
                    that.initAuto();
                }, 3000)
            },

            /**
             * Инициализируем элементы
             *
             * @method initElements
             *
             */
            initElements:  function () {
                var that = this,
                    elements = {};

                that.elements = elements;

                elements.container = $(that._params.container);
                if(!elements.container.size()) return false;

                elements.tabs = elements.container.find(".js-social-widgets-tab");
                elements.tabsArray = {};
                elements.tabs.each(function(){
                    elements.tabsArray[$(this).attr("data-tab")] = $(this);
                });
                elements.contents = elements.container.find(".js-social-widgets-content");
                elements.contentsArray = {};
                elements.contents.each(function(){
                    elements.contentsArray[$(this).attr("data-content")] = $(this);
                });
                that.elements = elements;
            },
            /**
             * Инициализируем табы переключения
             *
             * @method initTabs
             *
             */
            initTabs:  function () {

                var that = this,
                    elements = that.elements,
                    $container = elements.container;

                $container.hover(
                    function() {
                        that.pauseAuto();
                    }, function() {
                        that.initAuto();
                    }
                );
                for(var key in that.data){
                    if(typeof elements.contentsArray[key] === "undefined"){
                        continue;
                    }
                    if(key == "instagram"){
                        that.runInstagram();
                    }else{
                        elements.contentsArray[key].html(that.data[key]);
                    }
                    if(that._params.preload == "switch") break;
                }
                $container.find("[data-tab]").on("click", function(){
                    var $tab = $(this),
                        socialCurrent = elements.tabs.filter("._active").attr("data-tab"),
                        socialNew = $tab.attr("data-tab");

                    if(socialCurrent != socialNew){
                        that.stopAuto();
                        that.switch(socialNew);
                    }
                });
            },
            /**
             * Переключение соц.сети
             *
             * @method switch
             *
             * @param {String} social
             *
             */
            switch: function(social){
                var that = this,
                    elements = that.elements,
                    $tab = elements.tabsArray[social],
                    $content = elements.contentsArray[social];

                that.active = social;
                elements.tabs.removeClass("_active");
                $tab.addClass("_active");
                elements.contents.removeClass("_show");

                if(!$content.html()){
                    if(social == "instagram"){
                        that.runInstagram();
                    }else {
                        $content.append(that.data[social]);
                    }
                }
                $content.addClass("_show");
            },
            /**
             * Инициализируем автоматическое переключение
             *
             * @method initAuto
             *
             */
            initAuto: function(){
                var that = this,
                    elements = that.elements,
                    timeAuto = that.getParam("timeAutoSwitch");

                if(!that.autoSwitch) return false;

                that.autoTimer = setInterval(function(){
                    var activeSocial = that.active,
                        $nextElement = elements.tabsArray[activeSocial].next(".js-social-widgets-tab");

                    if(!$nextElement.size())
                        $nextElement = elements.tabs.first();

                    var socialNext = $nextElement.attr("data-tab");
                    that.active = socialNext;
                    that.switch(socialNext)
                }, timeAuto);

            },
            /**
             * Инициализируем паузу автоматического переключения
             *
             * @method pauseAuto
             *
             */
            pauseAuto: function(){
                var that = this;

                clearInterval(that.autoTimer);
            },
            /**
             * Инициализируем остановку автоматического переключения
             *
             * @method pauseAuto
             *
             */
            stopAuto: function(){
                var that = this;

                clearInterval(that.autoTimer);
                that.autoSwitch = false;
            },

            /**
             * Запуск виджета инстаграм
             *
             * @method runInstagram
             *
             */
            runInstagram: function() {
                var that = this,
                    id = "#social-widgets-content-instagram";

                $(id).html("<div id='list-instagram' class='list-instagram'></div>");

                if (typeof Instafeed !== "undefined" && that.dataInst){
                    var feed = new Instafeed({
                        get: 'user',
                        userId: that.dataInst.userId,
                        target: 'list-instagram',
                        clientId: that.dataInst.clientId,
                        accessToken: that.dataInst.accessToken,
                        limit: that.dataInst.limit,
                        template: '<div class="list-instagram__item"><a class="list-instagram__link" href="{{link}}" target="_blank"><img class="list-instagram__img" src="{{image}}" alt="{{caption}}" /></a></div>'
                    });
                    feed.run();
                }
            }
        };
        return SocialWidgets;
    })(jQuery);
}

var videoPopup = {
    init: function(){
        $("body").on("click", '.js-video-popup', function(e){
            e.preventDefault();
            var href = $(this).data("href");

            if(href){
                $.magnificPopup.open({
                    items: {
                        src: href
                    },
                    type: 'iframe',
                    mainClass: 'mfp-fade',
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
    },
};

/*
 * Информационное сообщение
 */
var infoMessage = {
    init: function (container) {
        var _this = this;

        if(!container.length){
            return false;
        }

        if(_this.checkOpen(container)){
            _this.onClose(container);
        }
    },
    checkOpen: function(container){
        var _this = this,
            id = container.data('id');

        if(!$.cookie("info_massage_close_" + id)){
            return true;
        }

        return false;
    },
    runOpen: function(container){
        var _this = this;

        container.show();
    },
    onClose: function(container){
        var _this = this,
            id = container.data('id'),
            close = container.find('.js-info-message-close');

        close.on("click", function(){
            container.detach();
            $.cookie("info_massage_close_" + id, 1, {path: '/', expires: 365});
        });
    }
};

/*
 * Виджет акция в popup
 */
var popupAdvert = {
    init: function (container){
        var _this = this;

        if(!container.length){
            return false;
        }

        if(_this.isNotTabu() || _this.checkOpen(container)){
            _this.runLogic(container);
        }
    },
    checkOpen: function(container){
        var _this = this,
            id = container.data('id');

        if(!$.cookie("popup_advert_close_" + id)){
            return true;
        }

        return false;
    },
    isNotTabu: function(){
        var _this = this,
            is_tabu = $.cookie("popup_advert_tabu");

        if(typeof is_tabu === "undefined" && !is_tabu){
            return true;
        }

        return false
    },
    runLogic: function(container){
        var _this = this,
            time = parseInt(container.data("time")),
            tabu = parseInt(container.data("tabu")),
            id = container.data('id');

        if(!time){
            time = 10;
        }
        if(!tabu){
            tabu = 10;
        }
        setTimeout(function(){
            $.magnificPopup.open({
                items: {
                    src: container,
                    type: 'inline'
                },
                callbacks: {
                    open: function() {
                        $.cookie("popup_advert_tabu", 1, {path: '/', expires: tabu});
                        $.cookie("widget_popup_advert_close_" + id, 1, {path: '/', expires: 365});
                    }
                }
            });

        }, time * 1000);
    }
};

var productsPreviewList = {
    init: function () {
        var _this = this,
            productList = $('.js-preview-products');

        productList.each(function(){
            _this.images($(this));
        });
    },
    images: function (productList){
        var _this = this,
            isRetina = productList.data('retina'),
            isLazy = productList.data('image-lazy'),
            productImage = productList.find(".js-product-preview-img"),
            otherImageLazy = productList.find('.js-image-lazy');

        if(isLazy){
            productImage.lazy({
                afterLoad: function(element) {
                    if(isRetina){
                        element.retina({force_original_dimensions: false});
                    }
                    //new productTileGallery();
                },
            });
            if(otherImageLazy.length){
                otherImageLazy.lazy({bind: "event"});
            }
        }else if(isRetina){
            productImage.retina({force_original_dimensions: false});
        }
    },
};

/*
 * Плиточная галерея
 */
if (!window.productTileGallery) {

    productTileGallery = (function ($) {

        var productTileGallery = function (params) {
            if(!is_touch_device()) {
                this.init(params);
            }
        };

        productTileGallery.prototype = {
            _config: {
                images: {},
                heightFixed: true
            },
            init: function (params) {
                var that = this;

                if($("body").hasClass("touch") ){
                    return false;
                }
                that.params = $.extend({}, that._config, params);
                that.runGallery();
            },
            runGallery: function() {
                var that = this;
                var elements = $('.js-tile-gallery');
                elements.each(function (){
                    var element = $(this),
                        block = element.find(".js-tile-gallery-block"),
                        image = element.find(".js-product-preview-img"),
                        length = element.find('.js-tile-gallery-item').length;

                    if(!image.data("src")){
                        image.attr("data-src", image.attr("src"))
                    }

                    var src_default = image.data("src");

                    if(!element.size() || !block.size() || !image.size() || length < 2 || element.hasClass("_tile-active")){
                        return true;
                    }

                    element.addClass("_tile-active");
                    block.removeAttr("style");
                    block.removeClass("fixed");
                    element.find(".js-tile-gallery-item").on("mouseenter", function(){
                        block.addClass("_tile_hover");
                        if(that.params.heightFixed){
                            block.css("height", block.height() + "px");
                            block.css("line-height", block.height() + "px");
                            block.addClass("fixed");
                        }

                        var src = $(this).data("img");
                        $('<img>').attr('src', src).load(function () {
                            if(block.hasClass("_tile_hover")){
                                image.attr("src", src);
                                image.retina();
                            }
                        });
                    });

                    block.on("mouseleave",function(){
                        image.attr("src", src_default);
                        image.retina();
                        block.removeClass("_tile_hover");
                    });
                });
            }
        };
        return productTileGallery;
    })(jQuery);
};

var productsCarousel = {
    carouselProductsWrap: $('.js-carousel-products'),
    init: function (){
        var _this = this;

        if(!_this.carouselProductsWrap.length){
            return false;
        }

        _this.prepareProductListCarousels();
        _this.carouselInteraction();

        $(window).one('resize', _this.carouselsInit);
    },
    carouselsInit: function (){
        var _this = this;

        productsCarousel.carouselProductsWrap.each(function (){
            var $this = $(this);

            productsCarousel.carousel($this);
        });
    },
    prepareProductListCarousels: function (){
        var _this = this;

        _this.carouselProductsWrap.each(function (){
            var carousel = $(this).find('.js-products-list'),
                carouselWidth = carousel.outerWidth(),
                itemCarousel = $(this).find(".js-product-item"),
                countItems = itemCarousel.length,
                widthItem = itemCarousel.first().outerWidth(true),
                widthAllItems = widthItem * countItems,
                isButtons = widthAllItems > carouselWidth + 20;

            if(isButtons){
                var wrapButtons = $(this).find(".js-carousel-direction"),
                    classDisabled = $(this).data("loop") ? "" : " disabled";

                wrapButtons.append('<div data-index="prev" class="js-slider-init-interaction owl-prev' + classDisabled + '"><span class="carousel-prev"></span></div>');
                wrapButtons.append('<div data-index="next" class="js-slider-init-interaction owl-next"><span class="carousel-next"></span></div>');
            }

            var carouselOffsetRight = carousel.offset().left + carousel.outerWidth();
            itemCarousel.slice(0,5).each(function (){
                var itemOffsetLeft = $(this).offset().left;

                if (itemOffsetLeft < carouselOffsetRight) {
                    $(this).find(".owl-lazy").Lazy({
                        afterLoad: function(element) {
                            element.removeClass("owl-lazy");
                            if ($.Retina) {
                                element.retina();
                            }
                        },
                    });
                }
            });
        });
    },
    carouselInteraction: function (){
        var _this = this,
            productCarouselNav = $('.js-slider-init-interaction');

        productCarouselNav.on("click", function (){
            var carousel = $(this).closest('.js-carousel-products');

            _this.carousel(carousel, $(this).data("index"));
        });

        if(is_touch_device()){
            $('.js-products-list').each(function (){
                var $productList = $(this);

                $productList.swipe( {
                    allowPageScroll:"auto",
                    threshold: 20,
                    swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                        var $productListCarousel = $(this).closest(".js-carousel-products");

                        if(direction == 'left'){
                            _this.carousel($productListCarousel, "next");
                        }
                        else if(direction == 'right') {
                            _this.carousel($productListCarousel, "prev");
                        }
                    }
                });
            });
        }
    },
    carousel: function (productsList, goToSlide){
        if(productsList.hasClass("carousel-init")){
            return false;
        }
        var productList = productsList.find('.js-products-list'),
            navigation = productsList.find('.js-carousel-direction'),
            isLoop = false,
            contentCountCols = contentCols.getCount(productList.closest('.js-content-cols')),
            isMouseSwipe = productsList.data("swipe"),
            isMobileMiniTile = productsList.data("tile-mini"),
            isMobileMiniLarge = productsList.data("tile-large"),
            countProductsInMobile = 1,
            countPrductsSubtract = 0;

        if(isMobileMiniTile){
            countProductsInMobile = 2;
        }

        if(isMobileMiniLarge){
            countPrductsSubtract = 1
        }

        var isSettingLoop = productsList.data('loop'),
            countItems = productsList.find('.js-product-item').length,
            widthOneItem = parseInt(productList.find('.js-product-item').first().outerWidth()) + 11;
        if(isSettingLoop && productList.outerWidth() < countItems * widthOneItem){
            isLoop = true;
        }

        var responsive = { 0: { items: countProductsInMobile }, 480: { items: 2 }, 750: { items: 3 }, 1130: { items: 4-countPrductsSubtract }, 1400: { items: 5-countPrductsSubtract } };
        if(contentCountCols == 2 && !globalThemeSettings.isMobile){
            responsive = { 0: { items: countProductsInMobile }, 480: { items: 2 }, 750: { items: 3 }, 1400: { items: 4-countPrductsSubtract } };
        }else if (contentCountCols == 3 && !globalThemeSettings.isMobile){
            responsive = { 0: { items: countProductsInMobile }, 480: { items: 2 }, 750: { items: 3 } };
        }

        productList.addClass("owl-carousel").addClass("owl-theme");
        productList.owlCarousel({
            loop: isLoop,
            margin: 10,
            nav: true,
            dots: false,
            mouseDrag: isMouseSwipe,
            navText: ['<span class="carousel-prev"></span>','<span class="carousel-next"></span>'],
            navElement: "div",
            navContainer: navigation,
            responsive: responsive,
            lazyLoad: true,
            lazyLoadEager: 1,
            onInitialize: function (event){
                productsList.find(".js-slider-init-interaction").remove();
            },
            onInitialized: function (event){
                productsList.addClass("carousel-init");
            },
            onDragged: function(event){
                if (typeof $.autobadgeFrontend !== 'undefined') {
                    $.autobadgeFrontend.reinit();
                }
            },
            onTranslated: function(event){
                if (typeof $.autobadgeFrontend !== 'undefined') {
                    $.autobadgeFrontend.reinit();
                }
            },
            onLoadedLazy: function(event){
                var wrap = $(event.currentTarget);
                if (wrap.length && $.Retina) {
                    wrap.find(".owl-item.active .owl-lazy").retina({force_original_dimensions: false});
                }
            },
        });
        if(goToSlide){
            if(goToSlide == "prev") {
                productList.trigger("prev.owl.carousel");
            }else if(goToSlide == "next"){
                productList.trigger("next.owl.carousel");
            }
        }
    }
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

var categoriesImages = {
    init: function (){
        var _this = this;

        _this.categoriesList();
    },
    categoriesList: function() {
        var _this = this,
            categories = $('.js-categories-list');

        categories.each(function (){
            var $this = $(this),
                isRetina = $this.data('retina'),
                isLazy = $this.data('lazy'),
                images = $this.find('.js-categories-item-image');

            if(isLazy){
                _this.lazyImages(images, isRetina);
            }else if(isRetina){
                _this.retinaImages(images);
            }
        });
    },
    lazyImages: function (images, isRetina){
        var _this = this;

        images.lazy({
            onFinishedAll: function(element) {
                if(isRetina){
                    _this.retinaImages(images);
                }
            },
        });
    },
    retinaImages: function (images){
        images.each(function(){
            var $this = $(this),
                imgUrl = $this.attr('src');

            if(imgUrl.indexOf("?") >= 0){
                var fileExtensionWithGet = imgUrl.substring(imgUrl.lastIndexOf('.') + 1),
                    imgUrl2x = imgUrl.replace("." + fileExtensionWithGet, "@2x." + fileExtensionWithGet);

                $this.attr("data-at2x", imgUrl2x);
            }
        }).promise().done(function(){
            images.retina();
        });
    }
};

var customGalleryPopup = {
    init: function (){
        var _this = this,
            galleryImage = $('.js-custom-gallery');

        galleryImage.on("click", function(e){
            e.preventDefault();
            var images = [],
                current = $(this),
                currentGroup = $(this).data('group'),
                position = 0,
                galleryImages = [];

                if(currentGroup){
                    galleryImages = $('.js-custom-gallery[data-group="'+currentGroup+'"]');
                }

                if(galleryImages.length > 1){
                    galleryImages.each(function (index) {
                        var href = $(this).attr('href'),
                            title = $(this).attr('title');

                        if (href){
                            if(current.attr('href') == href){
                                position = index;
                            }
                            images.push({href: href, title: title});
                        }
                    });
                    _this.openGallery(images, position);
                }else{
                    var href = current.attr('href'),
                        title = current.attr('title');

                    if(href){
                        images.push({href: href, title: title});
                        _this.openGallery(images);
                    }
                }
        });
    },
    openGallery: function (images, position){
        $(document).on("click", 'img, #swipebox-bottom-bar, .js-swipebox-thumbs-el', function (event){
            event.stopPropagation();
        });

        $(document).on("scroll", closeSwipe);
        $(document).on("click", '#swipebox-overlay', closeSwipe);

        function closeSwipe() {
            var $closeButton = $("#swipebox-close");
            if ($closeButton.length) {
                $closeButton.trigger("click");
            }
        }

        $.swipebox(images, {
            useSVG : false,
            hideBarsDelay: false,
            initialIndexOnArray: position,
            afterOpen: function() {
                $('#swipebox-overlay').addClass('opacity-black');
                $('#swipebox-bottom-bar').addClass("swipebox-bottom-bar--pos-center");
                $('#swipebox-arrows').addClass("swipebox-arrows--pos-center");
            }
        });
    }
};

var contentPopup = {
    init: function (){
        var _this = this;

        _this.loadPage();
        _this.loadInline();
    },
    loadPage: function (){
        var _this = this;

        $('body').on("click", '.js-page-popup', function (event){
            event.preventDefault();

            var $this = $(this),
                href = $this.attr('href');

            if(!href){
                href = $this.data('href');
            }

            $.magnificPopup.close();
            $('body').prepend("<div class='js-loading-bg mfp-bg mfp-ready'><div class='mfp-preloader'></div></div>");

            $.get(href + "?popup=1", function(data) {
                $('.js-loading-bg').remove();
                var content = false,
                    data = $("<div>").append(data);

                if($(data)) {
                    if ($(data).find('.js-page-popup-content').length) {
                        content = $(data).find('.js-page-popup-content');
                    }
                    if(content){
                        _this.openModal(content, 'page');
                    }else{
                        location.href = href;
                    }
                }
            });
        });
    },
    loadInline: function (){
        var _this = this;

        $('body').on("click", '.js-content-popup', function (event){
            event.preventDefault();

            var id = $(this).data("href");

            if(id){
                var content = $("#" + id).clone();
                if(content.length){
                    content.removeClass("display-none");
                    _this.openModal(content, "custom");
                }
            }
        });
    },
    openModal: function (content, type){
        var addClass = null
        if(type){
            addClass = " popup-content--" + type;
        }
        $.magnificPopup.open({
            items: {
                src: "<div class='popup-content" + addClass + "'>" + content.outerHTML() + "</div>"
            },
            type: 'inline'
        }, 0);

    },
};

var messages = {
    notifySuccess: function(text, offset){
        if(!text) text = 'Sent!';
        $.notify({
            message: text,
            icon: 'fal fa-check'
        },{
            delay: 5000,
            type: 'success',
            offset: offset,
            placement: {
                align: "right",
                from: 'bottom'
            },
            template: '<div data-notify="container" class="alert alert-{0} alert-add-product bs-bg" role="alert"><button type="button" aria-hidden="true" class="close" data-notify="dismiss">&times;</button><span data-notify="icon"></span> <span data-notify="title">{1}</span> <span data-notify="message">{2}</span><div class="progress" data-notify="progressbar"><div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div><a href="{3}" target="{4}" data-notify="url"></a></div>'
        });
    },
    notifyRemoveElement: function(text){
        if(!text) text = 'Deleted!';
        $.notify({
            message: text
        },{
            delay: 5000,
            offset: 10,
            placement: {
                align: "right",
                from: 'bottom'
            }
        });
    },
    notifyDanger: function(text){
        if(!text) text = 'An error has occurred!';
        $.notify({
            message: text,
            icon: 'fa fa-exclamation-circle'
        },{
            delay: 5000,
            type: 'danger',
            placement: {
                align: "right",
                from: 'bottom'
            }
        });
    }
};

var displayFontAwesome = {
    init: function (){
        var isHiddenIcons = $('body').hasClass('icons-hidden');

        if(isHiddenIcons){
            if ($.isFunction(window.fontSpy)) {
                fontSpy("Font Awesome\\ 5 Brands", {
                    glyphs: '\ue81a\ue82d\ue823',
                    success: function() {
                        $('body').removeClass("icons-hidden");
                    },
                    failure: function() {
                        $('body').removeClass("icons-hidden");
                    }
                });
            }else{
                $('body').removeClass("icons-hidden");
            }
        }
    }
};

var responsiveMenu = {
    init: function (){
        var _this = this;

        _this.responsive();
        window.addEventListener("resize", responsiveMenu.responsive);
    },
    responsive: function(){
        var _this = this;

        jQuery.each($(".js-resp-nav"), function(){
            responsiveMenu.responsived($(".js-resp-nav"), '.js-resp-nav-el', '.js-header-nav-sub');
        });
        responsiveMenu.categoriesResponsived($('.js-h-categories'), '.js-h-categories-item', '.js-categories-sub', $('.js-categories-h-else-items'));
        responsiveMenu.responsived($(".js-header-menu"), '.js-header-menu-item', '.js-header-submenu');
        responsiveMenu.responsived($('.js-category-filter-list'), '.js-category-filter-el', '.js-category-filter-subwrap');
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
            if(classSubElement){
                ItemElseSubwrapItems.removeClass(classSubElement);
            }
            if(classElement){
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
        menu.removeClass("responsived-before-init");
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
        menu.removeClass("responsived-before-init");
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

function Product(form, options) {
    this.form = $(form);
    this.add2cart = this.form.find(".add2cart");
    this.skFastButton = this.form.find(".js-sk-button-fastorder");
    this.fastButton = this.form.find(".js-button-fastorder");
    this.discount = this.form.closest('.js-product').find(".js-product-discount");
    this.savedWrap = this.form.closest('.js-product').find(".js-product-saving");
    this.button = this.add2cart.find("input[type=submit], .js-submit-form");
    this.isSkuUrl = this.form.data("sku-url");

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
            ProductCardGallery.selectLargePhoto($(this).closest('.js-product').find("#product-image-" + image_id));
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
        if (self.isSkuUrl){
            self.updateURLSku(sku_id);
        }
        self.updateFeaturesList(sku_id);
    });

    if($('.skus input[type=radio]').length) {
        var $initial_cb = this.form.find(".skus input[type=radio]:checked:not(:disabled)");
        if (!$initial_cb.length) {
            var first_sku = this.form.find(".skus input[type=radio]:not(:disabled):first");
            if(first_sku.length){
                $initial_cb = this.form.find(".skus input[type=radio]:not(:disabled):first").click().prop('checked', true);
                this.form.find(".js-radio-styler").removeClass("checked");
                first_sku.closest('.js-radio-styler').addClass("checked");
            }
        }
    }else if($('.skus option').length){
        var $initial_cb = this.form.find(".skus option:selected:not(:disabled)");
        if (!$initial_cb.length) {
            $initial_cb = this.form.find(".skus option:not(:disabled):first").click().prop('selected', true);
        }
    }

    if(typeof $initial_cb !== 'undefined' &&  $initial_cb.length && $initial_cb.data('image-id')) {
        $initial_cb.change();

        if ($initial_cb.length && $initial_cb.data('image-id') && $("#product-image-" + $initial_cb.data("image-id")).length) {
            ProductCardGallery.selectLargePhoto($(this).closest('.js-product').find("#product-image-" + $initial_cb.data("image-id")));
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
                ProductCardGallery.selectLargePhoto($(this).closest('.js-product').find("#product-image-" + sku.image_id));
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
            if (self.isSkuUrl){
                self.updateURLSku(sku.id);
            }
            self.updateFeaturesList(sku.id);

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

    if (!this.form.find('.skus input[type="radio"]:checked').length) {
        this.form.find('.skus input[type="radio"]:enabled:first').attr('checked', 'checked');
    }
    if (!this.form.find(".skus option:selected").length) {
        this.form.find(".skus option:enabled:first").attr('selected', 'selected');
    }

    self.showAllSkus();
    self.showAllStocks();
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
    var stock_wrap = this.form.find(".sku-" + sku_id + "-stock");
    stock_wrap.show();

    var cart_title = this.button.data('cart-title'),
        cart_preload_title = this.button.data('cart-preorder-title');

    if(cart_title && cart_preload_title){
        var html_icon = null;
        if(this.button.find('.js-icon').length){
            var html_icon = this.button.find('.js-icon').outerHTML();
        }

        if(stock_wrap.find('.js-stock-preorder').length){
            this.button.html(html_icon + cart_preload_title);
        }else{
            this.button.html(html_icon + cart_title);
        }
    }

    for (var service_id in this.services[sku_id]) {
        var v = this.services[sku_id][service_id];

        if (v === false) {
            this.form.find(".service-" + service_id).hide().find('input,select').attr('disabled', 'disabled').removeAttr('checked');
            this.form.find(".service-" + service_id).find(".js-checkbox-styler, label").addClass("disabled");
        } else {
            this.form.find(".service-" + service_id).show().find('input').removeAttr('disabled');
            this.form.find(".service-" + service_id).find(".js-checkbox-styler, label").removeClass("disabled");

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
        var input_checked = this.form.find('.skus input[type="radio"]:checked, .skus option:selected');
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
    self.updateSaved(price,compare_price);
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

Product.prototype.updateSaved = function (price, compare_price) {
    if (this.savedWrap.length){
        this.savedWrap.addClass('display-none');
        if(compare_price > price && price > 0){
            var saved = price - compare_price;

            this.savedWrap.html(this.currencyFormat(saved)).removeClass('display-none');
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

Product.prototype.showAllStocks = function () {
    $(".js-product-stocks-show").on("click", function (){
        var $this = $(this), outerWrap = $this.closest(".js-product-stocks");

        outerWrap.find(".js-product-stocks-item").toggleClass("hide");
        $this.toggleClass("open");
    });
};

Product.prototype.selectColor = function (){
    var self = this, colorOption = $('.js-product-color');

    colorOption.on("click", function (){
        self.writeTextColor($(this));
    });

    var currentColorOption = $('.js-product-color.selected');
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

Product.prototype.updateURLSku = function(sku_id) {
    var key_name = "sku";
    var search_object = stringToObject(window.location.search.substring(1));

    if (sku_id) {
        search_object[key_name] = sku_id;
    } else {
        delete search_object[key_name];
    }

    var search_string = objectToString(search_object);
    var new_URL = location.origin + location.pathname + search_string + location.hash;

    if (typeof history.replaceState === "function") {
        history.replaceState(null, document.title, new_URL);
    }

    function stringToObject(string) {
        var result = {};

        string = string.split("&");

        $.each(string, function(i, value) {
            if (value) {
                var pair = value.split("=");
                result[ decodeURIComponent( pair[0] ) ] = decodeURIComponent( pair[1] ? pair[1] : "" );
            }
        });

        return result;
    }

    function objectToString(object) {
        var result = "",
            array = [];

        $.each(object, function(key, value) {
            array.push(encodeURIComponent(key) + "=" + encodeURIComponent(value));
        });

        if (array.length) {
            result = "?" + array.join("&");
        }

        return result;
    }
};

Product.prototype.updateFeaturesList = function(sku_id) {
    var featuresWrap = $('.js-product-features');

    if(typeof this.sku_features != 'undefined' && typeof this.product_skus_features[sku_id] != 'undefined' && featuresWrap.length){
        var featuresList = this.sku_features,
            productFeaturesList = this.product_skus_features[sku_id],
            featuresCount = parseInt(this.short_features_count),
            featuresAliasesList = this.short_features_codes ? this.short_features_codes.split(',') : [];

        if(typeof this.schema_itemprops != 'undefined'){
            var schema_itemprops = this.schema_itemprops;
        }

        featuresWrap.each(function (){
            var featuresTableOne = $(this).find('table'),
                isShortFeatures = $(this).data("short"),
                isSchemaOrg = $(this).data('schema-org');

            featuresTableOne.html("");
            var indexIteration = 1, htmlTableTrFeature;

            for(var f_code in productFeaturesList) {
                var f_value = productFeaturesList[f_code],
                    isDivider = (typeof featuresList[f_code].type != 'undefined' && featuresList[f_code].type == 'divider');

                if(typeof featuresList[f_code] == 'undefined' && typeof featuresList[f_code + ".0"] != 'undefined'){
                    featuresList[f_code] = featuresList[f_code + ".0"];
                    featuresList[f_code].name = featuresList[f_code + ".0"].name;
                }
                if(typeof featuresList[f_code].type != 'undefined'){
                    if(isShortFeatures && featuresCount && indexIteration > featuresCount){
                        break;
                    }
                    if(isShortFeatures && featuresAliasesList.length && $.inArray(f_code, featuresAliasesList) === -1){
                        continue;
                    }
                    if(isShortFeatures && isDivider){
                        continue;
                    }
                    var itemFeaturesClass = "product_features-item";
                    if(isDivider){
                        itemFeaturesClass += " " +"divider";
                    }
                    htmlTableTrFeature = '<tr class="'+ itemFeaturesClass +'">';
                    htmlTableTrFeature += "<td class=\"product_features-title\">";
                    htmlTableTrFeature += "<span>" + featuresList[f_code].name + "</span>";
                    htmlTableTrFeature += "</td>";

                    var itemPropAttr = "", itemprop = null;
                    if(isSchemaOrg && typeof schema_itemprops != 'undefined'){

                        $.each(schema_itemprops, function(i, v) {
                            if(i == f_code){
                                itemprop = v;
                            }
                        });

                        if(itemprop !== null){
                            itemPropAttr = ' itemprop="'+ itemprop +'"';
                        }

                        if(itemprop !== null && itemprop == 'brand'){
                            itemPropAttr += ' itemscope itemtype="https://schema.org/Brand"';
                            f_value = '<span itemprop="name">'+f_value+'</span>';
                        }

                    }
                    htmlTableTrFeature += '<td class="product_features-value"'+ itemPropAttr +'>';
                    if(!isDivider){
                        if(isShortFeatures && featuresList[f_code].type == 'color'){
                            htmlTableTrFeature += "<span class=\"product_features__colors-short\">";
                        }
                        htmlTableTrFeature += f_value;
                        if(isShortFeatures && featuresList[f_code].type == 'color'){
                            htmlTableTrFeature += "</span>";
                        }
                    }
                    htmlTableTrFeature += "</td>";
                    htmlTableTrFeature += "</tr>";
                    featuresTableOne.append(htmlTableTrFeature);

                    indexIteration++;
                }
            }

        })
    }
};

var ProductCardGallery = {
    init: function(){
        var _this = this;

        _this.productPreviewsCarousel();
        _this.selectedPreviewImage();
        _this.popup();
        _this.popupVideo();
        _this.swipeLargePhoto($('.js-product-gallery-main'));

        if(!is_touch_device()){
            _this.productImageZoom($(".js-product-gallery-main-el").first());
        }

        var firstImageMainGallery = $('.js-product-gallery-main-el').first().find('img');
        if(firstImageMainGallery.length && $.Retina) {
            firstImageMainGallery.retina();
        }
    },
    productMainImageInitCarousel: function(mainGallery, goToSlide){
        var _this = this,
            position = 0;

        if(mainGallery.length && !mainGallery.hasClass('owl-loaded')){
            var currentPreview = mainGallery.closest('.js-product').find('.js-gallery-preview.selected');
            if(currentPreview.length){
                if(currentPreview.data("position") != "0"){
                    position = parseInt(currentPreview.data("position"));
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
                        _this.productImageZoom(mainGallery.find(".js-product-gallery-main-el"));
                    }
                    _this.displayImageTitle(mainGallery);
                },
                onChanged: function (event){
                    var gallery = $(event.currentTarget),
                        currentItemIndex = event.item.index,
                        currentItem = gallery.find('.js-product-gallery-main-el[data-position="'+currentItemIndex+'"]'),
                        currentItemId = currentItem.data("id"),
                        is_video = (currentItem.data("id") == "video");

                    if(is_video && !currentItem.find('iframe').length){
                        var videoUrl = currentItem.data("video-url"),
                            videoWidth = currentItem.data("video-width"),
                            videoHeight = currentItem.data("video-height");

                        currentItem.html('<iframe src="'+videoUrl+'" width="'+videoWidth+'" height="'+videoHeight+'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>');
                    }

                    if(currentItemId){
                        var previews = gallery.closest('.js-product').find('.js-gallery-preview');

                        previews.removeClass("selected");
                        var currentPreview = previews.filter("[data-id='" + currentItemId + "']");
                        currentPreview.addClass("selected");
                    }
                },
                onDragged: function(event){
                    var gallery = $(event.currentTarget);

                    if(gallery.length){
                        var previews = gallery.closest('.js-product').find('.js-gallery-preview'),
                            image = $(gallery).find(".owl-item.active .js-product-gallery-main-el"),
                            image_id = image.data("id");

                        if(image_id){
                            previews.removeClass("selected");
                            var currentPreview = previews.filter("[data-id='" + image_id + "']");
                            currentPreview.addClass("selected");
                        }
                        _this.displayImageTitle(gallery);
                        _this.productPreviewsCarouselGoTo(currentPreview.data("position"));
                    }
                },
            });

            if(goToSlide){
                if(goToSlide == "prev") {
                    mainGallery.trigger("next.owl.carousel");
                }else if(goToSlide == "next"){
                    mainGallery.trigger("prev.owl.carousel");
                }
            }
        }
    },
    productPreviewsCarousel: function (previewsCarousel){
        if(!previewsCarousel){
            previewsCarousel = $('.js-gallery-previews-carousel');
        }

        var contentCountCols = contentCols.getCount(previewsCarousel.closest('.js-content-cols')),
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

        if(previewsCarousel.length && position){
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
        var _this = this;

        $('body').on("click", ".js-product-image-popup, .js-gallery-preview-else", function (e) {
            e.preventDefault();

            var mainGallery = $(this).closest('.js-product').find(".js-product-gallery-main");
            if (mainGallery.data('photoswipe')){
                _this.popupPhotoswipe($(this), mainGallery);
            }else{
                _this.popupSwipebox($(this), mainGallery);
            }
        });
    },
    popupPhotoswipe: function(preview){
        var mainGalleryImages =  preview.closest('.js-product').find(".js-product-image-popup"),
            mainGallery = mainGalleryImages.closest(".js-product-gallery-main");

        $('body').on("click", ".pswp button", function (event){
            event.preventDefault();
        });

        var previews = preview.closest('.js-product').find('.js-gallery-preview'),
            mainPhoto = preview.closest('.js-product').find('.js-product-gallery-main-el'),
            pswpElement = document.querySelectorAll('.pswp')[0],
            position = preview.data('position'),
            items = [];

        if(mainGallery.find('[data-id="video"][data-position="0"]').length && position > 0){
            position = position-1;
        }

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
                    gallery.updateSize(true);
                };
                img.src = item.src;
            }
        });
        gallery.init();
    },
    popupSwipebox: function(preview){
        var mainGalleryImages =  preview.closest('.js-product').find(".js-product-image-popup"),
            mainGallery = mainGalleryImages.closest(".js-product-gallery-main"),
            isPreviews = mainGallery.data('popup-previews'),
            previews = preview.closest('.js-product').find('.owl-item:not(.cloned) .js-gallery-preview'),
            images = [],
            position = preview.data('position');

        if(mainGallery.find('[data-id="video"][data-position="0"]').length && position > 0){
            position = position-1;
        }

        if(!previews.length){
            /* список превью слева */
            previews = preview.closest('.js-product').find('.js-gallery-preview');
        }

        if (previews.length) {
            previews.each(function () {
                if(!$(this).data('video')){
                    images.push({href: $(this).find('a').attr('href'), src: $(this).find('img').attr('src')});
                }
            });
        } else {
            images.push({href: preview.attr('href'), src: preview.find('img').attr('src')});
        }

        function closeSwipebox(){
            var $closeButton = $("#swipebox-close");

            if ($closeButton.length) {
                $closeButton.trigger("click");
            }
        }

        $(document).on("scroll", closeSwipebox);
        $(document).on("click", '#swipebox-overlay', closeSwipebox);
        $(document).on("click", 'img, #swipebox-bottom-bar, .js-swipebox-thumbs-el', function (event){
            event.stopPropagation();
        });

        $.swipebox(images, {
            useSVG : false,
            hideBarsDelay: false,
            loopAtEnd: true,
            thumbs: true,
            initialIndexOnArray: position,
            afterOpen: function() {
                if (isPreviews && images.length > 1){
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
                }
                $('#swipebox-bottom-bar').addClass("swipebox-bottom-bar--pos-center");
                $('#swipebox-arrows').addClass("swipebox-arrows--pos-center");
            }
        });
    },
    swipeLargePhoto: function (mainGallery){
        if(is_touch_device()){
            mainGallery.swipe( {
                allowPageScroll:"auto",
                threshold: 20,
                swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                    var mainGallery = $(event.target).closest('.js-product').find('.js-product-gallery-main');
                    if(direction == 'left'){
                        ProductCardGallery.productMainImageInitCarousel(mainGallery,"prev");
                    }
                    else if(direction == 'right') {
                        ProductCardGallery.productMainImageInitCarousel(mainGallery,"next");
                    }
                }
            });
        }
    },
    selectLargePhoto: function (previewImage, isPreviewsCarouselGoTo){
        var _this = this,
            preview =  previewImage.parent(),
            image_id = preview.data("id"),
            mainGallery = previewImage.closest('.js-product').find('.js-product-gallery-main'),
            mainGalleryItems = mainGallery.find('.js-product-gallery-main-el');

        if(!mainGallery.hasClass('owl-loaded')){
            _this.productMainImageInitCarousel(mainGallery);
        }

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
    productImageZoom: function(mainGalleryImages){
        var mainGallery = mainGalleryImages.closest('.js-product-gallery-main');

        if(mainGalleryImages.length && mainGallery.length && mainGallery.data("zoom")){
            mainGalleryImages.each(function(){
                if(!$(this).find('img').hasClass('zoomImg')){
                    $(this).zoom({
                        url: $(this).attr('href'),
                        onZoomIn: function (){
                            $(this).parent().addClass("zooming");
                        },
                        onZoomOut: function (){
                            $(this).parent().removeClass("zooming");
                        }
                    });
                }
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

$(function(){
    if(!globalThemeSettings.isMobile){
        responsiveMenu.init();
    }
    if(globalThemeSettings.isDisplayFontAwesome){
        displayFontAwesome.init();
    }
    slider.init();
    ProductCardGallery.init();
    main.init();
    mobilePopupBlocks.init();
    mobileMenu.init();
    openMap.init();
    form.init();
    dropDownList.init();
    subscribeForm.init();
    moreText.init();
    anchorLink.init();
    tabs.init();
    accordionTabs.init();
    modalForm.init();
    countdown.init();
    productListUser.init();
    cart.init();
    sidebarMobileMenu.init();
    pagesTree.init();
    switchVersionSite.init();
    videoPopup.init();
    infoMessage.init($('.js-info-massage'));
    popupAdvert.init($('.js-popup-advert'));
    productsPreviewList.init();
    productsCarousel.init();
    categoriesImages.init();
    customGalleryPopup.init();
    contentPopup.init();
    fixedCart.init();

    if(globalThemeSettings.isFixedHeader){
        headerFixed.init();
    }
    if(globalThemeSettings.isFixedHeaderMobile){
        headerMobileFixed.init();
    }

    if (globalThemeSettings.isTileGalleryProductPreview){
        new productTileGallery();
    }
    if(globalThemeSettings.isDemoSettings){
        demoTest.init();
    }
    if(globalThemeSettings.isHorizontalMainMenu){
        categoriesHorizontalMenu.init();
    }

    if(!globalThemeSettings.isMobile){
        categoriesVerticalMenu.init();
        categoriesVerticalMenuUnfolding.init();
        sidebarCarousel.init();
        cartPreview.init();
        headerMenu.init();
    }
    new SocialWidgets({ container: ".js-social-widgets", timeAutoSwitch: 5000 });
});

$.fn.elementRealWidth = function () {
    $clone = this.clone()
        .css("visibility","hidden")
        .appendTo($('body'));
    var $width = $clone.outerWidth(true);
    $clone.remove();
    return $width;
};

jQuery.fn.outerHTML = function(s) {
    return s
        ? this.before(s).remove()
        : jQuery("<p>").append(this.eq(0).clone()).html();
};

function is_touch_device() {
    return 'ontouchstart' in window || navigator.maxTouchPoints;
};

function viewport() {
    var e = window, a = 'inner';
    if (!('innerWidth' in window )) {
        a = 'client';
        e = document.documentElement || document.body;
    }
    return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
}

function removeParam(key, sourceURL) {
    if(sourceURL){
        var rtn = sourceURL.split("?")[0],
            param,
            params_arr = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";

        if (queryString !== "") {
            params_arr = queryString.split("&");
            for (var i = params_arr.length - 1; i >= 0; i -= 1) {
                param = params_arr[i].split("=")[0];
                if (param === key) {
                    params_arr.splice(i, 1);
                }
            }
            rtn = rtn + "?" + params_arr.join("&");
        }
        return rtn;
    }else{
        return "";
    }
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function truncateText(text, maxLength){
    if(text && parseInt(maxLength)){
        var textLength = text.length;
        if(textLength > parseInt(maxLength)){
            var resultText = text.slice(0, maxLength);

            return resultText + "...";
        }
    }
    return text;
}

$.fn.pop = function() {
    var top = this.get(-1);
    this.splice(this.length-1,1);
    return top;
};

$.fn.shift = function() {
    var bottom = this.get(0);
    this.splice(0,1);
    return bottom;
};

$.urlParam = function(url, name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
    if (results==null){
        return null;
    }
    else{
        return results[1] || 0;
    }
}

$.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

function currencyFormat (number, no_html) {
    // Format a number with grouped thousands
    //
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +	 bugfix by: Michael White (http://crestidg.com)

    var i, j, kw, kd, km;
    var decimals = globalThemeSettings.currency.frac_digits;
    var dec_point = globalThemeSettings.currency.decimal_point;
    var thousands_sep = globalThemeSettings.currency.thousands_sep;

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
    var s = no_html ? globalThemeSettings.currency.sign : globalThemeSettings.currency.sign_html;
    if (!globalThemeSettings.currency.sign_position) {
        return s + globalThemeSettings.currency.sign_delim + number;
    } else {
        return number + globalThemeSettings.currency.sign_delim + s;
    }
};

function formatDate(date_string) {
    var date_array = date_string.split("-");
    var year = date_array[0],
        mount = date_array[1] - 1,
        day = date_array[2];
    return new Date(year, mount, day);
}




