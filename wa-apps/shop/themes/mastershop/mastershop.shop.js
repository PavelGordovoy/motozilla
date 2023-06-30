var filterForm = {
    init: function(){
        var _this = this;

        _this.openParam();
        _this.rangeParam($('.js-filter-range'));
        _this.dateParam();
        _this.showAllParams();
        _this.smartfilterRefreshHideParams();
        _this.clearSelectedFilter();
        _this.initFilterSubcategory();
    },
    openParam: function () {
        var _this = this;

        var optionLink = $('.js-filter-title');
        optionLink.on("click", function(){
            var $this = $(this), option = $this.closest('.js-filter-param');

            if(option.hasClass('close')){
                option.removeClass('close');
            }else{
                option.addClass('close');
            }

        });
    },
    rangeParam: function (fields){
        fields.each(function () {
            if (!$(this).find('.filter-slider').length) {
                $(this).append('<div class="filter-slider"></div>');
            } else {
                return;
            }


            var min = $(this).find('.js-min');
            var max = $(this).find('.js-max');
            var min_value = parseFloat(min.attr('placeholder'));
            var max_value = parseFloat(max.attr('placeholder'));
            var step = 1;
            var slider = $(this).find('.filter-slider');
            if (slider.data('step')) {
                step = parseFloat(slider.data('step'));
            } else {
                var diff = max_value - min_value;
                if (Math.round(min_value) != min_value || Math.round(max_value) != max_value) {
                    step = diff / 10;
                    var tmp = 0;
                    while (step < 1) {
                        step *= 10;
                        tmp += 1;
                    }
                    step = Math.pow(10, -tmp);
                    tmp = Math.round(100000 * Math.abs(Math.round(min_value) - min_value)) / 100000;
                    if (tmp && tmp < step) {
                        step = tmp;
                    }
                    tmp = Math.round(100000 * Math.abs(Math.round(max_value) - max_value)) / 100000;
                    if (tmp && tmp < step) {
                        step = tmp;
                    }
                }
            }
            slider.slider({
                range: true,
                min: parseFloat(min.attr('placeholder')),
                max: parseFloat(max.attr('placeholder')),
                step: step,
                values: [parseFloat(min.val().length ? min.val() : min.attr('placeholder')),
                    parseFloat(max.val().length ? max.val() : max.attr('placeholder'))],
                slide: function( event, ui ) {
                    var v = ui.values[0] == $(this).slider('option', 'min') ? '' : ui.values[0];
                    min.val(v);
                    v = ui.values[1] == $(this).slider('option', 'max') ? '' : ui.values[1];
                    max.val(v);
                },
                stop: function (event, ui) {
                    if(ui.value == ui.values[0]){
                        min.change();
                    }else{
                        max.change();
                    }
                }
            });
            min.add(max).change(function () {
                var v_min =  min.val() === '' ? slider.slider('option', 'min') : parseFloat(min.val());
                var v_max = max.val() === '' ? slider.slider('option', 'max') : parseFloat(max.val());
                if (v_max >= v_min) {
                    slider.slider('option', 'values', [v_min, v_max]);
                }
            });
        });
    },
    dateParam: function (){
        $('.js-filter-date').each(function () {
            var wrapDatePicker = $(this);

            wrapDatePicker.find(".js-datepicker").each( function() {
                var $thisDatePickerField = $(this),
                    code = $thisDatePickerField.data('code'),
                    $thisDatePickerFieldAlt = $thisDatePickerField.parent().find("input[type='hidden'][data-code='"+code+"']");

                $thisDatePickerField.datepicker({
                    altField: $thisDatePickerFieldAlt,
                    altFormat: "yy-mm-dd",
                    minDate: formatDate(wrapDatePicker.data("min")),
                    maxDate: formatDate(wrapDatePicker.data("max")),
                    changeMonth: true,
                    changeYear: true
                });

                $thisDatePickerField.on("change", function() {
                    var value = $thisDatePickerField.val();
                    if (!value) {
                        $thisDatePickerFieldAlt.val("");
                    }
                });
            });
        });
    },
    showAllParams: function (){
        var btn = $('.js-filter-params-all');

        btn.click(function (event){
            event.preventDefault();

            var $this = $(this),
                textShow = $this.data("text-show"),
                textHide = $this.data("text-hide"),
                optionsList = $this.closest(".js-filter-param"),
                optionsEls = optionsList.find('.js-filter-variant');

            if(optionsEls.is(":hidden")){
                optionsEls.removeClass("hide");
                $this.addClass('open');
                $this.text(textHide);
            }else{
                optionsEls.addClass("hide");
                $this.removeClass('open');
                $this.text(textShow);
            }
        });
    },
    smartfilterRefreshHideParams: function (){
        var isHideDisabledFilters = $('.js-filters').data('smartfilters-hide-option');

        if (typeof $.smartfiltersTheme !== 'undefined' && isHideDisabledFilters) {
            setTimeout(function (){
                var btnShowMore = $('.js-filter-params-all');

                btnShowMore.each(function (){
                    var thisBtn = $(this),
                        isOpenAllParams = thisBtn.hasClass('open'),
                        maxParams = parseInt(thisBtn.data('max-show-params')),
                        params = thisBtn.closest('.js-filter-param').find('label'),
                        indexVisibleParam = 0;

                    if(maxParams){
                        thisBtn.hide();
                        params.each(function (){
                            var thisParam = $(this);

                            thisParam.removeClass('js-filter-variant').removeClass('hide');
                            var isHiddenThisParam =  thisParam.hasClass('sf-label-disabled') && thisParam.is(':hidden');

                            if(!isHiddenThisParam){
                                indexVisibleParam++;
                            }

                            if(indexVisibleParam > maxParams){
                                thisBtn.show();
                                thisParam.addClass('js-filter-variant');
                                if(!isOpenAllParams){
                                    thisParam.addClass('hide');
                                }
                            }
                        });
                    }
                });

            }, 500);
        }
    },
    clearSelectedFilter: function (){
        var _this = $(this);

        $('body').on("click", ".js-filter-selected-remove", function (){
            var code = $(this).data('code');

            if($('.js-category-filter').length){
                horizontalFilter.clearFilterItem($('.js-category-filter').find(".js-category-filter-el[data-code='"+code+"']"), false);
            }

            if($('.js-mobile-filter').length){
                mobileFilter.clearFilterItem(code);
            }

            if($('.js-sidebar-filter').length){
                sidebarFilter.clearFilterItem(code);
            }

            if($('.js-filters').length){
                productList.updateProductList($('.js-filters form').first(), true);
            }

        });
    },
    seoFilterDisabledParams: function (){
        var _this = this;

        if (typeof window.seofilterInit === "function") {
            var firstForm = $('.filters form').filter(':visible').first(),
                allForms = $('.filters form');

            if(allForms.length > 1 && firstForm.length){
                var inputs = firstForm.find('input');

                inputs.each(function (){
                    var input = $(this),
                        code = input.data("code"),
                        inputOtherForms = allForms.find('input[data-code="'+ code +'"]');

                    if(input.is(':disabled')){
                        inputOtherForms.prop("disabled", true);
                        inputOtherForms.closest("label").addClass("disabled");
                        inputOtherForms.closest(".js-checkbox-styler, .js-radio-styler").addClass("disabled");
                    }else{
                        inputOtherForms.prop("disabled", false);
                        inputOtherForms.closest("label").removeClass("disabled");
                        inputOtherForms.closest(".js-checkbox-styler, .js-radio-styler").removeClass("disabled");
                    }
                });
            }

            _this.refreshStylerAllInputs(allForms);
        }
    },
    addFilterSubcategory: function(fields){
        var _this = this,
            subcategoriesWrap = $('.js-category-subcategories'),
            isSaveFilters = subcategoriesWrap.data('save-filters'),
            saveFiltersAliases = subcategoriesWrap.data('save-filters-aliases'),
            saveFields = fields,
            subcategiesLink = subcategoriesWrap.find('.js-category-link');

        if(isSaveFilters){
            if(saveFiltersAliases){
                saveFiltersAliases = saveFiltersAliases.split(',');

                if(saveFiltersAliases.length > 0){
                    saveFields = [];
                    fields.forEach(function(field) {
                        if(saveFiltersAliases.indexOf(field.name.replace("[]", "")) >= 0){
                            saveFields.push(field);
                        }
                    });
                }
            }

            if(saveFields.length){
                var url = _this.buildUrl(saveFields);

                if(subcategiesLink.length){
                    if(url.length < 2){
                        url = "";
                    }
                    subcategiesLink.each(function(){
                        var href = $(this).attr("href");
                        href = href.split("?")[0];
                        $(this).attr("href", href + url);
                    });
                }
            }else{
                subcategiesLink.each(function(){
                    var href = $(this).attr("href");
                    href = href.split("?")[0];
                    $(this).attr("href", href);
                });
            }
        }
    },
    initFilterSubcategory: function(){
        var _this = this,
            form = $('.js-filters form').first();

        if(form.length){
            var url = _this.buildUrl(form.serializeArray());

            _this.addFilterSubcategory(form.serializeArray());
        }
    },
    buildUrl: function (fields){
        var params = [],
            url = "";

        for (var i = 0; i < fields.length; i++) {
            var unitParamName = fields[i].name,
                isParamUnit = (unitParamName.indexOf('[unit]') > -1);

            if(isParamUnit){
                unitParamName = unitParamName.replace('[unit]', "");

                var savedUntilParamValue = fields[i].value,
                    unitParamMin = unitParamName + "[min]",
                    unitParamMax = unitParamName + "[max]";

                fields[i].value = '';
                for (var j = 0; j < fields.length; j++) {
                    if((fields[j].name == unitParamMin || fields[j].name == unitParamMax) && fields[j].value != ""){
                        fields[i].value = savedUntilParamValue;
                    }
                }
            }
        }

        for (var i = 0; i < fields.length; i++) {
            if (fields[i].value !== '') {
                params.push(fields[i].name + '=' + fields[i].value);
            }
        }

        if(params.length){
            url = '?' + params.join('&');
        }

        return url;
    },
    refreshStylerAllInputs: function (form){
        if(form === undefined){
            form = $('.js-category-filter, .js-sidebar-filter, .js-mobile-filter');
        }
        var checkedInputs = form.find('.js-checkbox-styler-input:checked, .js-radio-styler-input:checked');
        checkedInputs.closest('.js-radio-styler, .js-checkbox-styler').addClass('checked');
        checkedInputs.closest('label').addClass('checked');

        var noneCheckedInputs = form.find('.js-checkbox-styler-input:not(:checked), .js-radio-styler-input:not(:checked)');
        noneCheckedInputs.closest('.js-radio-styler, .js-checkbox-styler').removeClass('checked');
        noneCheckedInputs.closest('label').removeClass('checked');
    }
};

