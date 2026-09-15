;
(function ($) {
    var $window = $(window),
        toGranimColor = function (color) {
            if (!color) return null;
            // Granim alpha regex (.?\d{1,3}) can't match "0.5" — strip the leading zero
            var c = color.replace(/(rgba\([\d\s,]+,\s*)0(\.\d+\))$/, '$1$2');
            // Reject CSS vars, HSL, and anything Granim won't accept
            return /^#[0-9a-fA-F]{3,6}$|^rgba?\([\d.,\s]+\)$/.test(c) ? c : null;
        },
        // Repeater colors picked from the Global palette save as '' with the palette id in
        // __globals__ — resolve the id to its --e-global-color-{id} CSS variable, otherwise
        // every global-colored item is skipped and the canvas silently never renders.
        resolveItemColor = function (item, key, context) {
            var value = item[key];
            if (!value && item.__globals__ && item.__globals__[key]) {
                var match = item.__globals__[key].match(/id=([^&]+)/);
                if (match) {
                    value = getComputedStyle(context && context.length ? context[0] : document.body)
                        .getPropertyValue('--e-global-color-' + match[1]).trim();
                }
            }
            return toGranimColor(value);
        },
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
        };
    $window.on('elementor/frontend/init', function () {
        var ModuleHandler = elementorModules.frontend.handlers.Base,
            AnimatedGradientBg;

        AnimatedGradientBg = ModuleHandler.extend({

            bindEvents: function () {
                this.run();
            },

            getDefaultSettings: function () {
                return {
                    direction: 'left-right',
                };
            },

            settings: function (key) {
                return this.getElementSettings('sa_agbg_' + key);
            },

            onElementChange: debounce(function (prop) {
                if (prop.indexOf('sa_agbg_') !== -1) {
                    if ($('#' + this.Granim).length) {
                        $('#' + this.Granim).remove();
                    }
                    this.run();
                }
            }, 400),

            run: function () {
                var options = this.getDefaultSettings(),
                    elementID = this.getID(),
                    elementContainer = $('.elementor-element-' + elementID),
                    element = 'sa-agbg-' + elementID;

                if (this.settings('enable') !== 'yes') {
                    return;
                }

                if ($(this.$element).hasClass('elementor-widget')) {
                    elementContainer = $('.elementor-element-' + elementID + ' > :first-child');
                    elementContainer.css({
                        'position': 'relative',
                        'overflow': 'hidden',
                    });
                }

                if ($(this.$element).hasClass('elementor-column')) {
                    elementContainer = $('.elementor-element-' + elementID).find('.elementor-column-wrap');
                    elementContainer.css({
                        // 'position' : 'relative',
                        'overflow': 'hidden',
                    });
                }

                var $color_list = this.settings('color_list');
                var gradients = [];

                $color_list.forEach(function (item) {
                    var start = resolveItemColor(item, 'sa_agbg_start_color', elementContainer);
                    var end = resolveItemColor(item, 'sa_agbg_end_color', elementContainer);
                    if (!start || !end) {
                        return;
                    }
                    var stops = [start];
                    var mid = resolveItemColor(item, 'sa_agbg_mid_color', elementContainer);
                    if (mid) {
                        stops.push(mid);
                    }
                    stops.push(end);
                    gradients.push(stops);
                });

                if (!gradients.length) {
                    return;
                }

                // Granim requires all gradients to have the same stop count
                var firstLen = gradients[0].length;
                if (gradients.some(function (g) { return g.length !== firstLen; })) {
                    gradients = gradients.map(function (g) { return [g[0], g[g.length - 1]]; });
                }

                elementContainer.prepend('<canvas id="' + element + '" class="sa-animated-gradient-bg sa-d-block sa-w-100 sa-h-100"></canvas>');

                $('#' + element).css({
                    'position': 'absolute',
                    'top': 0,
                    'right': 0,
                    'bottom': 0,
                    'left': 0,
                    'pointer-events': 'none',
                });

                options.element = '#' + element;
                options.isPausedWhenNotInView = this.settings('pause_on_scroll') !== 'no';

                if (this.settings('direction')) {
                    options.direction = this.settings('direction');
                }

                var transitionSpeed = this.settings('transition_speed.size') || 7000;

                options.states = {
                    'default-state': {
                        'gradients': gradients,
                        'transitionSpeed': transitionSpeed,
                    }
                };

                var loopCount = parseInt(this.settings('loop_count')) || 0;
                var granimInstance;

                if (loopCount > 0) {
                    var transitionCount = 0;
                    var maxTransitions = loopCount * gradients.length;
                    options.onGradientChange = function () {
                        transitionCount++;
                        if (transitionCount >= maxTransitions && granimInstance) {
                            granimInstance.pause();
                        }
                    };
                }

                granimInstance = new Granim(options);
                this.Granim = element;
            }
        });


        elementorFrontend.hooks.addAction('frontend/element_ready/section', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(AnimatedGradientBg, {
                $element: $scope
            });
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/container', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(AnimatedGradientBg, {
                $element: $scope
            });
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/column', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(AnimatedGradientBg, {
                $element: $scope
            });
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(AnimatedGradientBg, {
                $element: $scope
            });
        });

    });

}(jQuery));
