;(function ($, elementor) {
    'use strict';

var widgetPortionEffect = function ($scope, $) {
    var $portionEffect = $scope.find('.sa-portion-effect');
    if (!$portionEffect.length) {
        return;
    }

    var $settings = $portionEffect.data('settings');

    $portionEffect.find('.sa-side').css('background-image', 'url(' + $settings.image + ')');

    var animated = false;

    var animate = function () {
        if (animated) { return; }
        animated = true;
        $portionEffect.addClass('sa-animated');
    };

    // Snap all panels to hidden state instantly — used for clean repeat reset
    var snapToHidden = function () {
        $portionEffect.addClass('sa-pe-no-trans');
        $portionEffect.removeClass('sa-animated');
        animated = false;
        requestAnimationFrame(function () {
            $portionEffect.removeClass('sa-pe-no-trans');
        });
    };

    if ($settings.entrance_animation === 'yes' && !elementorFrontend.isEditMode()) {
        var stagger    = ($settings.stagger !== undefined)    ? parseInt($settings.stagger, 10)    : 150;
        var repeat     = $settings.animation_repeat === 'yes';
        var threshold  = ($settings.threshold !== undefined)  ? parseInt($settings.threshold, 10) / 100 : 0.4;

        // Set per-block stagger delay as CSS var — inherited by .sa-side via transition-delay
        $portionEffect.find('.sa-block').each(function (i) {
            this.style.setProperty('--sky-pe-anim-delay', (i * stagger) + 'ms');
        });

        // Snap to hidden instantly (sa-pe-no-trans prevents transition flash)
        $portionEffect.addClass('sa-pe-ready sa-pe-no-trans');
        requestAnimationFrame(function () {
            $portionEffect.removeClass('sa-pe-no-trans');

            // 400ms delay: above-fold widgets animate visibly on page load
            setTimeout(function () {
                if ('IntersectionObserver' in window) {
                    // Two thresholds: 0 = fully gone (reset), threshold = enough visible (animate)
                    var thresholds = threshold > 0 ? [ 0, threshold ] : [ 0 ];

                    var observer = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.intersectionRatio >= threshold) {
                                // Enough of element visible → animate in
                                requestAnimationFrame(function () {
                                    requestAnimationFrame(animate);
                                });
                                if (!repeat) {
                                    observer.unobserve(entry.target);
                                }
                            } else if (repeat && !entry.isIntersecting) {
                                // Fully out of viewport → safe to snap to hidden, no white flash
                                snapToHidden();
                            }
                        });
                    }, { threshold: thresholds });

                    observer.observe($portionEffect[0]);
                } else {
                    animate();
                }
            }, 400);
        });

    } else {
        animate();
    }
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-portion-effect.default', widgetPortionEffect);
    });

}(jQuery, window.elementorFrontend));
