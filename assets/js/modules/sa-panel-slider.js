;(function ($, elementor) {
    'use strict';

var widgetPanelSlider = function ($scope, $) {
    var $panelSlider = $scope.find('.sa-panel-slider'),
        $panelSliderContainer = $panelSlider.find('.swiper'),
        $settings = $panelSlider.data('settings');

    if (!$panelSlider.length) {
        return;
    }
    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {

        var sliderThumbs = await new Swiper($panelSliderContainer, $settings);

        if ($settings.pauseOnHover) {
            $($panelSlider).hover(function () {
                sliderThumbs.autoplay.stop();
            }, function () {
                sliderThumbs.autoplay.start();
            });
        }

        var $sliderSettings = $panelSlider.data('slider-settings');

        if ('hover' == $sliderSettings.showContent) {
            $panelSlider.find('.swiper-slide').on('mouseover', function () {
                $(this).siblings().removeClass('sa-active');
                $(this).addClass('sa-active');
            })
            $panelSlider.find('.swiper-slide').on('mouseleave', function () {
                $(this).siblings().removeClass('sa-active');
                $(this).removeClass('sa-active');
            })
        }

        if ('active_hover' == $sliderSettings.showContent) {
            $panelSlider.find('.swiper-slide').on('mouseover', function () {
                $(this).addClass('sa-active');
            })
            $panelSlider.find('.swiper-slide').on('mouseleave', function () {
                if ($(this).hasClass('swiper-slide-active') !== true) {
                    $(this).removeClass('sa-active');
                }
            })
        }

        if ('active' == $sliderSettings.showContent || 'active_hover' == $sliderSettings.showContent) {
            $panelSlider.find('.swiper-slide.swiper-slide-active').siblings().removeClass('sa-active');
            $panelSlider.find('.swiper-slide.swiper-slide-active').addClass('sa-active');

            sliderThumbs.on('slideChangeTransitionEnd', function (e) {
                $panelSlider.find('.swiper-slide.swiper-slide-active').siblings().removeClass('sa-active');
                $panelSlider.find('.swiper-slide.swiper-slide-active').addClass('sa-active');
            });
        }

        if ('always' == $sliderSettings.showContent) {
            $panelSlider.find('.swiper-slide').addClass('sa-active');
        }

    };

};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-panel-slider.default', widgetPanelSlider);
    });

}(jQuery, window.elementorFrontend));
