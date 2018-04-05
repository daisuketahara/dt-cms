
(function($) {

    var columns = [];
    var labels = [];
    var buttons = [];
    var object;
    var ajaxUrl;
    var addUrl;
    var editUrl;
    var deleteUrl;
    var sorting;
    var sortColumn;
    var sortDirection;
    var functions;
    var limit;
    var offset = 0;
    var page = 1;
    var data = '';
    var total = 0;
    var view;
    var refresh = 0;
    var refreshInterval;

    $.fn.dtList = function(options){

        // Default options
        var settings = $.extend({
            columns: '',
            labels: '',
            buttons: '',
            functions: true,
            sorting: true,
            filter: false,
            view: true,
            ajaxUrl: '',
            addUrl: '',
            editUrl: '',
            deleteUrl: '',
            limit: 15,
            refresh: 0
        }, options );

        object = this;
        columns = settings.columns;
        labels = settings.labels;
        buttons = settings.buttons;
        filter = settings.filter;
        view = settings.view;
        refresh = settings.refresh;
        sorting = settings.sorting;
        functions = settings.functions;
        limit = settings.limit;
        ajaxUrl = settings.ajaxUrl;
        addUrl = settings.addUrl;
        editUrl = settings.editUrl;
        deleteUrl = settings.deleteUrl;

        this.wrap('<form action="" class="im-list-form" method="post"></form>');

        if (labels.length > 0) {

            var header = '<div class="dt-cards-header card text-white mb-2">';
            header += '<div class="card-body p-2">';

            header += '<div class="row">';
            header += '<div class="col-1"></div>';

            for (i = 0; i < labels.length; i++) {
                if (settings.sorting) headerClass = ' dt-cards-sort pointer';
                else headerClass = '';
                header += '<div class="col' + headerClass + ' pt-1">';
                header += labels[i];
                if (settings.sorting) header += '<a class="ml-3"><i class="fa fa-sort" aria-hidden="true"></i></a>';
                header += '</div>';
            }

            header += '<div class="col-2 text-right">';
            if (settings.addUrl != '') header += '<a href="' + settings.addUrl + '" class="table-add pointer float-right btn btn-success btn-sm"><i class="fa fa-plus"></i></a>';
            header += '</div>';
            header += '</div>';

            header += '</div>';
            header += '</div>';

            this.append(header);
        }

        if (settings.filter) {

            var filter = '<tr class="im-table-filter">';
            if (settings.functions) filter += '<td></td>';
            for (i = 0; i < columns.length; i++) {
                filter += "<td><input type=\"text\" name=\"filter-" + columns[i] + "\" data-column=\"" + columns[i] + "\" placeholder=\"filter...\"></td>";
            }
            if (settings.functions) filter += '<td class="text-right"></td>';
            filter += '</tr>';

            this.find('thead').append(filter);
        }

        this.append('<div id="dt-cards-body"></div>');
        load_body();

        return this;
    }

	function load_body() {

        clearInterval(refreshInterval);

        // Get filters

        var filter = [];
        jQuery('.im-table-filter').find('input').each(function() {
            filter.push(jQuery(this).data('column') + '=' + jQuery(this).val());
        });

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				limit: limit,
				offset: offset,
                sortColumn: sortColumn,
                sortDirection: sortDirection,
                filter: filter.join('&')
			},
			dataType: 'json',
			success: function(json) {

                var json = JSON.parse(json);
                data = json['data'];
                total = json['total'];

                $('#dt-cards-body').empty();

                var card = '';
                for (i = 0; i < data.length; i++) {

                    card += '<div id="row-' + data[i]['id'] + '" class="card mb-1">';
                        card += '<div class="card-body p-2">';
                            card += '<div class="row">';
                                card += '<div class="col-1 pt-2"><input type="checkbox"> ' + data[i]['id'] + '.</div>';
                                for (i2 = 0; i2 < columns.length; i2++) {

                                    if (columns[i2]['id2'] == undefined || columns[i2]['id1'] == '') {
                                        card += '<div class="col pt-2">' + formatValue(data[i][columns[i2]['id1']]) + '</div>';
                                    } else {
                                        card += '<div class="col"><span class="bold">' + formatValue(data[i][columns[i2]['id1']]) + '</span><br>';
                                        card += '<span class="italic font-sm">' + formatValue(data[i][columns[i2]['id2']]) + '</span></div>';
                                    }
                                }

                                card += '<div class="col-2 text-right pt-1">';

                                for (i3 = 0; i3 < buttons.length; i3++) {
                                    card += '<a ';
                                    if (buttons[i3]['href'] != undefined && buttons[i3]['href'] != '') card += 'href="' + buttons[i3]['href'] + data[i]['id'] + '/" ';
                                    if (buttons[i3]['id'] != undefined && buttons[i3]['id'] != '') card += 'id="' + buttons[i3]['id'] + '/' + data[i]['id'] + '/" ';
                                    if (buttons[i3]['class'] != undefined && buttons[i3]['class'] != '') card += 'class="btn btn-sm text-white pointer ml-1 ' + buttons[i3]['class'] + '" ';
                                    else card += 'class="btn btn-secondary btn-sm text-white pointer ml-1" ';
                                    card += '>';
                                    card += buttons[i3]['label'];
                                    card += '</a>';
                                }

                                if (view) card += '<a class="im-table-view btn btn-secondary btn-sm text-white pointer ml-1" data-id="' + data[i]['id'] + '"><i class="fa fa-search" aria-hidden="true"></i></a>';
                                if (editUrl != '') card += '<a href="' + editUrl + data[i]['id'] + '" class="im-table-edit btn btn-secondary btn-sm text-white pointer ml-1"><i class="fa fa-pencil-alt" aria-hidden="true"></i></a>';
                                if (deleteUrl != '') card += '<a class="im-table-delete btn btn-secondary btn-sm text-white pointer ml-1" data-id="' + data[i]['id'] + '"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                                card += '</div>';
                            card += '</div>';
                        card += '</div>';
                    card += '</div>';
                }

                $('#dt-cards-body').html(card);

                if (limit != 0) {
                    $('#dt-cards-body').append('<div class="text-center"><div class="dt-table-paginator"></div></div>');
                    $('.dt-table-paginator').dtPaginator({
                        page: page,
                        lastPage: Math.ceil(total/limit),
                        showNext: true,
                        showPrevious: true,
                        showFirst: true,
                        showLast: true,
                    });
                }
                if (refresh > 0) {
                    refreshInterval = setInterval(function() {
                        load_body()
                    }, (refresh*1000));
                }
			}
        });
    }
    function get_columns()
    {
        var columns = [];

        object.find('thead tr:first-child th').each(function() {
            columns.push($(this).data('column'));
        });
        return columns;
    }
    function formatValue(value)
    {
        if (value == null) return '';

        if (value === true) return '<a class="green"><i class="fas fa-check"></i></a>';
        if (value === false) return '<a class="red"><i class="fas fa-times"></i></a>';

        if (value.timestamp) {

            return moment(value.timestamp * 1000).format('YYYY-MM-DD HH:mm:ss');

            var d = new Date(value.timestamp * 1000);

            var date = d.getFullYear() + '-';
            date += d.getFullYear() + '-';


            //var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            //return d.toLocaleDateString('en-US', options);
        }

        return value;
    }

    $(document).on('click', '.im-table-sort', function()
    {

        var direction = jQuery(this).data('sort');

        object.find('thead tr:first-child .im-table-sort').each(function() {
            $(this).data('sort', '');
            $(this).find('a').html('<i class="fa fa-sort" aria-hidden="true"></i>');
        });

        sortColumn = $(this).data('column');

        if(direction == 'desc') {
            jQuery(this).data('sort','asc');
            $(this).find('a').html('<i class="fa fa-sort-up" aria-hidden="true"></i>');
            sortDirection = 'asc';
        } else {
            jQuery(this).data('sort','desc');
            $(this).find('a').html('<i class="fa fa-sort-down" aria-hidden="true"></i>');
            sortDirection = 'desc';
        }
        load_body();
    });
    $(document).on('blur', '.im-table-filter input', function()
    {
        load_body();
    });
    $(document).on('submit', '.im-table-form', function(e)
    {
        e.preventDefault();
        load_body();
    });
    $(document).on('click', '.pagination .page-link', function()
    {

        page = jQuery(this).data('page');
        offset = (page-1) * limit;
        load_body();
    });

    $(document).on('click', '.im-table-view', function()
    {

        var id = $(this).data('id');

        for (i = 0; i < data.length; i++) {
            if (data[i]['id'] == id) {
                var row = data[i];
                break;
            }
        }
        var content = '';

        for (var key in row) {
            var fieldTitle = key;
            if (object.find('[data-column="' + key + '"]').length) fieldTitle = $('[data-column="' + key + '"]').text();
            content += '<label class="modal-label">' + fieldTitle + '</label><br>' + formatValue(row[key]) + '<br><br>';
        }

        var buttons = '';
        if (editUrl != '') buttons += '<a href="' + editUrl + '/' + data[i]['id'] + '" class="im-table-edit btn btn-secondary btn-sm text-white pointer ml-1"><i class="fa fa-pencil-alt" aria-hidden="true"></i></a>';
        if (deleteUrl != '') buttons += '<a class="im-table-delete btn btn-secondary btn-sm text-white pointer ml-1" data-id="' + data[i]['id'] + '"><i class="fa fa-trash" aria-hidden="true"></i></a>';

        $('#dt-modal').dtModal({
            title: translations['View record'] + ': ' + row['id'],
            content: content,
            buttons: buttons,
            size: 'lg'
        });
    });

    $(document).on('click', '.im-table-delete', function()
    {

        var id = $(this).data('id');

        var content = '<p>' + translations['You are about to delete a record, this cannot be undone.'] + '</p>';
        content += '<p>' + translations['Do you want to proceed?'] + '</p>';

        var buttons = '<button type="button" class="btn btn-default" data-dismiss="modal">' + translations['Cancel'] + '</button>';
        buttons += '<button type="button" id="confirm-delete" class="btn btn-danger btn-ok" data-url="' + deleteUrl + '" data-id="' + id + '" data-dismiss="modal">' + translations['Delete'] + '</button>';

        $('#dt-modal').dtModal({
            title: translations['Confirm delete'],
            content: content,
            buttons: buttons
        });
    });
    jQuery(document).on('click', '#confirm-delete', function(e)
    {

        var url = $(this).data('url');
        var id = $(this).data('id');

		$.ajax({
			url: url + '/' + id,
			type: 'POST',
			dataType: 'html',
			success: function(result) {

                if (result == 1){
                    setTimeout(function() {
                        object.find('#row-'+id).css({'background': '#d23430'});
                        object.find('#row-'+id + ' td').each(function() {
                            $(this).wrapInner('<div class="td_wrapper"></div>');
                        });
                        object.find('#row-'+id + ' .td_wrapper').each(function(index, element) {
                            $(this).slideUp();
                        });
                        object.find('#row-'+id + ' td').animate({
                            'padding-top': '0px',
                            'padding-bottom': '0px'
                        }, function() {
                            load_body();
                        });
                    },400);

                } else {

                    var content = '<p class="red">' + translations['An error has occurred.'] + '</div>';
                    content += '<p class="red">' + translations['Please try again.'] + '</div>';

                    $('#dt-modal').dtModal({
                        title: translations['Error'],
                        content: content,
                    });
                }

			}
        });
    });
}( jQuery ));
