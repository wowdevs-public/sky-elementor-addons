;(function ($, elementor) {
    'use strict';

var widgetAdvancedSlider = function ($scope, $) {
    var $slider = $scope.find('.sa-advanced-slider'),
        $sliderContainer = $slider.find('.swiper'),
        $settings = $slider.data('settings');

    if (!$slider.length) {
        return;
    }

    function activateSlideVideo($slide) {
        // Lazy-load iframe: set src from data-src on first activation
        $slide.find('iframe[data-src]').each(function () {
            this.src = this.dataset.src;
            delete this.dataset.src;
        });
        // Resume already-loaded iframes
        $slide.find('[data-video-type="youtube"] iframe').each(function () {
            try {
                this.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
            } catch (e) {}
        });
        $slide.find('[data-video-type="vimeo"] iframe').each(function () {
            try {
                this.contentWindow.postMessage('{"method":"play"}', '*');
            } catch (e) {}
        });
        // HTML5 video
        $slide.find('video').each(function () {
            this.play().catch(function () {});
        });
    }

    function deactivateSlideVideo($slide) {
        $slide.find('[data-video-type="youtube"] iframe').each(function () {
            try {
                this.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
            } catch (e) {}
        });
        $slide.find('[data-video-type="vimeo"] iframe').each(function () {
            try {
                this.contentWindow.postMessage('{"method":"pause"}', '*');
            } catch (e) {}
        });
        $slide.find('video').each(function () {
            this.pause();
        });
    }

    var hasVideo = $slider.find('.sa-slide-video-wrapper').length > 0;

    // Destroy any existing Swiper instance on this container before re-init
    if ($sliderContainer[0] && $sliderContainer[0].swiper) {
        $sliderContainer[0].swiper.destroy(true, true);
    }

    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {
        var swiper = await new Swiper($sliderContainer, $settings);

        if ($settings.pauseOnHover) {
            $($sliderContainer).hover(function () {
                (this).swiper.autoplay.stop();
            }, function () {
                (this).swiper.autoplay.start();
            });
        }

        if (hasVideo) {
            // Load and play the initial active slide's video immediately
            activateSlideVideo($slider.find('.swiper-slide-active'));

            swiper.on('slideChangeTransitionStart', function () {
                deactivateSlideVideo($slider.find('.swiper-slide-prev, .swiper-slide-next'));
            });

            swiper.on('slideChangeTransitionEnd', function () {
                activateSlideVideo($slider.find('.swiper-slide-active'));
            });
        }
    };

};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-advanced-slider.default', widgetAdvancedSlider);
    });

}(jQuery, window.elementorFrontend));
