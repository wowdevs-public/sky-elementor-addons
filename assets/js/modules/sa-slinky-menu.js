; (function ($, elementor) {
    'use strict';

    var widgetSlinkyMenu = function ($scope, $) {
        var $slinkyMenu = $scope.find('.sa-slinky-menu');
        var $settings = $slinkyMenu.data('settings');

        if (!$slinkyMenu.length) {
            return;
        }

        $slinkyMenu.removeClass('sa-d-none');

        var options = {
            resize: $settings.resize !== false,
            speed: $settings.speed || 300,
            title: $settings.title === true
        };

        var $menu = $($settings.id);

        $menu.slinky(options);

        // Slinky measures the active pane once at init and writes the result as an inline
        // height on the menu, which is `overflow: hidden`. Inside a container that is
        // hidden at that moment — an offcanvas, a closed tab or accordion, a modal — the
        // measurement is 0 and never repeats, so the menu stays invisible even after the
        // container opens. Its `resize` option only re-measures on level changes, not on
        // visibility, and the library exposes no public method to force it.
        if ('undefined' === typeof IntersectionObserver) {
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                var $pane = $menu.find('ul.active').first();

                if (!$pane.length) {
                    $pane = $menu.find('ul').first();
                }

                var height = $pane.outerHeight();

                // Left live rather than disconnected: an offcanvas can be closed and
                // reopened, and the pane in view may differ each time.
                if (height && height !== $menu.height()) {
                    $menu.height(height);
                }
            });
        });

        observer.observe($menu[0]);

    };
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-slinky-menu.default', widgetSlinkyMenu);
    });

}(jQuery, window.elementorFrontend));