var sidebarFilter = {
    init: function(){
        var _this = this;

        _this.filterSend();
    },
    filterSend: function (){
        var _this = this;

        $('body').on('change', '.js-ajax-filter form.js-sidebar-filter input', function () {
            horizontalFilter.changeOption($(this));
            mobileFilter.changeOption($(this));

            productList.updateProductList($(this).closest('form'), true);
        });

        $('.js-ajax-filter form.js-sidebar-filter').submit(function () {
            productList.updateProductList($(this), true);
            return false;
        });
    },
    changeOption: function (option){
        var code = option.data("code"),
            type = option.attr("type"),
            value = option.val(),
            input = $("form.js-sidebar-filter").find('input[data-code="'+ code +'"]');

        if(type == 'text'){
            var wrapRange = input.closest(".js-filter-range");

            input.val(value);

            if(wrapRange.length){
                wrapRange.find(".filter-slider").remove();
                filterForm.rangeParam(wrapRange);
            }
        }else if (type == 'radio' || type == 'checkbox'){
            if(type == 'radio'){
                var optionName = option.attr('name'),
                    allRadioButtons = $("form.js-sidebar-filter").find('input[type="radio"][name="'+ optionName +'"]');

                allRadioButtons.prop('checked', false);
                allRadioButtons.closest(".js-checkbox-styler, .js-radio-styler").removeClass("checked");
                allRadioButtons.closest("label").removeClass("checked");
            }
            if(option.is(':checked')){
                input.prop('checked', true);
                input.closest(".js-checkbox-styler, .js-radio-styler").addClass("checked");
                input.closest("label").addClass("checked");
            }else{
                input.prop('checked', false);
                input.closest(".js-checkbox-styler, .js-radio-styler").removeClass("checked");
                input.closest("label").removeClass("checked");
            }
        }

    },
    clearFilterItem: function (code){
        var item = $('.js-sidebar-filter').find(".js-filter-param[data-code='"+code+"']");
        item.find('input[type="checkbox"], input[type="radio"]').prop( "checked", false );
        item.find('input[type="checkbox"], input[type="radio"]').closest('.js-checkbox-styler, .js-radio-styler').removeClass("checked");
        item.find('input[type="checkbox"], input[type="radio"]').closest('label').removeClass("checked");
        item.find('input[type="text"]').val("");

        /* range options refresh */
        $('.js-sidebar-filter .filter-slider').remove();
        filterForm.rangeParam($('.js-sidebar-filter .js-filter-range'));
    }
};

