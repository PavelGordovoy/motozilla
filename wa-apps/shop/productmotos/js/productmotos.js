(function($) {
    $.products.motosAction = function () {
        this.load('?plugin=productmotos', function () {
            $("#s-sidebar li.selected").removeClass('selected');
            $("#s-productmotos").addClass('selected');
        });
    }
    $.products.motoAction = function (id) {
        this.load('?plugin=productmotos&action=edit&id=' + id, function () {
            if (!$("#s-productmotos").hasClass('selected')) {
                $("#s-sidebar li.selected").removeClass('selected');
                $("#s-productmotos").addClass('selected');
            }
        });
    }
})(jQuery);