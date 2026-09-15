;
(function ($) {
    var $window = $(window),
        debounce = function (func, wait, immediate) {
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
                if (callNow)
                    func.apply(context, args);
            };
        };

    $window.on('elementor/frontend/init', function () {
        var ModuleHandler = elementorModules.frontend.handlers.Base,
            EqualHeight;

        EqualHeight = ModuleHandler.extend({

            _applied: [],

            settings: function (key) {
                return this.getElementSettings('sa_eqh_' + key);
            },

            isDisabledOnDevice: function () {
                var breakpoints = (elementorFrontend.config && elementorFrontend.config.breakpoints)
                                  || elementorFrontendConfig.breakpoints,
                    windowWidth = $window.outerWidth(),
                    tabletWidth = breakpoints.lg,
                    mobileWidth = breakpoints.md;

                if (this.settings('disable_on_mobile') === 'yes' && windowWidth < mobileWidth) {
                    return true;
                }
                if (this.settings('disable_on_tablet') === 'yes' && windowWidth >= mobileWidth && windowWidth < tabletWidth) {
                    return true;
                }
                return false;
            },

            cleanup: function () {
                this._applied.forEach(function ($el) {
                    $el.matchHeight({ remove: true });
                });
                this._applied = [];
            },

            getTargetGroups: function (elementContainer) {
                var applyElements = this.settings('apply_elements') || 'select_widgets',
                    selectorMap = {
                        'widgets':         '.elementor-widget',
                        'widgets_1st':     '.elementor-widget > :nth-child(1)',
                        'widgets_1st_2nd': '.elementor-widget > :nth-child(2)',
                        'widgets_1st_3rd': '.elementor-widget > :nth-child(3)',
                        'widgets_2nd':     '.elementor-widget > :nth-child(1) > :nth-child(1)',
                        'widgets_2nd_2nd': '.elementor-widget > :nth-child(1) > :nth-child(2)',
                        'widgets_3rd':     '.elementor-widget > :nth-child(1) > :nth-child(1) > :nth-child(1)',
                    };

                // Select Widgets mode: one independent matchHeight group per widget type
                if (applyElements === 'select_widgets') {
                    var widgetList = this.settings('widget_list') || [];
                    if (!widgetList.length) {
                        return [];
                    }
                    return widgetList
                        .map(function (widgetType) {
                            return elementContainer.find('.elementor-widget-' + widgetType);
                        })
                        .filter(function ($group) { return $group.length > 0; });
                }

                // Legacy selector-map modes: single combined group
                var selector = selectorMap[applyElements];
                if (!selector && applyElements === 'custom') {
                    selector = this.settings('apply_elements_custom') || null;
                }
                if (selector) {
                    var $group = elementContainer.find(selector);
                    return $group.length ? [$group] : [];
                }

                return [];
            },

            run: function () {
                this.cleanup();

                if (this.settings('enable') !== 'yes') {
                    return;
                }

                if (this.isDisabledOnDevice()) {
                    return;
                }

                var elementContainer = $('.elementor-element-' + this.getID()),
                    applyElements    = this.settings('apply_elements') || 'select_widgets',
                    groups           = this.getTargetGroups(elementContainer),
                    self             = this,
                    options          = {
                        byRow    : applyElements !== 'select_widgets',
                        property : this.settings('css_property') === 'min_height' ? 'min-height' : 'height'
                    };

                groups.forEach(function ($group) {
                    $group.matchHeight(options);
                    self._applied.push($group);
                });
            },

            onUnload: function () {
                this.cleanup();
            },

            onElementChange: debounce(function (prop) {
                if (prop.indexOf('sa_eqh_') !== -1) {
                    this.run();
                }
            }, 400),

            bindEvents: function () {
                this.run();
                $window.on('resize orientationchange', debounce(this.run.bind(this), 100));
            }
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/section', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(EqualHeight, { $element: $scope });
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/container', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(EqualHeight, { $element: $scope });
        });

    });

}(jQuery));
