
(function($) {
    $.fn.dtModal = function(options){

        // Default options
        var settings = $.extend({
            title: '',
            content: '',
            url: false,
            showHeader: true,
            showFooter: true,
            buttons: '<button type="button" class="btn btn-secondary" data-dismiss="modal">' + translations['Close'] + '</button>',
            size: ''
        }, options );

        this.find('.modal-title').html(settings.title);
        this.find('.modal-body').html(settings.content);
        this.find('.modal-footer').html(settings.buttons);
        this.find('.modal-dialog').removeClass('modal-lg').removeClass('modal-xl');
        if (settings.size == 'xl') this.find('.modal-dialog').addClass('modal-xl');
        if (settings.size == 'lg') this.find('.modal-dialog').addClass('modal-lg');

        this.modal('show');
        return;
    }
}( jQuery ));
