(function ($) {
    $.form_appearance = {
        activeTheme: 'new',
        usedTheme: 0,
        styles: {
            default: {
                header: {
                    logo: 'default',
                    logo_src: '',
                    text: $__('Application Web-forms')
                },
                fonts: {
                    header: {
                        family: 'Helvetica,sans-serif',
                        size: '14px',
                        style: 'normal',
                        color: '#000'
                    },
                    title: {
                        family: 'Helvetica,sans-serif',
                        size: '22px',
                        style: 'normal',
                        color: '#000'
                    },
                    description: {
                        family: 'Helvetica,sans-serif',
                        size: '14px',
                        style: 'normal',
                        color: '#000'
                    },
                    ftitle: {
                        family: 'Helvetica,sans-serif',
                        size: '14px',
                        style: 'bold',
                        color: '#000'
                    },
                    ftext: {
                        family: 'Helvetica,sans-serif',
                        size: '14px',
                        style: 'normal',
                        color: '#000'
                    },
                    fdesc: {
                        family: 'Helvetica,sans-serif',
                        size: '14px',
                        style: 'normal',
                        color: '#000000'
                    },
                    presuf: {
                        family: 'Helvetica,sans-serif',
                        size: '14px',
                        style: 'normal',
                        color: '#000'
                    },
                    error: {
                        family: 'Helvetica,sans-serif',
                        size: '14px',
                        style: 'italic',
                        color: '#ff0000'
                    },
                    button: {
                        family: 'Helvetica,sans-serif',
                        size: '14px',
                        style: 'normal',
                        color: '#000'
                    },
                    tgst: {
                        family: 'Helvetica,sans-serif',
                        size: '14px',
                        style: 'bold',
                        color: '#000'
                    },
                    tgcol: {
                        family: 'Helvetica,sans-serif',
                        size: '14px',
                        style: 'normal',
                        color: '#000000'
                    }
                },
                background: {
                    page: {
                        pattern: 'none'
                    },
                    form: {
                        pattern: 'color',
                        color: '#fff',
                        pattern_src: ''
                    },
                    header: {
                        pattern: 'color',
                        color: '#eee',
                        pattern_src: ''
                    },
                    title: {
                        pattern: 'none'
                    },
                    formdesk: {
                        pattern: 'none'
                    },
                    field: {
                        pattern: 'none'
                    },
                    fdesc: {
                        pattern: 'none'
                    },
                    fname: {
                        pattern: 'none'
                    },
                    hover_highlight: {
                        pattern: 'none'
                    },
                    button_block: {
                        pattern: 'none'
                    },
                    button: {
                        pattern: 'color',
                        color: '#dddddd'
                    },
                    button_hover: {
                        pattern: 'color',
                        color: '#eee'
                    },
                    errors: {
                        pattern: 'color',
                        color: '#ffffff'
                    },
                    field_err_text: {
                        pattern: 'color',
                        color: '#ffcccc'
                    },
                    ftext: {
                        pattern: 'color',
                        color: '#ffffff'
                    },
                    ftextfoc: {
                        pattern: 'color',
                        color: '#ffffff'
                    }
                },
                borders: {
                    form: {
                        width: '1px',
                        style: 'solid',
                        color: '#ccc',
                        radius: 0
                    },
                    fields: {
                        width: '1px',
                        style: 'solid',
                        color: '#cccccc',
                        radius: 0
                    },
                    fdesc: {
                        width: '0',
                        style: 'solid',
                        color: '#000000',
                        radius: 0
                    },
                    fname: {
                        width: '0',
                        style: 'solid',
                        color: '#000000',
                        radius: 0
                    },
                    frows: {
                        width: '0',
                        style: 'solid',
                        color: '#000',
                        radius: 0
                    },
                    ffocus: {
                        width: '1px',
                        style: 'solid',
                        color: '#99ccff',
                        radius: 0
                    },
                    errors: {
                        width: '1px',
                        style: 'solid',
                        color: '#ff0000',
                        radius: 0
                    },
                    field_err_text: {
                        width: '1px',
                        style: 'solid',
                        color: '#ff0000',
                        radius: 0
                    },
                    button: {
                        width: '1px',
                        style: 'solid',
                        color: '#aaaaaa',
                        radius: 0
                    }
                },
                shadows: {
                    form: {
                        'h-shadow': 0,
                        'v-shadow': 0,
                        blur: 5,
                        spread: 0,
                        color: '#cccccc',
                        inset: 0
                    },
                    header: {
                        'h-shadow': 0,
                        'v-shadow': 0,
                        blur: 0,
                        spread: 0,
                        color: '#000000',
                        inset: 0
                    },
                    fields: {
                        'h-shadow': 0,
                        'v-shadow': 0,
                        blur: 0,
                        spread: 0,
                        color: '#000000',
                        inset: 0
                    },
                    fdesc: {
                        'h-shadow': 0,
                        'v-shadow': 0,
                        blur: 0,
                        spread: 0,
                        color: '#000000',
                        inset: 0
                    },
                    fname: {
                        'h-shadow': 0,
                        'v-shadow': 0,
                        blur: 0,
                        spread: 0,
                        color: '#000000',
                        inset: 0
                    },
                    ffocus: {
                        'h-shadow': 0,
                        'v-shadow': 0,
                        blur: 5,
                        spread: 0,
                        color: '#99ccff',
                        inset: 0
                    },
                    button: {
                        'h-shadow': 0,
                        'v-shadow': 0,
                        blur: 0,
                        spread: 0,
                        color: '#000000',
                        inset: 0
                    },
                    errors: {
                        'h-shadow': 0,
                        'v-shadow': 0,
                        blur: 0,
                        spread: 0,
                        color: '#000',
                        inset: 0
                    }
                },
                sizes: {
                    form: {
                        size: 'adaptive',
                        width: '700',
                        height: ''
                    },
                    header: {
                        width: '',
                        height: '35'
                    },
                    button_block: {
                        width: '',
                        height: ''
                    }
                },
                marginpadding: {
                    form: {
                        'margin-top': 0,
                        'margin-right': 'auto',
                        'margin-bottom': 0,
                        'margin-left': 'auto',
                        'padding-top': 0,
                        'padding-right': 0,
                        'padding-bottom': 0,
                        'padding-left': 0
                    },
                    header: {
                        'padding-top': 5,
                        'padding-right': 5,
                        'padding-bottom': 5,
                        'padding-left': 10,
                        'margin-top': 0,
                        'margin-right': 0,
                        'margin-bottom': 0,
                        'margin-left': 0
                    },
                    fields: {
                        'margin-top': 0,
                        'margin-right': 0,
                        'margin-bottom': 0,
                        'margin-left': 0,
                        'padding-top': 1,
                        'padding-right': 1,
                        'padding-bottom': 1,
                        'padding-left': 1
                    },
                    frows: {
                        'margin-top': 0,
                        'margin-right': 0,
                        'margin-bottom': 5,
                        'margin-left': 0,
                        'padding-top': 10,
                        'padding-right': 0,
                        'padding-bottom': 10,
                        'padding-left': 0
                    },
                    fdesc: {
                        'margin-top': 5,
                        'margin-right': 0,
                        'margin-bottom': 0,
                        'margin-left': 0,
                        'padding-top': 0,
                        'padding-right': 0,
                        'padding-bottom': 0,
                        'padding-left': 0
                    },
                    fname: {
                        'margin-top': 0,
                        'margin-right': 0,
                        'margin-bottom': 0,
                        'margin-left': 0,
                        'padding-top': 0,
                        'padding-right': 5,
                        'padding-bottom': 0,
                        'padding-left': 5
                    },
                    errors: {
                        'margin-top': 10,
                        'margin-right': 0,
                        'margin-bottom': 5,
                        'margin-left': 0,
                        'padding-top': 5,
                        'padding-right': 5,
                        'padding-bottom': 5,
                        'padding-left': 5
                    },
                    button: {
                        'margin-top': 0,
                        'margin-right': 0,
                        'margin-bottom': 0,
                        'margin-left': 0,
                        'padding-top': 2,
                        'padding-right': 6,
                        'padding-bottom': 2,
                        'padding-left': 6
                    },
                    button_block: {
                        'margin-top': 0,
                        'margin-right': 0,
                        'margin-bottom': 0,
                        'margin-left': 0,
                        'padding-top': 10,
                        'padding-right': 10,
                        'padding-bottom': 10,
                        'padding-left': 10
                    }
                },
                alignment: {
                    header: 'left',
                    title: 'center',
                    fdesc: 'justify',
                    fname: 'left',
                    button: 'left'
                },
                additional: {
                    table_grid: {
                        header: {
                            color: '#e6e6e6'
                        },
                        even: {
                            color: '#fff'
                        },
                        odd: {
                            color: '#f5f5f5'
                        },
                        highlight: {
                            color: '#ffffcf'
                        }
                    },
                    rating: {
                        color: '#ffd700'
                    },
                    rangeslider: {
                        line: {
                            color: '#ed5565'
                        },
                        inactive_line: {
                            color: '#e1e4e9'
                        },
                        circle: {
                            color: '#da4453'
                        },
                        value: {
                            color: '#fff'
                        },
                        value_block: {
                            color: '#ed5565'
                        },
                        inactive_value: {
                            color: '#999'
                        },
                        inactive_value_block: {
                            color: '#e1e4e9'
                        },
                        grid: {
                            color: '#e1e4e9'
                        },
                        grid_value: {
                            color: '#999'
                        }
                    }
                }
            }
        },
        initPageAppearance: function () {
            // Выбор цвета
            $("#color-picker-column .colorpicker").simpleColor({
                boxWidth: 54,
                boxHeight: 54,
                cellWidth: 16,
                cellHeight: 16,
                colorChooser: $("#color-picker-column .color-chooser"),
                chooserCSS: {
                    'display': 'inline-block',
                    'height': '115px',
                    'left': 0,
                    'margin': 0,
                    'overflow-y': 'scroll',
                    'position': 'relative',
                    'top': 0,
                    'vertical-align': 'bottom',
                    'width': '188px'
                },
                onSelect: function (hex, element) {
                    element.closest("#color-picker-column").find(".colorpicker_input").val('#' + hex);
                    $.form_appearance.setColor('#' + hex);
                }
            });

            $("#important-rule").change(function () {
                $('#themes-menu option[data-id="' + $.form_appearance.activeTheme + '"]').attr('data-important', $(this).prop('checked') ? 1 : 0);
            });

            this.selectThemes($("#themes-menu")[0]);
        },
        // Установление цвета для поля
        setColor: function (color) {
            var that = this;
            var property = $("#properties-menu").val();
            var field;
            if (property == 'fonts') {
                field = $("#fonts-menu").val();
            } else if (property == 'background') {
                field = $("#bg-menu").val();
            } else if (property == 'borders') {
                field = $("#border-menu").val();
            } else if (property == 'shadows') {
                field = $("#shadow-menu").val();
            } else if (property == 'additional') {
                field = $("#additional-menu").val();
            }
            if (property == 'additional' && (field == 'table_grid' || field == 'rangeslider')) {
                this.styles[this.activeTheme][property][field][$("#additional-" + (field == 'table_grid' ? "tg" : "rs") + "-menu").val()]['color'] = color;
            } else {
                this.styles[this.activeTheme][property][field]['color'] = color;
            }
            this['update' + that.capitalizeFirstLetter(property)](this.styles[this.activeTheme][property]);
        },
        // Инициализация темы дизайна
        initThemeDesign: function (theme) {
            if (theme == 'new') {
                this.styles['new'] = $.extend(true, {}, this.styles['default']);
            }

            // Создаем блок стилей для темы. Необходимо для настроек hover
            var cssBlock = $(".t" + theme + "-theme-css");
            if (!cssBlock.length) {
                $("body").append("<div class='t" + theme + "-theme-css'>");
            }
            this.updateHeader(this.styles[theme]['header']);
            this.updateFonts(this.styles[theme]['fonts']);
            this.updateBackground(this.styles[theme]['background']);
            this.updateBorders(this.styles[theme]['borders']);
            this.updateShadows(this.styles[theme]['shadows']);
            this.updateSizes(this.styles[theme]['sizes']);
            this.updateMarginpadding(this.styles[theme]['marginpadding']);
            this.updateAlignment(this.styles[theme]['alignment']);
            this.updateAdditional(this.styles[theme]['additional']);
        },
        // Выбор тем дизайна
        selectThemes: function (select) {
            this.activeTheme = select.value;
            this.initThemeDesign(select.value);
            $.each(this.styles, function (i, v) {
                $(".multiform-wrap").removeClass("t" + i + "-theme");
            });
            if (select.value !== 'new') {
                $(".appearance-tab .f-not-new").removeClass("hidden");
                $(".multiform-wrap").removeClass("default-theme").addClass("t" + this.activeTheme + "-theme").attr("data-theme", "t" + this.activeTheme + "-theme");
                $("#important-rule").prop("checked", parseInt($(select).find('option[data-id="' + $.form_appearance.activeTheme + '"]').attr('data-important')));
                if ($.form_appearance.usedTheme == this.activeTheme) {
                    $(".appearance-tab .f-use-theme").addClass("hidden");
                    $(".appearance-tab .f-theme-used").removeClass("hidden");
                } else {
                    $(".appearance-tab .f-use-theme").removeClass("hidden");
                    $(".appearance-tab .f-theme-used").addClass("hidden");
                }
            } else {
                $(".appearance-tab .f-not-new").addClass("hidden");
                $(".appearance-tab .f-theme-used").addClass("hidden");
                $(".multiform-wrap").addClass("default-theme").removeClass($(".multiform-wrap").attr("data-theme"));
                $("#important-rule").prop("checked", false);
            }
            $("#columns").removeClass();
            $("#properties-menu option:selected").prop("selected", false);
        },
        // Выбор свойств
        selectProperties: function (select) {
            var val = select.value;
            $("#columns").removeClass().addClass("show" + this.capitalizeFirstLetter(val));
            if (typeof this.styles[this.activeTheme][val] == 'undefined') {
                this.styles[this.activeTheme][val] = {};
            }
            val == 'buttons' && this.setButtons();
            this.resetColumns();
        },
        // Сброс выделения в некоторых столбцах
        resetColumns: function () {
            $("#header-menu option:selected, #fonts-menu option:selected, #bg-menu option:selected, #border-menu option:selected, #shadow-menu option:selected, #sizes-menu option:selected, #marginpadding-menu option:selected, #alignment-menu option:selected, #additional-menu option:selected").prop('selected', false);
        },
        /* Header */
        selectHeader: function (select) {
            var val = select.value;
            if (val !== 'text') {
                this.styles[this.activeTheme]['header']['logo'] = val;
            }
            switch (val) {
                case 'custom':
                    $("#columns").removeClass("showHeaderText").addClass("showCustomLogo showHeaderEdit");
                    if (typeof this.styles[this.activeTheme]['header']['logo_src'] !== 'undefined') {
                        $("#logo-custom input[type='text']").val(this.styles[this.activeTheme]['header']['logo_src']);
                    } else {
                        var text = $("#logo-custom").find(":text").val();
                        if (text !== 'http://' && text !== '') {
                            this.styles[this.activeTheme]['header']['logo_src'] = text;
                        }
                    }
                    break;
                case 'text':
                    $("#columns").removeClass("showCustomLogo").addClass("showHeaderText showHeaderEdit");
                    if (typeof this.styles[this.activeTheme]['header']['text'] !== 'undefined') {
                        $("#logo-text input").val(this.styles[this.activeTheme]['header']['text']);
                    }
                    break;
                case 'none':
                    $("#columns").removeClass("showCustomLogo showHeaderText showHeaderEdit");
                    this.styles[this.activeTheme]['header']['logo_src'] = '';
                    break;
                case 'hide':
                    $("#columns").removeClass("showCustomLogo showHeaderText showHeaderEdit");
                    this.styles[this.activeTheme]['header']['logo_src'] = '';
                    break;
                case 'default':
                    $("#columns").removeClass("showCustomLogo showHeaderText showHeaderEdit");
                    this.styles[this.activeTheme]['header']['logo_src'] = $(select).find(":selected").data("src");
                    break;
            }
            this.updateHeader(this.styles[this.activeTheme]['header']);
        },
        updateHeader: function (styles) {
            if (styles['logo'] == 'hide') {
                $(".multiform-wrap .multiform-header").hide();
            } else {
                if (styles['logo'] == 'custom' || styles['logo'] == 'default') {
                    $(".multiform-wrap .multiform-header img").attr("src", styles['logo'] == 'default' ? $("#default-logo").data("src") : (styles['logo_src'] !== '' && styles['logo_src'] !== 'http://' ? styles['logo_src'] : $("#default-logo").data("src")));
                }
                if (styles['logo'] == 'none') {
                    $(".multiform-wrap .multiform-header img").hide();
                } else {
                    $(".multiform-wrap .multiform-header img").show();
                }
                if (typeof styles['text'] !== 'undefined') {
                    $(".multiform-wrap .multiform-header span").text(styles['text']);
                }
                $(".multiform-wrap .multiform-header").show();
            }
            //$("#header-menu").val(styles['logo']);
        },
        /* Fonts */
        selectFonts: function (select) {
            this.styles[this.activeTheme]['fonts'][$("#fonts-menu").val()][select.getAttribute('name')] = select.value;
            this.updateFonts(this.styles[this.activeTheme]['fonts']);
        },
        setFonts: function (select) {
            if (typeof this.styles[this.activeTheme]['fonts'][select.value]['family'] !== 'undefined') {
                $("#fonts-fam-menu").val(this.styles[this.activeTheme]['fonts'][select.value]['family']);
            } else {
                $("#fonts-fam-menu option:selected").prop("selected", false);
            }
            if (typeof this.styles[this.activeTheme]['fonts'][select.value]['style'] !== 'undefined') {
                $("#fonts-st-menu").val(this.styles[this.activeTheme]['fonts'][select.value]['style']);
            } else {
                $("#fonts-st-menu option:selected").prop("selected", false);
            }
            if (typeof this.styles[this.activeTheme]['fonts'][select.value]['size'] !== 'undefined') {
                $("#fonts-size-menu").val(this.styles[this.activeTheme]['fonts'][select.value]['size']);
            } else {
                $("#fonts-size-menu option:selected").prop("selected", false);
            }
            if (typeof this.styles[this.activeTheme]['fonts'][select.value]['color'] !== 'undefined') {
                $(".colorpicker_input").val(this.styles[this.activeTheme]['fonts'][select.value]['color']);
                $("#color-picker-column .colorpicker").setColor(this.styles[this.activeTheme]['fonts'][select.value]['color']);
            } else {
                $("#color-picker-column .colorpicker").setColor('#000000');
                $(".colorpicker_input").val('#000000');
            }
            this.updateFonts(this.styles[this.activeTheme]['fonts']);
        },
        updateFonts: function (styles) {
            $.each(styles, function (elem, style) {
                var field;
                if (elem == 'header') {
                    field = $(".multiform-header");
                } else if (elem == 'title') {
                    field = $(".multiform-title");
                } else if (elem == 'description') {
                    field = $(".multiform-form-description");
                } else if (elem == 'ftitle') {
                    field = $(".multiform-gap-name");
                } else if (elem == 'ftext') {
                    field = $(".multiform-gap-value input[type='text'], .multiform-gap-value textarea, .multiform-gap-value input[type='email'], .multiform-gap-value select, .multiform-gap-value .multiform-formula span");
                } else if (elem == 'fdesc') {
                    field = $(".multiform-gap-description");
                } else if (elem == 'presuf') {
                    field = $(".multiform-gap-value .prefix, .multiform-gap-value .suffix");
                } else if (elem == 'error') {
                    field = $(".multiform-wrap .errormsg, .multiform-mask-error");
                } else if (elem == 'button') {
                    field = $(".mf-button");
                } else if (elem == 'title') {
                    field = $(".multiform-title");
                } else if (elem == 'tgst') {
                    field = $(".multiform-grid th");
                } else if (elem == 'tgcol') {
                    field = $(".multiform-grid thead td");
                }
                style.family && field.css('font-family', style.family);
                if (style.style) {
                    style.style == 'bold' && (field.css('font-weight', style.style), field.css('font-style', 'normal'));
                    style.style == 'bolditalic' && (field.css('font-weight', 'bold'), field.css('font-style', 'italic'));
                    (style.style !== 'bold' && style.style !== 'bolditalic') && (field.css('font-weight', 400), field.css('font-style', style.style));
                }
                style.size && field.css('font-size', style.size);
                style.color && field.css('color', style.color), $("a", field).css('color', style.color);
            });
        },
        /* Background */
        selectBackground: function (select) {
            var option = $(select).find(":selected");
            $("#columns").addClass("showBacks");
            if (typeof option.data('class') !== 'undefined') {
                $("#columns").removeClass('showPatterns showPatternAll showPatternCus showPicker').addClass(option.data('class'));
            } else {
                $("#columns").removeClass('showPatterns showPatternAll showPatternCus showPicker');
            }
            if (typeof this.styles[this.activeTheme]['background'][select.value] == 'undefined') {
                this.styles[this.activeTheme]['background'][select.value] = {};
            }
            this.setBackground(select);
        },
        setBackground: function (select) {
            if (typeof this.styles[this.activeTheme]['background'][select.value]['pattern'] !== 'undefined') {
                $("#bg-pattern-menu").val(this.styles[this.activeTheme]['background'][select.value]['pattern']);
                $("#bg-custom-column input[type='text']").removeClass("inited");
                if (this.styles[this.activeTheme]['background'][select.value]['pattern'] == 'color' && typeof this.styles[this.activeTheme]['background'][select.value]['color'] !== 'undefined') {
                    $(".colorpicker_input").val(this.styles[this.activeTheme]['background'][select.value]['color']);
                    $("#color-picker-column .colorpicker").setColor(this.styles[this.activeTheme]['background'][select.value]['color']);
                    $("#columns").addClass("showPicker");
                } else if (this.styles[this.activeTheme]['background'][select.value]['pattern'] == 'pattern') {
                    $("#columns").addClass("showPatternAll").removeClass("showPicker");
                    $("#bg-patterns-column li").removeClass();
                    $("#bg-patterns-column li img[src='" + this.styles[this.activeTheme]['background'][select.value]['pattern_src'] + "']").closest("li").addClass("selected");
                } else {
                    this.styles[this.activeTheme]['background'][select.value]['pattern'] !== 'none' && $("#bg-pattern-menu option:selected").prop("selected", false);
                    $("#color-picker-column .colorpicker").setColor('#000000');
                    $(".colorpicker_input").val('#000000');
                }
            } else {
                $("#bg-pattern-menu option:selected").prop("selected", false);
            }
        },
        selectPattern: function (select) {
            var option = $(select).find(":selected");
            if (typeof option.data('class') !== 'undefined') {
                $("#columns").removeClass("showPatternAll showPatternCus showPicker").addClass(option.data('class'));
            } else {
                $("#columns").removeClass('showPatternAll showPatternCus showPicker');
            }
            this.styles[this.activeTheme]['background'][$("#bg-menu").val()][select.getAttribute('name')] = select.value;
            if (select.value == 'none') {
                delete this.styles[this.activeTheme]['background'][$("#bg-menu").val()]['pattern_src'];
                delete this.styles[this.activeTheme]['background'][$("#bg-menu").val()]['color'];
                $("#bg-patterns-column li").removeClass();
            } else {
                if (typeof this.styles[this.activeTheme]['background'][$("#bg-menu").val()]['pattern_src'] == 'undefined') {
                    $("#bg-patterns-column li").removeClass();
                } else if (!$("#bg-custom-column input[type='text']").hasClass("inited")) {
                    $("#bg-custom-column input[type='text']").val(this.styles[this.activeTheme]['background'][$("#bg-menu").val()]['pattern_src']);
                    $("#bg-custom-column input[type='text']").addClass("inited");
                }
                if ($("#bg-custom-column input[type='text']").val() !== 'http://' && $("#bg-custom-column input[type='text']").val() !== '' && select.value == 'custom') {
                    this.styles[this.activeTheme]['background'][$("#bg-menu").val()]['pattern_src'] = $("#bg-custom-column input[type='text']").val();
                }
            }
            this.updateBackground(this.styles[this.activeTheme]['background']);
        },
        selectPatternAction: function (btn, id) {
            this.styles[this.activeTheme]['background'][$("#bg-menu").val()]['pattern_src'] = id !== '0' ? btn.find("img").attr("src") : '';
            btn.closest("li").addClass("selected").siblings().removeClass();
            this.updateBackground(this.styles[this.activeTheme]['background']);
        },
        updateBackground: function (styles) {
            $.each(styles, function (elem, style) {
                var field;
                if (elem == 'header') {
                    field = $(".multiform-header");
                } else if (elem == 'page') {
                    field = $(".interactive .body");
                } else if (elem == 'form') {
                    field = $(".multiform-wrap");
                } else if (elem == 'field') {
                    field = $(".multiform-gap-field");
                } else if (elem == 'fname') {
                    field = $(".multiform-gap-name");
                } else if (elem == 'field_err_text') {
                    field = $(".multiform-temp-file.error");
                } else if (elem == 'ftext') {
                    field = $(".multiform-gap-value input[type='text'], .multiform-gap-value textarea, .multiform-gap-value input[type='email'], .multiform-gap-value select, .multiform-gap-value .multiform-formula span").not(".multiform-temp-file");
                } else if (elem == 'fdesc') {
                    field = $(".multiform-gap-description");
                } else if (elem == 'button') {
                    field = $(".mf-button");
                } else if (elem == 'button_block') {
                    field = $(".multiform-submit");
                } else if (elem == 'errors') {
                    field = $(".multiform-wrap .errormsg, .multiform-mask-error");
                } else if (elem == 'formdesk') {
                    field = $(".multiform-form-description");
                } else if (elem == 'title') {
                    field = $(".multiform-title");
                }
                if (elem == 'hover_highlight') {
                    $(".t" + $.form_appearance.activeTheme + "-theme-css-" + elem).remove();
                    if (style.pattern !== 'none' && style.color) {
                        var style = "<style>." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-field:hover { background-color: " + style.color + " !important; }</style>";
                        $(".t" + $.form_appearance.activeTheme + "-theme-css").append("<div class='t" + $.form_appearance.activeTheme + "-theme-css-" + elem + "'>" + style + "</div>");
                    }
                } else if (elem == 'button_hover') {
                    $(".t" + $.form_appearance.activeTheme + "-theme-css-" + elem).remove();
                    var style = "<style>." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .mf-button:hover { " + (style.pattern == 'none' ? 'background: none !important;' : (style.color ? "background-color: " + style.color + " !important;" : (style.color == '' ? 'background: transparent !important;' : ""))) + " }</style>";
                    $(".t" + $.form_appearance.activeTheme + "-theme-css").append("<div class='t" + $.form_appearance.activeTheme + "-theme-css-" + elem + "'>" + style + "</div>");
                } else if (elem == 'ftextfoc') {
                    $(".t" + $.form_appearance.activeTheme + "-theme-css-" + elem).remove();
                    var style = "<style>." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='text']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value textarea:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='email']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value select:focus { " + (style.pattern == 'none' ? 'background: none !important;' : (style.color ? "background-color: " + style.color + " !important;" : (style.color == '' ? 'background: transparent !important;' : ""))) + " }</style>";
                    $(".t" + $.form_appearance.activeTheme + "-theme-css").append("<div class='t" + $.form_appearance.activeTheme + "-theme-css-" + elem + "'>" + style + "</div>");
                } else {
                    style.color && field.css('background-color', style.color);
                    style.pattern_src && field.css('background-image', 'url("' + style.pattern_src + '")');
                    style.pattern == 'none' && field.css('background', 'none');
                    style.color == '' && field.css('background-color', 'transparent');
                    style.pattern_src == '' && field.css('background-image', 'none');
                }
            });
        },
        /* Borders */
        selectBorders: function (select) {
            this.styles[this.activeTheme]['borders'][$("#border-menu").val()][select.getAttribute('name')] = select.value;
            this.updateBorders(this.styles[this.activeTheme]['borders']);
        },
        setBorders: function (select) {
            if (typeof this.styles[this.activeTheme]['borders'][select.value] == 'undefined') {
                this.styles[this.activeTheme]['borders'][select.value] = {};
            }
            if (typeof this.styles[this.activeTheme]['borders'][select.value]['width'] !== 'undefined') {
                $("#border-thick-menu").val(this.styles[this.activeTheme]['borders'][select.value]['width']);
            } else {
                $("#border-thick-menu option:selected").prop("selected", false);
            }
            if (typeof this.styles[this.activeTheme]['borders'][select.value]['style'] !== 'undefined') {
                $("#border-style-menu").val(this.styles[this.activeTheme]['borders'][select.value]['style']);
            } else {
                $("#border-style-menu option:selected").prop("selected", false);
            }
            if (typeof this.styles[this.activeTheme]['borders'][select.value]['radius'] !== 'undefined') {
                $("#border-radius-column input").val(this.styles[this.activeTheme]['borders'][select.value]['radius']);
            } else {
                $("#border-radius-column input").val('');
            }

            if (typeof this.styles[this.activeTheme]['borders'][select.value]['color'] !== 'undefined') {
                $(".colorpicker_input").val(this.styles[this.activeTheme]['borders'][select.value]['color']);
                $("#color-picker-column .colorpicker").setColor(this.styles[this.activeTheme]['borders'][select.value]['color']);
            } else {
                $("#color-picker-column .colorpicker").setColor('#000000');
                $(".colorpicker_input").val('#000');
            }
            this.updateBorders(this.styles[this.activeTheme]['borders']);
        },
        updateBorders: function (styles) {
            $.each(styles, function (elem, style) {
                var field;
                if (elem == 'form') {
                    field = $(".multiform-wrap");
                } else if (elem == 'fields') {
                    field = $(".multiform-gap-value input[type='text'], .multiform-gap-value textarea, .multiform-gap-value input[type='email'], .multiform-gap-value select, .multiform-gap-value .multiform-formula span").not(".multiform-temp-file.error");
                } else if (elem == 'fdesc') {
                    field = $(".multiform-gap-description");
                } else if (elem == 'fname') {
                    field = $(".multiform-gap-name");
                } else if (elem == 'frows') {
                    field = $(".multiform-gap-field");
                } else if (elem == 'field_err_text') {
                    field = $(".multiform-temp-file.error");
                } else if (elem == 'errors') {
                    field = $(".multiform-wrap .errormsg, .multiform-mask-error");
                } else if (elem == 'button') {
                    field = $(".mf-button");
                }
                if (elem == 'ffocus') {
                    $(".t" + $.form_appearance.activeTheme + "-theme-css-border-" + elem).remove();
                    var styles = "<style>";
                    if (typeof style.width !== 'undefined') {
                        styles += "." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='text']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value textarea:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='email']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value select:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value .multiform-formula span:focus { border-width: " + style.width + " !important; }";
                    }
                    if (typeof style.style !== 'undefined' && typeof style.width !== 'undefined' && style.width !== '0') {
                        styles += "." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='text']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value textarea:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='email']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value select:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value .multiform-formula span:focus { border-style: " + style.style + " !important; }";
                    }
                    if (typeof style.color !== 'undefined' && typeof style.width !== 'undefined' && style.width !== '0') {
                        styles += "." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='text']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value textarea:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='email']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value select:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value .multiform-formula span:focus { border-color: " + style.color + " !important; }";
                    }
                    if (typeof style.radius !== 'undefined') {
                        styles += "." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='text']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value textarea:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='email']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value select:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value .multiform-formula span:focus { -webkit-border-radius: " + style.radius + "px !important; -moz-border-radius: " + style.radius + "px !important; border-radius: " + style.radius + "px !important; }";
                    }
                    styles += "</style>";
                    $(".t" + $.form_appearance.activeTheme + "-theme-css").append("<div class='t" + $.form_appearance.activeTheme + "-theme-css-border-" + elem + "'>" + styles + "</div>");
                } else {
                    typeof style.width !== 'undefined' && field.css('border-width', style.width);
                    typeof style.style !== 'undefined' && field.css('border-style', style.style);
                    typeof style.color !== 'undefined' && field.css('border-color', style.color);
                    typeof style.radius !== 'undefined' && field.css('border-radius', style.radius + 'px');
                }
            });
        },
        /* Shadows */
        selectShadows: function (input) {
            this.styles[this.activeTheme]['shadows'][$("#shadow-menu").val()][input.getAttribute('name')] = (input.getAttribute('name') == 'inset' ? ($(input).prop("checked") ? 1 : 0) : input.value);
            this.updateShadows(this.styles[this.activeTheme]['shadows']);
        },
        setShadows: function (select) {
            if (typeof this.styles[this.activeTheme]['shadows'][select.value] == 'undefined') {
                this.styles[this.activeTheme]['shadows'][select.value] = {};
            }
            if (typeof this.styles[this.activeTheme]['shadows'][select.value]['h-shadow'] !== 'undefined') {
                $("#shadows-h-shadow").val(this.styles[this.activeTheme]['shadows'][select.value]['h-shadow']);
            } else {
                $("#shadows-h-shadow").val('');
            }
            if (typeof this.styles[this.activeTheme]['shadows'][select.value]['v-shadow'] !== 'undefined') {
                $("#shadows-v-shadow").val(this.styles[this.activeTheme]['shadows'][select.value]['v-shadow']);
            } else {
                $("#shadows-v-shadow").val('');
            }
            if (typeof this.styles[this.activeTheme]['shadows'][select.value]['blur'] !== 'undefined') {
                $("#shadows-blur").val(this.styles[this.activeTheme]['shadows'][select.value]['blur']);
            } else {
                $("#shadows-blur").val('');
            }
            if (typeof this.styles[this.activeTheme]['shadows'][select.value]['spread'] !== 'undefined') {
                $("#shadows-spread").val(this.styles[this.activeTheme]['shadows'][select.value]['spread']);
            } else {
                $("#shadows-spread").val('');
            }
            if (typeof this.styles[this.activeTheme]['shadows'][select.value]['inset'] !== 'undefined') {
                $("#shadows-inset").prop('checked', !!parseInt(this.styles[this.activeTheme]['shadows'][select.value]['inset']));
            } else {
                $("#shadows-inset").prop('checked', false);
            }

            if (typeof this.styles[this.activeTheme]['shadows'][select.value]['color'] !== 'undefined') {
                $(".colorpicker_input").val(this.styles[this.activeTheme]['shadows'][select.value]['color']);
                $("#color-picker-column .colorpicker").setColor(this.styles[this.activeTheme]['shadows'][select.value]['color']);
            } else {
                $("#color-picker-column .colorpicker").setColor('#000000');
                $(".colorpicker_input").val('#000');
            }
            this.updateShadows(this.styles[this.activeTheme]['shadows']);
        },
        updateShadows: function (styles) {
            $.each(styles, function (elem, style) {
                var field;
                if (elem == 'form') {
                    field = $(".multiform-wrap");
                } else if (elem == 'header') {
                    field = $(".multiform-header");
                } else if (elem == 'fields') {
                    field = $(".multiform-gap-value input[type='text'], .multiform-gap-value textarea, .multiform-gap-value input[type='email'], .multiform-gap-value select, .multiform-gap-value .multiform-formula span");
                } else if (elem == 'fdesc') {
                    field = $(".multiform-gap-description");
                } else if (elem == 'fname') {
                    field = $(".multiform-gap-name");
                } else if (elem == 'errors') {
                    field = $(".multiform-wrap .errormsg, .multiform-mask-error");
                } else if (elem == 'button') {
                    field = $(".mf-button");
                }
                if (elem == 'ffocus') {
                    $(".t" + $.form_appearance.activeTheme + "-theme-css-" + elem).remove();
                    var shadow = (typeof style['h-shadow'] !== 'undefined' ? style['h-shadow'] + 'px' : 0) +
                        (typeof style['v-shadow'] !== 'undefined' ? ' ' + style['v-shadow'] + 'px' : ' 0') +
                        (typeof style['blur'] !== 'undefined' ? ' ' + style['blur'] + 'px' : ' 0') +
                        (typeof style['spread'] !== 'undefined' ? ' ' + style['spread'] + 'px' : ' 0') +
                        (typeof style['color'] !== 'undefined' ? ' ' + style['color'] : ' #000000') +
                        (typeof style['inset'] !== 'undefined' && !!parseInt(style['inset']) ? ' inset' : '');
                    var styles = "<style>";
                    styles += "." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='text']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value textarea:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value input[type='email']:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value select:focus, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-gap-value .multiform-formula span:focus { -webkit-box-shadow: " + shadow + " !important; -moz-box-shadow: " + shadow + " !important; box-shadow: " + shadow + " !important; }";
                    styles += "</style>";
                    $(".t" + $.form_appearance.activeTheme + "-theme-css").append("<div class='t" + $.form_appearance.activeTheme + "-theme-css-" + elem + "'>" + styles + "</div>");
                } else {
                    var shadow = (typeof style['h-shadow'] !== 'undefined' ? style['h-shadow'] + 'px' : 0) +
                        (typeof style['v-shadow'] !== 'undefined' ? ' ' + style['v-shadow'] + 'px' : ' 0') +
                        (typeof style['blur'] !== 'undefined' ? ' ' + style['blur'] + 'px' : ' 0') +
                        (typeof style['spread'] !== 'undefined' ? ' ' + style['spread'] + 'px' : ' 0') +
                        (typeof style['color'] !== 'undefined' ? ' ' + style['color'] : ' #000000') +
                        (typeof style['inset'] !== 'undefined' && !!parseInt(style['inset']) ? ' inset' : '');
                    field.css('box-shadow', shadow);
                }
            });
        },
        /* Sizes */
        selectSizes: function (select) {
            var option = $(select).find(":selected");
            $("#columns").addClass("showSize");

            if (typeof option.data('class') !== 'undefined') {
                $("#columns").removeClass('showFormSize showSizeValues hideWidth').addClass(option.data('class'));
            } else {
                $("#columns").removeClass('showFormSize showSizeValues hideWidth');
            }

            if (typeof this.styles[this.activeTheme]['sizes'][select.value] == 'undefined') {
                this.styles[this.activeTheme]['sizes'][select.value] = {};
            }

            this.setSizes(select);
        },
        setSizes: function (select) {
            if (typeof this.styles[this.activeTheme]['sizes'][select.value]['size'] !== 'undefined') {
                $("#sizes-form-menu").val(this.styles[this.activeTheme]['sizes'][select.value]['size']);
                if (typeof this.styles[this.activeTheme]['sizes'][select.value]['width'] !== 'undefined' || typeof this.styles[this.activeTheme]['sizes'][select.value]['height'] !== 'undefined') {
                    $("#columns").addClass("showSizeValues");
                } else {
                    $("#sizes-form-menu option:selected").prop("selected", false);
                }
            }
            if (typeof this.styles[this.activeTheme]['sizes'][select.value]['width'] !== 'undefined') {
                $("#sizes-width-field").val(this.styles[this.activeTheme]['sizes'][select.value]['width']);
            } else {
                $("#sizes-width-field").val('');
            }
            if (typeof this.styles[this.activeTheme]['sizes'][select.value]['height'] !== 'undefined') {
                $("#sizes-height-field").val(this.styles[this.activeTheme]['sizes'][select.value]['height']);
            } else {
                $("#sizes-height-field").val('');
            }
            this.updateSizes(this.styles[this.activeTheme]['sizes']);
        },
        selectSizesForm: function (select) {
            $("#columns").addClass("showSizeValues");
            this.styles[this.activeTheme]['sizes'][$("#sizes-menu").val()]['size'] = select.value;
            this.updateSizes(this.styles[this.activeTheme]['sizes']);
        },
        updateSizes: function (styles) {
            $.each(styles, function (elem, style) {
                var field;
                var width = style.width == '' ? 'auto' : style.width;
                if (elem == 'form') {
                    field = $(".multiform-wrap");
                    if (style.size == 'adaptive') {
                        field.addClass("multiform-adaptive-width");
                        field.css('width', '100%');
                        width && field.css('max-width', width + (width == 'auto' ? '' : 'px'));
                    } else {
                        width && field.css('width', width + (width == 'auto' ? '' : 'px'));
                        field.css('max-width', '');
                        field.removeClass("multiform-adaptive-width");
                    }
                } else if (elem == 'header') {
                    field = $(".multiform-header");
                } else if (elem == 'button_block') {
                    field = $(".multiform-submit");
                } else if (elem == 'button') {
                    field = $(".mf-button");
                }
                (elem !== 'form' && width) && field.css('width', width + (width == 'auto' ? '' : 'px'));
                var height = style.height == '' ? 'auto' : style.height;
                height && field.css('height', height + (height == 'auto' ? '' : 'px'));

            });
        },
        /* Buttons */
        setButtons: function () {
            if (typeof this.styles[this.activeTheme]['buttons']['submit'] !== 'undefined' && typeof this.styles[this.activeTheme]['buttons']['submit']['text'] !== 'undefined') {
                $("#buttons-column input").val(this.styles[this.activeTheme]['buttons']['submit']['text']);
            }
        },
        /* Margin/padding */
        selectMarginpadding: function (select) {
            var val = select.value;
            $("#columns").addClass("showMarpad");

            if (val == 'form') {
                $("#columns").addClass("hidePad");
            } else {
                $("#columns").removeClass("hidePad");
            }
            if (val == 'header') {
                $("#columns").addClass("hideMar");
            } else {
                $("#columns").removeClass("hideMar");
            }

            if (typeof this.styles[this.activeTheme]['marginpadding'][val] == 'undefined') {
                this.styles[this.activeTheme]['marginpadding'][val] = {};
            }
            this.setMarginpadding(select);
        },
        setMarginpadding: function (select) {
            if (typeof this.styles[this.activeTheme]['marginpadding'][select.value]['margin-top'] !== 'undefined') {
                $("#marpad-margin-top").val(this.styles[this.activeTheme]['marginpadding'][select.value]['margin-top']);
            } else {
                $("#marpad-margin-top").val('');
            }
            if (typeof this.styles[this.activeTheme]['marginpadding'][select.value]['margin-right'] !== 'undefined') {
                $("#marpad-margin-right").val(this.styles[this.activeTheme]['marginpadding'][select.value]['margin-right']);
            } else {
                $("#marpad-margin-right").val('');
            }
            if (typeof this.styles[this.activeTheme]['marginpadding'][select.value]['margin-bottom'] !== 'undefined') {
                $("#marpad-margin-bottom").val(this.styles[this.activeTheme]['marginpadding'][select.value]['margin-bottom']);
            } else {
                $("#marpad-margin-bottom").val('');
            }
            if (typeof this.styles[this.activeTheme]['marginpadding'][select.value]['margin-left'] !== 'undefined') {
                $("#marpad-margin-left").val(this.styles[this.activeTheme]['marginpadding'][select.value]['margin-left']);
            } else {
                $("#marpad-margin-left").val('');
            }
            if (typeof this.styles[this.activeTheme]['marginpadding'][select.value]['padding-top'] !== 'undefined') {
                $("#marpad-padding-top").val(this.styles[this.activeTheme]['marginpadding'][select.value]['padding-top']);
            } else {
                $("#marpad-padding-top").val('');
            }
            if (typeof this.styles[this.activeTheme]['marginpadding'][select.value]['padding-right'] !== 'undefined') {
                $("#marpad-padding-right").val(this.styles[this.activeTheme]['marginpadding'][select.value]['padding-right']);
            } else {
                $("#marpad-padding-right").val('');
            }
            if (typeof this.styles[this.activeTheme]['marginpadding'][select.value]['padding-bottom'] !== 'undefined') {
                $("#marpad-padding-bottom").val(this.styles[this.activeTheme]['marginpadding'][select.value]['padding-bottom']);
            } else {
                $("#marpad-padding-bottom").val('');
            }
            if (typeof this.styles[this.activeTheme]['marginpadding'][select.value]['padding-left'] !== 'undefined') {
                $("#marpad-padding-left").val(this.styles[this.activeTheme]['marginpadding'][select.value]['padding-left']);
            } else {
                $("#marpad-padding-left").val('');
            }
            this.updateMarginpadding(this.styles[this.activeTheme]['marginpadding']);
        },
        selectMarPadValues: function (input) {
            this.styles[this.activeTheme]['marginpadding'][$("#marginpadding-menu").val()][input.getAttribute('name')] = input.value;
            this.updateMarginpadding(this.styles[this.activeTheme]['marginpadding']);
        },
        updateMarginpadding: function (styles) {
            $.each(styles, function (elem, style) {
                var field;
                if (elem == 'form') {
                    field = $(".multiform-wrap");
                } else if (elem == 'header') {
                    field = $(".multiform-header");
                } else if (elem == 'fields') {
                    field = $(".multiform-gap-value input[type='text'], .multiform-gap-value textarea, .multiform-gap-value input[type='email'], .multiform-gap-value select, .multiform-gap-value .multiform-formula span");
                } else if (elem == 'fdesc') {
                    field = $(".multiform-gap-description");
                } else if (elem == 'fname') {
                    field = $(".multiform-gap-name");
                } else if (elem == 'errors') {
                    field = $(".multiform-wrap .errormsg, .multiform-mask-error");
                } else if (elem == 'button') {
                    field = $(".mf-button");
                } else if (elem == 'button_block') {
                    field = $(".multiform-submit");
                } else if (elem == 'frows') {
                    field = $(".multiform-gap-field");
                }
                var marTop = style['margin-top'] == '' ? 0 : style['margin-top'];
                var marRight = style['margin-right'] == '' ? 0 : style['margin-right'];
                var marBottom = style['margin-bottom'] == '' ? 0 : style['margin-bottom'];
                var marLeft = style['margin-left'] == '' ? 0 : style['margin-left'];
                var padTop = style['padding-top'] == '' ? 0 : style['padding-top'];
                var padRight = style['padding-right'] == '' ? 0 : style['padding-right'];
                var padBottom = style['padding-bottom'] == '' ? 0 : style['padding-bottom'];
                var padLeft = style['padding-left'] == '' ? 0 : style['padding-left'];
                marTop !== undefined && field.css('margin-top', marTop + (marTop !== 'auto' ? 'px' : ''));
                marRight !== undefined && field.css('margin-right', marRight + (marRight !== 'auto' ? 'px' : ''));
                marBottom !== undefined && field.css('margin-bottom', marBottom + (marBottom !== 'auto' ? 'px' : ''));
                marLeft !== undefined && field.css('margin-left', marLeft + (marLeft !== 'auto' ? 'px' : ''));
                padTop !== undefined && field.css('padding-top', padTop + (padTop !== '' ? 'px' : 0));
                padRight !== undefined && field.css('padding-right', padRight + (padRight !== '' ? 'px' : ''));
                padBottom !== undefined && field.css('padding-bottom', padBottom + (padBottom !== '' ? 'px' : ''));
                padLeft !== undefined && field.css('padding-left', padLeft + (padLeft !== '' ? 'px' : ''));
            });
        },
        /* Alignment */
        selectAlignment: function (select) {
            this.styles[this.activeTheme]['alignment'][$("#alignment-menu").val()] = select.value;
            this.updateAlignment(this.styles[this.activeTheme]['alignment']);
        },
        setAlignment: function (select) {
            if (typeof this.styles[this.activeTheme]['alignment'][select.value] !== 'undefined') {
                $("#alignment-edit-menu").val(this.styles[this.activeTheme]['alignment'][select.value]);
            } else {
                $("#alignment-edit-menu :selected").prop('selected', false);
            }
            this.updateAlignment(this.styles[this.activeTheme]['alignment']);
        },
        updateAlignment: function (styles) {
            $.each(styles, function (elem, style) {
                var field;
                if (elem == 'header') {
                    field = $(".multiform-header");
                } else if (elem == 'fdesc') {
                    field = $(".multiform-form-description");
                } else if (elem == 'fname') {
                    field = $(".multiform-gap-name");
                } else if (elem == 'button') {
                    field = $(".multiform-submit");
                } else if (elem == 'title') {
                    field = $(".multiform-title");
                }
                style == 'left' && field.css('text-align', 'left');
                style == 'right' && field.css('text-align', 'right');
                style == 'center' && field.css('text-align', 'center');
                style == 'justify' && field.css('text-align', 'justify');
            });
        },
        /* Additional */
        selectAdditional: function (select) {
            if (select.value == 'table_grid' || select.value == 'rangeslider') {
                $("#columns").addClass("showAdd" + (select.value == 'table_grid' ? "TG" : "RS")).removeClass("showPicker");
                $("#additional-" + (select.value == 'table_grid' ? "tg" : "rs") + "-menu option:selected").prop('selected', false);
            } else {
                $("#columns").removeClass("showAddTG showAddRS").addClass("showPicker");
                this.setAdditional(select);
            }
        },
        setAdditional: function (select, table_grid) {
            var elem = table_grid ? this.styles[this.activeTheme]['additional'][table_grid][select.value] : this.styles[this.activeTheme]['additional'][select.value];

            if (typeof elem['color'] !== 'undefined') {
                $(".colorpicker_input").val(elem['color']);
                $("#color-picker-column .colorpicker").setColor(elem['color']);
            } else {
                $("#color-picker-column .colorpicker").setColor('#000');
                $(".colorpicker_input").val('#000000');
            }
            this.updateAdditional(this.styles[this.activeTheme]['additional']);
        },
        updateAdditional: function (styles) {
            $.each(styles, function (elem, style) {
                var field;
                if (elem == 'table_grid' || elem == 'rangeslider') {
                    $.form_appearance.updateAdditional(style);
                } else if (elem == 'rating') {
                    field = $(".multiform-rating .star-full");
                } else if (elem == 'header') {
                    field = $(".multiform-grid thead th, .multiform-grid thead td");
                } else if (elem == 'odd') {
                    field = $(".multiform-grid tbody tr.odd td, .multiform-grid tbody tr.odd th");
                } else if (elem == 'even') {
                    field = $(".multiform-grid tbody tr.even td, .multiform-grid tbody tr.even th");
                } else if (elem == 'line') {
                    field = $(".irs-bar");
                } else if (elem == 'inactive_line') {
                    field = $(".irs-line");
                } else if (elem == 'circle') {
                    field = $(".irs-handle>i:first-child");
                } else if (elem == 'value' || elem == 'value_block') {
                    field = $(".irs-from, .irs-to, .irs-single");
                } else if (elem == 'inactive_value' || elem == 'inactive_value_block') {
                    field = $(".irs-min, .irs-max");
                } else if (elem == 'grid') {
                    field = $(".irs-grid-pol");
                } else if (elem == 'grid_value') {
                    field = $(".irs-grid-text");
                }
                if (elem == 'highlight') {
                    $(".t" + $.form_appearance.activeTheme + "-theme-css-" + elem).remove();
                    if (style.color) {
                        var style = "<style>." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-grid tbody tr:hover td, ." + ($.form_appearance.activeTheme == 'new' ? 'default' : "t" + $.form_appearance.activeTheme) + "-theme .multiform-grid tbody tr:hover th { background-color: " + style.color + " !important; }</style>";
                        $(".t" + $.form_appearance.activeTheme + "-theme-css").append("<div class='t" + $.form_appearance.activeTheme + "-theme-css-" + elem + "'>" + style + "</div>");
                    }
                } else {
                    style.color && field.css((elem !== 'rating' && elem !== 'inactive_value' && elem !== 'grid_value' && elem !== 'value') ? 'background-color' : 'color', style.color);
                }
            });
        },
        // Сохранение формы
        saveThemeAction: function (btn) {
            if (this.activeTheme == 'new') {
                $("#appearance-save-dialog").waDialog({
                    'height': '150px',
                    'width': '550px',
                    'content': '<h1 class="align-center">' + $__('Specify theme name') + '</h1><div class="margin-block align-center" style="margin-top: 30px"><input style="width: 100%; height: 30px" name="theme-name" type="text"></div>',
                    'buttons': '<div class="align-center"><input type="submit" value="' + $__('Save') + '" class="button green"> ' + $__('or') + ' <a class="button red cancel" href="javascript:void(0)">' + $__('cancel') + '</a></div>',
                    disableButtonsOnSubmit: false,
                    onSubmit: function (d) {
                        var form = $(this);
                        if (!form.find(".temp").length) {
                            form.find("input[type='submit']").after("<i class='icon16 loading temp'></i>");
                            $.form_appearance.saveTheme(form.find("input[name='theme-name']").val(), d);
                        }
                        return false;
                    }
                });
            } else {
                $.form_appearance.saveTheme();
            }
        },
        // Сохранение темы
        saveTheme: function (name, dialog) {
            if (!$.multiform.isLoading()) {
                $.multiform.showLoading($__("Saving theme"));
                $.ajax({
                    url: "?module=form&action=appearanceSave",
                    dataType: "json",
                    type: "post",
                    data: {
                        id: $.form.currentForm,
                        theme_settings: this.styles[this.activeTheme],
                        name: name,
                        theme_id: this.activeTheme,
                        important: $("#important-rule").prop("checked") ? 1 : 0
                    },
                    success: function (response) {
                        dialog && dialog.trigger('close');
                        $(".f-save-theme-dialog").hide();

                        if (response.status == 'ok' && typeof response.data.theme_id !== 'undefined') {
                            if (!$("#themes-menu option[data-id='" + response.data.theme_id + "']").length) {
                                $("#themes-menu").append("<option data-id='" + response.data.theme_id + "' value='" + response.data.theme_id + "'>" + response.data.name + "</option>");
                                $("#themes-menu").val(response.data.theme_id);
                                $.form_appearance.styles[response.data.theme_id] = $.extend(true, {}, $.form_appearance.styles[$.form_appearance.activeTheme]);
                                $.form_appearance.activeTheme = response.data.theme_id + '';
                                $(".appearance-tab .f-not-new").removeClass("hidden");
                            }
                            $.multiform.showError($__("Theme saved"));
                        } else {
                            $.multiform.showError($__("Theme not saved. Check log files"));
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (console) {
                            console.log(jqXHR, textStatus, errorThrown);
                        }
                        dialog && dialog.trigger('close');
                        $.multiform.showError($__("Theme not saved. Check log files"));
                    }
                });

            }
            return false;
        },
        // Переименование темы
        renameThemeAction: function (btn) {
            $("#appearance-rename-dialog").waDialog({
                'height': '150px',
                'width': '550px',
                'content': '<h1 class="align-center">' + $__('Specify new theme name') + '</h1><div class="margin-block align-center" style="margin-top: 30px"><input style="width: 100%; height: 30px" name="theme-name" type="text" value="' + $("#themes-menu option:selected").text() + '"></div>',
                'buttons': '<div class="align-center"><input type="submit" value="' + $__('Rename') + '" class="button green"> ' + $__('or') + ' <a class="button red cancel" href="javascript:void(0)">' + $__('cancel') + '</a></div>',
                disableButtonsOnSubmit: false,
                onSubmit: function () {
                    var form = $(this);
                    if (!form.find(".temp").length) {
                        form.find("input[type='submit']").after("<i class='icon16 loading temp'></i>");
                        $.post("?action=handler", {
                            id: $.form_appearance.activeTheme,
                            data: 'renameTheme',
                            name: form.find("input[name='theme-name']").val()
                        }, function (response) {
                            $(".dialog").remove();
                            if (response.status == 'ok') {
                                $("#themes-menu :selected").text(response.data);
                                $.multiform.showError($__("Theme renamed"));
                            } else {
                                $.multiform.showError($__("Theme not renamed. Check log files"));
                            }
                        });
                    }
                    return false;
                }
            });
        },
        // Копирование формы
        duplicateThemeAction: function (btn) {
            $.post("?action=handler", {id: $.form_appearance.activeTheme, data: 'duplicateTheme'}, function (response) {
                if (response.status == 'ok' && typeof response.data.theme_id !== 'undefined') {
                    if (!$("#themes-menu option[data-id='" + response.data.theme_id + "']").length) {
                        $("#themes-menu").append("<option data-id='" + response.data.theme_id + "' value='" + response.data.theme_id + "'>" + response.data.name + "</option>");
                        $("#themes-menu").val(response.data.theme_id);
                        $(".appearance-tab .f-use-theme").removeClass("hidden");
                        $(".appearance-tab .f-theme-used").addClass("hidden");
                        $.form_appearance.styles[response.data.theme_id] = $.extend(true, {}, $.form_appearance.styles[$.form_appearance.activeTheme]);
                        $.form_appearance.activeTheme = response.data.theme_id + '';

                    }
                    $.multiform.showError($__("Theme duplicated"));
                } else {
                    $.multiform.showError($__("Theme not duplicated. Check log files"));
                }
            });
        },
        // Удаление темы
        deleteThemeAction: function (btn) {
            $("#appearance-delete-dialog").waDialog({
                'height': '200px',
                'width': '600px',
                'content': '<h1 class="align-center">' + $__('Are you sure?') + '</h1><div class="margin-block align-center" style="margin-top: 30px">' + $__('Form themes are global. If you delete this theme, it will be deleted from other forms.') + '<br>' + $__('If you just want to change theme for your form, use select field "Themes" and button "Use this theme"') + '</div>',
                'buttons': '<div class="align-center"><input type="submit" value="' + $__('Delete anyway') + '" class="button green"> ' + $__('or') + ' <a class="button red cancel" href="javascript:void(0)">' + $__('cancel') + '</a></div>',
                disableButtonsOnSubmit: false,
                onSubmit: function () {
                    var form = $(this);
                    if (!form.find(".temp").length) {
                        form.find("input[type='submit']").after("<i class='icon16 loading temp'></i>");
                        $.post("?action=handler", {
                            id: $.form_appearance.activeTheme,
                            data: 'deleteTheme'
                        }, function (response) {
                            $(".dialog").remove();
                            if (response.status == 'ok') {
                                $("#themes-menu option[data-id='" + $.form_appearance.activeTheme + "']").remove();
                                delete $.form_appearance.styles[$.form_appearance.activeTheme];
                                $(".appearance-tab .f-not-new").addClass("hidden");
                                $("#themes-menu option:first").prop("selected", true);
                                $.form_appearance.activeTheme = 'new';
                                $.form_appearance.initThemeDesign($.form_appearance.activeTheme);
                                $.multiform.showError($__("Theme deleted"));
                            } else {
                                $.multiform.showError($__("Theme not deleted. Check log files"));
                            }
                        });
                    }
                    return false;
                }
            });
        },
        // Использовать тему
        useThemeAction: function (btn) {
            $("#appearance-use-dialog").waDialog({
                'height': '180px',
                'width': '600px',
                'content': '<h1 class="align-center">' + $__('Pay attention') + '</h1><div class="margin-block align-center" style="margin-top: 30px">' + $__('Do not forget to save your changes') + '</div>',
                'buttons': '<div class="align-center"><input type="submit" value="' + $__('Use this theme') + '" class="button green"> <a href="#/save/theme/" class="button green f-save-theme-dialog js-action">' + $__('Save theme') + '</a> ' + $__('or') + ' <a class="button red cancel" href="javascript:void(0)">' + $__('cancel') + '</a></div>',
                disableButtonsOnSubmit: false,
                onSubmit: function () {
                    var form = $(this);
                    if (!form.find(".temp").length) {
                        form.find("input[type='submit']").after("<i class='icon16 loading temp'></i>");
                        $.post("?action=handler", {
                            id: $.form.currentForm,
                            theme_id: $.form_appearance.activeTheme,
                            data: 'useTheme'
                        }, function (response) {
                            $(".dialog").remove();
                            if (response.status == 'ok') {
                                $.form_appearance.usedTheme = $.form_appearance.activeTheme;
                                $(".appearance-tab .f-use-theme").addClass("hidden");
                                $(".appearance-tab .f-theme-used").removeClass("hidden");
                                $.multiform.showError($__("Theme is used"));
                            } else {
                                $.multiform.showError($__("Error. Check log files"));
                            }
                        });
                    }
                    return false;
                }
            });
        },
        // Верхний регистр для строки
        capitalizeFirstLetter: function (string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    };
})(jQuery);