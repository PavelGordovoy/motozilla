$(function(){function a(){}function g(){c.hide(),$(".review").removeClass("in-reply-to")}function h(a){$(".review-reply, .write-comment a").removeClass("opened"),a.addClass("opened");var b=a.parents("li:first"),c=parseInt(b.attr("data-id"),10)||0;o.call(a,c),$(".review").removeClass("in-reply-to"),b.find(".review:first").addClass("in-reply-to")}function l(){var b=(window.location.protocol+"//"+window.location.host+window.location.pathname).replace("/reviews/","")+"reviews/add/";$.post(b,d.serialize(),function(b){if("fail"==b.status)return n(d,!1),void m(d,b.errors);if("ok"!=b.status||!b.data.html)return void(console&&console.error("Error occured."));var f=b.data.html,g=parseInt(b.data.parent_id,10)||0,h=g?d.parents("li:first"):e,i=$("ul.reviews-branch:first",h);$(".comments-branch .new").removeClass("new"),g?(i.show().append(f),i.find("li:last .review").addClass("new")):(i.show().prepend(f),i.find("li:first .review").addClass("new")),$(".reviews-count-text").text(b.data.review_count_str),$(".reviews-count").text(b.data.count),d.find("input[name=count]").val(b.data.count),n(d,!0),e.find(".write-comment a").click(),a(),c.hide(),$("html, body").animate({scrollTop:$(".comments-branch .new").offset().top},1e3),"function"==typeof success&&success(b)},"json").error(function(a){console&&console.error(a.responseText?"Error occured: "+a.responseText:"Error occured.")})}function m(a,b){for(var c in b)$("[name="+c+"]",a).after($('<em class="errormsg"></em>').text(b[c])).addClass("error")}function n(a,b){b="undefined"==typeof b||b,$(".errormsg",a).remove(),$(".error",a).removeClass("error"),$(".wa-captcha-refresh",a).click(),b&&($("input[name=captcha], textarea",a).val(""),$("input[name=rate]",a).val(0),$("input[name=title]",a).val(""),$(".rate",a).trigger("clear"))}function o(a){var b=this;a?(b.parents(".actions:first").after(c),$(".rate ",d).trigger("clear").parents(".review-field:first").hide()):(b.parents(".write-comment").after(c),d.find(".rate").parents(".review-field:first").show()),n(d,!1),$("input[name=parent_id]",d).val(a),c.show()}function p(a,c,e){var f=b[c];d.off("keydown",a).on("keydown",a,function(a){if(a.keyCode==f.key&&a.altKey==f.alt&&a.ctrlKey==f.ctrl&&a.shiftKey==f.shift)return e()})}a();var b={"alt+enter":{ctrl:!1,alt:!0,shift:!1,key:13},"ctrl+enter":{ctrl:!0,alt:!1,shift:!1,key:13},"ctrl+s":{ctrl:!0,alt:!1,shift:!1,key:17}},c=$("#product-review-form"),d=c.find("form"),e=$("#page-content .reviews"),f=d.find("input[name=rate]");f.length||(f=$('<input name="rate" type="hidden" value=0>').appendTo(d)),$("#review-rate").rateWidget({onUpdate:function(a){f.val(a)}}),c.find(".c_close").on("click",function(){c.hide(),$(".review").removeClass("in-reply-to")}),e.off("click",".review-reply, .write-comment a").on("click",".review-reply, .write-comment a",function(){var a=$(this);return c.is(":visible")?a.hasClass("opened")?($(".review-reply, .write-comment a").removeClass("opened"),g()):(g(),h(a)):h(a),!1});var i=$(".wa-captcha"),j=$("#user-auth-provider li"),k=j.filter(".selected").attr("data-provider");"guest"!=k&&k?i.hide():i.show(),j.find("a").click(function(){var a=$(this),b=a.parents("li:first");if(b.hasClass("selected"))return!1;b.siblings(".selected").removeClass("selected"),b.addClass("selected");var c=b.attr("data-provider");if(d.find("input[name=auth_provider]").val(c),"guest"==c)return $("div.provider-fields").hide(),$("div.provider-fields[data-provider=guest]").show(),i.show(),!1;if(c==k)return $("div.provider-fields").hide(),$("div.provider-fields[data-provider="+c+"]").show(),i.hide(),!1;var e=(screen.width-600)/2,f=(screen.height-400)/2;return window.open($(this).attr("href"),"oauth","width=600,height=400,left="+e+",top="+f+",status=no,toolbar=no,menubar=no"),!1}),p("textarea","ctrl+enter",l),d.submit(function(){return l(),!1}),e.on("click",".show_more_answers",function(){var a=$(this).closest(".comments-branch");return a.hasClass("expanded")?(a.find(".hidden-answers").slideUp(),a.removeClass("expanded"),$(this).html('Больше ответов <i class="icon-angle-down"></i>')):(a.find(".hidden-answers").slideDown(),a.addClass("expanded"),$(this).html('Cвернуть <i class="icon-angle-up"></i>')),!1})});