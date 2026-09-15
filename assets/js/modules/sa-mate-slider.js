;(function ($, elementor) {
    'use strict';

var widgetMateSlider = function ($scope, $) {

    var $dataWrapper = $scope.find('.sa-mate-slider'),
        $primaryContainer = $dataWrapper.find('.sa-mate-primary.swiper'),
        $secondaryContainer = $dataWrapper.find('.sa-mate-secondary.swiper'),
        $primarySettings = $dataWrapper.data('primary-settings'),
        $secondarySettings = $dataWrapper.data('secondary-settings');

    if (!$dataWrapper.length) {
        return;
    }

    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {

        var secondary = await new Swiper($secondaryContainer, $secondarySettings);

        var primary = await new Swiper($primaryContainer, $primarySettings);

        primary.controller.control = secondary;
        secondary.controller.control = primary;

        if ($primarySettings.pauseOnHover) {
            $($primaryContainer).hover(function () {
                (this).swiper.autoplay.stop();
            }, function () {
                (this).swiper.autoplay.start();
            });
        }
    };
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-mate-slider.default', widgetMateSlider);
    });

}(jQuery, window.elementorFrontend));
