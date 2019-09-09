$('#previewButton').on('click', function (e) {
    e.preventDefault();
    const form = $('form[name="document_template_form"]');
    form.attr('target', '_blank');
    $('#submitPreview').click();
    form.attr('target', '_self');
});
