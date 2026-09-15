;(function ($, elementor) {
    'use strict';

var widgetNumber = function ($scope, $) {
    var $number = $scope.find('.sa-number'),
        $settings = $number.data('settings');

    if (!$number.length) {
        return;
    }

    if ($settings.animation == 'no') {
        return;
    }

    skyAddonsObserver($scope[0], function () {
        $($number).find('.sa-text').prop('Counter', 0).animate({
            Counter: $settings.number
        }, {
            duration: $settings.time,
            easing: 'swing',
            step: function (now) {
                $(this).text(Math.ceil(now));
            }
        });

    }, {
        root: null, // Use the viewport as the root
        rootMargin: '0px', // No margin around the root
        threshold: 0.8 // 80% visibility (1 - 0.8)
    });
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-number.default', widgetNumber);
    });

}(jQuery, window.elementorFrontend));
