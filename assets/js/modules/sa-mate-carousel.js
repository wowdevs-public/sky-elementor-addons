;(function ($, elementor) {
    'use strict';

// This widget uses the global carousel handler

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-mate-carousel.default', widgetGlobalCarousel);
    });

}(jQuery, window.elementorFrontend));
