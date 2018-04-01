
(function($) {

    var columns;
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

    $.fn.dtTable = function(options){

        // Default options
        var settings = $.extend({
            functions: true,
            sorting: true,
            filter: false,
            view: true,
            ajaxUrl: '',
            addUrl: '',
            editUrl: '',
            deleteUrl: '',
            limit: 15
        }, options );

        object = this;
        columns = get_columns();
        filter = settings.filter;
        view = settings.view;
        sorting = settings.sorting;
        functions = settings.functions;
        limit = settings.limit;
        ajaxUrl = settings.ajaxUrl;
        addUrl = settings.addUrl;
        editUrl = settings.editUrl;
        deleteUrl = settings.deleteUrl;

        this.wrap('<div class="table-responsive"></div>');
        this.wrap('<form action="" class="im-table-form" method="post"></form>');
        this.addClass('im-table');


        if (settings.sorting) {
            this.find('thead tr:first-child th').each(function() {
                $(this).append('<a class="ml-3"><i class="fa fa-sort" aria-hidden="true"></i></a>');
                $(this).addClass('im-table-sort pointer');
            });
        }

        // Add functions to header
        if (settings.functions) {
            this.find('thead tr:first-child').prepend('<th><input type="checkbox"></th>');
            if (settings.addUrl) var addBtn = '<a href="' + settings.addUrl + '" class="table-add pointer float-right btn btn-success btn-sm"><i class="fa fa-plus"></i></a>';
            else var addBtn = '';
            this.find('thead tr:first-child').append('<th class="width-200">' + addBtn + '</th>');
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
        load_body();

        return this;
    }

	function load_body() {

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

                object.find('tbody').remove();
                object.find('tfoot').remove();

                var tbody = '<tbody>';

                for (i = 0; i < data.length; i++) {

                    tbody += '<tr id="row-' + data[i]['id'] + '">';
                    if (functions) tbody += '<td><input type="checkbox"></td>';
                    for (i2 = 0; i2 < columns.length; i2++) {
                        tbody += "<td>" + formatValue(data[i][columns[i2]]) + "</td>";
                    }
                    if (functions) {
                        tbody += '<td class="text-right">';
                        if (view) tbody += '<a class="im-table-view btn btn-secondary btn-sm text-white pointer ml-1" data-id="' + data[i]['id'] + '"><i class="fa fa-search" aria-hidden="true"></i></a>';
                        if (editUrl != '') tbody += '<a href="' + editUrl + data[i]['id'] + '" class="im-table-edit btn btn-secondary btn-sm text-white pointer ml-1"><i class="fa fa-pencil-alt" aria-hidden="true"></i></a>';
                        if (deleteUrl != '') tbody += '<a class="im-table-delete btn btn-secondary btn-sm text-white pointer ml-1" data-id="' + data[i]['id'] + '"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                        tbody += '</td>';
                    }
                    tbody += '</tr>';
                }

                tbody += '</tbody>';

                $('.table thead').after(tbody);

                var colspan = object.find('thead tr:first-child th').length;
                object.find('tbody').after('<tfoot class="text-center"><tr><td colspan="' + colspan + '"><div class="dt-table-paginator"></div></td></tr></tfoot>');
                $('.dt-table-paginator').dtPaginator({
                    page: page,
                    lastPage: Math.ceil(total/limit),
                    showNext: true,
                    showPrevious: true,
                    showFirst: true,
                    showLast: true,
                });
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
            var d = new Date(value.timestamp * 1000);
            var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return d.toLocaleDateString('en-US', options);
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
