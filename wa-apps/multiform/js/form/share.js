(function ($) {
    $.form_share = {
        initPageShare: function () {
            // Изменение высоты фрейма
            $(".share-tab .f-height").on("input keyup", function () {
                $.form_share.changeIframeCode(parseInt($(this).val()));
            });
        },
        // Изменение высоты фрейма
        changeIframeCode: function (h) {
            var code = $("#iframeEmbedCode").val();
            if (h > 0) {
                var newcode = code.replace(/height="\d+"/, 'height="'+h+'"');
                $("#iframeEmbedCode").val(newcode);
            }
        }
    };
})(jQuery);