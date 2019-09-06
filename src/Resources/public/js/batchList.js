// mark shipped modal

$('#markShippedButtonLabel').on('click', function (e) {
    e.preventDefault();
    $('#checkShippedModal').modal('show');
});

$('#confirmationCheckShippedModal').on('click', function() {
   $('#markShippedButton').click();
});

// remove order modal

$('button[name="removeButton"]').on('click', function (e) {
    const element = $(e.target);
    const url = element.data('url');

    $('#removeOrderModal').modal('show');

    $('#removeOrderModalAgree').attr('href', url);
});

$('#downloadShippingNoteLabel').on('click', function (e) {
    e.preventDefault();
    const form = $('#filtersForm');
    form.attr('target', '_blank');
    $('#downloadShippingNotes').click();
    form.attr('target', '_self');
});

// disable autoship modal

$('button[name="disableAutoshipButton"]').on('click', function (e) {
    const element = $(e.target);
    const url = element.data('url');

    $('#disableModal').modal('show');

    $('#disableConfirmationButton').attr('href', url);
});

// enable autoship modal

$('button[name="enableAutoshipButton"]').on('click', function (e) {
    const element = $(e.target);
    const url = element.data('url');
    $('#enableModal').modal('show');

    $('#enableConfirmationButton').attr('href', url);
});

// run out of stock modal

$('#runOutOfStockLabel').on('click', function (e) {
    e.preventDefault();
    $('#runOutOfStockModal').modal('show');
});

$('#runOutOfStockConfirmationButton').on('click', function() {
    $('#runOutOfStockButton').click();
});

// run failed payments modal

$('#runFailedPaymentLabel').on('click', function (e) {
    e.preventDefault();
    $('#runFailedPaymentsModal').modal('show');
});

$('#failedPaymentsConfirmationButton').on('click', function() {
    $('#runFailedPaymentButton').click();
});