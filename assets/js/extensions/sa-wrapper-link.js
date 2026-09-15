;jQuery('body').on('click', '.sa-element-link', function () {
    var timeout,
        $element = jQuery(this),
        data = $element.data('sa-element-link'),
        id = 'sa-element-link-' + $element.data('id'),
        idSelector = '#' + id;

    if (jQuery(idSelector).length === 0) {
        var options = {
            href: data.url,
            target: data.is_external ? '_blank' : '_self',
            class: 'sa-d-none',
            id: id,
            rel: data.nofollow ? 'nofollow noreferrer' : ''
        };

        jQuery('body').append(
            jQuery(document.createElement('a')).prop(options)
        );

        jQuery(idSelector)[0].click();

        timeout = setTimeout(function () {
            jQuery('body').find(idSelector).remove();
            clearTimeout(timeout);
        }, 1000);

    }

});
