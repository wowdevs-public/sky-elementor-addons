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
      TiltEffectHandler;

    // Never binds on touch-only devices; inert under prefers-reduced-motion.
    var canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches,
      reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    TiltEffectHandler = ModuleHandler.extend({

      bindEvents: function () {
        this.run();
      },

      unbindEvents: function () {
        this.destroyTilt();
      },

      settings: function (key) {
        return this.getElementSettings('sa_tilt_' + key);
      },

      size: function (key, fallback) {
        var v = this.settings(key);
        return (v && typeof v.size !== 'undefined' && v.size !== '') ? parseFloat(v.size) : fallback;
      },

      destroyTilt: function () {
        var el = this._el;
        if (!el) {
          return;
        }
        el.removeEventListener('pointerenter', this._enter);
        el.removeEventListener('pointermove', this._move);
        el.removeEventListener('pointerleave', this._leave);
        this.reset();
        el.style.transition = '';
        this._el = null;
      },

      onElementChange: debounce(function (prop) {
        if (prop.indexOf('sa_tilt') !== -1) {
          this.destroyTilt();
          this.run();
        }
      }, 400),

      run: function () {
        if (!canHover || reducedMotion || this.settings('enable') !== 'yes') {
          return;
        }

        this._el = this.$element.get(0);
        this._raf = null;
        this._ev = null;

        this._enter = this.onEnter.bind(this);
        this._move = this.onMove.bind(this);
        this._leave = this.reset.bind(this);

        this._el.addEventListener('pointerenter', this._enter);
        this._el.addEventListener('pointermove', this._move);
        this._el.addEventListener('pointerleave', this._leave);
      },

      onEnter: function () {
        var el = this._el;
        el.style.transition = 'transform ' + this.size('speed', 300) + 'ms ' + (this.settings('easing') || 'cubic-bezier(.03,.98,.52,.99)');
        el.style.willChange = 'transform';
        if (this.settings('glare') === 'yes') {
          this.addGlare();
        }
      },

      // pointermove sampled through one requestAnimationFrame.
      onMove: function (event) {
        this._ev = event;
        if (this._raf) {
          return;
        }
        var self = this;
        this._raf = window.requestAnimationFrame(function () {
          self._raf = null;
          if (self._el) {
            self.apply(self._ev);
          }
        });
      },

      apply: function (event) {
        var el = this._el,
          rect = el.getBoundingClientRect(),
          px = (event.clientX - rect.left) / rect.width,
          py = (event.clientY - rect.top) / rect.height,
          max = this.size('max', 15),
          sign = this.settings('reverse') === 'yes' ? -1 : 1,
          axis = this.settings('axis') || 'both',
          scale = this.size('scale', 1),
          rx = axis === 'y' ? 0 : sign * max * (0.5 - py) * 2,
          ry = axis === 'x' ? 0 : sign * max * (px - 0.5) * 2;

        el.style.transform =
          'perspective(' + this.size('perspective', 1000) + 'px)' +
          ' rotateX(' + rx.toFixed(2) + 'deg) rotateY(' + ry.toFixed(2) + 'deg)' +
          (scale !== 1 ? ' scale3d(' + scale + ',' + scale + ',' + scale + ')' : '');

        if (this._glare) {
          this._glare.style.background = 'radial-gradient(circle at ' + (100 * px).toFixed(1) + '% ' + (100 * py).toFixed(1) + '%, ' + (this.settings('glare_color') || '#ffffff') + ', transparent 65%)';
        }
      },

      addGlare: function () {
        if (this._glare) {
          return;
        }
        var el = this._el,
          glare = document.createElement('div');
        glare.className = 'sa-tilt-glare';
        glare.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;border-radius:inherit;overflow:hidden;z-index:1;';
        // Opacity lives on the overlay itself so the picked color needs no rgba parsing.
        glare.style.opacity = this.size('glare_opacity', 0.4);
        if (getComputedStyle(el).position === 'static') {
          el.style.position = 'relative';
        }
        el.appendChild(glare);
        this._glare = glare;
      },

      reset: function () {
        var el = this._el;
        if (!el) {
          return;
        }
        el.style.transform = '';
        el.style.willChange = '';
        if (this._glare && this._glare.parentNode) {
          this._glare.parentNode.removeChild(this._glare);
        }
        this._glare = null;
        if (this._raf) {
          window.cancelAnimationFrame(this._raf);
          this._raf = null;
        }
      }
    });

    // One handler, three element types. `widget` alone meant the controls appeared for a
    // container (once the PHP registers them there) but nothing ever bound to it.
    ['widget', 'container', 'section'].forEach(function (type) {
      elementorFrontend.hooks.addAction('frontend/element_ready/' + type, function ($scope) {
        elementorFrontend.elementsHandler.addHandler(TiltEffectHandler, {
          $element: $scope
        });
      });
    });
  });

}(jQuery));
