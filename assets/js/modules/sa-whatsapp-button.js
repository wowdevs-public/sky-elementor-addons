/**
 * Start whatsapp-button widget script
 */

(function ($, elementor) {

    'use strict';

    // WhatsApp Button — popup toggle, business-hours correction, click tracking.
    //
    // Every link, label and the initial open/closed state come from PHP. This file
    // exists for the three things the server cannot finish on its own:
    //
    //   1. Opening and closing the chat card.
    //   2. Correcting the business-hours state. A full-page cache serves whatever
    //      state was true when the HTML was generated, so the same minute-of-week
    //      comparison PHP ran is repeated here against the visitor's clock.
    //   3. Firing the analytics event, which has to happen at click time.
    var widgetWhatsappButton = function ($scope, $) {

        var $root = $scope.find('.sa-wa');

        if (!$root.length) {
            return;
        }

        var whatsapp = {

            // Minutes in a week. Slots wrap around this so a Sunday-night shift is
            // still open on Monday morning.
            WEEK: 10080,

            $scope: $scope,
            $root: $root,
            root: $root[0],
            $panel: $root.find('.sa-wa__panel'),
            $toggle: $root.find('.sa-wa__toggle'),
            $status: $root.find('.sa-wa__status-text'),
            $offlineCta: $root.find('.sa-wa__cta-label--offline, .sa-wa__offline-note'),
            settings: $root.data('settings') || {},
            uid: $scope.data('id') || '',
            timer: null,

            isFloating: function () {
                return this.$panel.length > 0;
            },

            isOpen: function () {
                return this.$root.hasClass('sa-wa--open');
            },

            setOpen: function (open) {
                this.$root.toggleClass('sa-wa--open', open);
                this.$toggle.attr('aria-expanded', open ? 'true' : 'false');
            },

            /**
             * Repeat the server's schedule comparison against the visitor's clock.
             *
             * The site's UTC offset comes from PHP, so the date is shifted into site
             * time and read with the getUTC* accessors — that keeps the maths free
             * of the browser's own timezone.
             */
            computeState: function (slots) {
                slots = slots || [];

                if (!slots.length) {
                    return { online: true, nextOpen: '', gap: Infinity };
                }

                var days = this.settings.days || [],
                    now = new Date(Date.now() + (this.settings.offset || 0) * 1000),
                    day = (now.getUTCDay() + 6) % 7,
                    minute = day * 1440 + now.getUTCHours() * 60 + now.getUTCMinutes(),
                    nearestGap = Infinity,
                    nextOpen = '',
                    i, slot, elapsed, gap;

                for (i = 0; i < slots.length; i++) {
                    slot = slots[i];
                    elapsed = this.wrap(minute - slot[0]);

                    if (elapsed < slot[1]) {
                        return { online: true, nextOpen: '', gap: 0 };
                    }

                    gap = this.wrap(slot[0] - minute);

                    if (gap < nearestGap) {
                        nearestGap = gap;
                        nextOpen = (days[slot[2]] || '') + ' ' + slot[3];
                    }
                }

                return { online: false, nextOpen: nextOpen, gap: nearestGap };
            },

            /**
             * Re-check every agent row and report the team's state.
             *
             * An agent may keep hours the rest of the team does not, so each row is
             * judged on its own slots. The card is open while any one of them is,
             * and when all are away it quotes whichever returns first — the same
             * rule PHP applied when the HTML was written.
             */
            applyAgents: function () {
                var self = this,
                    list = this.settings.agentSlots || [],
                    template = this.settings.offlineText || '',
                    team = { online: false, nextOpen: '', gap: Infinity };

                for (var i = 0; i < list.length; i++) {
                    var state = this.computeState(list[i]),
                        $row = this.$root.find('[data-agent="' + i + '"]');

                    $row.toggleClass('sa-wa__agent--offline', !state.online);
                    $row.find('.sa-wa__agent-state').text(
                        template.replace('{next_open}', state.nextOpen)
                    );

                    if (state.online) {
                        team.online = true;
                    } else if (state.gap < team.gap) {
                        team.gap = state.gap;
                        team.nextOpen = state.nextOpen;
                    }
                }

                return list.length ? team : self.computeState(this.settings.slots);
            },

            // Positive remainder, so a slot that started before this point in the
            // week still lands in range.
            wrap: function (minutes) {
                return ((minutes % this.WEEK) + this.WEEK) % this.WEEK;
            },

            applyHours: function () {
                if (!this.settings.hours) {
                    return;
                }

                var state = this.applyAgents(),
                    text = state.online
                        ? (this.settings.onlineText || '')
                        : (this.settings.offlineText || '').replace('{next_open}', state.nextOpen);

                this.$root.toggleClass('sa-wa--offline', !state.online);
                this.$status.text(text);

                // The offline label carries {next_open} too, so a cached page would
                // otherwise announce a reply time that has already passed.
                if (!state.online && this.$offlineCta.length) {
                    this.$offlineCta.text(
                        (this.settings.offlineChatText || '').replace('{next_open}', state.nextOpen)
                    );
                }
            },

            // Send the click wherever the page already has a receiver. Nothing is
            // loaded or injected — if a tag manager is not present, nothing happens.
            track: function () {
                var name = this.settings.track;

                if (!name) {
                    return;
                }

                var payload = {
                    widget_id: this.uid,
                    page_url: window.location.href
                };

                if (window.dataLayer && typeof window.dataLayer.push === 'function') {
                    window.dataLayer.push($.extend({ event: name }, payload));
                }

                if (typeof window.gtag === 'function') {
                    window.gtag('event', name, $.extend({ method: 'whatsapp' }, payload));
                }

                if (typeof window.fbq === 'function') {
                    window.fbq('trackCustom', name, payload);
                }
            },

            // The toggle keeps a real wa.me href so the button still works without
            // JS. With JS and a chat card present, open the card instead.
            bindPanel: function () {
                var self = this;

                this.$toggle.on('click', function (e) {
                    e.preventDefault();
                    self.setOpen(!self.isOpen());
                });

                this.$root.find('.sa-wa__close').on('click', function (e) {
                    e.preventDefault();
                    self.setOpen(false);
                });

                $(document).on('click.sa-wa-' + this.uid, function (e) {
                    if (self.isOpen() && !self.root.contains(e.target)) {
                        self.setOpen(false);
                    }
                });

                $(document).on('keydown.sa-wa-' + this.uid, function (e) {
                    if ('Escape' === e.key) {
                        self.setOpen(false);
                    }
                });
            },

            // Catches a visitor still on the page when opening time arrives.
            bindClock: function () {
                var self = this;

                if (!this.settings.hours) {
                    return;
                }

                this.timer = window.setInterval(function () {
                    self.applyHours();
                }, 60000);

                this.$scope.on('destroy', function () {
                    window.clearInterval(self.timer);
                });
            },

            bindTracking: function () {
                var self = this;

                this.$root.on('click', 'a[href^="https://wa.me/"]', function () {
                    self.track();
                });
            },

            init: function () {
                // The anti-flash `hidden` attribute has already been overridden by
                // the stylesheet by this point. Removing it keeps the attribute and
                // the computed style telling assistive technology the same story.
                this.$root.removeAttr('hidden');

                this.applyHours();
                this.bindClock();
                this.bindTracking();

                if (this.isFloating()) {
                    this.bindPanel();
                }
            }
        };

        whatsapp.init();
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/sky-whatsapp-button.default', widgetWhatsappButton);
    });

}(jQuery, window.elementorFrontend));

/**
 * End whatsapp-button widget script
 */
