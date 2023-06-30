/*! jQuery UI - v1.11.4+CommonJS - 2015-08-28
 * http://jqueryui.com
 * Includes: widget.js
 * Copyright 2015 jQuery Foundation and other contributors; Licensed MIT */(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery"], factory);
    } else if (typeof exports === "object") {
        factory(require("jquery"));
    } else {
        factory(jQuery);
    }
}(function ($) {
    /*!
     * jQuery UI Widget 1.11.4
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     *
     * http://api.jqueryui.com/jQuery.widget/
     */
    var widget_uuid = 0,
        widget_slice = Array.prototype.slice;
    $.cleanData = (function (orig) {
        return function (elems) {
            var events, elem, i;
            for (i = 0; (elem = elems[i]) != null; i++) {
                try {
                    events = $._data(elem, "events");
                    if (events && events.remove) {
                        $(elem).triggerHandler("remove");
                    }
                } catch (e) {
                }
            }
            orig(elems);
        };
    })($.cleanData);
    $.mf_widget = function (name, base, prototype) {
        var fullName, existingConstructor, constructor, basePrototype,
            proxiedPrototype = {},
            namespace = name.split(".")[ 0 ];
        name = name.split(".")[ 1 ];
        fullName = namespace + "-" + name;
        if (!prototype) {
            prototype = base;
            base = $.mf_Widget;
        }
        $.expr[ ":" ][ fullName.toLowerCase() ] = function (elem) {
            return !!$.data(elem, fullName);
        };
        $[ namespace ] = $[ namespace ] || {};
        existingConstructor = $[ namespace ][ name ];
        constructor = $[ namespace ][ name ] = function (options, element) {
            if (!this._createWidget) {
                return new constructor(options, element);
            }
            if (arguments.length) {
                this._createWidget(options, element);
            }
        };
        $.extend(constructor, existingConstructor, {
            version: prototype.version,
            _proto: $.extend({}, prototype),
            _childConstructors: []
        });
        basePrototype = new base();
        basePrototype.options = $.mf_widget.extend({}, basePrototype.options);
        $.each(prototype, function (prop, value) {
            if (!$.isFunction(value)) {
                proxiedPrototype[ prop ] = value;
                return;
            }
            proxiedPrototype[ prop ] = (function () {
                var _super = function () {
                    return base.prototype[ prop ].apply(this, arguments);
                },
                    _superApply = function (args) {
                        return base.prototype[ prop ].apply(this, args);
                    };
                return function () {
                    var __super = this._super,
                        __superApply = this._superApply,
                        returnValue;
                    this._super = _super;
                    this._superApply = _superApply;
                    returnValue = value.apply(this, arguments);
                    this._super = __super;
                    this._superApply = __superApply;
                    return returnValue;
                };
            })();
        });
        constructor.prototype = $.mf_widget.extend(basePrototype, {
            widgetEventPrefix: existingConstructor ? (basePrototype.widgetEventPrefix || name) : name
        }, proxiedPrototype, {
            constructor: constructor,
            namespace: namespace,
            widgetName: name,
            widgetFullName: fullName
        });
        if (existingConstructor) {
            $.each(existingConstructor._childConstructors, function (i, child) {
                var childPrototype = child.prototype;
                $.mf_widget(childPrototype.namespace + "." + childPrototype.widgetName, constructor, child._proto);
            });
            delete existingConstructor._childConstructors;
        } else {
            base._childConstructors.push(constructor);
        }
        $.mf_widget.bridge(name, constructor);
        return constructor;
    };
    $.mf_widget.extend = function (target) {
        var input = widget_slice.call(arguments, 1),
            inputIndex = 0,
            inputLength = input.length,
            key,
            value;
        for (; inputIndex < inputLength; inputIndex++) {
            for (key in input[ inputIndex ]) {
                value = input[ inputIndex ][ key ];
                if (input[ inputIndex ].hasOwnProperty(key) && value !== undefined) {
                    if ($.isPlainObject(value)) {
                        target[ key ] = $.isPlainObject(target[ key ]) ?
                            $.mf_widget.extend({}, target[ key ], value) :
                            $.mf_widget.extend({}, value);
                    } else {
                        target[ key ] = value;
                    }
                }
            }
        }
        return target;
    };
    $.mf_widget.bridge = function (name, object) {
        var fullName = object.prototype.widgetFullName || name;
        $.fn[ name ] = function (options) {
            var isMethodCall = typeof options === "string",
                args = widget_slice.call(arguments, 1),
                returnValue = this;
            if (isMethodCall) {
                this.each(function () {
                    var methodValue,
                        instance = $.data(this, fullName);
                    if (options === "instance") {
                        returnValue = instance;
                        return false;
                    }
                    if (!instance) {
                        return $.error("cannot call methods on " + name + " prior to initialization; " +
                            "attempted to call method '" + options + "'");
                    }
                    if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
                        return $.error("no such method '" + options + "' for " + name + " widget instance");
                    }
                    methodValue = instance[ options ].apply(instance, args);
                    if (methodValue !== instance && methodValue !== undefined) {
                        returnValue = methodValue && methodValue.jquery ?
                            returnValue.pushStack(methodValue.get()) :
                            methodValue;
                        return false;
                    }
                });
            } else {
                if (args.length) {
                    options = $.mf_widget.extend.apply(null, [options].concat(args));
                }
                this.each(function () {
                    var instance = $.data(this, fullName);
                    if (instance) {
                        instance.option(options || {});
                        if (instance._init) {
                            instance._init();
                        }
                    } else {
                        $.data(this, fullName, new object(options, this));
                    }
                });
            }
            return returnValue;
        };
    };
    $.mf_Widget = function ( /* options, element */ ) {};
    $.mf_Widget._childConstructors = [];
    $.mf_Widget.prototype = {
        widgetName: "widget",
        widgetEventPrefix: "",
        defaultElement: "<div>",
        options: {
            disabled: false, create: null
        },
        _createWidget: function (options, element) {
            element = $(element || this.defaultElement || this)[ 0 ];
            this.element = $(element);
            this.uuid = widget_uuid++;
            this.eventNamespace = "." + this.widgetName + this.uuid;
            this.bindings = $();
            this.hoverable = $();
            this.focusable = $();
            if (element !== this) {
                $.data(element, this.widgetFullName, this);
                this._on(true, this.element, {
                    remove: function (event) {
                        if (event.target === element) {
                            this.destroy();
                        }
                    }
                });
                this.document = $(element.style ?
                    element.ownerDocument :
                    element.document || element);
                this.window = $(this.document[0].defaultView || this.document[0].parentWindow);
            }
            this.options = $.mf_widget.extend({},
                this.options,
                this._getCreateOptions(),
                options);
            this._create();
            this._trigger("create", null, this._getCreateEventData());
            this._init();
        },
        _getCreateOptions: $.noop,
        _getCreateEventData: $.noop,
        _create: $.noop,
        _init: $.noop, destroy: function () {
            this._destroy();
            this.element
                .unbind(this.eventNamespace)
                .removeData(this.widgetFullName)
                .removeData($.camelCase(this.widgetFullName));
            this.widget()
                .unbind(this.eventNamespace)
                .removeAttr("aria-disabled")
                .removeClass(
                    this.widgetFullName + "-disabled " +
                    "ui-state-disabled");
            this.bindings.unbind(this.eventNamespace);
            this.hoverable.removeClass("ui-state-hover");
            this.focusable.removeClass("ui-state-focus");
        },
        _destroy: $.noop, widget: function () {
            return this.element;
        }, option: function (key, value) {
            var options = key,
                parts,
                curOption,
                i;
            if (arguments.length === 0) {
                return $.mf_widget.extend({}, this.options);
            }
            if (typeof key === "string") {
                options = {};
                parts = key.split(".");
                key = parts.shift();
                if (parts.length) {
                    curOption = options[ key ] = $.mf_widget.extend({}, this.options[ key ]);
                    for (i = 0; i < parts.length - 1; i++) {
                        curOption[ parts[ i ] ] = curOption[ parts[ i ] ] || {};
                        curOption = curOption[ parts[ i ] ];
                    }
                    key = parts.pop();
                    if (arguments.length === 1) {
                        return curOption[ key ] === undefined ? null : curOption[ key ];
                    }
                    curOption[ key ] = value;
                } else {
                    if (arguments.length === 1) {
                        return this.options[ key ] === undefined ? null : this.options[ key ];
                    }
                    options[ key ] = value;
                }
            }
            this._setOptions(options);
            return this;
        },
        _setOptions: function (options) {
            var key;
            for (key in options) {
                this._setOption(key, options[ key ]);
            }
            return this;
        },
        _setOption: function (key, value) {
            this.options[ key ] = value;
            if (key === "disabled") {
                this.widget()
                    .toggleClass(this.widgetFullName + "-disabled", !!value);
                if (value) {
                    this.hoverable.removeClass("ui-state-hover");
                    this.focusable.removeClass("ui-state-focus");
                }
            }
            return this;
        }, enable: function () {
            return this._setOptions({disabled: false});
        },
        disable: function () {
            return this._setOptions({disabled: true});
        }, _on: function (suppressDisabledCheck, element, handlers) {
            var delegateElement,
                instance = this;
            if (typeof suppressDisabledCheck !== "boolean") {
                handlers = element;
                element = suppressDisabledCheck;
                suppressDisabledCheck = false;
            }
            if (!handlers) {
                handlers = element;
                element = this.element;
                delegateElement = this.widget();
            } else {
                element = delegateElement = $(element);
                this.bindings = this.bindings.add(element);
            }
            $.each(handlers, function (event, handler) {
                function handlerProxy() {
                    if (!suppressDisabledCheck &&
                        (instance.options.disabled === true ||
                            $(this).hasClass("ui-state-disabled"))) {
                        return;
                    }
                    return (typeof handler === "string" ? instance[ handler ] : handler)
                        .apply(instance, arguments);
                }
                if (typeof handler !== "string") {
                    handlerProxy.guid = handler.guid =
                        handler.guid || handlerProxy.guid || $.guid++;
                }
                var match = event.match(/^([\w:-]*)\s*(.*)$/),
                    eventName = match[1] + instance.eventNamespace,
                    selector = match[2];
                if (selector) {
                    delegateElement.delegate(selector, eventName, handlerProxy);
                } else {
                    element.bind(eventName, handlerProxy);
                }
            });
        }, _off: function (element, eventName) {
            eventName = (eventName || "").split(" ").join(this.eventNamespace + " ") +
                this.eventNamespace;
            element.unbind(eventName).undelegate(eventName);
            this.bindings = $(this.bindings.not(element).get());
            this.focusable = $(this.focusable.not(element).get());
            this.hoverable = $(this.hoverable.not(element).get());
        }, _delay: function (handler, delay) {
            function handlerProxy() {
                return (typeof handler === "string" ? instance[ handler ] : handler)
                    .apply(instance, arguments);
            }
            var instance = this;
            return setTimeout(handlerProxy, delay || 0);
        }, _hoverable: function (element) {
            this.hoverable = this.hoverable.add(element);
            this._on(element, {
                mouseenter: function (event) {
                    $(event.currentTarget).addClass("ui-state-hover");
                },
                mouseleave: function (event) {
                    $(event.currentTarget).removeClass("ui-state-hover");
                }
            });
        }, _focusable: function (element) {
            this.focusable = this.focusable.add(element);
            this._on(element, {
                focusin: function (event) {
                    $(event.currentTarget).addClass("ui-state-focus");
                },
                focusout: function (event) {
                    $(event.currentTarget).removeClass("ui-state-focus");
                }
            });
        }, _trigger: function (type, event, data) {
            var prop, orig,
                callback = this.options[ type ];
            data = data || {};
            event = $.Event(event);
            event.type = (type === this.widgetEventPrefix ?
                type :
                this.widgetEventPrefix + type).toLowerCase();
            event.target = this.element[ 0 ];
            orig = event.originalEvent;
            if (orig) {
                for (prop in orig) {
                    if (!(prop in event)) {
                        event[ prop ] = orig[ prop ];
                    }
                }
            }
            this.element.trigger(event, data);
            return !($.isFunction(callback) &&
                callback.apply(this.element[0], [event].concat(data)) === false ||
                event.isDefaultPrevented());
        }
    };
    $.each({show: "fadeIn", hide: "fadeOut"}, function (method, defaultEffect) {
        $.mf_Widget.prototype[ "_" + method ] = function (element, options, callback) {
            if (typeof options === "string") {
                options = {effect: options};
            }
            var hasOptions,
                effectName = !options ?
                method :
                options === true || typeof options === "number" ?
                defaultEffect :
                options.effect || defaultEffect;
            options = options || {};
            if (typeof options === "number") {
                options = {duration: options};
            }
            hasOptions = !$.isEmptyObject(options);
            options.complete = callback;
            if (options.delay) {
                element.delay(options.delay);
            }
            if (hasOptions && $.effects && $.effects.effect[ effectName ]) {
                element[ method ](options);
            } else if (effectName !== method && element[ effectName ]) {
                element[ effectName ](options.duration, options.easing, callback);
            } else {
                element.queue(function (next) {
                    $(this)[ method ]();
                    if (callback) {
                        callback.call(element[ 0 ]);
                    }
                    next();
                });
            }
        };
    });
    var widget = $.mf_widget;
}));
