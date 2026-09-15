;(function ($, elementor) {
    'use strict';

    var widgetImageCompare = function ($scope, $) {

        var $imageCompare = $scope.find('.sa-image-compare');
        var $settings = $imageCompare.data('settings');
        if (!$imageCompare.length) {
            return;
        }

        var viewers = document.querySelectorAll('#' + $settings.id);

        // Responsive starting point — PHP can't detect viewport, so JS overrides here
        var isMobile = window.innerWidth <= 767;
        if (isMobile && $settings.mobileStartingPoint) {
            $settings.startingPoint = $settings.mobileStartingPoint;
        }

        viewers.forEach(function (element) {
            new ImageCompare(element, $settings).mount();

            // DOM overrides — library sets these as inline styles, so we override post-mount

            // Circle Size
            if ($settings.addCircle && $settings.circleSize && $settings.circleSize !== 50) {
                $(element).find('.icv__circle').css({ width: $settings.circleSize + 'px', height: $settings.circleSize + 'px' });
            }

            // Circle Vertical Offset
            if ($settings.addCircle && $settings.circleVerticalOffset && $settings.circleVerticalOffset !== 0) {
                $(element).find('.icv__circle').css('transform', 'translateY(' + $settings.circleVerticalOffset + 'px)');
            }

            // Smoothing Easing
            if ($settings.smoothing && $settings.smoothingEase && 'ease-out' !== $settings.smoothingEase) {
                $(element).find('.icv__theme-wrapper, .icv__wrapper').css('transition-timing-function', $settings.smoothingEase);
            }

            // Label Fade Duration
            if ($settings.labelOptions && $settings.labelOptions.onHover && $settings.labelFadeDuration && $settings.labelFadeDuration !== 0.25) {
                $(element).find('.icv__label.on-hover').css('transition-duration', $settings.labelFadeDuration + 's');
            }

            // Entry Animation — fade-in only, no transform conflict with library positioning
            if ($settings.animateOnLoad) {
                var duration = $settings.entryAnimationDuration || 800;
                var ease = $settings.smoothingEase || 'ease-out';
                var keyframeStyle = '@keyframes sa-ic-fade-in { from { opacity: 0; } to { opacity: 1; } }';
                $('<style id="sa-ic-anim-' + $settings.id + '">' + keyframeStyle + '</style>').appendTo('head');
                $(element).find('.icv__control').css('animation', 'sa-ic-fade-in ' + duration + 'ms ' + ease + ' forwards');
            }
        });
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-image-compare.default', widgetImageCompare);
    });

}(jQuery, window.elementorFrontend));
