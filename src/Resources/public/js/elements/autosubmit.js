function autosubmit(inp) {
    let timeout = null;

    const waitTime = $(inp).data('waitTime');

    inp.keydown = function (e) {

        if (e.keyCode == 13) {
            e.preventDefault();
        }
    };

    $(inp).on('input', function (e) {
        clearTimeout(timeout);

        timeout = setTimeout(function () {
            const form = inp.closest('form');
            $(form).addClass('loading');
            form.submit();
        }, waitTime);
    });
}

for (let i = 0; i < $('input[data-autosubmit=true]').length; i++) {
    autosubmit($('input[data-autosubmit=true]')[i]);
}
