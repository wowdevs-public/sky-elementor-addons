;(function ($, elementor) {
    'use strict';

    var widgetLogoCarousel = function ($scope, $) {

        var $logoCarousel = $scope.find('.sa-logo-carousel'),
            $carouselContainer = $logoCarousel.find('.swiper'),
            $settings = $logoCarousel.data('settings');

        if (!$logoCarousel.length) {
            return;
        }

        var $tooltips = $logoCarousel.find('.sa-tippy-tooltip'),
            widgetID = $scope.data('id');

        $tooltips.each(function () {
            tippy(this, {
                allowHTML: true,
                theme: 'sa-tippy-' + widgetID
            });
        });

        // Disable loop when slide count <= highest slidesPerView to prevent Swiper blank-slide glitch
        if ($settings.loop) {
            var slideCount = $carouselContainer.find('.swiper-slide').length;
            var maxPerView = $settings.slidesPerView || 1;
            if ($settings.breakpoints) {
                Object.values($settings.breakpoints).forEach(function (bp) {
                    if (bp.slidesPerView && bp.slidesPerView > maxPerView) {
                        maxPerView = bp.slidesPerView;
                    }
                });
            }
            if (slideCount <= maxPerView) {
                $settings.loop = false;
            }
        }

        const Swiper = elementorFrontend.utils.swiper;
        initSwiper();
        async function initSwiper() {
            var swiper = await new Swiper($carouselContainer, $settings);
            if ($settings.pauseOnHover) {
                $carouselContainer.hover(function () {
                    this.swiper.autoplay.stop();
                }, function () {
                    this.swiper.autoplay.start();
                });
            }
        }

    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/sky-logo-carousel.default', widgetLogoCarousel);
    });

}(jQuery, window.elementorFrontend));
