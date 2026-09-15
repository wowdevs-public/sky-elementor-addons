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
        if (callNow) func.apply(context, args);
      };
    };

  $window.on('elementor/frontend/init', function () {
    var ModuleHandler = elementorModules.frontend.handlers.Base,
      SimpleParallaxHandler;

    SimpleParallaxHandler = ModuleHandler.extend({

      onInit: function () {
        this._spInstances = [];
        ModuleHandler.prototype.onInit.apply(this, arguments);
      },

      bindEvents: function () {
        this.run();
      },

      getDefaultSettings: function () {
        return {
          scale: 1.4,
          orientation: 'up',
          delay: 0,
        };
      },

      // v7 matches orientation with an exact switch on space-separated values
      // ('up left'). Stored control values still use the v6 hyphen form.
      normalizeOrientation: function (orientation) {
        return String(orientation || 'up').replace('-', ' ');
      },

      // Selectors come from free-text controls and are re-read on every keystroke
      // in the editor, so a half-typed one must not throw.
      querySafe: function (selector) {
        if (!selector) {
          return null;
        }
        try {
          return document.querySelector(selector);
        } catch (e) {
          return null;
        }
      },

      settings: function (key) {
        return this.getElementSettings('sa_sp_' + key);
      },

      destroyParallax: function () {
        this._spInstances.forEach(function (instance) {
          instance.destroy();
        });
        this._spInstances = [];
      },

      onElementChange: debounce(function (prop) {
        if (prop.indexOf('sa_sp') !== -1) {
          this.destroyParallax();
          this.run();
        }
      }, 400),

      run: function () {
        var self = this;

        if (this.settings('enable') !== 'yes' || typeof SimpleParallax === 'undefined') {
          return;
        }

        var options = this.getDefaultSettings();

        var scale = this.settings('scale');
        if (scale && scale.size) {
          options.scale = scale.size;
        }
        if (this.settings('orientation')) {
          options.orientation = this.normalizeOrientation(this.settings('orientation'));
        }
        var delay = this.settings('delay');
        if (delay && delay.size) {
          options.delay = delay.size;
        }
        var transition = this.settings('transition');
        if (transition === 'custom') {
          if (this.settings('transition_custom')) {
            options.transition = this.settings('transition_custom');
          }
        } else if (transition) {
          options.transition = transition;
        }
        var maxTransition = this.settings('max_transition');
        if (maxTransition && maxTransition.size) {
          options.maxTransition = maxTransition.size;
        }
        options.overflow = this.settings('overflow') === 'yes';

        // The library reads settings.customContainer as a DOM node when measuring
        // offsets, so resolve the selector here instead of handing it a string.
        var containerNode = this.querySafe(this.settings('custom_container'));
        if (containerNode) {
          options.customContainer = containerNode;
        }
        var customWrapper = this.settings('custom_wrapper');
        if (customWrapper && this.querySafe(customWrapper)) {
          options.customWrapper = customWrapper;
        }

        var container = this.$element;
        if (!container.length) {
          return;
        }

        var mediaType = this.settings('media_type');
        var mediaElements = container.find(mediaType === 'video' ? 'video' : 'img');

        if (mediaElements.length) {
          mediaElements.each(function () {
            var instance = new SimpleParallax($(this).get(0), options);
            self._spInstances.push(instance);
          });
        }
      }
    });

    elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
      elementorFrontend.elementsHandler.addHandler(SimpleParallaxHandler, {
        $element: $scope
      });
    });
  });

}(jQuery));
