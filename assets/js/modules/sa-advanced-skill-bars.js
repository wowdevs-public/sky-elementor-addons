;(function ($, elementor) {
    'use strict';

// IntersectionObserver is not usable here: during a fast/smooth scroll it delivers no
// entry at all for some items (measured: rows 3-4 of 72 got only the initial ratio 0),
// so no in-callback fallback can reach them. Sweep the pending items from a shared
// rAF-throttled scroll handler instead — one listener for every skill bar on the page.
var pending = [];
var bound   = false;
var ticking = false;

function sweep() {
    ticking = false;

    var viewH = window.innerHeight || document.documentElement.clientHeight;

    pending = pending.filter(function (entry) {
        if (!entry.el.isConnected) {
            return false; // editor re-render left a detached node behind
        }

        var rect = entry.el.getBoundingClientRect();

        if (!rect.width && !rect.height) {
            return true; // not laid out yet (hidden tab / accordion) — keep waiting
        }

        // Cap the requirement at what is physically reachable: an item taller than the
        // viewport can never show 80% of itself.
        var visible = Math.min(rect.bottom, viewH) - Math.max(rect.top, 0);
        var need    = Math.min(entry.threshold, viewH / rect.height * 0.95) * rect.height;

        if (rect.bottom <= 0 || visible >= need) {
            entry.fill();
            return false;
        }

        return true;
    });

    if (!pending.length && bound) {
        bound = false;
        window.removeEventListener('scroll', schedule);
        window.removeEventListener('resize', schedule);
    }
}

function schedule() {
    if (!ticking) {
        ticking = true;
        requestAnimationFrame(sweep);
    }
}

function watch(el, threshold, fill) {
    pending.push({ el: el, threshold: threshold, fill: fill });

    if (!bound) {
        bound = true;
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule, { passive: true });
    }

    schedule(); // first sweep also catches anything already past on load
}

var widgetAdvancedSkillBars = function ($scope, $) {
    var $advancedSkillBars = $scope.find('.sa-advanced-skills'),
        $items = $scope.find('.sa-skill-item');

    if (!$advancedSkillBars.length) {
        return;
    }

    var settings      = $advancedSkillBars.data('settings') || {};
    var animDuration  = settings.animDuration  || 2600;
    var animThreshold = settings.animThreshold || 0.8;
    var valuePrefix   = settings.valuePrefix   || '';
    var valueSuffix   = settings.valueSuffix   !== undefined ? settings.valueSuffix : '%';

    $items.each(function () {
        var $item  = $(this);
        var filled = false;

        watch(this, animThreshold, function () {
            if (filled) {
                return;
            }

            filled = true;

            $item.find('.sa-skill-progress-bar').each(function () {
                var $bar          = $(this);
                var skillMaxValue = parseFloat($bar.attr('data-max-value')) || 100;
                var skillFillVal  = parseFloat($bar.attr('data-width'))     || 0;

                // Keep the counter at the value's own precision, otherwise a decimal
                // skill (8.6) prints a number its own bar contradicts.
                var decimals = (String(skillFillVal).split('.')[1] || '').length;

                $bar.css('width', (skillFillVal * 100) / skillMaxValue + '%');
                $bar.children('.sa-skill-content-wrapper, .sa-skill-value').css('transform', 'scale(1)');

                $item.find('.sa-skill-value').prop('Counter', 0).animate({
                    Counter: skillFillVal
                }, {
                    duration: animDuration,
                    easing: 'swing',
                    step: function (now) {
                        $(this).text(valuePrefix + now.toFixed(decimals) + valueSuffix);
                    },
                    complete: function () {
                        $(this).text(valuePrefix + skillFillVal + valueSuffix);
                    }
                });
            });
        });
    });
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-advanced-skill-bars.default', widgetAdvancedSkillBars);
    });

}(jQuery, window.elementorFrontend));
