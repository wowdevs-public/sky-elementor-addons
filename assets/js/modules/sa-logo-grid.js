;(function ($, elementor) {
    'use strict';

    var widgetLogoGrid = function ($scope, $) {

        var $logoGrid = $scope.find('.sa-logo-grid');

        if (!$logoGrid.length) {
            return;
        }

        var $tooltips = $logoGrid.find('.sa-tippy-tooltip'),
            widgetID = $scope.data('id');

        $tooltips.each(function () {
            tippy(this, {
                allowHTML: true,
                theme: 'sa-tippy-' + widgetID
            });
        });

    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/sky-logo-grid.default', widgetLogoGrid);
    });

}(jQuery, window.elementorFrontend));
