
(function($) {
    $.fn.dtPaginator = function(options){

        // Default options
        var settings = $.extend({
            page: 1,
            lastPage: 1,
            showNext: false,
            showPrevious: false,
            showFirst: false,
            showLast: false,
            maxPageButtons: 9,
            showText: false
        }, options );


        var page = settings.page;
        var lastPage = settings.lastPage;
        var showNext = settings.showNext;
        var showPrevious = settings.showPrevious;
        var showFirst = settings.showFirst;
        var showLast = settings.showLast;
        var maxPageButtons = settings.maxPageButtons;
        var showText = settings.showText;

        if (lastPage == 1) return;

        var previousPage = page - 1;
        var nextPage = page + 1;
        var previousRange = Math.floor((maxPageButtons - 1)/2);
        var nextRange = Math.ceil((maxPageButtons - 1)/2);

        var paginator = '<nav aria-label="Paginator">';
        paginator += '<ul class="pagination">';

        if (showFirst) {
            var disabled = '';
            if (page == 1) disabled = ' disabled';
            paginator += '<li class="page-item' + disabled + '"><a class="page-link" data-page="1"><i class="fa fa-angle-double-left"></i></a></li>';
        }

        if (showPrevious) {
            var disabled = '';
            if (page == 1) disabled = ' disabled';
            paginator += '<li class="page-item' + disabled + '"><a class="page-link" data-page="' + previousPage + '"><i class="fa fa-angle-left"></i></a></li>';
        }

        var nextRangeOffset = 0;
        if (page-previousRange < 1) nextRangeOffset = previousRange - page - 1;

        var PreviousRangeOffset = 0;
        if (page+nextRange > lastPage) PreviousRangeOffset = nextRange - (lastPage - page);

        var startPage = 1;
        if (page-previousRange-PreviousRangeOffset > 1) startPage = page-previousRange-PreviousRangeOffset;

        var pageCount = maxPageButtons;
        if (lastPage < maxPageButtons) pageCount = lastPage;
        else if (startPage+maxPageButtons > lastPage) pageCount = lastPage - (startPage+maxPageButtons);

        for (i = 0; i < pageCount; i++) {

            var margin = '';
            if (i == 0 && startPage > 1) margin = ' ml-1';
            if (i == pageCount && (startPage+i) > lastPage) margin = ' mr-1';

            var active = '';
            if (page == startPage+i) active = ' active';
            paginator += '<li class="page-item' + margin + active + '"><a class="page-link" data-page="' + (startPage+i) + '">' + (startPage+i) + '</a></li>';
        }

        if (showNext) {
            var disabled = '';
            if (page == lastPage) disabled = ' disabled';
            paginator += '<li class="page-item' + disabled + '"><a class="page-link" data-page="' + nextPage + '"><i class="fas fa-angle-right"></i></a></li>';
        }

        if (showLast) {
            var disabled = '';
            if (page == lastPage) disabled = ' disabled';
            paginator += '<li class="page-item' + disabled + '"><a class="page-link"><i class="fas fa-angle-double-right"></i></a></li>';
        }

        paginator += '</ul>';
        paginator += '</nav>';

        this.append(paginator);

    }
    }( jQuery ));