var horizontalFilter = {
    init: function(){
        var _this = this;

        _this.filterSend();
        _this.clearFilter();
        _this.responsiveHorizontal();
        _this.resize();
        _this.open();
    },
    filterSend: function(){
        var _this = this;

        $('body').on('change', '.js-ajax-filter form.js-category-filter input', function () {
            _this.selectHorizontalFilter($(this).closest('.js-category-filter-el'));

            sidebarFilter.changeOption($(this));
            mobileFilter.changeOption($(this));

            productList.updateProductList($(this).closest('form'), true);
        });

        $('.js-ajax-filter form.js-category-filter').submit(function () {
            productList.updateProductList($(this), true);
            return false;
        });
    },
    changeOption: function (option){
        var _this = this,
            code = option.data("code"),
            type = option.attr("type"),
            value = option.val(),
            input = $("form.js-category-filter").find('input[data-code="'+ code +'"]');

        if(type == 'text'){
            var wrapRange = input.closest(".js-filter-range");

            input.val(value);

            if(wrapRange.length){
                wrapRange.find(".filter-slider").remove();
                filterForm.rangeParam(wrapRange);
            }
        }else if (type == 'radio' || type == 'checkbox'){
            if(type == 'radio'){
                var optionName = option.attr('name'),
                    allRadioButtons = $("form.js-category-filter").find('input[type="radio"][name="'+ optionName +'"]');

                allRadioButtons.prop('checked', false);
                allRadioButtons.closest(".js-checkbox-styler, .js-radio-styler").removeClass("checked");
                allRadioButtons.closest("label").removeClass("checked");
            }

            if(option.is(':checked')){
                input.prop('checked', true);
                input.closest("label").addClass("checked");
                input.closest(".js-radio-styler, .js-checkbox-styler").addClass("checked");
            }else{
                input.prop('checked', false);
                input.closest("label").removeClass("checked");
                input.closest(".js-radio-styler, .js-checkbox-styler").removeClass("checked");
            }
        }

        var wrapOuter = input.closest(".js-category-filter-el");
        _this.selectHorizontalFilter(wrapOuter);

    },
    responsiveHorizontal: function(){
        var _this = this, items = $(".js-resp-nav");

        responsiveMenu.responsived($('.js-category-filter-list'), '.js-category-filter-el', '.js-category-filter-subwrap');
        responsiveMenu.positionSubmenu('.js-category-filter-el', '.js-category-filter-list', '.js-category-filter-subwrap');
        _this.countSelectedFilterElse($('.js-category-filter-el[data-type="else"]'));

        /* range options refresh */
        $('.js-category-filter .filter-slider').remove();
        filterForm.rangeParam($('.js-category-filter .js-filter-range'));
    },
    resize: function(){
        var _this = this;

        $(window).resize(function() {
            _this.responsiveHorizontal();
        });
    },
    selectHorizontalFilter: function (item){
        var _this = this, elseItem = item.closest('.js-category-filter-el[data-type="else"]');

        if (elseItem.length){
            _this.countSelectedFilterElse(elseItem);
        }else{
            _this.countSelectedFilterItem(item);
        }
    },
    countSelectedFilterItem: function (item){
        var _this = this, countSelect = 0, type = item.data("type");

        if(type == 'checkbox'){
            var fields = item.find('input').serializeArray();
            countSelect = fields.length;
        }else if(type == 'radio'){
            if(item.find('input:radio:checked') && item.find('input:radio:checked').val()){
                countSelect = 1;
            }
        }else if(type == 'text'){
            var fields = item.find('input:text');

            fields.each(function () {
                if ($(this).val()){
                    countSelect++;
                }
            });
        }

        if(countSelect){
            item.addClass('selected');
            item.find('.js-filters-count').text(countSelect);
        }else{
            item.removeClass('selected');
        }

        return parseInt(countSelect);
    },
    countSelectedFilterElse: function (elseItem){
        var _this = this, elseCountSelect = 0;

        elseItem.find('.js-category-filter-el').each(function(){
            elseCountSelect += _this.countSelectedFilterItem($(this));
        });

        if(elseCountSelect){
            elseItem.addClass('selected');
            elseItem.find('.js-filters-count').first().text(elseCountSelect);
        }else{
            elseItem.removeClass('selected');
        }
    },
    clearFilter: function (){
        var _this = this;

        $('body').on("click", '.js-filter-remove', function(event){
            event.stopPropagation();

            var item = $(this).closest('.js-category-filter-el');

            if(item.data("type") == 'else'){
                _this.clearFilterItemElse(item);
            }else{
                _this.clearFilterItem(item, true);

            }
        });
    },
    clearFilterItem: function (item, isUpdateList) {
        var _this = this;

        item.find('input[type="checkbox"], input[type="radio"]').prop( "checked", false );
        item.find('input[type="checkbox"], input[type="radio"]').closest(".js-checkbox-styler, .js-radio-styler").removeClass("checked");
        item.find('input[type="checkbox"], input[type="radio"]').closest("label").removeClass("checked");
        item.find('input[type="text"]').val("");
        item.removeClass('selected');

        item.find('input[type="checkbox"], input[type="radio"], input[type="text"]').each(function(){
            mobileFilter.changeOption($(this));
            sidebarFilter.changeOption($(this));
        });

        /* range options refresh */
        $('.js-category-filter .filter-slider').remove();
        filterForm.rangeParam($('.js-category-filter .js-filter-range'));

        if(isUpdateList){
            productList.updateProductList(item.closest('form'), true);
        }

        var elseItem = item.closest('.js-category-filter-el[data-type="else"]');
        if(elseItem.length){
            _this.countSelectedFilterElse(elseItem);
        }
    },
    clearFilterItemElse: function (elseItem){
        var _this = this;

        elseItem.find('.js-category-filter-el').each(function(){
            _this.clearFilterItem($(this), false);
        });
        elseItem.removeClass('selected');
        productList.updateProductList(elseItem.closest('form'), true);
    },
    open: function (){
        var $element = $('.js-category-filter-el');

        $('body').on("mouseover", '.js-category-filter-el', function (){
            var subWrap = $(this).find('.js-category-filter-subwrap').first();

            subWrap.stop(true).delay(150).fadeIn(1, function(){
                var $currentElement = $(this).closest('.js-category-filter-el');

                $currentElement.addClass('open');
            });
        });

        $('body').on("mouseout", '.js-category-filter-el', function (){
            var subWrap = $(this).find('.js-category-filter-subwrap').first();

            subWrap.stop(true).delay(100).fadeOut(1, function(){
                var $currentElement = $(this).closest('.js-category-filter-el');

                $currentElement.removeClass('open');
            });
        });
    }
};

