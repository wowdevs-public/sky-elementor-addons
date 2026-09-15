;
(function ($) {
    var $window = $(window),
        debounce = function (func, wait, immediate) {
            // 'private' variable for instance
            // The returned function will be able to reference this due to closure.
            // Each call to the returned function will share this common timer.
            var timeout;

            // Calling debounce returns a new anonymous function
            return function () {
                // reference the context and args for the setTimeout function
                var context = this,
                    args = arguments;

                // Should the function be called now? If immediate is true
                //   and not already in a timeout then the answer is: Yes
                var callNow = immediate && !timeout;

                // This is the basic debounce behaviour where you can call this
                //   function several times, but it will only execute once
                //   [before or after imposing a delay].
                //   Each time the returned function is called, the timer starts over.
                clearTimeout(timeout);

                // Set the new timeout
                timeout = setTimeout(function () {

                    // Inside the timeout function, clear the timeout variable
                    // which will let the next execution run when in 'immediate' mode
                    timeout = null;

                    // Check if the function already ran with the immediate flag
                    if (!immediate) {
                        // Call the original function with apply
                        // apply lets you define the 'this' object as well as the arguments
                        //    (both captured before setTimeout)
                        func.apply(context, args);
                    }
                }, wait);

                // Immediate mode and no wait timer? Execute the function..
                if (callNow)
                    func.apply(context, args);
            };
        },
        // A slider handle the user cleared comes back as '' (or undefined when the control
        // was never saved). Anything else is a real number, including 0.
        isBlank = function (v) {
            return '' === v || null === v || undefined === v;
        },
        // anime.js 3.x resolves an easing name through its Penner table and calls
        // `.apply` on the result, so an unknown name (CSS names like 'ease-in-out'
        // from imported or hand-edited data) throws "Cannot read properties of
        // undefined (reading 'apply')" and kills every handler after it. Map the
        // CSS names, accept anything anime knows, fall back to the default otherwise.
        CSS_EASING_MAP = {
            'linear': 'linear',
            'ease': 'easeInOutSine',
            'ease-in': 'easeInSine',
            'ease-out': 'easeOutSine',
            'ease-in-out': 'easeInOutSine'
        },
        ANIME_EASING = /^(linear|spring|steps|cubicBezier|ease(In|Out|InOut|OutIn)(Quad|Cubic|Quart|Quint|Sine|Expo|Circ|Back|Bounce|Elastic))(\(.*\))?$/,
        normalizeEasing = function (value, fallback) {
            if (typeof value !== 'string' || '' === value) {
                return fallback;
            }
            if (CSS_EASING_MAP[value]) {
                return CSS_EASING_MAP[value];
            }
            return ANIME_EASING.test(value) ? value : fallback;
        };
    $window.on('elementor/frontend/init', function () {
        var ModuleHandler = elementorModules.frontend.handlers.Base,
            FloatingEffects;

        FloatingEffects = ModuleHandler.extend({

            bindEvents: function () {
                this.run();
            },

            getDefaultSettings: function () {
                return {
                    direction: 'alternate',
                    easing: 'easeInOutSine',
                    loop: true
                };
            },

            settings: function (key) {
                return this.getElementSettings('sa_floating_ef_' + key);
            },

            // Returns [from, to] for one transform axis, or null when the axis carries no
            // motion and should be left out of the anime options entirely.
            //
            // The guards this replaces read `settings('rotate_x.sizes.from').length !== 0`.
            // A slider value is a number, `.length` on it is undefined, and `undefined !== 0`
            // is always true — so every axis of an enabled group animated at its default.
            // Switching Rotate on for a small tilt gave a 45deg tumble across X, Y and Z.
            axis: function (key) {
                var val = this.settings(key);
                if (!val || !val.sizes) { return null; }

                var rawFrom = val.sizes.from,
                    rawTo   = val.size || val.sizes.to;

                // Both handles cleared — the "leave this axis alone" the old guards meant to catch.
                if (isBlank(rawFrom) && isBlank(rawTo)) { return null; }

                var from = parseFloat(rawFrom) || 0,
                    to   = parseFloat(rawTo) || 0;

                // Nothing to animate. This is what keeps an untouched axis inert now that the
                // non-primary defaults are a flat 0 -> 0.
                if (from === to) { return null; }

                return [from, to];
            },

            onElementChange: debounce(function (prop) {
                if (prop.indexOf('sa_floating') !== -1) {
                    this.anime && this.anime.restart();
                    this.run();
                }
            }, 400),

            run: function () {
                var options = this.getDefaultSettings(),
                    element = this.$element.get(0);

                options.targets = element;

                if (this.settings('enable') !== 'yes') {
                    return;
                }

                // Each group writes only the axes that actually move — see axis().
                var self = this,
                    addAxis = function (prop, key, group) {
                        var range = self.axis(key);
                        if (!range) { return; }
                        options[prop] = {
                            value: range,
                            duration: self.settings(group + '_duration.size'),
                            delay: self.settings(group + '_delay.size') || 0
                        };
                    };

                if (this.settings('translate_toggle')) {
                    addAxis('translateX', 'translate_x', 'translate');
                    addAxis('translateY', 'translate_y', 'translate');
                }

                if (this.settings('rotate_toggle')) {
                    addAxis('rotateX', 'rotate_x', 'rotate');
                    addAxis('rotateY', 'rotate_y', 'rotate');
                    addAxis('rotateZ', 'rotate_z', 'rotate');
                }

                if (this.settings('scale_toggle')) {
                    addAxis('scaleX', 'scale_x', 'scale');
                    addAxis('scaleY', 'scale_y', 'scale');
                }

                if (this.settings('skew_toggle')) {
                    addAxis('skewX', 'skew_x', 'skew');
                    addAxis('skewY', 'skew_y', 'skew');
                }

                options.easing = normalizeEasing(this.settings('easing'), options.easing);

                if (
                    this.settings('translate_toggle') ||
                    this.settings('rotate_toggle') ||
                    this.settings('scale_toggle') ||
                    this.settings('skew_toggle')
                ) {
                    this.anime = window.anime && window.anime(options);
                }

            }
        });


        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(FloatingEffects, {
                $element: $scope
            });
        });
    });

}(jQuery));
