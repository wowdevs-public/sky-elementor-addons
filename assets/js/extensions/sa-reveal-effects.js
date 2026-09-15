;
(function ($) {
    var $window = $(window);

    var debounce = function (func, wait, immediate) {
        var timeout;
        return function () {
            var context = this,
                args = arguments;
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                timeout = null;
                if (!immediate) {
                    func.apply(context, args);
                }
            }, wait);
            if (callNow) {
                func.apply(context, args);
            }
        };
    };

    $window.on('elementor/frontend/init', function () {
        var ModuleHandler = elementorModules.frontend.handlers.Base,
            RevealEffects;

        RevealEffects = ModuleHandler.extend({

            bindEvents: function () {
                this._cleanup();
                this.run();
            },

            getDefaultSettings: function () {
                return {
                    direction: 'lr',
                    easing: 'easeInOutQuint',
                    duration: 600,
                    delay: 100,
                    bgColors: ['#111'],
                    coverArea: 0
                };
            },

            settings: function (key) {
                return this.getElementSettings('sa_reveal_fx_' + key);
            },

            onElementChange: debounce(function (prop) {
                if (prop.indexOf('sa_reveal_fx') !== -1) {
                    this._cleanup();
                    this.run();
                }
            }, 400),

            _cleanup: function () {
                var instances = this._revealInstances;
                if (!instances || !instances.length) {
                    return;
                }
                instances.forEach(function (inst) {
                    var el = inst.el;
                    if (el && inst.content && el.parentNode) {
                        el.innerHTML = inst.content.innerHTML;
                        el.classList.remove('block-revealer');
                    }
                });
                this._revealInstances = [];
            },

            run: function () {
                var self = this,
                    options = this.getDefaultSettings(),
                    elementID = this.getID(),
                    element = this.$element.get(0);

                if (this.settings('enable') !== 'yes') {
                    return;
                }

                if (this.$element.hasClass('elementor-widget')) {
                    element = $('.elementor-element-' + elementID).get(0);
                }

                if (!element) {
                    return;
                }

                if (this.settings('direction')) {
                    options.direction = this.settings('direction');
                }

                var bgColors = this.settings('bg_colors');
                if (bgColors) {
                    var parsed = bgColors.split(/[ ,]+/).filter(Boolean);
                    if (parsed.length) {
                        options.bgColors = parsed;
                    }
                }

                options.duration = parseInt(this.settings('duration.size'), 10) || options.duration;
                options.delay    = parseInt(this.settings('delay.size'), 10)    || options.delay;

                if (this.settings('easing')) {
                    options.easing = this.settings('easing');
                }

                options.onHalfway = function (contentEl) {
                    contentEl.style.opacity = 1;
                };

                // backward-compat: layers was NUMBER (plain int), now SLIDER ({size, unit})
                var layers = parseInt(this.settings('layers.size'), 10) || parseInt(this.settings('layers'), 10) || 1;

                var coverAreaRaw = parseInt(this.settings('cover_area'), 10);
                if (!isNaN(coverAreaRaw)) {
                    options.coverArea = coverAreaRaw;
                }

                var contentHidden = this.settings('content_show') !== 'yes';
                var isLoop        = this.settings('loop') === 'yes';
                var thresholdRaw  = parseInt(this.settings('threshold.size'), 10);
                var threshold     = (thresholdRaw > 0 && thresholdRaw <= 100) ? thresholdRaw / 100 : 0.8;

                var targets = $(element);
                if (this.settings('select_type') === 'custom') {
                    var selector = this.settings('selector');
                    if (selector && selector.length) {
                        targets = $(element).find(selector);
                    }
                }

                if (!targets.length) {
                    return;
                }

                self._revealInstances = [];

                targets.each(function () {
                    var el = this;
                    var revealerEffect = new RevealFx(el, {
                        layers: layers,
                        isContentHidden: contentHidden,
                        revealSettings: options
                    });
                    self._revealInstances.push(revealerEffect);

                    skyAddonsObserver(el, function () {
                        revealerEffect.reveal();
                    }, {
                        rootMargin: '0px',
                        threshold: threshold,
                        loop: isLoop
                    });
                });
            }
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(RevealEffects, { $element: $scope });
        });
        elementorFrontend.hooks.addAction('frontend/element_ready/section', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(RevealEffects, { $element: $scope });
        });
        elementorFrontend.hooks.addAction('frontend/element_ready/container', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(RevealEffects, { $element: $scope });
        });
    });

}(jQuery));
