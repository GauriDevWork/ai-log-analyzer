jQuery(document).on('click', '.aila-ai-btn', function () {
    const wrapper = jQuery(this).closest('.aila-error');
    const error = JSON.parse(wrapper.attr('data-error'));

    const output = wrapper.find('.aila-ai-response');
    output.text('Asking AI…').show();

    jQuery.post(ajaxurl, {
        action: 'aila_explain_error',
        error: error,
        _ajax_nonce: AILA_AJAX.nonce
    }, function (response) {
        if (response.success) {
            output.text(response.data);
        } else {
            output.text('AI error: ' + response.data);
        }
    });
});