var mobileFilter = {
    init: function(){
        var _this = this;

        _this.filterSend();
        _this.open();
        _this.closeByButton();
        _this.closeByBg();
    },
    filterSend: function (){
        var _this = this;

        $('body').on('change', '.js-ajax-filter form.js-mobile-filter input', function () {
            horizontalFilter.changeOption($(this));
            sidebarFilter.changeOption($(this));

            productList.updateProductList($(this).closest('form'), true);
        });
        $('.js-ajax-filter form.js-mobile-filter').submit(function () {
            productList.updateProductList($(this), true);
            return false;
        });

        $('body').on("click", '.js-ajax-filter form.js-mobile-filter .js-submit-form', function (){
            _this.close($(this).closest(".js-mobile-filter-outer"));
        });
    },
    changeOption: function (option){
        var code = option.data("code"),
            type = option.attr("type"),
            value = option.val(),
            input = $("form.js-mobile-filter").find('input[data-code="'+ code +'"]');

        if(type == 'text'){
            var wrapRange = input.closest(".js-filter-range");

            input.val(value);

            if(wrapRange.length){
                wrapRange.find(".filter-slider").remove();
                filterForm.rangeParam(wrapRange);
            }
        }else if (type == 'radio' || type == 'checkbox'){
            if(type == 'radio'){
                var optionName = option.attr('name'),
                    allRadioButtons = $("form.js-mobile-filter").find('input[type="radio"][name="'+ optionName +'"]');

                allRadioButtons.prop('checked', false);
                allRadioButtons.closest(".js-checkbox-styler, .js-radio-styler").removeClass("checked");
                allRadioButtons.closest("label").removeClass("checked");
            }

            if(option.is(':checked')){
                input.prop('checked', true);
                input.closest(".js-checkbox-styler, .js-radio-styler").addClass("checked");
                input.closest("label").addClass("checked");
            }else{
                input.prop('checked', false);
                input.closest(".js-checkbox-styler, .js-radio-styler").removeClass("checked");
                input.closest("label").removeClass("checked");
            }
        }
    },
    clearFilterItem: function (code){
        var item = $('.js-mobile-filter').find(".js-filter-param[data-code='"+code+"']");
        item.find('input[type="checkbox"], input[type="radio"]').prop( "checked", false );
        item.find('input[type="checkbox"], input[type="radio"]').closest("label").removeClass("checked");
        item.find('input[type="checkbox"], input[type="radio"]').closest(".js-checkbox-styler, .js-radio-styler").removeClass("checked");
        item.find('input[type="text"]').val("");

        /* range options refresh */
        $('.js-mobile-filter .filter-slider').remove();
        filterForm.rangeParam($('.js-mobile-filter .js-filter-range'));
    },
    open: function (){
        var _this = this,
            button = $('.js-btn-open-filter'),
            filterWrap = $('.js-mobile-filter-outer'),
            filterButtons = filterWrap.find('.js-mobile-filter-btns');

        if(filterWrap.length && button.length){
            button.on("click", function (){
                if (filterWrap.is(":visible")) {
                    _this.close(menu);
                } else {
                    filterWrap.css("left", "100%");
                    filterWrap.addClass("show");
                    filterWrap.animate({"left": "0"}, 300, function () {
                        filterButtons.addClass("show");
                        filterButtons.css("bottom", "-100%");
                        filterButtons.animate({"bottom": "0"}, 300, function () {
                            filterWrap.removeAttr("style");
                            filterButtons.removeAttr("style");
                        });
                    });
                    $("body").addClass("overflow-hidden").append("<div class='js-m-bg m-bg'></div>");
                }
            });
        }
    },
    closeByButton: function (){
        var _this = this,
            button = $('.js-mobile-filter-close');

        button.on("click", function (){
            _this.close($(this).closest('.js-mobile-filter-outer'));
        });
    },
    closeByBg: function (){
        var _this = this;

        $('body').on("click", '.js-m-bg', function (){
            _this.close($('.js-mobile-filter-outer'));
        });
    },
    close: function (filterWrap){
        if(filterWrap.length){
            var filterBtnsWrap = filterWrap.find('.js-mobile-filter-btns');

            filterBtnsWrap.removeAttr("style");
            filterBtnsWrap.removeClass("show");
            filterWrap.animate({"left": "-100%"}, 300, function (){
                filterWrap.removeAttr("style");
                filterWrap.removeClass("show");
                $("body").removeClass("overflow-hidden");
                $(".js-m-bg").remove()
            });
        }
    }
};

