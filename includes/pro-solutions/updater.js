jQuery(document).ready(function ($) {
    $('#update-sky-pro-plugin').on('click', function () {
        const button = $(this);
        button.prop('disabled', true).text('Updating...');

        $.ajax({
            url: SkyUpdater.ajax_url,
            method: 'POST',
            data: {
                action: 'update_sky_pro_plugin',
                _ajax_nonce: SkyUpdater.nonce,
            },
            success: function (response) {
                $('.sky-addons-update-solutions').append(response);
            },
            error: function () {
                alert('An unexpected error occurred.');
            },
            complete: function () {
                button.prop('disabled', false).text('Update Sky Pro Plugin');
            },
        });
    });
});