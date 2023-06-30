function multiformForm() {

    this.formHash = "", this.ssl = 'unset', this.initDone = !1, this.init = function (options) {
        if (this.initDone === !1) {
            this.initDone = !0;
            for (var key in options) {
                this[key] = options[key];
            }
            var that = this;
            this.addFrame(this.formHash, this.src);
            this.addEvent(window, "message", function (event) {
                that.resizeForm(event);
            }, this.formId);
        }
    }, this.addFrame = function (hash, src) {
        if (hash && src) {
            var that = this;
            var frame = document.createElement("iframe");
            frame.setAttribute("id", "multiformForm-frame-" + hash);
            frame.height = this.height || 500;
            frame.allowTransparency = !0;
            frame.frameborder = 0;
            frame.scrolling = this.scrolling || "auto";
            frame.setAttribute("src", src + "?v=" + parseInt((new Date).getTime() / 864e5));
            frame.width = this.width || 500;
            frame.setAttribute("style", "width: 100%; border: 0 none;");
            document.getElementById("multiform-" + hash).appendChild(frame);
            frame.onload = function () {
                that.attachOnResizeEvent();
                that.resizeIFrame(that.formHash);
            };
        }
    }, this.resizeForm = function (event) {
        if (event.origin != "http://" + this.host && event.origin != "https://" + this.host) {
            return;
        }
        var data = event.data.split('|');

        if (typeof data[1] !== 'undefined' && parseInt(data[1]) && this.isMessageFromCorrectForm(data[1])) {
            var frame = document.getElementById("multiformForm-frame-" + this.formHash);
            this.updateHeight(frame, data[0]);
        }
    }, this.isMessageFromCorrectForm = function (dataId) {
        dataId = dataId || "";
        return parseInt(dataId) == parseInt(this.formId) ? !0 : !1;
    }, this.updateHeight = function (frame, height) {
        frame.style.height = height + "px";
    }, this.attachOnResizeEvent = function () {
        if (typeof window.__multiformForms != "undefined") {
            window.__multiformForms.push(this);
        } else {
            window.__multiformForms = [];
            window.__multiformForms.push(this);
            window.oldHandler = window.onresize;
        }
        window.onresize = function () {
            for (var i = 0; i < window.__multiformForms.length; i++)
                window.__multiformForms[i].resizeIFrame(window.__multiformForms[i].formHash);
            window.oldHandler && window.oldHandler();
        };
    }, this.resizeIFrame = function (formHash) {
        if (window.postMessage) {
            var iframe = document.getElementById("multiformForm-frame-" + formHash);
            var origin = this.determineSecurity() + this.host;
            iframe.contentWindow.postMessage("resizeMultiform|" + this.formId, origin);
        }
    }, this.determineSecurity = function () {
        return this.ssl == 1 ? "https://" : "http://";
    }, this.addEvent = function (obj, type, fn, unique) {
        obj.attachEvent ? (obj["e" + type + fn + unique] = fn, obj[type + fn + unique] = function () {
            obj["e" + type + fn + unique](window.event);
        }, obj.attachEvent("on" + type, obj[type + fn + unique])) : obj.addEventListener(type, fn, !1);
    };
}
