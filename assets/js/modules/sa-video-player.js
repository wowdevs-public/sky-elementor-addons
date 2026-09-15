;(function ($, elementor) {
    'use strict';

    // Every live player on the page, so starting one can pause the rest.
    var saVideoPlayers = [];

    // What YouTube serves for a thumbnail size a video does not have: a 120x90 grey
    // placeholder, returned with a 404 that the browser still decodes happily. A real
    // thumbnail is never that narrow, so the decoded width is the tell.
    var SA_YT_PLACEHOLDER_WIDTH = 120;

    /**
     * Swap an optimistic poster for its guaranteed fallback when the optimistic one
     * came back as YouTube's placeholder.
     *
     * PHP emits `maxresdefault` plus a `data-sa-fallback` of `hqdefault`, rather than
     * probing with a blocking HEAD request on every render. The image is downloaded
     * either way; this only reads the result.
     */
    function saPosterFallback(img) {
        var fallback = img.getAttribute('data-sa-fallback');

        if (!fallback || img.saFallbackDone) {
            return;
        }

        var settle = function () {
            if (img.saFallbackDone) {
                return;
            }

            img.saFallbackDone = true;

            // naturalWidth is 0 when the image failed outright, and exactly 120 on the
            // placeholder. Anything else decoded to a real frame.
            if (img.naturalWidth && img.naturalWidth !== SA_YT_PLACEHOLDER_WIDTH) {
                return;
            }

            img.src = fallback;
        };

        // Cached images can already be decoded before this runs, and fire no event.
        if (img.complete) {
            settle();
            return;
        }

        img.addEventListener('load', settle);
        img.addEventListener('error', settle);
    }

    var widgetVideoPlayer = function ($scope, $) {
        var $player = $scope.find('.sa-video-player');

        if (!$player.length) {
            return;
        }

        var el        = $player[0],
            settings  = $player.data('settings') || {},
            chapters  = settings.chapters || [],
            chapterUi = settings.chapterUi || {},
            frameCfg  = settings.frames || {},
            $frame    = $player.find('.sa-vp-frame'),
            $media    = $player.find('.sa-vp-media'),
            $overlay  = $player.find('.sa-vp-overlay');

        if (!$media.length) {
            return;
        }

        // The editor re-renders the widget in place — tear the old instance down first.
        if (el.saVideoPlayer) {
            el.saVideoPlayer.destroy();
        }

        var player = {

            plyr: null,
            started: false,      // has playback ever begun (gates sticky)
            stickyClosed: false, // visitor dismissed the mini player
            scrollBound: false,
            ticking: false,
            frameHeight: 0,      // measured on first play, before the frame goes fixed
            activeIndex: -1,     // chapter the playhead is inside
            segmentsBuilt: false,
            framesStarted: false,
            hoverBound: false,
            segmentMap: null,    // segment DOM index → chapter index (-1 = lead block)
            $segFills: null,
            $segments: null,
            $segTip: null,
            $hover: null,
            $preview: null,
            $titleOverlay: $player.find('.sa-vp-chapter-overlay'),

            /* ── Plyr ────────────────────────────────────────────────── */

            build: function () {
                if (this.plyr) {
                    return this.plyr;
                }

                var self = this;

                this.plyr = new Plyr($media[0], skyAddonsPlyrOptions(settings.plyr || {}));

                this.plyr.on('ready', function () {
                    self.seekToStart();
                });

                // Segments and the hover layer both need a duration, which only exists
                // once metadata is in.
                this.plyr.on('loadedmetadata', function () {
                    var progress = el.querySelector('.plyr__progress');

                    if (!progress) {
                        return;
                    }

                    self.buildSegments(progress);
                    self.buildHoverLayer(progress);
                    self.bindProgressHover(progress, self.plyr.duration);
                });

                this.plyr.on('playing', function () {
                    self.started = true;
                    // Cache the frame height now, while it is still in normal flow —
                    // once it goes fixed it can no longer be measured in place.
                    self.frameHeight = $frame.outerHeight();
                    self.pauseOthers();
                    self.bindScroll();

                    if (frameCfg.timing === 'play') {
                        self.startFrames();
                    }
                });

                this.plyr.on('timeupdate', function () {
                    self.enforceEnd();
                    self.syncChapter();
                });

                return this.plyr;
            },

            // Build (if needed) and play. Used by the overlay, chapters and viewport.
            play: function () {
                var self = this,
                    plyr = this.build();

                $player.removeClass('sa-vp-lazy');

                var start = function () {
                    // Plyr persists `muted` in localStorage and storage *wins over the
                    // config we pass* — its `set muted()` falls back to the stored value
                    // whenever it is handed a non-boolean. So a single mute, once, would
                    // otherwise leave the player silent on every later visit even though
                    // the widget is configured unmuted.
                    //
                    // Forced on the first play only, and never when the author actually
                    // asked for Muted or Autoplay: clicking play means "I want sound",
                    // but a visitor who then mutes deliberately keeps it.
                    if (!self.started && !(settings.plyr && settings.plyr.muted)) {
                        plyr.muted = false;
                    }

                    plyr.play();
                };

                if (plyr.ready) {
                    start();
                    return;
                }

                plyr.once('ready', start);
            },

            pauseOthers: function () {
                var self = this;

                saVideoPlayers.forEach(function (other) {
                    if (other !== self && other.plyr && other.plyr.playing) {
                        other.plyr.pause();
                    }
                });
            },

            /* ── Start / end time ────────────────────────────────────── */

            seekToStart: function () {
                if (!settings.startTime) {
                    return;
                }

                try {
                    this.plyr.currentTime = settings.startTime;
                } catch (e) { /* provider not ready to seek — skip */ }
            },

            enforceEnd: function () {
                if (!settings.endTime || !this.plyr) {
                    return;
                }

                if (this.plyr.currentTime < settings.endTime) {
                    return;
                }

                this.plyr.pause();

                if (settings.plyr && settings.plyr.loop && settings.plyr.loop.active) {
                    this.plyr.currentTime = settings.startTime || 0;
                    this.plyr.play();
                }
            },

            /* ── Chapters ────────────────────────────────────────────── */

            // Where a chapter ends: the next chapter's start, or the video's end.
            chapterEnd: function (index) {
                if (index + 1 < chapters.length) {
                    return chapters[index + 1].time;
                }

                return this.plyr.duration || chapters[index].time;
            },

            // 0–100 through a single chapter at the current playhead.
            chapterPercent: function (index, time) {
                var start = chapters[index].time,
                    end   = this.chapterEnd(index),
                    span  = end - start;

                if (span <= 0) {
                    return time >= end ? 100 : 0;
                }

                return Math.max(0, Math.min(100, ((time - start) / span) * 100));
            },

            indexAt: function (time) {
                var index = -1;

                for (var i = 0; i < chapters.length; i++) {
                    if (time >= chapters[i].time) {
                        index = i;
                    }
                }

                return index;
            },

            // Runs on every timeupdate: cheap work always, DOM churn only on change.
            syncChapter: function () {
                if (!chapters.length) {
                    return;
                }

                var time  = this.plyr.currentTime,
                    index = this.indexAt(time);

                if (index !== this.activeIndex) {
                    this.activeIndex = index;
                    this.markActive(index);
                }

                if (index < 0) {
                    return;
                }

                var percent = this.chapterPercent(index, time);

                if (chapterUi.fill) {
                    this.setItemFill(index, percent);
                }

                if (this.$segFills && this.segmentMap) {
                    this.setSegmentFill(index, percent);
                }
            },

            // Chapter changed — repaint the parts that only move on a boundary.
            markActive: function (index) {
                var $items = $player.find('.sa-vp-chapter');

                $items.removeClass('sa-active sa-past');

                $items.each(function (i) {
                    if (i < index) {
                        $(this).addClass('sa-past');
                    }
                    if (chapterUi.fill) {
                        this.style.setProperty('--sa-vp-chapter-progress', i < index ? '100%' : '0%');
                    }
                });

                if (index > -1) {
                    $items.eq(index).addClass('sa-active');
                }

                if (this.$segFills) {
                    this.$segFills.each(function (i) {
                        var chapterIndex = player.segmentMap[i];
                        this.style.width = (chapterIndex > -1 && chapterIndex < index) ? '100%' : '0%';
                    });
                }

                if (this.$titleOverlay && this.$titleOverlay.length) {
                    var label = index > -1 ? chapters[index].label : '';

                    // .text() on a fresh span — never inject the label as markup.
                    this.$titleOverlay.empty();

                    if (label) {
                        this.$titleOverlay.append($('<span/>').text(label));
                    }
                }

                this.scrollChapterIntoView(index);
            },

            setItemFill: function (index, percent) {
                var item = $player.find('.sa-vp-chapter').get(index);

                if (item) {
                    item.style.setProperty('--sa-vp-chapter-progress', percent + '%');
                }
            },

            setSegmentFill: function (index, percent) {
                var self = this;

                this.$segFills.each(function (i) {
                    if (self.segmentMap[i] === index) {
                        this.style.width = percent + '%';
                    }
                });
            },

            // Keep the playing chapter visible in the rail / a scrolling list.
            scrollChapterIntoView: function (index) {
                if (index < 0) {
                    return;
                }

                var list = $player.find('.sa-vp-chapters').get(0),
                    item = $player.find('.sa-vp-chapter').get(index);

                if (!list || !item) {
                    return;
                }

                // Only when the list actually scrolls — otherwise this yanks the page.
                if (list.scrollWidth > list.clientWidth) {
                    list.scrollTo({ left: item.offsetLeft - list.offsetLeft, behavior: 'smooth' });
                } else if (list.scrollHeight > list.clientHeight) {
                    list.scrollTo({ top: item.offsetTop - list.offsetTop, behavior: 'smooth' });
                }
            },

            /* ── Segmented progress bar ──────────────────────────────── */

            // A layer of blocks laid over Plyr's track, one per chapter. It sits below
            // the range input (which owns z-index 2) and is pointer-events: none, so
            // seeking and keyboard control are untouched.
            buildSegments: function (progress) {
                if (this.segmentsBuilt || !chapterUi.segments || !chapters.length) {
                    return;
                }

                var duration = this.plyr.duration;

                if (!duration) {
                    return;
                }

                this.segmentsBuilt = true;

                // A leading block when the first chapter does not start at zero,
                // so the segments always add up to the full track.
                var ranges = [];

                if (chapters[0].time > 0) {
                    ranges.push({ start: 0, end: chapters[0].time, index: -1 });
                }

                for (var i = 0; i < chapters.length; i++) {
                    ranges.push({
                        start: Math.min(chapters[i].time, duration),
                        end: Math.min(this.chapterEnd(i), duration),
                        index: i,
                    });
                }

                var html = '';
                this.segmentMap = [];

                for (var r = 0; r < ranges.length; r++) {
                    var width = Math.max(0, ((ranges[r].end - ranges[r].start) / duration) * 100);

                    this.segmentMap.push(ranges[r].index);
                    html += '<span class="sa-vp-segment" style="width:' + width + '%"><i class="sa-vp-segment-fill"></i></span>';
                }

                var layer = document.createElement('div');
                layer.className = 'sa-vp-segments';
                layer.innerHTML = html;
                progress.appendChild(layer);

                this.$segFills = $(layer).find('.sa-vp-segment-fill');
                this.$segments = $(layer).find('.sa-vp-segment');

                $player.addClass('sa-vp-has-segments');
                this.markActive(this.activeIndex);
            },

            /* ── Seek-bar hover: frame preview + chapter label ───────── */

            // One floating column above the cursor holding both the preview frame and
            // the chapter label, so the two never have to be stacked by hand.
            buildHoverLayer: function (progress) {
                if (this.$hover || (!chapterUi.segments && !frameCfg.scrub)) {
                    return;
                }

                var wrap = document.createElement('div');
                wrap.className = 'sa-vp-hover';
                wrap.innerHTML =
                    (frameCfg.scrub ? '<img class="sa-vp-preview" alt="" />' : '') +
                    (chapterUi.segments ? '<div class="sa-vp-seg-tip"></div>' : '');

                progress.appendChild(wrap);

                this.$hover   = $(wrap);
                this.$preview = this.$hover.find('.sa-vp-preview');
                this.$segTip  = this.$hover.find('.sa-vp-seg-tip');
            },

            // The segment layer cannot receive events, so hover is derived from the
            // pointer position on the track itself.
            bindProgressHover: function (progress, duration) {
                if (this.hoverBound || !this.$hover) {
                    return;
                }

                this.hoverBound = true;

                var self = this;

                $(progress).on('mousemove.saVideoPlayer', function (e) {
                    var rect  = progress.getBoundingClientRect(),
                        ratio = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)),
                        time  = ratio * duration,
                        index = self.indexAt(time);

                    self.$hover
                        .css('left', ratio * 100 + '%')
                        .addClass('sa-visible');

                    if (self.$segments) {
                        self.$segments.removeClass('sa-hover');
                        self.$segments.each(function (i) {
                            if (self.segmentMap[i] === index) {
                                $(this).addClass('sa-hover');
                            }
                        });
                    }

                    if (self.$segTip.length) {
                        self.$segTip.text(index > -1 ? chapters[index].label : '');
                    }

                    if (self.$preview.length) {
                        // Frames are cached per whole second, so a slow drag across the
                        // bar decodes far fewer times than it fires mousemove.
                        self.frames.grab(time, function (url) {
                            self.$preview.attr('src', url);
                            self.$hover.addClass('sa-has-frame');
                        });
                    }
                });

                $(progress).on('mouseleave.saVideoPlayer', function () {
                    self.$hover.removeClass('sa-visible');

                    if (self.$segments) {
                        self.$segments.removeClass('sa-hover');
                    }
                });
            },

            /* ── Frame probe (chapter thumbnails + hover preview) ────── */

            // A detached <video> used purely as a frame source. It is never attached to
            // the DOM and never plays; it just seeks and hands frames to a canvas.
            //
            // Only ever runs against a self-hosted/external file. A cross-origin file
            // without CORS headers either fails to load or taints the canvas, and
            // `toDataURL()` throws — both paths abort quietly and leave whatever
            // fallback image the markup already has.
            frames: {
                el: null,
                canvas: null,
                ctx: null,
                ready: false,
                dead: false,
                busy: false,
                pending: null,   // newest scrub request; older ones are dropped
                cache: {},       // whole second → data URL
                onReady: [],

                start: function (src) {
                    if (this.el || this.dead || !src) {
                        return;
                    }

                    var self = this;

                    this.el     = document.createElement('video');
                    this.canvas = document.createElement('canvas');
                    this.ctx    = this.canvas.getContext('2d');

                    this.el.addEventListener('error', function () {
                        self.fail();
                    });

                    this.el.addEventListener('loadedmetadata', function () {
                        self.canvas.width  = 320;
                        self.canvas.height = self.el.videoWidth
                            ? Math.round(320 * (self.el.videoHeight / self.el.videoWidth))
                            : 180;
                        self.ready = true;

                        var queued = self.onReady;
                        self.onReady = [];
                        queued.forEach(function (fn) { fn(); });
                    });

                    this.el.addEventListener('seeked', function () {
                        self.draw();
                    });

                    this.el.muted       = true;
                    this.el.playsInline = true;
                    this.el.preload     = 'auto';
                    this.el.crossOrigin = 'anonymous';
                    this.el.src         = src;
                },

                fail: function () {
                    this.dead    = true;
                    this.ready   = false;
                    this.busy    = false;
                    this.pending = null;
                    this.onReady = [];

                    if (this.el) {
                        this.el.removeAttribute('src');
                        this.el.load();
                        this.el = null;
                    }
                },

                whenReady: function (fn) {
                    if (this.dead) {
                        return;
                    }
                    if (this.ready) {
                        fn();
                        return;
                    }
                    this.onReady.push(fn);
                },

                // Frames are keyed by whole second, so dragging across the bar decodes
                // far fewer times than mousemove fires.
                grab: function (time, callback) {
                    if (this.dead) {
                        return;
                    }

                    var key = Math.max(0, Math.floor(time));

                    if (this.cache[key]) {
                        callback(this.cache[key]);
                        return;
                    }

                    this.pending = { key: key, callback: callback };
                    this.run();
                },

                run: function () {
                    var self = this;

                    if (this.dead || this.busy || !this.pending) {
                        return;
                    }

                    this.whenReady(function () {
                        if (self.dead || self.busy || !self.pending) {
                            return;
                        }

                        self.busy = true;

                        var limit = (self.el.duration && isFinite(self.el.duration)) ? self.el.duration - 0.1 : self.pending.key;
                        self.el.currentTime = Math.max(0, Math.min(self.pending.key, limit));
                    });
                },

                draw: function () {
                    var job = this.pending;

                    this.busy    = false;
                    this.pending = null;

                    if (!job) {
                        return;
                    }

                    try {
                        this.ctx.drawImage(this.el, 0, 0, this.canvas.width, this.canvas.height);
                        this.cache[job.key] = this.canvas.toDataURL('image/jpeg', 0.7);
                    } catch (e) {
                        this.fail();   // tainted canvas — cross-origin without CORS
                        return;
                    }

                    job.callback(this.cache[job.key]);
                    this.run();
                },
            },

            // Kick the probe off. Both chapter thumbnails and the hover preview wait
            // on the same element, so this is the single entry point.
            startFrames: function () {
                if (this.framesStarted || !frameCfg.timing) {
                    return;
                }

                this.framesStarted = true;
                this.frames.start($media.find('source').attr('src') || $media.attr('src'));

                if (frameCfg.chapters) {
                    this.captureThumbs();
                }
            },

            // Fill every chapter thumbnail that has no author-set image.
            captureThumbs: function () {
                var self  = this,
                    MAX   = 30,
                    index = 0;

                var thumbFor = function (i) {
                    return $player.find('.sa-vp-chapter[data-index="' + i + '"] .sa-vp-chapter-thumb img').get(0);
                };

                // Author-set images win; only empty slots get a captured frame.
                var next = function () {
                    while (index < chapters.length && chapters[index].image) {
                        index++;
                    }

                    if (index >= chapters.length || index >= MAX) {
                        return;
                    }

                    var at = index;

                    self.frames.grab(chapters[at].time, function (url) {
                        var image = thumbFor(at);

                        if (image) {
                            image.src = url;
                            image.parentNode.classList.add('sa-captured');
                        }

                        index++;
                        next();
                    });
                };

                next();
            },

            seekTo: function (time) {
                var plyr = this.build();

                $player.removeClass('sa-vp-lazy');

                var jump = function () {
                    plyr.currentTime = time;
                    plyr.play();
                };

                if (plyr.ready) {
                    jump();
                    return;
                }

                plyr.once('ready', jump);
            },

            /* ── Sticky on scroll ────────────────────────────────────── */

            bindScroll: function () {
                if (this.scrollBound || !settings.sticky || !settings.sticky.enabled) {
                    return;
                }

                this.scrollBound = true;
                $(window).on('scroll.saVideoPlayer resize.saVideoPlayer', this.onScroll);
                this.onScroll();
            },

            // rAF-throttled: the listener only ever schedules a measure.
            onScroll: function () {
                if (player.ticking) {
                    return;
                }

                player.ticking = true;
                window.requestAnimationFrame(function () {
                    player.ticking = false;
                    player.updateSticky();
                });
            },

            updateSticky: function () {
                if (!this.started || this.stickyClosed) {
                    return;
                }

                // Measured against the frame's own height, not the wrapper's — the
                // wrapper may also hold a chapter list, which would delay the switch.
                var rect = el.getBoundingClientRect(),
                    past = rect.top + (this.frameHeight / 2) < 0;

                if (past === $player.hasClass('sa-vp-is-sticky')) {
                    return;
                }

                if (past) {
                    // Freeze the space the player leaves behind so the page does not jump.
                    el.style.minHeight = this.frameHeight + 'px';
                    $player.addClass('sa-vp-is-sticky');
                } else {
                    $player.removeClass('sa-vp-is-sticky');
                    el.style.minHeight = '';
                }
            },

            closeSticky: function () {
                this.stickyClosed = true;
                $player.removeClass('sa-vp-is-sticky');
                el.style.minHeight = '';

                if (this.plyr) {
                    this.plyr.pause();
                }
            },

            /* ── Wiring ──────────────────────────────────────────────── */

            bind: function () {
                var self = this;

                $overlay.on('click', function () {
                    self.play();
                });

                $overlay.on('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ' || e.keyCode === 13 || e.keyCode === 32) {
                        e.preventDefault();
                        self.play();
                    }
                });

                $player.on('click', '.sa-vp-chapter', function () {
                    self.seekTo(parseFloat($(this).data('time')) || 0);
                });

                $player.on('keydown', '.sa-vp-chapter', function (e) {
                    if (e.key === 'Enter' || e.key === ' ' || e.keyCode === 13 || e.keyCode === 32) {
                        e.preventDefault();
                        self.seekTo(parseFloat($(this).data('time')) || 0);
                    }
                });

                $player.on('click', '.sa-vp-close', function (e) {
                    e.preventDefault();
                    self.closeSticky();
                });
            },

            destroy: function () {
                $(window).off('scroll.saVideoPlayer resize.saVideoPlayer', this.onScroll);
                $player.find('.plyr__progress').off('.saVideoPlayer');
                this.frames.fail();   // releases the probe element and its buffered data

                if (this.plyr) {
                    try {
                        this.plyr.destroy();
                    } catch (e) { /* already gone */ }
                    this.plyr = null;
                }

                var index = saVideoPlayers.indexOf(this);

                if (index > -1) {
                    saVideoPlayers.splice(index, 1);
                }

                delete el.saVideoPlayer;
            },

            init: function () {
                var self = this;

                $player.find('img[data-sa-fallback]').each(function () {
                    saPosterFallback(this);
                });

                this.bind();

                // Frame capture is independent of the player — it uses its own probe
                // element, so it can run before Plyr exists. That is what lets the
                // chapter thumbnails and the hover preview be ready before the first
                // click, at the cost of loading video data for every visitor who
                // scrolls past. Hence the author-facing timing control.
                if (frameCfg.timing === 'view') {
                    skyAddonsObserver(el, function () {
                        self.startFrames();
                    });
                }

                // Lazy load defers the whole Plyr construction — and with it the
                // provider iframe and its scripts — until the visitor asks for it.
                if (settings.lazyLoad) {
                    return;
                }

                this.build();

                if (settings.playOnView) {
                    skyAddonsObserver(el, function () {
                        self.play();
                    });
                }
            },
        };

        el.saVideoPlayer = player;
        saVideoPlayers.push(player);
        player.init();
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/sky-video-player.default', widgetVideoPlayer);
    });

}(jQuery, window.elementorFrontend));
