$(document).ready(function(){
    $waHeaderPlugin = {
        self: this,
        dom: {
            header: $("#wa-header"),
            stiky: $(".waheader-stiky-ad"),
            stiky_content: $("#waheader-1"),
            control: $(".waheader-stiky-ad__control"),
            moreapps: $("#wa-moreapps")
        },
        
        init: function() {
            self = this;
            this.regEvents();
            
            var $waHeaderBlock = this.dom.header;
            this.dom.stiky_content.html($waHeaderBlock);
        },

        regEvents: function() {
            this.dom.control.on(waheader_type_event, this.toggle);
            this.dom.moreapps.on('click', this.moreapps);
            $(window).on('resize', this.calcHeight);
        },

        open: function() {
            this.dom.stiky.addClass("waheader-stiky-ad_opened");
            this.dom.stiky.removeClass("waheader-stiky-ad_closed");
            this.dom.stiky.css("animation", "waheader-move-in 0.5s ease 0s 1 normal forwards running");
        }, 

        close: function() {
            if(self.dom.moreapps.hasClass('uarr')) {
                self.dom.moreapps.trigger('click');
                self.calcHeight();
            }

            this.dom.stiky.addClass("waheader-stiky-ad_closed");
            this.dom.stiky.removeClass("waheader-stiky-ad_opened");
            this.dom.stiky.css("animation", "waheader-move-out 0.5s ease 0s 1 normal forwards running");
        },

        toggle: function(e) {
            if(self.dom.stiky.hasClass("waheader-stiky-ad_closed")) {
                self.open();
            } else {
                self.close();
            }
        },

        moreapps: function(e) {
            self.calcHeight();
        },

        calcHeight: function() {
            setTimeout(function(){
                var headerHeight = self.dom.header.height() < 100 
                    ? 100 
                    : self.dom.header.height();
                self.dom.stiky.css('height', headerHeight);
            }, 0);
        }
    }

    $waHeaderPlugin.init();
});