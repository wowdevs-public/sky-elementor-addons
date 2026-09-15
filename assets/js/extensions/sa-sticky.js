; (function ($) {
    'use strict';

    /**
     * Sticky extension (UIKit-style engine — no library, no GSAP).
     *
     * On stick: the element goes `position: fixed` and a **placeholder** of the exact same
     * size takes its slot, so surrounding content never jumps. A throttled scroll/resize
     * loop keeps it aligned. Features: stick to Top or Bottom, "Stick Until" push-out,
     * entrance animation, and Show-on-Scroll-Up (hide while scrolling down). Active-state
     * styling (bg/shadow/padding/border) is pure CSS via the `.sa-stuck` class. Frontend
     * only — fixing in the edit canvas would trap the element.
     */
    $(window).on('elementor/frontend/init', function () {

        if (typeof elementorModules === 'undefined') { return; }

        var Sticky = elementorModules.frontend.handlers.Base.extend({

            bindEvents: function () {
                if (elementorFrontend.isEditMode()) { return; }
                if ('yes' !== this.setting('enable')) { return; }

                this._el = this.$element[0];
                this._until = this.resolveUntil();
                this._pos = this.setting('stick_to') || 'top';
                this._anim = this.setting('anim') || 'none';
                this._scrollUp = 'yes' === this.setting('scroll_up');
                this._lastY = window.pageYOffset;
                this._ticking = false;
                this._stuck = false;
                this._hidden = false;

                this._onScroll = this.onScroll.bind(this);
                window.addEventListener('scroll', this._onScroll, { passive: true });
                window.addEventListener('resize', this._onScroll);
                this.update();
            },

            unbindEvents: function () {
                if (this._onScroll) {
                    window.removeEventListener('scroll', this._onScroll);
                    window.removeEventListener('resize', this._onScroll);
                }
                this.unstick();
            },

            setting: function (key) { return this.getElementSettings('sa_sticky_' + key); },

            offset: function () {
                var v = this.setting('offset');
                return (v && '' !== v.size && typeof v.size !== 'undefined') ? parseFloat(v.size) : 0;
            },

            // Bare ID → element. Respects a typed #/. selector too. Null if not found.
            resolveUntil: function () {
                var raw = (this.setting('until') || '').trim();
                if (!raw) { return null; }
                var sel = /^[#.]/.test(raw) ? raw : '#' + raw;
                try { return document.querySelector(sel); } catch (e) { return null; }
            },

            // Sticky allowed at the current viewport width (per the device toggles).
            enabled: function () {
                var w = window.innerWidth,
                    bp = { mobile: 767, tablet: 1024 };
                try {
                    var r = elementorFrontend.config.responsive.breakpoints;
                    if (r) {
                        if (r.mobile && r.mobile.value) { bp.mobile = parseInt(r.mobile.value, 10); }
                        if (r.tablet && r.tablet.value) { bp.tablet = parseInt(r.tablet.value, 10); }
                    }
                } catch (e) { }

                if (w <= bp.mobile) { return 'yes' !== this.setting('disable_mobile'); }
                if (w <= bp.tablet) { return 'yes' !== this.setting('disable_tablet'); }
                return true;
            },

            onScroll: function () {
                if (this._ticking) { return; }
                this._ticking = true;
                var self = this;
                window.requestAnimationFrame(function () {
                    self.update();
                    self._ticking = false;
                });
            },

            // Off-screen transform used by the scroll-up hide + slide entrance.
            hiddenTransform: function () {
                return this._pos === 'bottom' ? 'translateY(150%)' : 'translateY(-150%)';
            },

            stick: function () {
                var el = this._el,
                    r = el.getBoundingClientRect(),
                    cs = getComputedStyle(el);

                var ph = document.createElement('div');
                ph.className = 'sa-sticky-placeholder';
                ph.style.width = r.width + 'px';
                ph.style.height = r.height + 'px';
                ph.style.margin = cs.margin;
                ph.style.flex = '0 0 auto';
                // Placement, not just size. Going fixed takes the element out of flow, so in a
                // grid parent the placeholder is what holds its cell — and an auto-placed
                // placeholder does not land in the cell an explicitly placed element had, which
                // shunts every following item into the wrong track. `order` does the same job
                // for a flex parent that reorders its children.
                ph.style.gridArea = cs.gridArea;
                ph.style.order = cs.order;
                el.parentNode.insertBefore(ph, el);
                this._ph = ph;

                el.style.position = 'fixed';
                el.style.margin = '0';
                el.style.zIndex = this.setting('zindex') || 99;
                el.style.transition = 'transform .4s ease, opacity .4s ease, background-color .3s ease, box-shadow .3s ease, padding .3s ease, border-radius .3s ease';
                el.classList.add('sa-stuck');
                this._stuck = true;
                this._hidden = false;

                this.playEntrance();
            },

            // One-shot entrance when the element becomes stuck.
            playEntrance: function () {
                if (this._anim === 'none') { return; }
                var el = this._el;
                if (this._anim === 'slide') { el.style.transform = this.hiddenTransform(); }
                else if (this._anim === 'fade') { el.style.opacity = '0'; }
                // Two frames so the start state paints before the transition runs.
                window.requestAnimationFrame(function () {
                    window.requestAnimationFrame(function () {
                        el.style.transform = '';
                        el.style.opacity = '';
                    });
                });
            },

            unstick: function () {
                if (!this._stuck) { return; }
                var el = this._el;
                el.style.position = '';
                el.style.top = '';
                el.style.bottom = '';
                el.style.left = '';
                el.style.width = '';
                el.style.margin = '';
                el.style.zIndex = '';
                el.style.transform = '';
                el.style.opacity = '';
                el.style.transition = '';
                el.classList.remove('sa-stuck');
                if (this._ph && this._ph.parentNode) { this._ph.parentNode.removeChild(this._ph); }
                this._ph = null;
                this._stuck = false;
                this._hidden = false;
            },

            // Show-on-scroll-up: slide the element out while scrolling down, back in on up.
            applyScrollDirection: function () {
                if (!this._scrollUp || !this._stuck) { return; }
                var y = window.pageYOffset, el = this._el;
                if (y > this._lastY + 4 && !this._hidden) {
                    this._hidden = true;
                    el.style.transform = this.hiddenTransform();
                } else if (y < this._lastY - 4 && this._hidden) {
                    this._hidden = false;
                    el.style.transform = '';
                }
                this._lastY = y;
            },

            update: function () {
                var el = this._el;
                if (!el) { return; }

                if (!this.enabled()) { this.unstick(); return; }

                var offset = this.offset(),
                    toBottom = this._pos === 'bottom',
                    ref = this._stuck ? this._ph : el,
                    refRect = ref.getBoundingClientRect(),
                    vh = window.innerHeight,
                    shouldStick = toBottom ? (refRect.bottom >= vh - offset) : (refRect.top <= offset);

                if (shouldStick) {
                    if (!this._stuck) { this.stick(); }

                    // Re-read the placeholder each frame so width/left survive resize.
                    var box = this._ph.getBoundingClientRect();
                    el.style.left = box.left + 'px';
                    el.style.width = box.width + 'px';

                    if (toBottom) {
                        el.style.top = 'auto';
                        el.style.bottom = offset + 'px';
                    } else {
                        var top = offset;
                        // Footer push-out: when the target nears the sticky line, ride it up.
                        if (this._until) {
                            var limit = this._until.getBoundingClientRect().top - el.offsetHeight;
                            if (limit < offset) { top = limit; }
                        }
                        el.style.bottom = 'auto';
                        el.style.top = top + 'px';
                    }

                    this.applyScrollDirection();
                } else if (this._stuck) {
                    this.unstick();
                }
            }
        });

        ['container', 'widget'].forEach(function (name) {
            elementorFrontend.hooks.addAction('frontend/element_ready/' + name, function ($scope) {
                elementorFrontend.elementsHandler.addHandler(Sticky, { $element: $scope });
            });
        });
    });

}(jQuery));
