;(function ($) {
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
        if (callNow) func.apply(context, args);
      };
    };
  $window.on('elementor/frontend/init', function () {
    var ModuleHandler = elementorModules.frontend.handlers.Base,
      RipplesEffect;

    RipplesEffect = ModuleHandler.extend({

      bindEvents: function () {
        this.run();
        $window.on('resize.ripples-' + this.getID(), debounce(function () {
          if (this.RippleEl) {
            $(this.RippleEl).ripples('updateSize');
          }
        }.bind(this), 200));
      },

      unbindEvents: function () {
        $window.off('resize.ripples-' + this.getID());
        if (this.RippleEl) {
          $(this.RippleEl).ripples('destroy');
          this.RippleEl = null;
        }
      },

      getDefaultSettings: function () {
        return {
          interactive: true,
        };
      },

      settings: function (key) {
        return this.getElementSettings('sa_rf_' + key);
      },

      onElementChange: debounce(function (prop) {
        if (prop.indexOf('sa_rf_') === -1) {
          return;
        }

        var el = $(this.RippleEl);

        if (prop === 'sa_rf_enable') {
          if (this.settings('enable') !== 'yes') {
            if (this.RippleEl) {
              el.ripples('destroy');
              this.RippleEl = null;
            }
          } else {
            this.run();
          }
          return;
        }

        if (!this.RippleEl) {
          return;
        }

        if (prop === 'sa_rf_drop_radius') {
          el.ripples('set', 'dropRadius', this.settings('drop_radius.size') || 20);
        } else if (prop === 'sa_rf_perturbance') {
          el.ripples('set', 'perturbance', this.settings('perturbance.size') || 0.03);
        } else {
          el.ripples('destroy');
          this.run();
        }
      }, 300),

      run: function () {
        var options = this.getDefaultSettings(),
          elementID = this.getID(),
          elementContainer = $('.elementor-element-' + elementID),
          element = $('.elementor-element-' + elementID);

        if (this.settings('enable') !== 'yes') {
          return;
        }

        if ($(this.$element).hasClass('elementor-widget')) {
          elementContainer.css({ 'position': 'relative' });
        }

        if ($(this.$element).hasClass('elementor-column')) {
          elementContainer = $('.elementor-element-' + elementID).find('.elementor-column-wrap');
          element = elementContainer;
          elementContainer.css({ 'position': 'relative' });
        }

        if (this.settings('drop_radius.size')) {
          options.dropRadius = this.settings('drop_radius.size') || 20;
        }
        if (this.settings('perturbance.size')) {
          options.perturbance = this.settings('perturbance.size') || 0.03;
        }
        if (this.settings('resolution')) {
          options.resolution = this.settings('resolution') || 256;
        }

        options.interactive = true;
        options.id = elementID;
        options.crossOrigin = 'anonymous';

        this.RippleEl = element;
        $(element).ripples(options);
      }
    });


    elementorFrontend.hooks.addAction('frontend/element_ready/section', function ($scope) {
      elementorFrontend.elementsHandler.addHandler(RipplesEffect, {
        $element: $scope
      });
    });

    elementorFrontend.hooks.addAction('frontend/element_ready/container', function ($scope) {
      elementorFrontend.elementsHandler.addHandler(RipplesEffect, {
        $element: $scope
      });
    });

    elementorFrontend.hooks.addAction('frontend/element_ready/column', function ($scope) {
      elementorFrontend.elementsHandler.addHandler(RipplesEffect, {
        $element: $scope
      });
    });

    elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
      elementorFrontend.elementsHandler.addHandler(RipplesEffect, {
        $element: $scope
      });
    });

  });

}(jQuery));