var brands = {
    brandsWrap: $('.js-home-brands'),
    init: function (){
        var _this = this;

        if(!_this.brandsWrap.length){
            return false;
        }

        if (_this.brandsWrap.outerWidth() < 900){
            _this.brandsWrap.addClass("compact");
        }else{
            _this.brandsWrap.removeClass("compact");
        }

        _this.prepareBrandsCarousels();
        _this.carouselInteraction();

        $(window).one('resize', brands.brandsCarousel);
    },
    prepareBrandsCarousels: function (){
        var _this = this,
            carousel = _this.brandsWrap.find('.js-home-brands-carousel'),
            carouselWidth = carousel.outerWidth(true),
            itemCarousel = _this.brandsWrap.find(".js-home-brand-el"),
            countItems = itemCarousel.length,
            widthItem = itemCarousel.first().outerWidth(true),
            widthAllItems = widthItem * countItems,
            isButtons = widthAllItems > carouselWidth + 10;

        if(isButtons){
            var wrapButtons = _this.brandsWrap.find(".js-home-brands-direction");
            wrapButtons.append('<div data-index="prev" class="js-carousel-brands-on-initialized owl-prev disabled"><span class="carousel-prev"></span></div>');
            wrapButtons.append('<div data-index="next" class="js-carousel-brands-on-initialized owl-next"><span class="carousel-next"></span></div>');
        }

        var carouselOffsetRight = carousel.offset().left + carousel.outerWidth();
        itemCarousel.slice(0,6).each(function (){
            var itemOffsetLeft = $(this).offset().left;

            if (itemOffsetLeft < carouselOffsetRight) {
                $(this).find(".owl-lazy").Lazy({
                    afterLoad: function(element) {
                        element.removeClass("owl-lazy");
                    },
                });
            }
        });
    },
    carouselInteraction: function (){
        var _this = this,
            brandsCarouselNav = $('.js-carousel-brands-on-initialized');

        brandsCarouselNav.on("click", function (){
            _this.brandsCarousel($(this).data("index"));
        });

        if(is_touch_device()){
            $('.js-home-brands-carousel').swipe( {
                allowPageScroll:"auto",
                threshold: 20,
                swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                    if(direction == 'left'){
                        brands.brandsCarousel("next");
                    }
                    else if(direction == 'right') {
                        brands.brandsCarousel("prev");
                    }
                }
            });
        }
    },
    brandsCarousel: function (goToSlide){
        if(brands.brandsWrap.hasClass("carousel-init")){
            return false;
        }

        var carousel = brands.brandsWrap.find('.js-home-brands-carousel'),
            nav = brands.brandsWrap.find('.js-home-brands-direction'),
            contentCountCols = contentCols.getCount(brands.brandsWrap.closest('.js-content-cols'));

        var responsive ={ 0: {items: 2}, 450: {items: 3}, 700: {items: 4}, 1200: {items: 5}, 1400: {items: 6} };
        if(contentCountCols == 2){
            responsive = { 0: {items: 2}, 450: {items: 3}, 700: {items: 4}, 1400: {items: 5} };
        }else if (contentCountCols == 3){
            responsive = { 0: {items: 2}, 450: {items: 3}, 700: {items: 4} };
        }

        carousel.owlCarousel({
            loop:false,
            margin: 10,
            nav: true,
            dots: false,
            navText: ['<span class="carousel-prev"></span>','<span class="carousel-next"></span>'],
            navElement: "div",
            navContainer: nav,
            responsive: responsive,
            lazyLoad: true,
            onInitialize: function (event){
                brands.brandsWrap.find(".js-carousel-brands-on-initialized").remove();
            },
            onInitialized: function (event){
                brands.brandsWrap.addClass("carousel-init");
            },
        });

        carousel.on('changed.owl.carousel', function(event) {
            if(event.page.size <= 4 && brands.brandsWrap.outerWidth() < 900){
                brands.brandsWrap.addClass("compact");
            }else{
                brands.brandsWrap.removeClass("compact");
            }
        });
        if(goToSlide){
            if(goToSlide == "prev") {
                carousel.trigger("prev.owl.carousel");
            }else if(goToSlide == "next"){
                carousel.trigger("next.owl.carousel");
            }
        }
    },
};

