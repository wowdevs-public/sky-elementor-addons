;(function ($, elementor) {
    'use strict';

var widgetContentSwitcher = function ($scope, $) {

    var $contentSwitcher = $scope.find('.sa-content-switcher'),
        $settings = $contentSwitcher.data('settings');

    if (!$contentSwitcher.length) {
        return;
    }

    var switcherToggle = $contentSwitcher.find('.sa-switcher-toggle'),
        checkbox = $($settings.checkbox),
        switcherWrapper = $contentSwitcher.find('.sa-switcher-wrap'),
        contentWrapper = $contentSwitcher.find('.sa-content-wrapper');

    // Apply user-defined transition speed as a CSS custom property
    if ($settings.transitionSpeed) {
        $contentSwitcher.css('--sa-transition-speed', $settings.transitionSpeed + 'ms');
    }

    // ── Binary toggle mode ────────────────────────────────────────────────────
    if ($settings.type !== 'button') {

        function activateBinaryItem(isSecondary) {
            if (isSecondary) {
                switcherWrapper.find('.sa-switch-item').removeClass('sa-active');
                switcherWrapper.find('.sa-switch-item.sa-secondary').addClass('sa-active');
                contentWrapper.find('.sa-switch-content-item').removeClass('sa-active');
                contentWrapper.find('.sa-switch-content-item.sa-secondary').addClass('sa-active');
            } else {
                switcherWrapper.find('.sa-switch-item').removeClass('sa-active');
                switcherWrapper.find('.sa-switch-item.sa-primary').addClass('sa-active');
                contentWrapper.find('.sa-switch-content-item').removeClass('sa-active');
                contentWrapper.find('.sa-switch-content-item.sa-primary').addClass('sa-active');
            }
        }

        switcherToggle.on('click', function () {
            activateBinaryItem(checkbox.is(':checked'));
        });

        // Deep link: activate the matching item on page load
        var hash = window.location.hash ? window.location.hash.slice(1) : '';
        if (hash) {
            var $primary   = switcherWrapper.find('.sa-switch-item.sa-primary');
            var $secondary = switcherWrapper.find('.sa-switch-item.sa-secondary');
            if ($primary.data('slug') === hash) {
                checkbox.prop('checked', false);
                activateBinaryItem(false);
            } else if ($secondary.data('slug') === hash) {
                checkbox.prop('checked', true);
                activateBinaryItem(true);
            }
        }
    }

    // ── Button (tabs) mode ────────────────────────────────────────────────────
    if ($settings.type === 'button') {

        var borderSize = $settings.borderSize || 0,
            isVertical = $settings.orientation === 'vertical',
            tabs       = $contentSwitcher.find('.sa-switcher-tabs');

        function positionSelector($item) {
            var pos = $item.position();
            if (isVertical) {
                $contentSwitcher.find('.sa-selector').css({
                    top:    pos.top + 'px',
                    height: $item.outerHeight() + 'px',
                });
            } else {
                $contentSwitcher.find('.sa-selector').css({
                    left:  pos.left + 'px',
                    width: ($item.innerWidth() + borderSize) + 'px',
                });
            }
        }

        var activeItem = tabs.find('.sa-active');
        if (activeItem.length) {
            positionSelector(activeItem);
        }

        tabs.on('click', 'a', function (e) {
            e.preventDefault();

            var id = $(this).data('id');

            switcherWrapper.find('.sa-switcher-tabs a').removeClass('sa-active');
            $(this).addClass('sa-active');

            contentWrapper.find('.sa-switch-content-item').removeClass('sa-active');
            contentWrapper.find('#' + id).addClass('sa-active');

            positionSelector($(this));
        });

        // Deep link: click the matching tab on page load
        var hash = window.location.hash ? window.location.hash.slice(1) : '';
        if (hash) {
            var $target = tabs.find('[data-slug="' + hash + '"]');
            if ($target.length) {
                $target.trigger('click');
            }
        }

        if ($('body').hasClass('rtl')) {
            $contentSwitcher.find('.sa-switcher-tabs .sa-selector').css({
                right: 'auto',
            });
        }
    }

};
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-content-switcher.default', widgetContentSwitcher);
    });

}(jQuery, window.elementorFrontend));
