;(function ($, elementor) {
    'use strict';

    var SaReadingProgress = {

        initDefault: function ($scope) {
            var $el    = $scope.find('.sa-reading-progress.sa-skin-default');
            var $inner = $el.find('.sa-rp-inner');
            if (!$el.length) return;

            $(window).off('scroll.sa-rp-default');

            function getMax() {
                return Math.max(1, $(document).height() - $(window).height());
            }

            function update() {
                var pct = Math.min(100, ($(window).scrollTop() / getMax()) * 100);
                $inner.css('width', pct + '%');
            }

            update();
            $(window).on('scroll.sa-rp-default', function () {
                window.requestAnimationFrame(update);
            });
        },

        initFancyHorizontal: function ($scope) {
            var $el   = $scope.find('.sa-reading-progress.sa-skin-fancy-horizontal');
            var $span = $el.find('span');
            if (!$el.length) return;

            $(window).off('scroll.sa-rp-fh');

            function getMax() {
                return Math.max(1, $(document).height() - $(window).height());
            }

            function update() {
                var pct = Math.min(100, ($(window).scrollTop() / getMax()) * 100);
                $el.css('width', pct + '%');
                if ($span.length) $span.text(Math.round(pct));
            }

            update();
            $(window).on('scroll.sa-rp-fh', function () {
                window.requestAnimationFrame(update);
            });
        },

        initFancyVertical: function ($scope) {
            var $el   = $scope.find('.sa-reading-progress.sa-skin-fancy-vertical');
            var $span = $el.find('span');
            if (!$el.length) return;

            $(window).off('scroll.sa-rp-fv');

            function getMax() {
                return Math.max(1, $(document).height() - $(window).height());
            }

            function update() {
                var pct = Math.min(100, ($(window).scrollTop() / getMax()) * 100);
                $el.css('height', pct + '%');
                if ($span.length) $span.text(Math.round(pct));
            }

            update();
            $(window).on('scroll.sa-rp-fv', function () {
                window.requestAnimationFrame(update);
            });
        },

        initScrollTop: function ($scope) {
            var $el = $scope.find('.sa-reading-progress.sa-skin-scroll-top');
            if (!$el.length) return;

            var path = $el.find('path')[0];
            if (!path) return;

            var pathLen   = path.getTotalLength();
            var threshold = parseInt($el.data('scroll-threshold'), 10) || 50;
            var duration  = parseInt($el.data('scroll-duration'),  10) || 550;

            path.style.transition       = 'none';
            path.style.strokeDasharray  = pathLen + ' ' + pathLen;
            path.style.strokeDashoffset = pathLen;
            path.getBoundingClientRect();
            path.style.transition = 'stroke-dashoffset 10ms linear';

            $(window).off('scroll.sa-rp-st');

            function getMax() {
                return Math.max(1, $(document).height() - $(window).height());
            }

            function update() {
                var st      = $(window).scrollTop();
                var offset  = pathLen - (st * pathLen / getMax());
                path.style.strokeDashoffset = Math.max(0, offset);
                $el.toggleClass('sa-active-progress', st > threshold);
            }

            update();
            $(window).on('scroll.sa-rp-st', function () {
                window.requestAnimationFrame(update);
            });

            $el.off('click.sa-rp-st').on('click.sa-rp-st', function (e) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: 0 }, duration);
            });
        },

        initWithCursor: function ($scope) {
            var $dot = $scope.find('.sa-reading-progress.sa-skin-with-cursor');
            if (!$dot.length) return;

            // Clean up previous instance
            $('body').find('.sa-rp-cursor-outer, .sa-rp-cursor-inner').remove();
            $(window).off('scroll.sa-rp-wc');
            $(document).off('mousemove.sa-rp-wc');

            // Read CSS vars from dot (inherits from {{WRAPPER}})
            var dotEl  = $dot[0];
            var cs     = window.getComputedStyle(dotEl);
            var lag    = parseFloat(cs.getPropertyValue('--sky-rp-cursor-lag').trim())    || 150;
            var blend  = cs.getPropertyValue('--sky-rp-cursor-blend-mode').trim()         || 'normal';
            var dotSize = cs.getPropertyValue('--sky-rp-cursor-dot-size').trim()          || '8px';
            var primary  = cs.getPropertyValue('--sky-r-p-primary-color').trim()          || 'blueviolet';
            var secondary = cs.getPropertyValue('--sky-r-p-secondary-color').trim()       || 'rgba(0,0,0,0.15)';
            var ringColor = cs.getPropertyValue('--sky-rp-cursor-ring-color').trim()      || secondary;

            // Build DOM
            var $outer = $('<div class="sa-progress-with-cursor-2 sa-rp-cursor-outer"></div>').appendTo('body');
            var $inner = $('<div class="sa-progress-with-cursor-3 sa-rp-cursor-inner"></div>').appendTo('body');
            var $wrap  = $([
                '<div class="sa-progress-wrap">',
                    '<svg class="sa-progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">',
                        '<path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"/>',
                    '</svg>',
                '</div>'
            ].join('')).appendTo($outer);

            var outEl = $outer[0];
            var innEl = $inner[0];

            var ringThickness = cs.getPropertyValue('--sky-rp-cursor-ring-thickness').trim() || '2px';
            var outerSize     = cs.getPropertyValue('--sky-reading-progress-size').trim()    || '0px';

            // Propagate CSS vars to body elements (they can't inherit from {{WRAPPER}})
            function setVar(el, name, val) { el.style.setProperty(name, val); }
            [outEl, innEl].forEach(function (el) {
                setVar(el, '--sky-rp-cursor-dot-size',         dotSize);
                setVar(el, '--sky-rp-cursor-blend-mode',       blend);
                setVar(el, '--sky-r-p-primary-color',          primary);
                setVar(el, '--sky-r-p-secondary-color',        ringColor);
                setVar(el, '--sky-rp-cursor-ring-color',       ringColor);
                setVar(el, '--sky-rp-cursor-ring-thickness',   ringThickness);
                setVar(el, '--sky-reading-progress-size',      outerSize);
            });

            // Staggered lag: outer = full lag, inner = half lag (creates depth effect)
            var lagHalf = Math.round(lag * 0.5);
            outEl.style.transition = [
                'left ' + lag     + 'ms ease-out',
                'top '  + lag     + 'ms ease-out',
                'transform 400ms ease',
                'opacity 400ms ease'
            ].join(', ');
            innEl.style.transition = [
                'left ' + lagHalf + 'ms ease-out',
                'top '  + lagHalf + 'ms ease-out',
                'transform 300ms ease',
                'opacity 300ms ease'
            ].join(', ');

            // Blend mode on dot
            dotEl.style.mixBlendMode = blend;

            // SVG progress ring setup
            var path = $wrap.find('path')[0];
            if (path) {
                var pathLen = path.getTotalLength();
                path.style.transition       = 'none';
                path.style.strokeDasharray  = pathLen + ' ' + pathLen;
                path.style.strokeDashoffset = pathLen;
                path.style.stroke           = primary;
                path.getBoundingClientRect();
                path.style.transition = 'stroke-dashoffset 10ms linear';

                $(window).on('scroll.sa-rp-wc', function () {
                    window.requestAnimationFrame(function () {
                        var st  = $(window).scrollTop();
                        var max = Math.max(1, $(document).height() - $(window).height());
                        path.style.strokeDashoffset = Math.max(0, pathLen - (st * pathLen / max));
                    });
                });
            }

            // Fade in all layers on first mousemove
            var activated = false;
            var rafId     = null;

            $(document).on('mousemove.sa-rp-wc', function (e) {
                var x = e.clientX, y = e.clientY;

                if (!activated) {
                    activated = true;
                    $dot.add($outer).add($inner).addClass('sa-rp-cursor-shown');
                }

                if (rafId) cancelAnimationFrame(rafId);
                rafId = requestAnimationFrame(function () {
                    dotEl.style.left = x + 'px';
                    dotEl.style.top  = y + 'px';
                    outEl.style.left = x + 'px';
                    outEl.style.top  = y + 'px';
                    innEl.style.left = x + 'px';
                    innEl.style.top  = y + 'px';
                });
            });

            // Hover effects on .hover-target elements
            $(document).find('.hover-target')
                .off('mouseenter.sa-rp-wc mouseleave.sa-rp-wc')
                .on('mouseenter.sa-rp-wc', function () {
                    $dot.addClass('sa-rp-cursor-hover');
                    $outer.add($inner).addClass('hover');
                })
                .on('mouseleave.sa-rp-wc', function () {
                    $dot.removeClass('sa-rp-cursor-hover');
                    $outer.add($inner).removeClass('hover');
                });
        },

        register: function () {
            var self = this;
            jQuery(window).on('elementor/frontend/init', function () {
                var hooks = elementorFrontend.hooks;
                hooks.addAction('frontend/element_ready/sky-reading-progress.default',                  function ($s) { self.initDefault($s); });
                hooks.addAction('frontend/element_ready/sky-reading-progress.sky-skin-fancy-horizontal', function ($s) { self.initFancyHorizontal($s); });
                hooks.addAction('frontend/element_ready/sky-reading-progress.sky-skin-fancy-vertical',   function ($s) { self.initFancyVertical($s); });
                hooks.addAction('frontend/element_ready/sky-reading-progress.sky-skin-scroll-top',       function ($s) { self.initScrollTop($s); });
                hooks.addAction('frontend/element_ready/sky-reading-progress.sky-skin-with-cursor',      function ($s) { self.initWithCursor($s); });
            });
        }
    };

    SaReadingProgress.register();

}(jQuery, window.elementorFrontend));
