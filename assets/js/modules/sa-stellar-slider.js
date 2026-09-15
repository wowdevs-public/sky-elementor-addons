;(function ($, elementor) {
    'use strict';

var widgetStellarSlider = function ($scope, $) {

    var $stellarSlider = $scope.find('.sa-stellar-slider'),
        $container     = $stellarSlider.find('.swiper'),
        $settings      = $stellarSlider.data('settings');

    if (!$stellarSlider.length) {
        return;
    }

    // A copy, so the jQuery .data() cache stays clean when the editor
    // re-initialises the widget.
    var options = $.extend(true, {}, $settings);

    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {

        var slider = await new Swiper($container, options);

        if (options.pauseOnHover) {
            $stellarSlider.hover(function () {
                slider.autoplay.stop();
            }, function () {
                slider.autoplay.start();
            });
        }
    };
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-stellar-slider.default', widgetStellarSlider);
    });

}(jQuery, window.elementorFrontend));
