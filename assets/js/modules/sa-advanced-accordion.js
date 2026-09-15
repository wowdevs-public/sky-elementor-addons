;(function ($, elementor) {
    'use strict';

var widgetAdvancedAccordion = function ($scope, $) {
    var $advancedAccordion = $scope.find('.sa-advanced-accordion');
    var $settings = $advancedAccordion.data('settings');

    if (!$advancedAccordion.length) {
        return;
    }

    // The vendor library gives every trigger role="button", aria-expanded and aria-controls,
    // and binds a keydown handler — but never a tabindex. A div with role="button" and no
    // tabindex is announced as a button while sitting outside the tab order, so it cannot be
    // reached at all and the library's own keydown can never fire. Toggling lives only in its
    // click handler, which meant no panel could be opened from the keyboard.
    //
    // Not rendered as a real <button>: the trigger holds the widget's configurable heading,
    // and <button> takes phrasing content only — a heading nested in one is invalid markup
    // and assistive tech flattens it into the button's name, losing the heading level.
    $advancedAccordion.find('.sa-ac-trigger').attr('tabindex', 0);

    $advancedAccordion.on('keydown', '.sa-ac-trigger', function (e) {
        // What a native button honours. ' ' is the modern key name for Space, 'Spacebar' the
        // legacy one. Arrow/Home/End stay with the library's own roving-focus handler.
        if ('Enter' !== e.key && ' ' !== e.key && 'Spacebar' !== e.key) {
            return;
        }
        // Space would otherwise scroll the page.
        e.preventDefault();
        this.click();
    });

    var accOptions = {
        duration:     $settings.duration,
        showMultiple: $settings.showMultiple,
        collapse:     $settings.collapse,
        elementClass: 'sa-ac-item',
        triggerClass: 'sa-ac-trigger',
        panelClass:   'sa-ac-panel',
        activeClass:  'is-active',
    };

    var $cols = $advancedAccordion.children('.sa-acc-col');

    if ($cols.length) {
        // Multi-column: init on each column div so accordion.js finds
        // .sa-ac-item as direct children. Map global openOnInit indices
        // to per-column indices so the correct item opens in the right column.
        var openOnInit = $settings.openOnInit || [];
        var offset = 0;

        $cols.each(function () {
            var colEl    = this;
            var colCount = $(colEl).children('.sa-ac-item').length;
            var colOpen  = openOnInit
                .filter(function (i) { return i >= offset && i < offset + colCount; })
                .map(function (i) { return i - offset; });

            new Accordion(colEl, $.extend({}, accOptions, { openOnInit: colOpen }));
            offset += colCount;
        });
    } else {
        new Accordion('#' + $settings.id, $.extend({}, accOptions, {
            openOnInit: $settings.openOnInit,
        }));
    }
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-advanced-accordion.default', widgetAdvancedAccordion);
    });

}(jQuery, window.elementorFrontend));