var reviews = {
    reviewsWrap: $('.js-home-reviews'),
    init: function (){
        var _this = this;

        if(!_this.reviewsWrap.length){
            return false;
        }

        _this.prepareReviewsCarousels();
        _this.carouselInteraction();

        $(window).one('resize', reviews.reviewsCarousel);
    },
    prepareReviewsCarousels: function (){
        var _this = this,
            carousel = _this.reviewsWrap.find('.js-reviews-carousel'),
            carouselWidth = carousel.outerWidth(),
            itemCarousel = _this.reviewsWrap.find(".js-home-reviews-item"),
            countItems = itemCarousel.length,
            widthItem = itemCarousel.first().outerWidth(true),
            widthAllItems = widthItem * countItems,
            isButtons = widthAllItems > carouselWidth + 40;

        if(isButtons){
            var wrapButtons = _this.reviewsWrap.find(".js-carousel-direction");

            wrapButtons.append('<div data-index="prev" class="js-carousel-reviews-on-initialized owl-prev disabled"><span class="carousel-prev"></span></div>');
            wrapButtons.append('<div data-index="next" class="js-carousel-reviews-on-initialized owl-next"><span class="carousel-next"></span></div>');
        }

        var carouselOffsetRight = carousel.offset().left + carousel.outerWidth();
        itemCarousel.slice(0,2).each(function (){
            var itemOffsetLeft = $(this).offset().left;

            if (itemOffsetLeft < carouselOffsetRight) {
                $(this).find(".owl-lazy").Lazy({
                    afterLoad: function(element) {
                        element.removeClass("owl-lazy");
                    },
                });
            }
        });
    },
    carouselInteraction: function (){
        var _this = this,
            reviewsCarouselNav = $('.js-carousel-reviews-on-initialized');

        reviewsCarouselNav.on("click", function (){
            _this.reviewsCarousel($(this).data("index"));
        });

        if(is_touch_device()){
            $('.js-reviews-carousel').swipe( {
                allowPageScroll:"auto",
                threshold: 20,
                swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                    if(direction == 'left'){
                        reviews.reviewsCarousel("next");
                    }
                    else if(direction == 'right') {
                        reviews.reviewsCarousel("prev");
                    }
                }
            });
        }
    },
    reviewsCarousel: function (goToSlide){
        if(reviews.reviewsWrap.hasClass("carousel-init")){
            return false;
        }

        var carousel = reviews.reviewsWrap.find('.js-reviews-carousel'),
            nav = reviews.reviewsWrap.find('.js-carousel-direction'),
            contentCountCols = contentCols.getCount(reviews.reviewsWrap.closest('.js-content-cols'));

        var responsive = { 0: { items: 1 }, 1000: { items: 2 } };
        if(contentCountCols == 2){
            responsive = { 0: { items: 1 }, 1250: { items: 2 } };
        }else if (contentCountCols == 3){
            responsive = { 0: { items: 1 } };
        }

        carousel.owlCarousel({
            loop:false,
            margin: 30,
            nav: true,
            dots: false,
            navText: ['<span class="carousel-prev"></span>','<span class="carousel-next"></span>'],
            navElement: "div",
            navContainer: nav,
            responsive: responsive,
            autoHeight: true,
            lazyLoad: true,
            onInitialize: function (event){
                reviews.reviewsWrap.find(".js-carousel-reviews-on-initialized").remove();
            },
            onInitialized: function (event){
                reviews.reviewsWrap.addClass("carousel-init");
            },
            onLoadedLazy: function(event){
                var reviews = $(event.currentTarget);
                if (reviews.length && $.Retina) {
                    reviews.find(".owl-item.active .owl-lazy").retina();
                }
            }
        });

        if(goToSlide){
            if(goToSlide == "prev") {
                carousel.trigger("prev.owl.carousel");
            }else if(goToSlide == "next"){
                carousel.trigger("next.owl.carousel");
            }
        }
    }
};

