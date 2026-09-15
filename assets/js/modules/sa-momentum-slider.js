;(function ($, elementor) {
    'use strict';

    /**
     * Momentum Slider.
     *
     * The images track is the only interactive Swiper. The number, title and link tracks are
     * vertical Swipers with touch disabled, bound to it through Swiper's controller module —
     * so they follow the drag continuously (the "momentum" of the name) instead of snapping
     * on slide change.
     *
     * Everything is looked up through $scope. The previous build used
     * document.querySelector('.momentum-slider-pagination'), so a second widget on the page
     * grabbed the FIRST widget's pagination and drove the wrong slider.
     */
    var widgetMomentumSlider = function ($scope, $) {
        var $slider = $scope.find('.sa-momentum-slider');

        if (!$slider.length) {
            return;
        }

        var $imagesEl = $slider.find('.sa-ms-images');

        if (!$imagesEl.length) {
            return;
        }

        var settings = $slider.data('settings') || {};
        var Swiper = elementorFrontend.utils.swiper;

        // The editor re-runs this hook on every settings change. Without an explicit destroy
        // the old instances stay bound to the same DOM and fight the new ones for control.
        $slider.find('.swiper').each(function () {
            if (this.swiper) {
                this.swiper.destroy(true, true);
            }
        });

        // Shared config for the three text tracks. They must never own autoplay, navigation
        // or pagination — the images track is the single source of truth for all of that,
        // and a second autoplay would double-advance the whole group.
        //
        // HORIZONTAL, not vertical. A vertical Swiper's container height IS its axis, so it
        // needs a fixed height to translate through; these boxes are sized by their text, so
        // the wrapper collapsed and the titles never moved while the numbers (which had a
        // fixed height) did — the widget showed "02" beside title #1. Horizontal takes its
        // length from the column width, which is always known, and autoHeight lets the box
        // grow for a two-line title instead of clipping it.
        function textTrackConfig(extra) {
            return $.extend({
                direction: 'horizontal',
                slidesPerView: 1,
                autoHeight: true,
                allowTouchMove: false,
                loop: !!settings.loop,
                speed: settings.speed || 600,
                observer: !!settings.observer,
                observeParents: !!settings.observer,
                mousewheel: false,
                keyboard: false,
                a11y: {enabled: false}
            }, extra || {});
        }

        initSwipers();

        /**
         * The slide counter. NOT a Swiper — it stays put and only its digits change.
         *
         * Returns a no-op when the widget has no number, so the caller never has to check.
         */
        function makeCounter() {
            var $numbers = $slider.find('.sa-ms-numbers');
            var $current = $numbers.find('.sa-ms-number-current');

            if (!$current.length) {
                return function () {};
            }

            // Written by PHP from the slide total. 0 means the Plain format — do not pad.
            var pad = parseInt($numbers.attr('data-pad'), 10) || 0;

            return function (index) {
                var label = String(index + 1);

                while (label.length < pad) {
                    label = '0' + label;
                }

                if ($current.text() === label) {
                    return;
                }

                $current.text(label);

                // Restart the roll. Removing the class is not enough on its own — the browser
                // coalesces the remove and the add into one frame and the animation never
                // re-runs; reading offsetWidth forces the reflow that separates them.
                var el = $current[0];
                el.classList.remove('sa-is-rolling');
                void el.offsetWidth;
                el.classList.add('sa-is-rolling');
            };
        }

        async function initSwipers() {
            var tracks = [];
            var paintNumber = makeCounter();

            var $titles = $slider.find('.sa-ms-titles');
            var $links = $slider.find('.sa-ms-links');

            if ($titles.length) {
                tracks.push(await new Swiper($titles[0], textTrackConfig()));
            }

            if ($links.length) {
                tracks.push(await new Swiper($links[0], textTrackConfig()));
            }

            // Drives the Timeline bullet fill. Declared as a duration on the widget so the CSS
            // animation matches the real autoplay delay instead of guessing at it.
            if (settings.autoplay && settings.autoplay.delay) {
                $slider[0].style.setProperty('--sa-autoplay-duration', settings.autoplay.delay + 'ms');
            }

            var images = await new Swiper($imagesEl[0], settings);

            // Two ways to drive the text, and the choice is NOT cosmetic.
            //
            // controller syncs by translate ratio. That needs both sliders to cover comparable
            // translate ranges, and in loop mode they do not: the images track runs a fractional
            // slidesPerView (1.4 by default) while the text tracks are pinned at 1, so Swiper
            // generates a different clone count for each and the titles run ahead of the image
            // they caption by the difference — worse the further from index 0. At 2.6 per view
            // with three slides the title sticks on the last item entirely.
            //
            // With loop off the ranges do correspond, and controller is worth keeping there: it
            // tracks a drag continuously, which is the whole point of this widget's name. With
            // loop on the text is synced by index instead — the same realIndex the counter uses,
            // so the number and the title can no longer disagree.
            if (tracks.length && !settings.loop) {
                // One-way: images drive the text. Making it bidirectional would let a text track
                // echo the position back and the pair can oscillate on a fast drag.
                images.controller.control = tracks;
            }

            // realIndex, not activeIndex: in loop mode Swiper prepends cloned slides, so
            // activeIndex counts the clones and the counter would read 02 on the first slide.
            images.on('slideChange', function () {
                var index = this.realIndex;

                paintNumber(index);

                if (settings.loop) {
                    tracks.forEach(function (track) {
                        // slideToLoop, not slideTo: the text tracks are looped too, so their own
                        // clones sit in front of slide 0 and a raw index would land on one.
                        track.slideToLoop(index);
                    });
                }
            });

            // PHP already printed the first number, so this only fires when the slider starts
            // somewhere else.
            paintNumber(images.realIndex);

            if (settings.pauseOnHover && images.autoplay) {
                $slider.on('mouseenter', function () {
                    images.autoplay.stop();
                }).on('mouseleave', function () {
                    images.autoplay.start();
                });
            }
        }
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-momentum-slider.default', widgetMomentumSlider);
    });

}(jQuery, window.elementorFrontend));
