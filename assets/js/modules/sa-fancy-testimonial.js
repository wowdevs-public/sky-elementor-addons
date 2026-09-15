;(function ($, elementor) {
    'use strict';

    /**
     * Fancy Testimonial — one review slider (Swiper, fade) with three avatar layouts.
     * No animation library: every effect is a CSS animation or transition driven by a
     * class or a custom property. This file only wires Swiper up.
     *
     *   wave    — avatars are a second Swiper riding a drawn sine curve. Swiper's
     *             `thumbs` module owns the HIGHLIGHT and scrolls the rail to the active
     *             avatar; the clicks are ours (see below), as is lifting each slide onto
     *             the curve via `--sa-ft-wy`.
     *   cluster — a static wall; JS forwards a click to the matching slide and marks
     *             the active avatar.
     *
     * `elementorFrontend.utils.swiper` resolves ASYNCHRONOUSLY — every instance must be
     * awaited. Handing the un-awaited promise to `thumbs.swiper` silently kills both
     * sliders (the rail renders empty and the cards never initialise).
     */

    var TAU = Math.PI * 2;
    var WAVE_CYCLES = 2;   // must match Fancy_Testimonial::WAVE_CYCLES

    var layouts = {

        cluster: {
            init: function (ctx) {
                ctx.bindAvatarClicks();
            }
        },

        wave: {
            init: async function (ctx) {
                var railEl = ctx.root.querySelector('.sa-fancy-testimonial__rail');
                var config = $(ctx.root).data('rail-settings') || {};
                var depth = config.waveDepth || 45;

                if (!railEl) {
                    return {};
                }

                // Lift every slide onto the curve PHP drew: same sine, same cycles, so
                // the avatars sit exactly on the line at any width. `setTranslate` fires
                // on every frame of a drag as well as on snap, so no extra listeners.
                var place = function (rail) {
                    var width = rail.width || 1;

                    rail.slides.forEach(function (slide) {
                        var x = slide.swiperSlideOffset + rail.translate + slide.offsetWidth / 2;

                        slide.style.setProperty('--sa-ft-wy', (-depth * Math.sin(TAU * WAVE_CYCLES * x / width)).toFixed(2) + 'px');
                    });
                };

                var rail = await new ctx.Swiper(railEl, $.extend({}, config, {
                    on: {
                        afterInit: place,
                        setTranslate: place,
                        resize: place
                    }
                }));

                // `destroy()` runs synchronously and cannot cancel the await above, so a
                // teardown that landed mid-await leaves this instance orphaned — clean up
                // after ourselves instead of leaking a live Swiper and its listeners.
                if (ctx.destroyed) {
                    rail.destroy(true, false);
                    return {};
                }

                ctx.rail = rail;

                // Click-to-review is OURS, exactly as Cluster does it. Swiper's thumbs
                // module maps a click through `clickedIndex` and loop maths on a LOOPED
                // thumbs swiper (Thumbs.onThumbClick) and lands on the WRONG slide: when
                // neither `prevAll`/`nextAll` matches, dom7's `.index()` returns undefined
                // and `slideTo`'s `index = 0` default coerces that to the first review.
                // It also runs on `tap`, which Swiper emits from onTouchEnd on POINTERUP —
                // before `click` — so it would fade toward slide 0 ahead of us every time.
                // `slideToClickedSlide` cannot save it either: Thumbs.init() force-writes
                // `slideToClickedSlide: false` onto the rail. The afterInit hook below
                // unbinds that handler; everything else thumbs does is still wanted.
                //
                // Delegated on the rail, not bound per button: Swiper makes the loop
                // clones after this runs, and a per-node listener would miss every clone.
                // A DOM `click` rather than Swiper's `tap` because the avatar is a real
                // <button> and `tap` never fires for keyboard activation. Swiper's own
                // click guard does NOT stop propagation unless it is mid-animation, so the
                // drag that ends on this element still delivers a click — `allowClick` is
                // the flag that tells them apart, and it is the one Swiper reads too.
                ctx.on(railEl, 'click', function (event) {
                    var spot = event.target.closest('.sa-fancy-testimonial__spot');

                    if (!spot || !ctx.swiper || !ctx.rail || !ctx.rail.allowClick) {
                        return;
                    }

                    var index = ctx.slideIndex(spot);

                    if (index >= 0) {
                        ctx.swiper.slideToLoop(index);
                    }
                });

                // thumbs still owns the active class and pulls the rail to the active
                // avatar — it reaches the loop clones that `setActive` cannot. Thumbs.init()
                // binds its click mapping during the review slider's `beforeInit`, so
                // `afterInit` is the first moment we can drop it. Nothing else listens for
                // `tap` on the rail.
                return {
                    thumbs: { swiper: rail },
                    on: {
                        afterInit: function (swiper) {
                            if (swiper.thumbs && swiper.thumbs.swiper) {
                                swiper.thumbs.swiper.off('tap');
                            }
                        }
                    }
                };
            },

            destroy: function (ctx) {
                if (ctx.rail && ctx.rail.destroy) {
                    ctx.rail.destroy(true, false);
                }
                ctx.rail = null;
            }
        }
    };

    var widgetFancyTestimonial = function ($scope) {
        var root = $scope[0].querySelector('.sa-fancy-testimonial');
        if (!root) {
            return;
        }

        // The editor re-fires element_ready on every edit — drop the previous instance.
        if (root.saFancyTestimonial) {
            root.saFancyTestimonial.destroy();
        }

        var settings = $(root).data('settings') || {};
        var sliderEl = root.querySelector('.sa-fancy-testimonial__slider');
        if (!sliderEl) {
            return;
        }

        var layout = layouts[settings.layout] || layouts.wave;

        var ctx = {
            root: root,
            settings: settings,
            spots: Array.prototype.slice.call(root.querySelectorAll('.sa-fancy-testimonial__spot')),
            Swiper: elementorFrontend.utils.swiper,
            swiper: null,
            rail: null,
            listeners: [],
            destroyed: false,

            on: function (target, type, handler) {
                target.addEventListener(type, handler);
                this.listeners.push([target, type, handler]);
            },

            /** `data-index` as an integer, or -1 when it is missing or unparseable. */
            slideIndex: function (spot) {
                var index = parseInt(spot.getAttribute('data-index'), 10);

                return isNaN(index) ? -1 : index;
            },

            /** Layouts whose avatars are not Swiper slides wire their own clicks. */
            bindAvatarClicks: function () {
                var self = this;

                this.spots.forEach(function (spot) {
                    var button = spot.querySelector('.sa-fancy-testimonial__avatar');

                    if (button) {
                        self.on(button, 'click', function () {
                            var index = self.slideIndex(spot);

                            if (self.swiper && index >= 0) {
                                self.swiper.slideToLoop(index);
                            }
                        });
                    }
                });
            },

            /**
             * Trim every review to the word limit and hang a Read More link off it.
             * Runs AFTER Swiper init on purpose — loop clones exist by then, so the
             * copies get their own working link instead of a dead one.
             *
             * The full markup is kept in a variable and restored on expand; only the
             * collapsed state is plain text, so a review's own formatting survives.
             */
            readMore: function () {
                var self = this;
                var limit = parseInt(this.settings.wordLimit, 10);

                if (!limit) {
                    return;
                }

                Array.prototype.forEach.call(this.root.querySelectorAll('.sa-fancy-testimonial__text'), function (el) {
                    var full = el.innerHTML;
                    var words = el.textContent.trim().split(/\s+/);

                    if (words.length <= limit) {
                        return;
                    }

                    var short = words.slice(0, limit).join(' ') + '…';
                    var link = document.createElement('a');
                    var open = false;

                    link.className = 'sa-fancy-testimonial__more';
                    link.href = 'javascript:void(0);';
                    link.setAttribute('role', 'button');

                    var render = function () {
                        if (open) {
                            el.innerHTML = full;
                        } else {
                            el.textContent = short;
                        }

                        link.textContent = open ? self.settings.readLess : self.settings.readMore;
                        link.setAttribute('aria-expanded', open ? 'true' : 'false');
                        el.appendChild(link);   // innerHTML wiped it; the node itself survives
                    };

                    self.on(link, 'click', function () {
                        open = !open;
                        render();

                        // The slide just changed height — autoHeight has to be told.
                        if (self.swiper && self.swiper.updateAutoHeight) {
                            self.swiper.updateAutoHeight(200);
                        }
                    });

                    render();
                });
            },

            setActive: function (index) {
                this.spots.forEach(function (spot) {
                    spot.classList.toggle('is-active', parseInt(spot.getAttribute('data-index'), 10) === index);
                });
            },


            destroy: function () {
                // Read back after every `await` below: an instance still suspended in one
                // would otherwise re-register listeners onto the emptied array.
                this.destroyed = true;

                this.listeners.forEach(function (entry) {
                    entry[0].removeEventListener(entry[1], entry[2]);
                });
                this.listeners = [];

                if (layout.destroy) {
                    layout.destroy(this);
                }

                if (this.swiper && this.swiper.destroy) {
                    this.swiper.destroy(true, false);
                }
                this.swiper = null;

                delete this.root.saFancyTestimonial;
            }
        };

        root.saFancyTestimonial = ctx;
        root.classList.toggle('sa-ft-reduced', window.matchMedia('(prefers-reduced-motion: reduce)').matches);

        (async function () {
            var extra = (await layout.init(ctx)) || {};

            if (ctx.destroyed) {
                return;
            }

            var swiper = await new ctx.Swiper(sliderEl, $.extend({}, settings, extra));

            if (ctx.destroyed) {
                swiper.destroy(true, false);
                return;
            }

            ctx.swiper = swiper;

            ctx.swiper.on('slideChange', function () {
                ctx.setActive(ctx.swiper.realIndex);
            });

            if (settings.pauseOnHover) {
                ctx.on(root, 'mouseenter', function () {
                    ctx.swiper.autoplay.stop();
                });
                ctx.on(root, 'mouseleave', function () {
                    ctx.swiper.autoplay.start();
                });
            }

            ctx.setActive(ctx.swiper.realIndex || 0);
            ctx.readMore();
        }());
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/sky-fancy-testimonial.default', widgetFancyTestimonial);
    });

}(jQuery, window.elementorFrontend));