var productList = {
    init: function (){
        var _this = this;

        _this.switchProductsView();
        _this.ajaxSorting();
        _this.pagination();
        _this.paginationPageAjax();
        _this.setCountPerPageProducts();

        window.addEventListener('popstate', function(event) {
            location.reload();
        });
    },
    switchProductsView: function (){
        var _this = this,
            switchBtn = $('.js-switch-product-view');

        $('body').on('click', '.js-switch-product-view', function(){
            var $this = $(this),
                type = $this.data('view');

            _this.setCookieProductView(type);

            switchBtn.removeClass('bs-color');
            $this.addClass('bs-color');

            _this.updateProductList(null, false, window.location.href);
        });
    },
    setCookieProductView: function (view){
        var cookieOptions = {expires: 7, path: '/'};

        $.cookie('productPreviewView', view, cookieOptions);
    },
    ajaxSorting: function (){
        var _this = this;

        $('body').on('click', '.js-ajax-sorting a', function(event) {
            event.preventDefault();

            var $link = $(this),
                url = $link.attr('href'),
                isSearchPage = $link.closest('.js-search-page').length > 0;

            if(url){
                var urlGetSort = $.urlParam(url, 'sort'),
                    urlGetOrder = $.urlParam(url, 'order'),
                    urlGetPage = $.urlParam(url, 'page');

                var selectorForms = (isSearchPage) ? $('.js-filters form, .js-search-form') : $('.js-filters form');

                selectorForms.each(function (){
                    var $form = $(this),
                        resetLink = $form.find('.js-filter-reset'),
                        inputSort = $form.find('input[name="sort"]'),
                        inputOrder = $form.find('input[name="order"]');

                    if(urlGetSort && urlGetOrder){
                        if(inputSort.length){
                            inputSort.val(urlGetSort);
                        }else{
                            $form.append('<input type="hidden" name="sort" value="'+urlGetSort+'">');
                        }

                        if(inputOrder.length){
                            inputOrder.val(urlGetOrder);
                        }else{
                            $form.append('<input type="hidden" name="order" value="'+urlGetOrder+'">');
                        }
                        if(resetLink.length){
                            var categoryUrl = resetLink.data("category-url");
                            resetLink.attr("href", categoryUrl + "?sort="+ urlGetSort +"&order=" + urlGetOrder);
                        }
                    }else{
                        inputSort.remove();
                        inputOrder.remove();
                        if(resetLink.length){
                            var categoryUrl = resetLink.data("category-url");
                            resetLink.attr("href", categoryUrl);
                        }
                    }
                });

                var filterForm = $('.js-filters form').first();
                if(filterForm.length && urlGetPage){
                    filterForm.prepend('<input type="hidden" name="page" value="'+urlGetPage+'">');
                }
                _this.updateProductList(filterForm, true, url);
                filterForm.find("input[type='hidden'][name='page']").remove();
            }
        });
    },
    pagination: function (){
        var _this = this,
            isPagingLazyload = $('.js-paging-nav[data-lazyload="1"]').length,
            isButtonElse = ($('.js-paging-load-else').length && $('.js-paging-load-else').is(":visible"));

        _this.pagingElse();
        if(isPagingLazyload && !isButtonElse){
            _this.lazyloading();
        }
    },
    paginationPageAjax: function (){
        var _this = this;

        $("body").on("click", '.js-pagination-ajax a', function (event){
            var productListWrap = $("#product-list");

            if(productListWrap.length){
                event.preventDefault();

                var href = $(this).attr("href"),
                    productListWrapTop = productListWrap.offset().top - 90;

                _this.updateProductList(null, true, href);

                $('html,body').animate({
                    scrollTop: productListWrapTop
                }, 500);
            }
        });
    },
    lazyloading: function (){
        var _this = this;

        if ($.fn.lazyLoad) {
            var paging = $('.js-paging-nav[data-lazyload="1"]');
            if (!paging.length) {
                return;
            }
            var current = paging.find('li.selected'),
                next = current.next(),
                win = $(window);

            win.lazyLoad('stop');

            if (next.length) {
                win.lazyLoad({
                    container: '#product-list .product-list',
                    load: function () {
                        win.lazyLoad('sleep');

                        var paging = $('.js-paging-nav[data-lazyload="1"]');
                            current = paging.find('li.selected'),
                            next = current.next(),
                            url = next.find('a').attr('href');

                        if (!url) {
                            win.lazyLoad('stop');
                            return;
                        }

                        var productPreviewView = paging.data('product-view');
                        if(productPreviewView){
                            productList.setCookieProductView(productPreviewView);
                        }

                        var product_list = $('#product-list .product-list'),
                            loading = paging.parent().find('.loading').parent();

                        if (!loading.length) {
                            loading = $('<div class="paging-loading-icon"><i class="loading products-loading bs-color"></i></div>').insertBefore(paging);
                        }

                        var buttonElse = $('.js-paging-load-else');
                        buttonElse.hide();

                        var pagingPages = $('.js-pagination');
                        pagingPages.hide();

                        loading.show();
                        $.get(url + "&_=" + (new Date().getTime()) + Math.random(), function (html) {
                            var tmp = $('<div></div>').html(html);
                            productsPreviewList.images(tmp.find('.js-preview-products'));
                            product_list.append(tmp.find('#product-list .product-list').children());

                            var tmp_paging = tmp.find('.js-paging-nav[data-lazyload="1"]');
                            paging.replaceWith(tmp_paging);
                            paging = tmp_paging;
                            var current = paging.find('li.selected');
                            next = current.next();

                            if (!!(history.pushState && history.state !== undefined)){
                                window.history.pushState({}, '', url);
                            }

                            if (next.length) {
                                win.lazyLoad('wake');
                            } else {
                                win.lazyLoad('stop');
                            }

                            if (typeof $.autobadgeFrontend !== 'undefined') {
                                $.autobadgeFrontend.reinit();
                            }

                            var buttonElse = paging.find('.js-paging-load-else');
                            buttonElse.hide();

                            var pagingPages = paging.find('.js-pagination');
                            pagingPages.hide();

                            loading.hide();
                            tmp.remove();
                        });
                    }
                });
            }
        }
    },
    pagingElse: function (isLazyLoad){
        var _this = this;

        $('body').on("click", ".js-paging-load-else", function(){
            var buttonElse = $(this),
                isLazyLoad = buttonElse.data("after-lazyload"),
                paginationWrap = buttonElse.closest('.js-paging-nav'),
                currentPage = paginationWrap.find('.js-pagination .selected'),
                nextPage = currentPage.next(),
                nextPageUrl = nextPage.find('a').attr("href"),
                productsList = buttonElse.closest('#product-list').find('.js-preview-products'),
                productPreviewView = paginationWrap.data('product-view');

            if(productPreviewView){
                productList.setCookieProductView(productPreviewView);
            }

            buttonElse.addClass("loading");
            if(nextPageUrl){
                $(window).lazyLoad && $(window).lazyLoad('sleep');
                $.get(nextPageUrl + "&_=" + (new Date().getTime()) + Math.random(), function(html) {
                    var tmp = $('<div></div>').html(html);

                    productsList.append(tmp.find('.js-preview-products').html());
                    paginationWrap.html(tmp.find('.js-paging-nav').html());
                    buttonElse.removeClass("loading");

                    productsPreviewList.images($('#product-list').find('.js-preview-products'));

                    if(isLazyLoad){
                        _this.lazyloading();
                    }

                    if (!!(history.pushState && history.state !== undefined)){
                        window.history.pushState({}, '', nextPageUrl);
                    }

                    if (typeof $.autobadgeFrontend !== 'undefined') {
                        $.autobadgeFrontend.reinit();
                    }

                });
            }
        });
    },
    updateProductList: function(form, $isPushState, url){
        var _this = this;

        if(!url){
            url = window.location.search;
        }

        if(form && form.length){
            url = filterForm.buildUrl(form.serializeArray());

            filterForm.addFilterSubcategory(form.serializeArray());
        }

        url = removeParam("_", url);

        var isNoParams = url.indexOf('?') < 0,
            getUrl = url,
            getTime = (new Date().getTime()) + Math.random();

        if(isNoParams){
            getUrl = getUrl + '?_=' + getTime;
        }else{
            getUrl = getUrl + '&_='+ getTime;
        }

        $(window).lazyLoad && $(window).lazyLoad('sleep');
        $('#product-list').html('<div><i class="js-loading products-loading bs-color"></i></div>');

        $.get(getUrl, function(html) {
            var tmp = $('<div></div>').html(html);
            $('#product-list').html(tmp.find('#product-list').html());
            productsPreviewList.images($('#product-list').find('.js-preview-products'));
            if (!!(history.pushState && history.state !== undefined) && $isPushState) {
                if(url == "?" || url == "") url = window.location.pathname;

                window.history.pushState({}, '', url);
            }
            $(window).lazyLoad && $(window).lazyLoad('reload');

            if (typeof $.autobadgeFrontend !== 'undefined') {
                $.autobadgeFrontend.reinit();
            }

            filterForm.smartfilterRefreshHideParams();
        });
    },
    setCountPerPageProducts: function () {
        $('body').on("click", '.js-select-per-page', function () {
            var $this = $(this),
                count = $this.data("count"),
                cookieOptions = {expires: 7, path: '/'};

            $.cookie('products_per_page', count, cookieOptions);
            var href = window.location.href.replace(/(page=)\w+/g, 'page=1');
            window.location.href = href;
        });

    },
};

$(function(){
    reviews.init();
    brands.init();
    productList.init();

    if(globalThemeSettings.isFilters){
        filterForm.init();
        mobileFilter.init();
        if(!globalThemeSettings.isMobile){
            horizontalFilter.init();
            sidebarFilter.init();
        }
    }
});

/* plugin seofilters */
$(function(){
    if(typeof window.seofilterOnFilterSuccessCallbacks === 'undefined'){
        window.seofilterOnFilterSuccessCallbacks = []
    }
    window.seofilterOnFilterSuccessCallbacks.push(
        function(){
            moreText.addLink();
            filterForm.refreshStylerAllInputs();
        }
    )
    if(globalThemeSettings.isSeoFilterBlockedOPtions){
        window.seofilterOnFilterSuccessCallbacks.push(
            function(){
                filterForm.seoFilterDisabledParams();
            }
        )
        filterForm.seoFilterDisabledParams();
    }
});
