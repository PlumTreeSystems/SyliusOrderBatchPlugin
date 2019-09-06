$('.filters-container').on('click', '.filter-tag > label > i', function(e){
    $(e.target).closest('.filter-tag').remove();
});

$('#saveFilterButton').on('click', function (e) {
    e.stopPropagation();
    e.preventDefault();
    $('#save-filter-modal').modal('show');
});

$('#saveBatchButton').on('click', function (e) {
    e.preventDefault();
    $('#save-batch-modal').modal('show');
});

// save filter modal
$('#save-filter-modal').modal({
    onApprove: function() {
        $('#filterSubmit').click();
        $('#filtersForm').addClass('loading');
    }
});

$('input[name="modalFilterName"]').on('change', function (e) {
    const val = e.target.value;
    $('input[name="_filterName"]').val(val);
});

// save batch modal

$('#save-batch-modal').modal({
    onApprove: function() {
        $('#batchSubmit').click();
        $('#filtersForm').addClass('loading');
        $('#ordersListContent').addClass('loading');
    }
});

$('input[name="modalBatchName"]').on('change', function (e) {
    const val = e.target.value;
    $('input[name="_batchName"]').val(val);
});

// filter search request
$( document ).ready(function () {
    const url = $('#saveFilterButton').data('filterUrl');

    $.ajax({
        url: url,
        type: "GET",
        contentType: 'application/json',
        success: function (res) {
            const resData = JSON.parse(res);
            $('#filtersSearch').autocomplete({
                minLength: 0,
                source: resData,
                focus: function( event, ui ) {
                    $('#filtersSearch').val( ui.item.label );
                    return false;
                },
                select: function (event, ui) {
                    addFilter(ui.item.id, ui.item.label);
                    $('#filtersSearch').val();
                }
            });

        },
        error: function (err) {
            console.error(err);
        }
    });
});

// batch filter search request
$( document ).ready(function () {
    const url = $('#save-batch-modal').data('batchFilterUrl');

    if (typeof url == 'undefined') {
        return;
    }

    $.ajax({
        url: url,
        type: "GET",
        contentType: 'application/json',
        success: function (res) {
            const resData = JSON.parse(res);
            $('#modalBatchName').autocomplete({
                minLength: 0,
                source: resData,
                select: function (event, ui) {
                    $('#modalBatchName').val( ui.item.label );
                    $('input[name="_batchName"]').val(ui.item.label);
                }
            });

        },
        error: function (err) {
            console.error(err);
        }
    });
});

// shows batch names list on focus
$('#modalBatchName').on('focus', function () {
    const e = jQuery.Event("keydown");
    e.keyCode = 50;
    $("#modalBatchName").trigger(e);
});

function addFilter(value, title) {
    if ($('#filter' + value).length === 0) {
        const element = '<div class="filter-tag">' +
            '<label for="filter' + value + '" class="ui blue labeled icon button">' +
            '<i class="icon remove"></i> ' + title + '</label>' +
            '<input type="hidden" id="filter' + value + '" name="filter[]" value="' + value + '"></div>';
        $('.filters-container').append(element);
    }
}
