;(function ($, elementor) {
    'use strict';

var widgetAdvancedAccordion = function ($scope, $) {
    var $advancedAccordion = $scope.find('.sa-advanced-accordion');
    var $settings = $advancedAccordion.data('settings');

    if (!$advancedAccordion.length) {
        return;
    }

    // The vendor library gives every trigger role="button", aria-expanded and aria-controls,
    // and binds a keydown handler — but never a tabindex. A div with role="button" and no
    // tabindex is announced as a button while sitting outside the tab order, so it cannot be
    // reached at all and the library's own keydown can never fire. Toggling lives only in its
    // click handler, which meant no panel could be opened from the keyboard.
    //
    // Not rendered as a real <button>: the trigger holds the widget's configurable heading,
    // and <button> takes phrasing content only — a heading nested in one is invalid markup
    // and assistive tech flattens it into the button's name, losing the heading level.
    $advancedAccordion.find('.sa-ac-trigger').attr('tabindex', 0);

    $advancedAccordion.on('keydown', '.sa-ac-trigger', function (e) {
        // What a native button honours. ' ' is the modern key name for Space, 'Spacebar' the
        // legacy one. Arrow/Home/End stay with the library's own roving-focus handler.
        if ('Enter' !== e.key && ' ' !== e.key && 'Spacebar' !== e.key) {
            return;
        }
        // Space would otherwise scroll the page.
        e.preventDefault();
        this.click();
    });

    var accOptions = {
        duration:     $settings.duration,
        showMultiple: $settings.showMultiple,
        collapse:     $settings.collapse,
        elementClass: 'sa-ac-item',
        triggerClass: 'sa-ac-trigger',
        panelClass:   'sa-ac-panel',
        activeClass:  'is-active',
    };

    var $cols = $advancedAccordion.children('.sa-acc-col');

    if ($cols.length) {
        // Multi-column: init on each column div so accordion.js finds
        // .sa-ac-item as direct children. Map global openOnInit indices
        // to per-column indices so the correct item opens in the right column.
        var openOnInit = $settings.openOnInit || [];
        var offset = 0;

        $cols.each(function () {
            var colEl    = this;
            var colCount = $(colEl).children('.sa-ac-item').length;
            var colOpen  = openOnInit
                .filter(function (i) { return i >= offset && i < offset + colCount; })
                .map(function (i) { return i - offset; });

            new Accordion(colEl, $.extend({}, accOptions, { openOnInit: colOpen }));
            offset += colCount;
        });
    } else {
        new Accordion('#' + $settings.id, $.extend({}, accOptions, {
            openOnInit: $settings.openOnInit,
        }));
    }
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-advanced-accordion.default', widgetAdvancedAccordion);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

// IntersectionObserver is not usable here: during a fast/smooth scroll it delivers no
// entry at all for some items (measured: rows 3-4 of 72 got only the initial ratio 0),
// so no in-callback fallback can reach them. Sweep the pending items from a shared
// rAF-throttled scroll handler instead — one listener for every skill bar on the page.
var pending = [];
var bound   = false;
var ticking = false;

function sweep() {
    ticking = false;

    var viewH = window.innerHeight || document.documentElement.clientHeight;

    pending = pending.filter(function (entry) {
        if (!entry.el.isConnected) {
            return false; // editor re-render left a detached node behind
        }

        var rect = entry.el.getBoundingClientRect();

        if (!rect.width && !rect.height) {
            return true; // not laid out yet (hidden tab / accordion) — keep waiting
        }

        // Cap the requirement at what is physically reachable: an item taller than the
        // viewport can never show 80% of itself.
        var visible = Math.min(rect.bottom, viewH) - Math.max(rect.top, 0);
        var need    = Math.min(entry.threshold, viewH / rect.height * 0.95) * rect.height;

        if (rect.bottom <= 0 || visible >= need) {
            entry.fill();
            return false;
        }

        return true;
    });

    if (!pending.length && bound) {
        bound = false;
        window.removeEventListener('scroll', schedule);
        window.removeEventListener('resize', schedule);
    }
}

function schedule() {
    if (!ticking) {
        ticking = true;
        requestAnimationFrame(sweep);
    }
}

function watch(el, threshold, fill) {
    pending.push({ el: el, threshold: threshold, fill: fill });

    if (!bound) {
        bound = true;
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule, { passive: true });
    }

    schedule(); // first sweep also catches anything already past on load
}

var widgetAdvancedSkillBars = function ($scope, $) {
    var $advancedSkillBars = $scope.find('.sa-advanced-skills'),
        $items = $scope.find('.sa-skill-item');

    if (!$advancedSkillBars.length) {
        return;
    }

    var settings      = $advancedSkillBars.data('settings') || {};
    var animDuration  = settings.animDuration  || 2600;
    var animThreshold = settings.animThreshold || 0.8;
    var valuePrefix   = settings.valuePrefix   || '';
    var valueSuffix   = settings.valueSuffix   !== undefined ? settings.valueSuffix : '%';

    $items.each(function () {
        var $item  = $(this);
        var filled = false;

        watch(this, animThreshold, function () {
            if (filled) {
                return;
            }

            filled = true;

            $item.find('.sa-skill-progress-bar').each(function () {
                var $bar          = $(this);
                var skillMaxValue = parseFloat($bar.attr('data-max-value')) || 100;
                var skillFillVal  = parseFloat($bar.attr('data-width'))     || 0;

                // Keep the counter at the value's own precision, otherwise a decimal
                // skill (8.6) prints a number its own bar contradicts.
                var decimals = (String(skillFillVal).split('.')[1] || '').length;

                $bar.css('width', (skillFillVal * 100) / skillMaxValue + '%');
                $bar.children('.sa-skill-content-wrapper, .sa-skill-value').css('transform', 'scale(1)');

                $item.find('.sa-skill-value').prop('Counter', 0).animate({
                    Counter: skillFillVal
                }, {
                    duration: animDuration,
                    easing: 'swing',
                    step: function (now) {
                        $(this).text(valuePrefix + now.toFixed(decimals) + valueSuffix);
                    },
                    complete: function () {
                        $(this).text(valuePrefix + skillFillVal + valueSuffix);
                    }
                });
            });
        });
    });
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-advanced-skill-bars.default', widgetAdvancedSkillBars);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

var widgetAdvancedSlider = function ($scope, $) {
    var $slider = $scope.find('.sa-advanced-slider'),
        $sliderContainer = $slider.find('.swiper'),
        $settings = $slider.data('settings');

    if (!$slider.length) {
        return;
    }

    function activateSlideVideo($slide) {
        // Lazy-load iframe: set src from data-src on first activation
        $slide.find('iframe[data-src]').each(function () {
            this.src = this.dataset.src;
            delete this.dataset.src;
        });
        // Resume already-loaded iframes
        $slide.find('[data-video-type="youtube"] iframe').each(function () {
            try {
                this.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
            } catch (e) {}
        });
        $slide.find('[data-video-type="vimeo"] iframe').each(function () {
            try {
                this.contentWindow.postMessage('{"method":"play"}', '*');
            } catch (e) {}
        });
        // HTML5 video
        $slide.find('video').each(function () {
            this.play().catch(function () {});
        });
    }

    function deactivateSlideVideo($slide) {
        $slide.find('[data-video-type="youtube"] iframe').each(function () {
            try {
                this.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
            } catch (e) {}
        });
        $slide.find('[data-video-type="vimeo"] iframe').each(function () {
            try {
                this.contentWindow.postMessage('{"method":"pause"}', '*');
            } catch (e) {}
        });
        $slide.find('video').each(function () {
            this.pause();
        });
    }

    var hasVideo = $slider.find('.sa-slide-video-wrapper').length > 0;

    // Destroy any existing Swiper instance on this container before re-init
    if ($sliderContainer[0] && $sliderContainer[0].swiper) {
        $sliderContainer[0].swiper.destroy(true, true);
    }

    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {
        var swiper = await new Swiper($sliderContainer, $settings);

        if ($settings.pauseOnHover) {
            $($sliderContainer).hover(function () {
                (this).swiper.autoplay.stop();
            }, function () {
                (this).swiper.autoplay.start();
            });
        }

        if (hasVideo) {
            // Load and play the initial active slide's video immediately
            activateSlideVideo($slider.find('.swiper-slide-active'));

            swiper.on('slideChangeTransitionStart', function () {
                deactivateSlideVideo($slider.find('.swiper-slide-prev, .swiper-slide-next'));
            });

            swiper.on('slideChangeTransitionEnd', function () {
                activateSlideVideo($slider.find('.swiper-slide-active'));
            });
        }
    };

};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-advanced-slider.default', widgetAdvancedSlider);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

var widgetAnimatedHeading = function ($scope, $) {

    var $animatedHeading = $scope.find('.sa-animated-heading');
    var $settings = $animatedHeading.data('settings');
    if (!$animatedHeading.length || !$settings) {
        return;
    }

    var isRtl = $('body').hasClass('rtl')
        || $('html').attr('dir') === 'rtl'
        || $animatedHeading.css('direction') === 'rtl';

    skyAddonsObserver($scope[0], function () {
        var selector = $animatedHeading.data('id');
        var style = $settings.style;
        delete $settings.style;

        if ('typed' === style) {
            new Typed('#' + selector, $settings);
        } else if ('animated' === style) {
            // Morphext reads .text() (entities decoded) and writes innerHTML — feed it the
            // escaped markup so a title like <img onerror> renders as text, not HTML.
            var $morph = $('#' + selector);
            $morph.text($morph.html());
            $morph.Morphext($settings);
        } else if ('highlight' === style) {
            saHighlight('#' + selector, $settings);
        } else if ('glitch' === style) {
            saGlitch('#' + selector, $settings);
        } else if ('reveal' === style) {
            saReveal('#' + selector, $settings, isRtl);
        } else if ('word-rotate' === style) {
            saWordRotate('#' + selector, $settings);
        } else if ('split-chars' === style) {
            saSplitChars('#' + selector, $settings, isRtl);
        } else if ('gravity' === style) {
            saGravity('#' + selector, $settings, isRtl);
        } else if ('flip-chars' === style) {
            saFlipChars('#' + selector, $settings, isRtl);
        } else if ('vortex' === style) {
            saVortex('#' + selector, $settings, isRtl);
        } else if ('wave-in' === style) {
            saWaveIn('#' + selector, $settings, isRtl);
        }
    }, {
        root: null,
        rootMargin: '0px',
        threshold: 0.8
    });

};

function saHighlight(selector, settings) {
    var $el = $(selector);
    var words = settings.strings || [];
    var interval = settings.interval || 2000;
    var i = 0;

    if (!words.length) { return; }

    function showWord() {
        var $span = $('<span class="sa-word sa-highlight-word">').html(words[i % words.length]);
        $el.html($span);
        setTimeout(function () { $span.addClass('sa--active'); }, 30);
        i++;
    }

    showWord();
    setInterval(showWord, interval);
}

function saGlitch(selector, settings) {
    var $el = $(selector);
    var words = settings.strings || [];
    var interval = settings.interval || 2000;
    var chars = '!<>-_\\/[]{}=+*^?#@';
    var i = 0;
    var timer;

    if (!words.length) { return; }

    function decodeHtml(html) { return $('<span>').html(html).text(); }

    function glitch(word) {
        var decoded = decodeHtml(word);
        var duration = 600;
        var start = Date.now();
        clearInterval(timer);
        timer = setInterval(function () {
            var elapsed = Date.now() - start;
            var progress = Math.min(elapsed / duration, 1);
            var result = decoded.split('').map(function (ch, idx) {
                if (idx < Math.floor(progress * decoded.length)) { return decoded[idx]; }
                return chars[Math.floor(Math.random() * chars.length)];
            }).join('');
            $el.text(result);
            if (progress >= 1) { clearInterval(timer); $el.text(decoded); }
        }, 30);
    }

    glitch(words[i++]);
    setInterval(function () { glitch(words[i++ % words.length]); }, interval);
}

function saReveal(selector, settings, isRtl) {
    var $el = $(selector);
    var words = settings.strings || [];
    var interval = settings.interval || 2000;
    var i = 0;

    if (!words.length) { return; }

    function showWord() {
        var cls = 'sa-reveal-word' + (isRtl ? ' sa-reveal-rtl' : '');
        $el.html($('<span>').addClass(cls).html(words[i++ % words.length]));
    }

    showWord();
    setInterval(showWord, interval);
}

function saWordRotate(selector, settings) {
    var $el = $(selector);
    var words = settings.strings || [];
    var interval = settings.interval || 2000;
    var i = 0;

    if (!words.length) { return; }

    function showWord() {
        var $old = $el.find('.sa-rotate-word');
        if ($old.length) {
            $old.addClass('sa-rotate-exit').one('animationend webkitAnimationEnd', function () { $(this).remove(); });
        }
        $el.append($('<span class="sa-rotate-word">').html(words[i++ % words.length]));
    }

    showWord();
    setInterval(showWord, interval);
}

function saCharWord(selector, settings, isRtl, cls, makeSpan) {
    var $el = $(selector);
    var words = settings.strings || [];
    var interval = settings.interval || 2000;
    var i = 0;

    if (!words.length) { return; }

    function decodeHtml(html) { return $('<span>').html(html).text(); }

    function showWord() {
        var $old = $el.find('.' + cls);
        var $new = makeSpan(decodeHtml(words[i++ % words.length]), isRtl);
        if ($old.length) {
            $old.css({ position: 'absolute', left: 0, top: 0 });
            exitWord($old);
        }
        $el.append($new);
    }

    showWord();
    setInterval(showWord, interval);
}

// Throw distance for the char-flight styles, in px-per-em of the heading itself.
// Hardcoded px cannot work at both ends: 70px is twice the box of a single short word
// (its chars land outside the widget, ghosting over whatever sits next to it) and barely
// a nudge on a 120px display heading.
function saEm($el) {
    return parseFloat($el.css('font-size')) || 16;
}

// Builds one character span. Whitespace gets an extra class because the char
// spans are display:inline-block, and a lone collapsible space inside an
// inline-block renders at zero width — `.sa-char-space` restores it.
function saCharSpan(cls, ch) {
    var $char = $('<span>').addClass(cls).text(ch);
    if (/\s/.test(ch)) { $char.addClass('sa-char-space'); }
    return $char;
}

function saSplitChars(selector, settings, isRtl) {
    var $el = $(selector);
    var words = settings.strings || [];
    var interval = settings.interval || 2000;
    var i = 0;

    if (!words.length) { return; }

    function decodeHtml(html) { return $('<span>').html(html).text(); }

    function throwDistance() {
        return saEm($el) * 0.9;
    }

    function makeWordSpan(word) {
        var $wrap = $('<span class="sa-split-word">');
        var d = throwDistance();
        if (isRtl) { $wrap.attr('dir', 'rtl'); }
        decodeHtml(word).split('').forEach(function (ch, idx) {
            var tx = (Math.random() * 2 - 1) * d, ty = (Math.random() * 2 - 1) * d, rot = (Math.random() * 300 - 150);
            var $char = saCharSpan('sa-split-char', ch);
            $char.css({ transform: 'translate(' + tx + 'px,' + ty + 'px) rotate(' + rot + 'deg)', opacity: 0 });
            $wrap.append($char);
            setTimeout(function () {
                $char.css({ transform: 'translate(0,0) rotate(0deg)', opacity: 1, transition: 'transform 0.55s cubic-bezier(0.22,1,0.36,1), opacity 0.4s ease' });
            }, 20 + idx * 35);
        });
        return $wrap;
    }

    function showWord() {
        var $old = $el.find('.sa-split-word');
        if ($old.length) {
            $old.css({ position: 'absolute', left: 0, top: 0 });
            var d = throwDistance();
            $old.find('.sa-split-char').each(function (idx) {
                var $c = $(this), tx = (Math.random() * 2 - 1) * d, ty = (Math.random() * 2 - 1) * d, rot = (Math.random() * 300 - 150);
                setTimeout(function () {
                    $c.css({ transform: 'translate(' + tx + 'px,' + ty + 'px) rotate(' + rot + 'deg)', opacity: 0, transition: 'transform 0.35s ease, opacity 0.28s ease' });
                }, idx * 25);
            });
            setTimeout(function () { $old.remove(); }, 600);
        }
        $el.append(makeWordSpan(words[i++ % words.length]));
    }

    showWord();
    setInterval(showWord, interval);
}

function saGravity(selector, settings, isRtl) {
    var $el = $(selector);
    var words = settings.strings || [];
    var interval = settings.interval || 2000;
    var i = 0;

    if (!words.length) { return; }

    function decodeHtml(html) { return $('<span>').html(html).text(); }

    function makeWordSpan(word) {
        var $wrap = $('<span class="sa-gravity-word">');
        if (isRtl) { $wrap.attr('dir', 'rtl'); }
        decodeHtml(word).split('').forEach(function (ch, idx) {
            $wrap.append(saCharSpan('sa-gravity-char', ch).css('animation-delay', (idx * 40) + 'ms'));
        });
        return $wrap;
    }

    function showWord() {
        var $old = $el.find('.sa-gravity-word');
        if ($old.length) {
            $old.css({ position: 'absolute', left: 0, top: 0 }).addClass('sa-gravity-exit');
            setTimeout(function () { $old.remove(); }, 450);
        }
        $el.append(makeWordSpan(words[i++ % words.length]));
    }

    showWord();
    setInterval(showWord, interval);
}

function saFlipChars(selector, settings, isRtl) {
    var $el = $(selector);
    var words = settings.strings || [];
    var interval = settings.interval || 2000;
    var i = 0;

    if (!words.length) { return; }

    function decodeHtml(html) { return $('<span>').html(html).text(); }

    function makeWordSpan(word) {
        var $wrap = $('<span class="sa-flip-word">');
        if (isRtl) { $wrap.attr('dir', 'rtl'); }
        decodeHtml(word).split('').forEach(function (ch, idx) {
            $wrap.append(saCharSpan('sa-flip-char', ch).css('animation-delay', (idx * 50) + 'ms'));
        });
        return $wrap;
    }

    function showWord() {
        var $old = $el.find('.sa-flip-word');
        if ($old.length) {
            $old.css({ position: 'absolute', left: 0, top: 0 }).addClass('sa-flip-exit');
            setTimeout(function () { $old.remove(); }, 450);
        }
        $el.append(makeWordSpan(words[i++ % words.length]));
    }

    showWord();
    setInterval(showWord, interval);
}

function saVortex(selector, settings, isRtl) {
    var $el = $(selector);
    var words = settings.strings || [];
    var interval = settings.interval || 2000;
    var i = 0;

    if (!words.length) { return; }

    function decodeHtml(html) { return $('<span>').html(html).text(); }

    function makeWordSpan(word) {
        var $wrap = $('<span class="sa-vortex-word">');
        var step = saEm($el) * 0.8;
        if (isRtl) { $wrap.attr('dir', 'rtl'); }
        var chars = decodeHtml(word).split('');
        chars.forEach(function (ch, idx) {
            var cx = (idx - (chars.length - 1) / 2) * step, rot = (Math.random() * 360 - 180);
            var $char = saCharSpan('sa-vortex-char', ch);
            $char.css({ transform: 'translateX(' + (-cx) + 'px) rotate(' + rot + 'deg) scale(0)', opacity: 0 });
            $wrap.append($char);
            setTimeout(function () {
                $char.css({ transform: 'translateX(0) rotate(0deg) scale(1)', opacity: 1, transition: 'transform 0.6s cubic-bezier(0.34,1.56,0.64,1), opacity 0.35s ease' });
            }, 20 + idx * 45);
        });
        return $wrap;
    }

    function showWord() {
        var $old = $el.find('.sa-vortex-word');
        if ($old.length) {
            $old.css({ position: 'absolute', left: 0, top: 0 });
            var total = $old.find('.sa-vortex-char').length;
            var step = saEm($el) * 0.8;
            $old.find('.sa-vortex-char').each(function (idx) {
                var $c = $(this), cx = (idx - (total - 1) / 2) * step, rot = (Math.random() * 360 - 180);
                setTimeout(function () {
                    $c.css({ transform: 'translateX(' + (-cx) + 'px) rotate(' + rot + 'deg) scale(0)', opacity: 0, transition: 'transform 0.35s ease, opacity 0.28s ease' });
                }, idx * 30);
            });
            setTimeout(function () { $old.remove(); }, 550);
        }
        $el.append(makeWordSpan(words[i++ % words.length]));
    }

    showWord();
    setInterval(showWord, interval);
}

function saWaveIn(selector, settings, isRtl) {
    var $el = $(selector);
    var words = settings.strings || [];
    var interval = settings.interval || 2000;
    var i = 0;

    if (!words.length) { return; }

    function decodeHtml(html) { return $('<span>').html(html).text(); }

    function showWord() {
        $el.empty();
        var $wrap = $('<span>');
        if (isRtl) { $wrap.attr('dir', 'rtl'); }
        decodeHtml(words[i++ % words.length]).split('').forEach(function (ch, idx) {
            $wrap.append(saCharSpan('sa-wave-char', ch).css('animation-delay', (idx * 55) + 'ms'));
        });
        $el.append($wrap);
    }

    showWord();
    setInterval(showWord, interval);
}

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-animated-heading.default', widgetAnimatedHeading);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

var widgetAudioPlayer = function ($scope, $) {
    var $el      = $scope.find('.sa-audio-player'),
        $audio   = $scope.find('audio')[0],
        settings = $el.data('settings');

    if (!$el.length || !$audio) { return; }

    var player = new Plyr($audio, skyAddonsPlyrOptions({
        controls:    [],
        autoplay:    settings.autoplay || false,
        loop:        { active: settings.loop || false },
        clickToPlay: false,
    }));

    var isVinyl   = $el.hasClass('sa-style-vinyl'),
        vinylFill = isVinyl ? $el.find('.sa-vinyl-fill')[0] : null,
        vinylCirc = 565;

    $el.find('.sa-btn-play-pause').on('click', function () {
        player.togglePlay();
    });

    $el.find('.sa-progress-bar').on('click', function (e) {
        if (!player.duration) { return; }
        var pct = e.offsetX / $(this).outerWidth();
        var $fill = $el.find('.sa-progress-fill');
        $fill.css('transition', 'none');
        if (vinylFill) { vinylFill.style.transition = 'none'; }
        player.currentTime = pct * player.duration;
        setTimeout(function () {
            $fill.css('transition', '');
            if (vinylFill) { vinylFill.style.transition = ''; }
        }, 50);
    });

    var $volume = $el.find('.sa-volume-slider');

    $volume.on('input', function () {
        player.volume = parseFloat(this.value);
    });

    var syncVolume = function () {
        if (!$volume.length) { return; }
        var vol = player.muted ? 0 : player.volume;
        $volume.val(vol);
        $volume[0].style.setProperty('--sa-volume', (vol * 100) + '%');
    };

    player.on('ready volumechange', syncVolume);

    player.on('timeupdate', function () {
        if (!player.duration) { return; }
        var pct = (player.currentTime / player.duration) * 100;
        $el.find('.sa-progress-fill').css('width', pct + '%');
        $el.find('.sa-progress-bar').attr('aria-valuenow', Math.round(pct));
        $el.find('.sa-time-current').text(saFormatTime(player.currentTime));
        if (vinylFill) {
            vinylFill.style.strokeDashoffset = vinylCirc - (pct / 100) * vinylCirc;
        }
    });

    player.on('ready loadedmetadata', function () {
        $el.find('.sa-time-total').text(saFormatTime(player.duration || 0));
    });

    player.on('play',  function () { $el.addClass('sa-is-playing'); });
    player.on('pause', function () { $el.removeClass('sa-is-playing'); });
    player.on('ended', function () { $el.removeClass('sa-is-playing'); });
};

function saFormatTime(seconds) {
    if (isNaN(seconds) || seconds < 0) { return '0:00'; }
    var m = Math.floor(seconds / 60),
        s = Math.floor(seconds % 60);
    return m + ':' + (s < 10 ? '0' : '') + s;
}

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-audio-player.default', widgetAudioPlayer);
    });

}(jQuery, window.elementorFrontend));

;(function ($) {
    'use strict';

    var widgetChangelog = function ($scope) {
        var $wrapper = $scope.find('.sa-changelog-wrapper');
        if (!$wrapper.length) return;

        var limit    = parseInt($wrapper.data('versions-limit'), 10) || 3;
        var step     = parseInt($wrapper.data('load-step'), 10) || 3;
        var btnLabel = $wrapper.data('load-more-text') || 'Load More Versions';
        var $cards   = $wrapper.find('.sa-changelog-version');

        if ($cards.length <= limit) return;

        $cards.slice(limit).hide();

        var remaining = $cards.length - limit;

        var $btn = $(
            '<a class="sa-cl-load-more" type="button">' +
                '<span class="sa-cl-icon"></span>' +
                '<span class="sa-cl-btn-label"></span>' +
                '<span class="sa-cl-count">+' + remaining + '</span>' +
            '</a>'
        );

        // .text(), never string concat — the label is user input decoded from a data attribute.
        $btn.find('.sa-cl-btn-label').text(btnLabel);

        $wrapper.after($btn);

        $btn.on('click', function () {
            if ($btn.hasClass('sa-cl-loading')) return;

            $btn.addClass('sa-cl-loading');

            var $hidden  = $cards.filter(':hidden');
            var $toShow  = $hidden.slice(0, step);
            var revealCount = $toShow.length;

            $toShow.each(function (i) {
                var $card = $(this);
                setTimeout(function () {
                    $card.slideDown(400);
                }, i * 80);
            });

            setTimeout(function () {
                $btn.removeClass('sa-cl-loading');

                var stillHidden = $cards.filter(':hidden').length;

                if (stillHidden === 0) {
                    $btn.addClass('sa-cl-done');
                    setTimeout(function () { $btn.remove(); }, 500);
                } else {
                    // Pop animation on count badge update
                    var $count = $btn.find('.sa-cl-count');
                    $count.text('+' + stillHidden).addClass('sa-cl-pop');
                    setTimeout(function () { $count.removeClass('sa-cl-pop'); }, 400);
                }
            }, (revealCount - 1) * 80 + 430);
        });
    };

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/sky-changelog.default',
            widgetChangelog
        );
    });

}(jQuery));

;(function ($, elementor) {
    'use strict';

var widgetContentSwitcher = function ($scope, $) {

    var $contentSwitcher = $scope.find('.sa-content-switcher'),
        $settings = $contentSwitcher.data('settings');

    if (!$contentSwitcher.length) {
        return;
    }

    var switcherToggle = $contentSwitcher.find('.sa-switcher-toggle'),
        checkbox = $($settings.checkbox),
        switcherWrapper = $contentSwitcher.find('.sa-switcher-wrap'),
        contentWrapper = $contentSwitcher.find('.sa-content-wrapper');

    // Apply user-defined transition speed as a CSS custom property
    if ($settings.transitionSpeed) {
        $contentSwitcher.css('--sa-transition-speed', $settings.transitionSpeed + 'ms');
    }

    // ── Binary toggle mode ────────────────────────────────────────────────────
    if ($settings.type !== 'button') {

        function activateBinaryItem(isSecondary) {
            if (isSecondary) {
                switcherWrapper.find('.sa-switch-item').removeClass('sa-active');
                switcherWrapper.find('.sa-switch-item.sa-secondary').addClass('sa-active');
                contentWrapper.find('.sa-switch-content-item').removeClass('sa-active');
                contentWrapper.find('.sa-switch-content-item.sa-secondary').addClass('sa-active');
            } else {
                switcherWrapper.find('.sa-switch-item').removeClass('sa-active');
                switcherWrapper.find('.sa-switch-item.sa-primary').addClass('sa-active');
                contentWrapper.find('.sa-switch-content-item').removeClass('sa-active');
                contentWrapper.find('.sa-switch-content-item.sa-primary').addClass('sa-active');
            }
        }

        switcherToggle.on('click', function () {
            activateBinaryItem(checkbox.is(':checked'));
        });

        // Deep link: activate the matching item on page load
        var hash = window.location.hash ? window.location.hash.slice(1) : '';
        if (hash) {
            var $primary   = switcherWrapper.find('.sa-switch-item.sa-primary');
            var $secondary = switcherWrapper.find('.sa-switch-item.sa-secondary');
            if ($primary.data('slug') === hash) {
                checkbox.prop('checked', false);
                activateBinaryItem(false);
            } else if ($secondary.data('slug') === hash) {
                checkbox.prop('checked', true);
                activateBinaryItem(true);
            }
        }
    }

    // ── Button (tabs) mode ────────────────────────────────────────────────────
    if ($settings.type === 'button') {

        var borderSize = $settings.borderSize || 0,
            isVertical = $settings.orientation === 'vertical',
            tabs       = $contentSwitcher.find('.sa-switcher-tabs');

        function positionSelector($item) {
            var pos = $item.position();
            if (isVertical) {
                $contentSwitcher.find('.sa-selector').css({
                    top:    pos.top + 'px',
                    height: $item.outerHeight() + 'px',
                });
            } else {
                $contentSwitcher.find('.sa-selector').css({
                    left:  pos.left + 'px',
                    width: ($item.innerWidth() + borderSize) + 'px',
                });
            }
        }

        var activeItem = tabs.find('.sa-active');
        if (activeItem.length) {
            positionSelector(activeItem);
        }

        tabs.on('click', 'a', function (e) {
            e.preventDefault();

            var id = $(this).data('id');

            switcherWrapper.find('.sa-switcher-tabs a').removeClass('sa-active');
            $(this).addClass('sa-active');

            contentWrapper.find('.sa-switch-content-item').removeClass('sa-active');
            contentWrapper.find('#' + id).addClass('sa-active');

            positionSelector($(this));
        });

        // Deep link: click the matching tab on page load
        var hash = window.location.hash ? window.location.hash.slice(1) : '';
        if (hash) {
            var $target = tabs.find('[data-slug="' + hash + '"]');
            if ($target.length) {
                $target.trigger('click');
            }
        }

        if ($('body').hasClass('rtl')) {
            $contentSwitcher.find('.sa-switcher-tabs .sa-selector').css({
                right: 'auto',
            });
        }
    }

};
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-content-switcher.default', widgetContentSwitcher);
    });

}(jQuery, window.elementorFrontend));

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

;(function ($, elementor) {
    'use strict';

var widgetFellowSlider = function ($scope, $) {

    var $fellowSlider = $scope.find('.sa-fellow-slider'),
        $fellowContainer = $fellowSlider.find('.sa-fellow.swiper'),
        $itemsContainer = $fellowSlider.find('.sa-fellow-items'),
        $playerSettings = $fellowSlider.data('player-settings');

    if (!$fellowSlider.length) {
        return;
    }

    /**
     * Keep the list pointing at whatever the player is showing.
     *
     * `realIndex` rather than `activeIndex`: with Loop on, Swiper prepends and appends
     * duplicate slides, so activeIndex counts those too and would drift off the rows by
     * however many duplicates exist. realIndex is always the original post's position,
     * which is exactly what data-index holds.
     *
     * The rail is scrolled by hand instead of with scrollIntoView. scrollIntoView walks the
     * whole ancestor chain and scrolls every scrollable box up to the document, so an
     * autoplaying slider below the fold would drag the page down to itself on every slide
     * change — with two or more on a page that reads as the page scrolling on its own.
     * Writing scrollTop on the rail touches that one element and nothing above it.
     *
     * The delta math reproduces block: 'nearest' — no movement while the row is fully
     * visible, otherwise the smallest scroll that brings the nearer edge flush.
     */
    function syncActiveRow(player) {
        var $rows = $itemsContainer.find('.sa-fellow-row');
        var $row = $rows.filter('[data-index="' + player.realIndex + '"]');

        $rows.removeClass('sa-active');

        if (!$row.length) {
            return;
        }

        $row.addClass('sa-active');

        var rail = $itemsContainer[0];

        if (!rail) {
            return;
        }

        var railRect = rail.getBoundingClientRect();
        var rowRect = $row[0].getBoundingClientRect();
        var delta = 0;

        if (rowRect.top < railRect.top) {
            delta = rowRect.top - railRect.top;
        } else if (rowRect.bottom > railRect.bottom) {
            delta = rowRect.bottom - railRect.bottom;
        }

        if (!delta) {
            return;
        }

        if (typeof rail.scrollTo === 'function') {
            rail.scrollTo({ top: rail.scrollTop + delta, behavior: 'smooth' });
        } else {
            rail.scrollTop += delta;
        }
    }

    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {

        var player = await new Swiper($fellowContainer, $playerSettings);

        // Delegated, so it survives any re-render of the list markup.
        $itemsContainer.on('click', '.sa-fellow-row', function () {
            // slideToLoop, not slideTo — with Loop on, slideTo would take the duplicate at
            // that raw position instead of the post the row stands for.
            player.slideToLoop(parseInt(this.getAttribute('data-index'), 10) || 0);
        });

        player.on('slideChange', function () {
            syncActiveRow(player);
        });

        syncActiveRow(player);

        if ($playerSettings.pauseOnHover) {
            $($fellowContainer).hover(function () {
                (this).swiper.autoplay.stop();
            }, function () {
                (this).swiper.autoplay.start();
            });
        }
    };
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-fellow-slider.default', widgetFellowSlider);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

// This widget uses the global carousel handler

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-generic-carousel.default', widgetGlobalCarousel);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

var widgetGlorySlider = function ($scope, $) {

    var $glorySlider = $scope.find('.sa-glory-slider'),
        $playerContainer = $glorySlider.find('.sa-glory-player'),
        $thumbsContainer = $glorySlider.find('.sa-glory-thumbs'),
        $playerSettings = $glorySlider.data('player-settings'),
        $thumbsSettings = $glorySlider.data('thumbs-settings');

    if (!$glorySlider.length) {
        return;
    }

    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {
        var playerThumbs = null;
        if ($thumbsContainer.length) {
            playerThumbs = await new Swiper($thumbsContainer, $thumbsSettings);
        }

        var player = await new Swiper($playerContainer, $playerSettings);

        if (playerThumbs) {
            player.controller.control = playerThumbs;
            playerThumbs.controller.control = player;

            var testWidth = $glorySlider.find('.sa-glory-player .swiper-slide-active').width();
            $glorySlider.find('.sa-glory-thumbs').width(testWidth);
        }

        player.on('slideChange', function () {
            resetVideos();
        });
    };


    function resetVideos() {
        $($glorySlider).find('.sa-video-player').css('z-index', -1);
        var videos = $($glorySlider).find('.sa-player-iframe');
        Array.prototype.forEach.call(videos, function (video) {
            var src = video.src;
            video.src = src.replace("?autoplay=1", "");
            $($glorySlider).find('.sa-player-iframe').prop("src", "");
        });
    }

    $('.sa-play-button').on('click', function () {
        var videoURL = $(this).data('src').split('?')[0]; // also removed @param
        var sliderWrapper = $(this).closest('.sa-player-wrapper');
        sliderWrapper.find('.sa-player-iframe').attr("src", videoURL + "?autoplay=1");
        sliderWrapper.find('.sa-video-player').css('z-index', 10);

    });

};
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-glory-slider.default', widgetGlorySlider);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

    var widgetImageCompare = function ($scope, $) {

        var $imageCompare = $scope.find('.sa-image-compare');
        var $settings = $imageCompare.data('settings');
        if (!$imageCompare.length) {
            return;
        }

        var viewers = document.querySelectorAll('#' + $settings.id);

        // Responsive starting point — PHP can't detect viewport, so JS overrides here
        var isMobile = window.innerWidth <= 767;
        if (isMobile && $settings.mobileStartingPoint) {
            $settings.startingPoint = $settings.mobileStartingPoint;
        }

        viewers.forEach(function (element) {
            new ImageCompare(element, $settings).mount();

            // DOM overrides — library sets these as inline styles, so we override post-mount

            // Circle Size
            if ($settings.addCircle && $settings.circleSize && $settings.circleSize !== 50) {
                $(element).find('.icv__circle').css({ width: $settings.circleSize + 'px', height: $settings.circleSize + 'px' });
            }

            // Circle Vertical Offset
            if ($settings.addCircle && $settings.circleVerticalOffset && $settings.circleVerticalOffset !== 0) {
                $(element).find('.icv__circle').css('transform', 'translateY(' + $settings.circleVerticalOffset + 'px)');
            }

            // Smoothing Easing
            if ($settings.smoothing && $settings.smoothingEase && 'ease-out' !== $settings.smoothingEase) {
                $(element).find('.icv__theme-wrapper, .icv__wrapper').css('transition-timing-function', $settings.smoothingEase);
            }

            // Label Fade Duration
            if ($settings.labelOptions && $settings.labelOptions.onHover && $settings.labelFadeDuration && $settings.labelFadeDuration !== 0.25) {
                $(element).find('.icv__label.on-hover').css('transition-duration', $settings.labelFadeDuration + 's');
            }

            // Entry Animation — fade-in only, no transform conflict with library positioning
            if ($settings.animateOnLoad) {
                var duration = $settings.entryAnimationDuration || 800;
                var ease = $settings.smoothingEase || 'ease-out';
                var keyframeStyle = '@keyframes sa-ic-fade-in { from { opacity: 0; } to { opacity: 1; } }';
                $('<style id="sa-ic-anim-' + $settings.id + '">' + keyframeStyle + '</style>').appendTo('head');
                $(element).find('.icv__control').css('animation', 'sa-ic-fade-in ' + duration + 'ms ' + ease + ' forwards');
            }
        });
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-image-compare.default', widgetImageCompare);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

    var widgetLogoCarousel = function ($scope, $) {

        var $logoCarousel = $scope.find('.sa-logo-carousel'),
            $carouselContainer = $logoCarousel.find('.swiper'),
            $settings = $logoCarousel.data('settings');

        if (!$logoCarousel.length) {
            return;
        }

        var $tooltips = $logoCarousel.find('.sa-tippy-tooltip'),
            widgetID = $scope.data('id');

        $tooltips.each(function () {
            tippy(this, {
                allowHTML: true,
                theme: 'sa-tippy-' + widgetID
            });
        });

        // Disable loop when slide count <= highest slidesPerView to prevent Swiper blank-slide glitch
        if ($settings.loop) {
            var slideCount = $carouselContainer.find('.swiper-slide').length;
            var maxPerView = $settings.slidesPerView || 1;
            if ($settings.breakpoints) {
                Object.values($settings.breakpoints).forEach(function (bp) {
                    if (bp.slidesPerView && bp.slidesPerView > maxPerView) {
                        maxPerView = bp.slidesPerView;
                    }
                });
            }
            if (slideCount <= maxPerView) {
                $settings.loop = false;
            }
        }

        const Swiper = elementorFrontend.utils.swiper;
        initSwiper();
        async function initSwiper() {
            var swiper = await new Swiper($carouselContainer, $settings);
            if ($settings.pauseOnHover) {
                $carouselContainer.hover(function () {
                    this.swiper.autoplay.stop();
                }, function () {
                    this.swiper.autoplay.start();
                });
            }
        }

    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/sky-logo-carousel.default', widgetLogoCarousel);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

    var widgetLogoGrid = function ($scope, $) {

        var $logoGrid = $scope.find('.sa-logo-grid');

        if (!$logoGrid.length) {
            return;
        }

        var $tooltips = $logoGrid.find('.sa-tippy-tooltip'),
            widgetID = $scope.data('id');

        $tooltips.each(function () {
            tippy(this, {
                allowHTML: true,
                theme: 'sa-tippy-' + widgetID
            });
        });

    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/sky-logo-grid.default', widgetLogoGrid);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

// This widget uses the global carousel handler

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-luster-carousel.default', widgetGlobalCarousel);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

// This widget uses the global carousel handler

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-mate-carousel.default', widgetGlobalCarousel);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

var widgetMateSlider = function ($scope, $) {

    var $dataWrapper = $scope.find('.sa-mate-slider'),
        $primaryContainer = $dataWrapper.find('.sa-mate-primary.swiper'),
        $secondaryContainer = $dataWrapper.find('.sa-mate-secondary.swiper'),
        $primarySettings = $dataWrapper.data('primary-settings'),
        $secondarySettings = $dataWrapper.data('secondary-settings');

    if (!$dataWrapper.length) {
        return;
    }

    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {

        var secondary = await new Swiper($secondaryContainer, $secondarySettings);

        var primary = await new Swiper($primaryContainer, $primarySettings);

        primary.controller.control = secondary;
        secondary.controller.control = primary;

        if ($primarySettings.pauseOnHover) {
            $($primaryContainer).hover(function () {
                (this).swiper.autoplay.stop();
            }, function () {
                (this).swiper.autoplay.start();
            });
        }
    };
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-mate-slider.default', widgetMateSlider);
    });

}(jQuery, window.elementorFrontend));

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

;(function ($, elementor) {
    'use strict';

// This widget uses the global carousel handler

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-naive-carousel.default', widgetGlobalCarousel);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

var widgetNumber = function ($scope, $) {
    var $number = $scope.find('.sa-number'),
        $settings = $number.data('settings');

    if (!$number.length) {
        return;
    }

    if ($settings.animation == 'no') {
        return;
    }

    skyAddonsObserver($scope[0], function () {
        $($number).find('.sa-text').prop('Counter', 0).animate({
            Counter: $settings.number
        }, {
            duration: $settings.time,
            easing: 'swing',
            step: function (now) {
                $(this).text(Math.ceil(now));
            }
        });

    }, {
        root: null, // Use the viewport as the root
        rootMargin: '0px', // No margin around the root
        threshold: 0.8 // 80% visibility (1 - 0.8)
    });
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-number.default', widgetNumber);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

var widgetPanelSlider = function ($scope, $) {
    var $panelSlider = $scope.find('.sa-panel-slider'),
        $panelSliderContainer = $panelSlider.find('.swiper'),
        $settings = $panelSlider.data('settings');

    if (!$panelSlider.length) {
        return;
    }
    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {

        var sliderThumbs = await new Swiper($panelSliderContainer, $settings);

        if ($settings.pauseOnHover) {
            $($panelSlider).hover(function () {
                sliderThumbs.autoplay.stop();
            }, function () {
                sliderThumbs.autoplay.start();
            });
        }

        var $sliderSettings = $panelSlider.data('slider-settings');

        if ('hover' == $sliderSettings.showContent) {
            $panelSlider.find('.swiper-slide').on('mouseover', function () {
                $(this).siblings().removeClass('sa-active');
                $(this).addClass('sa-active');
            })
            $panelSlider.find('.swiper-slide').on('mouseleave', function () {
                $(this).siblings().removeClass('sa-active');
                $(this).removeClass('sa-active');
            })
        }

        if ('active_hover' == $sliderSettings.showContent) {
            $panelSlider.find('.swiper-slide').on('mouseover', function () {
                $(this).addClass('sa-active');
            })
            $panelSlider.find('.swiper-slide').on('mouseleave', function () {
                if ($(this).hasClass('swiper-slide-active') !== true) {
                    $(this).removeClass('sa-active');
                }
            })
        }

        if ('active' == $sliderSettings.showContent || 'active_hover' == $sliderSettings.showContent) {
            $panelSlider.find('.swiper-slide.swiper-slide-active').siblings().removeClass('sa-active');
            $panelSlider.find('.swiper-slide.swiper-slide-active').addClass('sa-active');

            sliderThumbs.on('slideChangeTransitionEnd', function (e) {
                $panelSlider.find('.swiper-slide.swiper-slide-active').siblings().removeClass('sa-active');
                $panelSlider.find('.swiper-slide.swiper-slide-active').addClass('sa-active');
            });
        }

        if ('always' == $sliderSettings.showContent) {
            $panelSlider.find('.swiper-slide').addClass('sa-active');
        }

    };

};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-panel-slider.default', widgetPanelSlider);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

var widgetPdfViewer = function ($scope, $) {
    var $pdfViewer = $scope.find('.sa-pdf-viewer'),
        $settings = $pdfViewer.data('settings'),
        $options = $pdfViewer.data('pdf-settings');

    if (!$pdfViewer.length) {
        return;
    }

    PDFObject.embed($settings.pdfUrl, $settings.id, $options);
};
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-pdf-viewer.default', widgetPdfViewer);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

var widgetPortionEffect = function ($scope, $) {
    var $portionEffect = $scope.find('.sa-portion-effect');
    if (!$portionEffect.length) {
        return;
    }

    var $settings = $portionEffect.data('settings');

    $portionEffect.find('.sa-side').css('background-image', 'url(' + $settings.image + ')');

    var animated = false;

    var animate = function () {
        if (animated) { return; }
        animated = true;
        $portionEffect.addClass('sa-animated');
    };

    // Snap all panels to hidden state instantly — used for clean repeat reset
    var snapToHidden = function () {
        $portionEffect.addClass('sa-pe-no-trans');
        $portionEffect.removeClass('sa-animated');
        animated = false;
        requestAnimationFrame(function () {
            $portionEffect.removeClass('sa-pe-no-trans');
        });
    };

    if ($settings.entrance_animation === 'yes' && !elementorFrontend.isEditMode()) {
        var stagger    = ($settings.stagger !== undefined)    ? parseInt($settings.stagger, 10)    : 150;
        var repeat     = $settings.animation_repeat === 'yes';
        var threshold  = ($settings.threshold !== undefined)  ? parseInt($settings.threshold, 10) / 100 : 0.4;

        // Set per-block stagger delay as CSS var — inherited by .sa-side via transition-delay
        $portionEffect.find('.sa-block').each(function (i) {
            this.style.setProperty('--sky-pe-anim-delay', (i * stagger) + 'ms');
        });

        // Snap to hidden instantly (sa-pe-no-trans prevents transition flash)
        $portionEffect.addClass('sa-pe-ready sa-pe-no-trans');
        requestAnimationFrame(function () {
            $portionEffect.removeClass('sa-pe-no-trans');

            // 400ms delay: above-fold widgets animate visibly on page load
            setTimeout(function () {
                if ('IntersectionObserver' in window) {
                    // Two thresholds: 0 = fully gone (reset), threshold = enough visible (animate)
                    var thresholds = threshold > 0 ? [ 0, threshold ] : [ 0 ];

                    var observer = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.intersectionRatio >= threshold) {
                                // Enough of element visible → animate in
                                requestAnimationFrame(function () {
                                    requestAnimationFrame(animate);
                                });
                                if (!repeat) {
                                    observer.unobserve(entry.target);
                                }
                            } else if (repeat && !entry.isIntersecting) {
                                // Fully out of viewport → safe to snap to hidden, no white flash
                                snapToHidden();
                            }
                        });
                    }, { threshold: thresholds });

                    observer.observe($portionEffect[0]);
                } else {
                    animate();
                }
            }, 400);
        });

    } else {
        animate();
    }
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-portion-effect.default', widgetPortionEffect);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

    var SaReadingProgress = {

        initDefault: function ($scope) {
            var $el    = $scope.find('.sa-reading-progress.sa-skin-default');
            var $inner = $el.find('.sa-rp-inner');
            if (!$el.length) return;

            $(window).off('scroll.sa-rp-default');

            function getMax() {
                return Math.max(1, $(document).height() - $(window).height());
            }

            function update() {
                var pct = Math.min(100, ($(window).scrollTop() / getMax()) * 100);
                $inner.css('width', pct + '%');
            }

            update();
            $(window).on('scroll.sa-rp-default', function () {
                window.requestAnimationFrame(update);
            });
        },

        initFancyHorizontal: function ($scope) {
            var $el   = $scope.find('.sa-reading-progress.sa-skin-fancy-horizontal');
            var $span = $el.find('span');
            if (!$el.length) return;

            $(window).off('scroll.sa-rp-fh');

            function getMax() {
                return Math.max(1, $(document).height() - $(window).height());
            }

            function update() {
                var pct = Math.min(100, ($(window).scrollTop() / getMax()) * 100);
                $el.css('width', pct + '%');
                if ($span.length) $span.text(Math.round(pct));
            }

            update();
            $(window).on('scroll.sa-rp-fh', function () {
                window.requestAnimationFrame(update);
            });
        },

        initFancyVertical: function ($scope) {
            var $el   = $scope.find('.sa-reading-progress.sa-skin-fancy-vertical');
            var $span = $el.find('span');
            if (!$el.length) return;

            $(window).off('scroll.sa-rp-fv');

            function getMax() {
                return Math.max(1, $(document).height() - $(window).height());
            }

            function update() {
                var pct = Math.min(100, ($(window).scrollTop() / getMax()) * 100);
                $el.css('height', pct + '%');
                if ($span.length) $span.text(Math.round(pct));
            }

            update();
            $(window).on('scroll.sa-rp-fv', function () {
                window.requestAnimationFrame(update);
            });
        },

        initScrollTop: function ($scope) {
            var $el = $scope.find('.sa-reading-progress.sa-skin-scroll-top');
            if (!$el.length) return;

            var path = $el.find('path')[0];
            if (!path) return;

            var pathLen   = path.getTotalLength();
            var threshold = parseInt($el.data('scroll-threshold'), 10) || 50;
            var duration  = parseInt($el.data('scroll-duration'),  10) || 550;

            path.style.transition       = 'none';
            path.style.strokeDasharray  = pathLen + ' ' + pathLen;
            path.style.strokeDashoffset = pathLen;
            path.getBoundingClientRect();
            path.style.transition = 'stroke-dashoffset 10ms linear';

            $(window).off('scroll.sa-rp-st');

            function getMax() {
                return Math.max(1, $(document).height() - $(window).height());
            }

            function update() {
                var st      = $(window).scrollTop();
                var offset  = pathLen - (st * pathLen / getMax());
                path.style.strokeDashoffset = Math.max(0, offset);
                $el.toggleClass('sa-active-progress', st > threshold);
            }

            update();
            $(window).on('scroll.sa-rp-st', function () {
                window.requestAnimationFrame(update);
            });

            $el.off('click.sa-rp-st').on('click.sa-rp-st', function (e) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: 0 }, duration);
            });
        },

        initWithCursor: function ($scope) {
            var $dot = $scope.find('.sa-reading-progress.sa-skin-with-cursor');
            if (!$dot.length) return;

            // Clean up previous instance
            $('body').find('.sa-rp-cursor-outer, .sa-rp-cursor-inner').remove();
            $(window).off('scroll.sa-rp-wc');
            $(document).off('mousemove.sa-rp-wc');

            // Read CSS vars from dot (inherits from {{WRAPPER}})
            var dotEl  = $dot[0];
            var cs     = window.getComputedStyle(dotEl);
            var lag    = parseFloat(cs.getPropertyValue('--sky-rp-cursor-lag').trim())    || 150;
            var blend  = cs.getPropertyValue('--sky-rp-cursor-blend-mode').trim()         || 'normal';
            var dotSize = cs.getPropertyValue('--sky-rp-cursor-dot-size').trim()          || '8px';
            var primary  = cs.getPropertyValue('--sky-r-p-primary-color').trim()          || 'blueviolet';
            var secondary = cs.getPropertyValue('--sky-r-p-secondary-color').trim()       || 'rgba(0,0,0,0.15)';
            var ringColor = cs.getPropertyValue('--sky-rp-cursor-ring-color').trim()      || secondary;

            // Build DOM
            var $outer = $('<div class="sa-progress-with-cursor-2 sa-rp-cursor-outer"></div>').appendTo('body');
            var $inner = $('<div class="sa-progress-with-cursor-3 sa-rp-cursor-inner"></div>').appendTo('body');
            var $wrap  = $([
                '<div class="sa-progress-wrap">',
                    '<svg class="sa-progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">',
                        '<path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"/>',
                    '</svg>',
                '</div>'
            ].join('')).appendTo($outer);

            var outEl = $outer[0];
            var innEl = $inner[0];

            var ringThickness = cs.getPropertyValue('--sky-rp-cursor-ring-thickness').trim() || '2px';
            var outerSize     = cs.getPropertyValue('--sky-reading-progress-size').trim()    || '0px';

            // Propagate CSS vars to body elements (they can't inherit from {{WRAPPER}})
            function setVar(el, name, val) { el.style.setProperty(name, val); }
            [outEl, innEl].forEach(function (el) {
                setVar(el, '--sky-rp-cursor-dot-size',         dotSize);
                setVar(el, '--sky-rp-cursor-blend-mode',       blend);
                setVar(el, '--sky-r-p-primary-color',          primary);
                setVar(el, '--sky-r-p-secondary-color',        ringColor);
                setVar(el, '--sky-rp-cursor-ring-color',       ringColor);
                setVar(el, '--sky-rp-cursor-ring-thickness',   ringThickness);
                setVar(el, '--sky-reading-progress-size',      outerSize);
            });

            // Staggered lag: outer = full lag, inner = half lag (creates depth effect)
            var lagHalf = Math.round(lag * 0.5);
            outEl.style.transition = [
                'left ' + lag     + 'ms ease-out',
                'top '  + lag     + 'ms ease-out',
                'transform 400ms ease',
                'opacity 400ms ease'
            ].join(', ');
            innEl.style.transition = [
                'left ' + lagHalf + 'ms ease-out',
                'top '  + lagHalf + 'ms ease-out',
                'transform 300ms ease',
                'opacity 300ms ease'
            ].join(', ');

            // Blend mode on dot
            dotEl.style.mixBlendMode = blend;

            // SVG progress ring setup
            var path = $wrap.find('path')[0];
            if (path) {
                var pathLen = path.getTotalLength();
                path.style.transition       = 'none';
                path.style.strokeDasharray  = pathLen + ' ' + pathLen;
                path.style.strokeDashoffset = pathLen;
                path.style.stroke           = primary;
                path.getBoundingClientRect();
                path.style.transition = 'stroke-dashoffset 10ms linear';

                $(window).on('scroll.sa-rp-wc', function () {
                    window.requestAnimationFrame(function () {
                        var st  = $(window).scrollTop();
                        var max = Math.max(1, $(document).height() - $(window).height());
                        path.style.strokeDashoffset = Math.max(0, pathLen - (st * pathLen / max));
                    });
                });
            }

            // Fade in all layers on first mousemove
            var activated = false;
            var rafId     = null;

            $(document).on('mousemove.sa-rp-wc', function (e) {
                var x = e.clientX, y = e.clientY;

                if (!activated) {
                    activated = true;
                    $dot.add($outer).add($inner).addClass('sa-rp-cursor-shown');
                }

                if (rafId) cancelAnimationFrame(rafId);
                rafId = requestAnimationFrame(function () {
                    dotEl.style.left = x + 'px';
                    dotEl.style.top  = y + 'px';
                    outEl.style.left = x + 'px';
                    outEl.style.top  = y + 'px';
                    innEl.style.left = x + 'px';
                    innEl.style.top  = y + 'px';
                });
            });

            // Hover effects on .hover-target elements
            $(document).find('.hover-target')
                .off('mouseenter.sa-rp-wc mouseleave.sa-rp-wc')
                .on('mouseenter.sa-rp-wc', function () {
                    $dot.addClass('sa-rp-cursor-hover');
                    $outer.add($inner).addClass('hover');
                })
                .on('mouseleave.sa-rp-wc', function () {
                    $dot.removeClass('sa-rp-cursor-hover');
                    $outer.add($inner).removeClass('hover');
                });
        },

        register: function () {
            var self = this;
            jQuery(window).on('elementor/frontend/init', function () {
                var hooks = elementorFrontend.hooks;
                hooks.addAction('frontend/element_ready/sky-reading-progress.default',                  function ($s) { self.initDefault($s); });
                hooks.addAction('frontend/element_ready/sky-reading-progress.sky-skin-fancy-horizontal', function ($s) { self.initFancyHorizontal($s); });
                hooks.addAction('frontend/element_ready/sky-reading-progress.sky-skin-fancy-vertical',   function ($s) { self.initFancyVertical($s); });
                hooks.addAction('frontend/element_ready/sky-reading-progress.sky-skin-scroll-top',       function ($s) { self.initScrollTop($s); });
                hooks.addAction('frontend/element_ready/sky-reading-progress.sky-skin-with-cursor',      function ($s) { self.initWithCursor($s); });
            });
        }
    };

    SaReadingProgress.register();

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

// This widget uses the global carousel handler

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-review-carousel.default', widgetGlobalCarousel);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

// This widget uses the global carousel handler

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-sapling-carousel.default', widgetGlobalCarousel);
    });

}(jQuery, window.elementorFrontend));

; (function ($, elementor) {
    'use strict';

    var widgetSlinkyMenu = function ($scope, $) {
        var $slinkyMenu = $scope.find('.sa-slinky-menu');
        var $settings = $slinkyMenu.data('settings');

        if (!$slinkyMenu.length) {
            return;
        }

        $slinkyMenu.removeClass('sa-d-none');

        var options = {
            resize: $settings.resize !== false,
            speed: $settings.speed || 300,
            title: $settings.title === true
        };

        var $menu = $($settings.id);

        $menu.slinky(options);

        // Slinky measures the active pane once at init and writes the result as an inline
        // height on the menu, which is `overflow: hidden`. Inside a container that is
        // hidden at that moment — an offcanvas, a closed tab or accordion, a modal — the
        // measurement is 0 and never repeats, so the menu stays invisible even after the
        // container opens. Its `resize` option only re-measures on level changes, not on
        // visibility, and the library exposes no public method to force it.
        if ('undefined' === typeof IntersectionObserver) {
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                var $pane = $menu.find('ul.active').first();

                if (!$pane.length) {
                    $pane = $menu.find('ul').first();
                }

                var height = $pane.outerHeight();

                // Left live rather than disconnected: an offcanvas can be closed and
                // reopened, and the pane in view may differ each time.
                if (height && height !== $menu.height()) {
                    $menu.height(height);
                }
            });
        });

        observer.observe($menu[0]);

    };
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-slinky-menu.default', widgetSlinkyMenu);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

var widgetStellarSlider = function ($scope, $) {

    var $stellarSlider = $scope.find('.sa-stellar-slider'),
        $container     = $stellarSlider.find('.swiper'),
        $settings      = $stellarSlider.data('settings');

    if (!$stellarSlider.length) {
        return;
    }

    // A copy, so the jQuery .data() cache stays clean when the editor
    // re-initialises the widget.
    var options = $.extend(true, {}, $settings);

    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {

        var slider = await new Swiper($container, options);

        if (options.pauseOnHover) {
            $stellarSlider.hover(function () {
                slider.autoplay.stop();
            }, function () {
                slider.autoplay.start();
            });
        }
    };
};

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-stellar-slider.default', widgetStellarSlider);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

    var SCROLL_DEBOUNCE_MS = 180;

    /**
     * Single source of truth for .is-collapsible max-height.
     *
     * Reads desired state from two inputs:
     *   1. is-collapsed class (scroll-driven) — DEBOUNCED so fast scrolling
     *      collapses cleanly to final state without animation pile-up.
     *   2. parent .sa-toc-item :hover (when hoverExpand=true) — INSTANT for
     *      snappy interaction feel.
     *
     * Always animates between measured scrollHeight ↔ 0 so transitions are
     * pixel-perfect at every content size (no CSS max-height snap).
     */
    function setupCollapseManager(navEl, hoverExpand) {
        var collapsibles = navEl.querySelectorAll('.is-collapsible');

        collapsibles.forEach(function (el) {
            var item = el.parentElement; // .sa-toc-item

            // Initial sync — no transition needed (same value → same value)
            if (el.classList.contains('is-collapsed')) {
                el.style.maxHeight = '0px';
                el.style.opacity = '0';
            } else {
                el.style.maxHeight = el.scrollHeight + 'px';
                el.style.opacity = '1';
            }

            function apply() {
                var hovering     = hoverExpand && item && item.matches(':hover');
                var shouldBeOpen = !el.classList.contains('is-collapsed') || hovering;

                if (shouldBeOpen) {
                    el.style.maxHeight = el.scrollHeight + 'px';
                    el.style.opacity   = '1';
                } else {
                    // Lock current height as transition start frame, reflow,
                    // then animate to 0 next frame.
                    el.style.maxHeight = el.scrollHeight + 'px';
                    void el.offsetHeight;
                    requestAnimationFrame(function () {
                        el.style.maxHeight = '0px';
                        el.style.opacity   = '0';
                    });
                }
            }

            // Debounce scroll-driven class flips so rapid scrolling settles
            // on final state instead of firing N overlapping animations.
            var scrollTimer;
            function debouncedApply() {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(apply, SCROLL_DEBOUNCE_MS);
            }

            new MutationObserver(debouncedApply).observe(el, {
                attributes: true,
                attributeFilter: ['class']
            });

            // Hover bypasses debounce — instant feedback
            if (hoverExpand && item) {
                item.addEventListener('mouseenter', function () {
                    clearTimeout(scrollTimer); // cancel any pending scroll apply
                    apply();
                });
                item.addEventListener('mouseleave', function () {
                    clearTimeout(scrollTimer);
                    apply();
                });
            }
        });
    }

    /**
     * Render one widget's list with tocbot, then hand the singleton straight back.
     *
     * tocbot keeps its options and its scroll handler in MODULE scope — there is no
     * instance API (verified against the bundled build: module-level `options`, and
     * `destroy()` clears innerHTML). So with N widgets on a page the old code's
     * init-per-widget loop left only the LAST one wired to the scroll handler; every
     * earlier list rendered fine and then never highlighted again.
     *
     * tocbot is still the right tool for BUILDING the nested list — it handles
     * hasInnerContainers, ignoreSelector, collapseDepth and the heading walk. It is just
     * never left holding the scroll sync. Init → snapshot the HTML → destroy → restore.
     * `destroy()` wipes the element it rendered into, hence the restore.
     */
    function buildList(settings) {
        var navEl = document.querySelector(settings.tocSelector);

        if (!navEl) {
            return null;
        }

        // Always disabled: scroll tracking and smooth scroll are ours now, per instance.
        //
        // `scrollSmooth: false` matters even though we destroy right after. tocbot's
        // smooth-scroll module binds an ANONYMOUS listener on document.body and keeps no
        // reference to it, so `destroy()` — which only unbinds its named handlers — cannot
        // remove it. Left on, every init leaked one more body listener that fired on every
        // .sa-toc-link and ran a second scroll animation against our own, landing headings
        // ~430px off instead of at the configured offset and highlighting the wrong link.
        var buildSettings = $.extend({}, settings, {
            disableTocScrollSync: true,
            enableUrlHashUpdateOnScroll: false,
            scrollSmooth: false
        });

        // No destroy() before init(). `destroy()` resolves its target from tocbot's
        // module-scope options, which still hold the PREVIOUS widget's tocSelector — so a
        // pre-init destroy blanked the list that widget had already rendered, leaving every
        // instance but the last an empty box. init() already clears its own target
        // internally after merging the new options, so nothing is lost by dropping it.
        // init() ASSIGNS window.onhashchange and window.onscrollend (assignment, not
        // addEventListener), and destroy() never clears them — so tocbot's own updateToc
        // kept firing on every scroll-end against the last list it rendered, fighting this
        // widget's scroll spy for the active class. Restore whatever was there instead of
        // nulling, so a third party that owns those hooks gets them back.
        var prevHashChange = window.onhashchange;
        var prevScrollEnd  = window.onscrollend;

        tocbot.init(buildSettings);

        var html = navEl.innerHTML;

        tocbot.destroy();
        navEl.innerHTML = html;

        window.onhashchange = prevHashChange;
        window.onscrollend  = prevScrollEnd;

        return navEl;
    }

    /**
     * Per-instance scroll spy. Everything it touches is reached through navEl, so any
     * number of widgets can run side by side.
     */
    function setupScrollSpy(navEl, settings) {
        var activeLinkClass = settings.activeLinkClass || 'is-active-link';
        var activeListClass = settings.activeListItemClass || 'is-active-li';
        var collapsedClass  = settings.isCollapsedClass || 'is-collapsed';
        var offset          = typeof settings.headingsOffset === 'number' ? settings.headingsOffset : 100;
        var BOTTOM_PX       = 30;

        var links   = [];
        var targets = [];

        // Pair each link with its heading, dropping any link whose target is missing —
        // the two arrays are indexed together, so they must be filtered together.
        navEl.querySelectorAll('a[href*="#"]').forEach(function (a) {
            var hash = a.getAttribute('href').split('#')[1];
            if (!hash) { return; }

            var target = document.getElementById(decodeURIComponent(hash));
            if (!target) { return; }

            links.push(a);
            targets.push(target);
        });

        if (!links.length) {
            return;
        }

        // Smooth scroll + hash, re-implemented because tocbot owned the click handler and it
        // went with the instance. Landing the heading exactly on the same offset the spy
        // measures against means the entry you clicked is the entry that highlights.
        navEl.addEventListener('click', function (e) {
            var link = e.target.closest('a[href*="#"]');
            if (!link || !navEl.contains(link)) { return; }

            var index = links.indexOf(link);
            if (index === -1) { return; }

            e.preventDefault();

            var extra = typeof settings.scrollSmoothOffset === 'number' ? settings.scrollSmoothOffset : 0;
            var top   = targets[index].getBoundingClientRect().top + window.scrollY - offset + extra;

            window.scrollTo({
                top: Math.max(0, top),
                behavior: false === settings.scrollSmooth ? 'auto' : 'smooth'
            });

            // pushState, not `location.hash` — assigning the hash makes the browser jump to
            // the anchor itself, which cancels the smooth scroll we just started.
            if (window.history && window.history.pushState) {
                window.history.pushState(null, '', link.getAttribute('href'));
            }

            lastIndex = index;
            setActive(index);
        });

        function setActive(index) {
            navEl.querySelectorAll('.' + activeLinkClass).forEach(function (el) {
                el.classList.remove(activeLinkClass);
            });
            navEl.querySelectorAll('.' + activeListClass).forEach(function (el) {
                el.classList.remove(activeListClass);
            });

            var activeLink = index >= 0 ? links[index] : null;

            if (activeLink) {
                activeLink.classList.add(activeLinkClass);

                var li = activeLink.closest('li');
                while (li) {
                    li.classList.add(activeListClass);
                    li = li.parentElement ? li.parentElement.closest('li') : null;
                }
            }

            // Collapse every branch that does not contain the active link. This is the
            // class setupCollapseManager() watches, so the animation still runs through
            // the same single code path it always did.
            navEl.querySelectorAll('.is-collapsible').forEach(function (ul) {
                if (activeLink && ul.contains(activeLink)) {
                    ul.classList.remove(collapsedClass);
                } else {
                    ul.classList.add(collapsedClass);
                }
            });
        }

        function currentIndex() {
            // At the very bottom the last heading can never reach the offset line, because
            // the page has run out of scroll. Without this the final entry is unreachable.
            var atBottom = (window.innerHeight + Math.round(window.scrollY)) >=
                           (document.documentElement.scrollHeight - BOTTOM_PX);

            if (atBottom) {
                return links.length - 1;
            }

            var index = -1;

            for (var i = 0; i < targets.length; i++) {
                if (targets[i].getBoundingClientRect().top - offset <= 5) {
                    index = i;
                } else {
                    break; // headings are in document order — nothing later can match
                }
            }

            return index;
        }

        var lastIndex = null;
        var ticking   = false;

        function update() {
            ticking = false;

            var index = currentIndex();
            if (index === lastIndex) {
                return; // nothing changed — skip the DOM writes entirely
            }

            lastIndex = index;
            setActive(index);

            // replaceState, never pushState: one history entry per heading scrolled past
            // would make the back button useless on a long article.
            if (settings.enableUrlHashUpdateOnScroll && index >= 0 && window.history && window.history.replaceState) {
                window.history.replaceState(null, '', links[index].getAttribute('href'));
            }
        }

        function onScroll() {
            if (ticking) { return; }
            ticking = true;
            requestAnimationFrame(update);
        }

        // Stored on the element so an editor re-render can unbind the previous pass
        // instead of stacking a second listener on the same nav.
        if (navEl.saTocScrollHandler) {
            window.removeEventListener('scroll', navEl.saTocScrollHandler);
            window.removeEventListener('resize', navEl.saTocScrollHandler);
        }
        navEl.saTocScrollHandler = onScroll;

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });

        update();
    }

    var widgetTableOfContents = function ($scope, $) {

        var $el      = $scope.find('.sa-toc'),
            settings = $el.data('settings');

        if (!$el.length || !settings) {
            return;
        }

        var contentEl = document.querySelector(settings.contentSelector);

        if (!contentEl) {
            return;
        }

        // Inject IDs into headings that lack them — tocbot only reads existing IDs
        contentEl.querySelectorAll(settings.headingSelector || 'h2, h3, h4')
            .forEach(function (el, i) {
                if (!el.id) {
                    var slug = el.textContent.trim()
                        .toLowerCase()
                        .replace(/\s+/g, '-')
                        .replace(/[^a-z0-9-]/g, '')
                        .replace(/-+/g, '-')
                        .replace(/^-|-$/g, '');
                    el.id = slug || ('sa-toc-' + i);
                }
            });

        var navEl = buildList(settings);

        if (!navEl) {
            return;
        }

        var hoverExpand = $scope.hasClass('sa-toc-hover-yes');

        // Wait one frame so the restored markup is laid out before it is measured.
        requestAnimationFrame(function () {
            setupCollapseManager(navEl, hoverExpand);
        });

        // The editor re-renders on every settings change and headings move around as the
        // page is edited; tracking there fights the editing rather than helping it.
        if (!elementorFrontend.isEditMode()) {
            setupScrollSpy(navEl, settings);
        }
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/sky-table-of-contents.default',
            widgetTableOfContents
        );
    });

}(jQuery, window.elementorFrontend));

/**
 * Start table widget script
 */

(function ($, elementor) {

    'use strict';

    // Table — sorting, search, pagination and export without a vendor library.
    // All markup (toolbar, buttons, cells, mobile labels) comes from PHP; this
    // only reorders, hides and reads what is already in the DOM.
    var widgetTable = function ($scope, $) {

        var $root = $scope.find('.sa-table__wrap');
        if (!$root.length) {
            return;
        }

        var root = $root[0],
            settings = $root.data('settings') || {};

        settings.i18n = settings.i18n || {};

        var table = {
            rows: [],
            filtered: [],
            visible: [],
            orderDirty: false,
            page: 1,
            perPage: settings.perPage || 10,
            sortIndex: -1,
            sortDir: 'asc',
            query: '',

            // PHP-rendered nodes. Anything missing simply disables that feature.
            cacheDom: function () {
                this.table = root.querySelector('.sa-table');
                this.body = root.querySelector('.sa-table__body');
                this.search = root.querySelector('.sa-table__search-input');
                this.length = root.querySelector('.sa-table__length-select');
                this.info = root.querySelector('.sa-table__info');
                this.pagination = root.querySelector('.sa-table__pagination');
                this.headCells = Array.prototype.slice.call(root.querySelectorAll('.sa-table__head .sa-table__head-column-cell'));
                this.exportButtons = Array.prototype.slice.call(root.querySelectorAll('.sa-table__export-btn'));

                return !!this.body;
            },

            // PHP decides this: a rowspan makes a row non-detachable, so
            // reordering or hiding it would shear the grid. Reading its verdict
            // rather than re-deriving one keeps the two in step.
            isManaged: function () {
                return this.body.classList.contains('sa-table__body--managed');
            },

            // Cache each row's searchable text and per-column sort keys once, so
            // filtering and sorting never touch the DOM to read a value.
            collectRows: function () {
                this.rows = Array.prototype.map.call(this.body.rows, function (tr, index) {
                    var values = {};

                    Array.prototype.forEach.call(tr.cells, function (cell) {
                        var column = cell.getAttribute('data-column');
                        if (column !== null) {
                            values[column] = cell.getAttribute('data-sort') || '';
                        }
                    });

                    return {
                        tr: tr,
                        values: values,
                        // Original position, so the stable-sort tiebreak is a
                        // subtraction rather than an indexOf scan per comparison.
                        index: index,
                        text: (tr.textContent || '').toLowerCase()
                    };
                });

                this.filtered = this.rows.slice();

                // Rows PHP already un-hid for the first page.
                this.visible = this.rows.filter(function (row) {
                    return !row.tr.classList.contains('sa-table__body-row--hidden');
                });
            },

            bindExport: function () {
                var self = this;

                this.exportButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        self.runExport(button.getAttribute('data-export'), button);
                    });
                });
            },

            bind: function () {
                var self = this;

                if (settings.sorting) {
                    this.headCells.forEach(function (cell) {
                        var button = cell.querySelector('.sa-table__sort');
                        if (!button) {
                            return;
                        }

                        button.addEventListener('click', function () {
                            self.toggleSort(parseInt(cell.getAttribute('data-column'), 10));
                        });
                    });
                }

                if (this.search) {
                    this.search.addEventListener('input', function () {
                        clearTimeout(self.searchTimer);
                        self.searchTimer = setTimeout(function () {
                            self.query = self.search.value.trim().toLowerCase();
                            self.page = 1;
                            self.applyFilter();
                            self.draw();
                        }, 180);
                    });
                }

                if (this.length) {
                    this.length.addEventListener('change', function () {
                        self.perPage = parseInt(self.length.value, 10) || self.perPage;
                        self.page = 1;
                        self.draw();
                    });
                }

                this.bindExport();
            },

            applyFilter: function () {
                var query = this.query;

                this.filtered = !query ? this.rows.slice() : this.rows.filter(function (row) {
                    return row.text.indexOf(query) !== -1;
                });
            },

            toggleSort: function (index) {
                if (isNaN(index)) {
                    return;
                }

                this.sortDir = this.sortIndex === index && this.sortDir === 'asc' ? 'desc' : 'asc';
                this.sortIndex = index;
                this.page = 1;
                this.applySort();
                this.markSortedColumn();
                this.draw();
            },

            // Stable sort: ties keep their original document order, so repeated
            // sorts on equal values never shuffle rows.
            applySort: function () {
                var index = this.sortIndex,
                    direction = this.sortDir === 'desc' ? -1 : 1;

                if (index < 0) {
                    return;
                }

                this.filtered.sort(function (a, b) {
                    var result = table.compare(a.values[index] || '', b.values[index] || '');
                    return result !== 0 ? result * direction : a.index - b.index;
                });

                this.orderDirty = true;
            },

            compare: function (a, b) {
                var numberA = parseFloat(a),
                    numberB = parseFloat(b),
                    bothNumeric = !isNaN(numberA) && !isNaN(numberB) && a !== '' && b !== '';

                if (bothNumeric) {
                    return numberA - numberB;
                }

                return a.localeCompare(b);
            },

            markSortedColumn: function () {
                var self = this;

                this.headCells.forEach(function (cell) {
                    var isActive = parseInt(cell.getAttribute('data-column'), 10) === self.sortIndex;

                    cell.classList.toggle('sa-table__head-column-cell--sorted', isActive);
                    cell.classList.toggle('sa-table__head-column-cell--desc', isActive && self.sortDir === 'desc');

                    if (cell.hasAttribute('aria-sort')) {
                        cell.setAttribute('aria-sort', isActive ? (self.sortDir === 'asc' ? 'ascending' : 'descending') : 'none');
                    }
                });
            },

            // Cost is proportional to what actually changed, not to table size.
            // Paging a 5,000-row table touches ~2 pages of rows, not 5,000.
            draw: function () {
                var start = settings.pagination ? (this.page - 1) * this.perPage : 0,
                    end = settings.pagination ? start + this.perPage : this.filtered.length,
                    next = this.filtered.slice(start, end);

                this.syncOrder();
                this.syncVisibility(next);

                this.toggleEmptyRow();
                this.renderPagination();
                this.updateInfo(start, Math.min(end, this.filtered.length));
            },

            // Only a sort changes document order. Filtering and paging do not, so
            // they never pay for moving nodes.
            syncOrder: function () {
                if (!this.orderDirty) {
                    return;
                }

                var fragment = document.createDocumentFragment();

                this.filtered.forEach(function (row) {
                    fragment.appendChild(row.tr);
                });

                this.body.appendChild(fragment);
                this.orderDirty = false;
            },

            // Diff the previous page against the next one and touch only the rows
            // that cross the boundary.
            syncVisibility: function (next) {
                next.forEach(function (row) {
                    row.staged = true;
                });

                this.visible.forEach(function (row) {
                    if (!row.staged) {
                        row.tr.classList.add('sa-table__body-row--hidden');
                    }
                });

                // Zebra parity has to count visible rows only — CSS :nth-child
                // would still count the rows pagination just hid.
                next.forEach(function (row, index) {
                    row.staged = false;
                    row.tr.classList.remove('sa-table__body-row--hidden');
                    row.tr.classList.toggle('sa-table__body-row--odd', index % 2 === 0);
                    row.tr.classList.toggle('sa-table__body-row--even', index % 2 === 1);
                });

                this.visible = next;
            },

            // The only node built in JS: a message row whose colspan depends on
            // the rendered column count.
            toggleEmptyRow: function () {
                if (this.filtered.length) {
                    if (this.emptyRow) {
                        this.emptyRow.remove();
                        this.emptyRow = null;
                    }
                    return;
                }

                if (this.emptyRow) {
                    return;
                }

                var columns = this.headCells.length || 1;

                this.emptyRow = document.createElement('tr');
                this.emptyRow.className = 'sa-table__empty';
                this.emptyRow.innerHTML = '<td colspan="' + columns + '"></td>';
                this.emptyRow.firstChild.textContent = settings.i18n.empty;
                this.body.appendChild(this.emptyRow);
            },

            renderPagination: function () {
                if (!this.pagination || !settings.pagination) {
                    return;
                }

                var pages = Math.ceil(this.filtered.length / this.perPage) || 1,
                    self = this;

                this.pagination.textContent = '';

                if (pages < 2) {
                    return;
                }

                this.pagination.appendChild(this.pageButton(settings.i18n.prev, this.page - 1, this.page === 1));

                this.pageNumbers(pages).forEach(function (page) {
                    if (page === '…') {
                        var gap = document.createElement('span');
                        gap.className = 'sa-table__page-gap';
                        gap.textContent = '…';
                        self.pagination.appendChild(gap);
                        return;
                    }

                    self.pagination.appendChild(self.pageButton(String(page), page, false, page === self.page));
                });

                this.pagination.appendChild(this.pageButton(settings.i18n.next, this.page + 1, this.page === pages));
            },

            pageButton: function (label, page, disabled, current) {
                var self = this,
                    button = document.createElement('button');

                button.type = 'button';
                button.className = 'sa-table__page' + (current ? ' sa-table__page--active' : '');
                button.textContent = label;

                if (disabled) {
                    button.disabled = true;
                }

                if (current) {
                    button.setAttribute('aria-current', 'page');
                }

                button.addEventListener('click', function () {
                    self.page = page;
                    self.draw();
                });

                return button;
            },

            // First, last, and a window around the current page — keeps the control
            // a fixed width however many pages there are.
            pageNumbers: function (pages) {
                var list = [],
                    from = Math.max(1, this.page - 1),
                    to = Math.min(pages, this.page + 1),
                    page;

                for (page = 1; page <= pages; page++) {
                    if (page === 1 || page === pages || (page >= from && page <= to)) {
                        list.push(page);
                    } else if (list[list.length - 1] !== '…') {
                        list.push('…');
                    }
                }

                return list;
            },

            updateInfo: function (start, end) {
                if (!this.info || !settings.info) {
                    return;
                }

                var total = this.filtered.length;

                this.info.textContent = settings.i18n.info
                    .replace('{start}', total ? start + 1 : 0)
                    .replace('{end}', end)
                    .replace('{total}', total);
            },

            runExport: function (action, button) {
                if (action === 'csv') {
                    this.download(this.toDelimited(','), settings.fileName + '.csv', 'text/csv');
                    return;
                }

                if (action === 'copy') {
                    this.copy(this.toDelimited('\t'), button);
                    return;
                }

                if (action === 'print') {
                    this.print();
                }
            },

            // Exports what the visitor is looking at: current filter, every page.
            toDelimited: function (separator) {
                var lines = [],
                    columns = settings.columns || [];

                if (columns.length) {
                    lines.push(columns.map(table.quote).join(separator));
                }

                this.filtered.forEach(function (row) {
                    var cells = Array.prototype.map.call(row.tr.cells, function (cell) {
                        return table.quote((cell.textContent || '').trim());
                    });

                    lines.push(cells.join(separator));
                });

                return lines.join('\n');
            },

            quote: function (value) {
                return /[",\t\n]/.test(value) ? '"' + value.replace(/"/g, '""') + '"' : value;
            },

            download: function (content, fileName, type) {
                var blob = new Blob(['﻿' + content], { type: type + ';charset=utf-8;' }),
                    url = URL.createObjectURL(blob),
                    link = document.createElement('a');

                link.href = url;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
            },

            copy: function (content, button) {
                var original = button.textContent,
                    done = function () {
                        button.textContent = settings.i18n.copied;
                        setTimeout(function () {
                            button.textContent = original;
                        }, 1500);
                    };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(content).then(done);
                    return;
                }

                var field = document.createElement('textarea');
                field.value = content;
                field.setAttribute('readonly', 'readonly');
                field.className = 'sa-table__clipboard';
                document.body.appendChild(field);
                field.select();
                document.execCommand('copy');
                field.remove();
                done();
            },

            // Print the filtered table in isolation, not the whole page.
            print: function () {
                var frame = document.createElement('iframe');

                frame.className = 'sa-table__print-frame';
                document.body.appendChild(frame);

                var doc = frame.contentWindow.document,
                    clone = this.table.cloneNode(true);

                Array.prototype.slice.call(clone.querySelectorAll('.sa-table__body-row--hidden, .sa-table__sort-icon')).forEach(function (node) {
                    node.remove();
                });

                doc.open();
                doc.write('<!doctype html><html><head><title>' + settings.fileName + '</title>');
                doc.write('<style>body{font-family:sans-serif}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px;text-align:left}</style>');
                doc.write('</head><body></body></html>');
                doc.close();
                doc.body.appendChild(doc.importNode(clone, true));

                frame.contentWindow.focus();
                frame.contentWindow.print();

                setTimeout(function () {
                    frame.remove();
                }, 1000);
            },

            init: function () {
                if (!this.cacheDom()) {
                    return;
                }

                this.collectRows();

                // Export works on any table; the rest needs detachable rows.
                if (!this.isManaged()) {
                    this.bindExport();
                    return;
                }

                this.bind();

                // PHP already emitted the rows in this order, so there is nothing
                // to re-sort — only the header indicator to set.
                if (settings.sorting && settings.sortColumn >= 0) {
                    this.sortIndex = settings.sortColumn;
                    this.sortDir = settings.sortOrder === 'desc' ? 'desc' : 'asc';
                    this.markSortedColumn();
                }

                this.draw();
            }
        };

        table.init();
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/sky-table.default', widgetTable);
    });

}(jQuery, window.elementorFrontend));

/**
 * End table widget script
 */

;(function ($, elementor) {
    'use strict';

// This widget uses the global carousel handler

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-team-member-carousel.default', widgetGlobalCarousel);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

// This widget uses the global carousel handler

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-testimonial-carousel.default', widgetGlobalCarousel);
    });

}(jQuery, window.elementorFrontend));

;(function ($, elementor) {
    'use strict';

// This widget uses the global carousel handler

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-ultra-carousel.default', widgetGlobalCarousel);
    });

}(jQuery, window.elementorFrontend));

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

;
(function ($) {
    var $window = $(window),
        toGranimColor = function (color) {
            if (!color) return null;
            // Granim alpha regex (.?\d{1,3}) can't match "0.5" — strip the leading zero
            var c = color.replace(/(rgba\([\d\s,]+,\s*)0(\.\d+\))$/, '$1$2');
            // Reject CSS vars, HSL, and anything Granim won't accept
            return /^#[0-9a-fA-F]{3,6}$|^rgba?\([\d.,\s]+\)$/.test(c) ? c : null;
        },
        // Repeater colors picked from the Global palette save as '' with the palette id in
        // __globals__ — resolve the id to its --e-global-color-{id} CSS variable, otherwise
        // every global-colored item is skipped and the canvas silently never renders.
        resolveItemColor = function (item, key, context) {
            var value = item[key];
            if (!value && item.__globals__ && item.__globals__[key]) {
                var match = item.__globals__[key].match(/id=([^&]+)/);
                if (match) {
                    value = getComputedStyle(context && context.length ? context[0] : document.body)
                        .getPropertyValue('--e-global-color-' + match[1]).trim();
                }
            }
            return toGranimColor(value);
        },
        debounce = function (func, wait, immediate) {
            // 'private' variable for instance
            // The returned function will be able to reference this due to closure.
            // Each call to the returned function will share this common timer.
            var timeout;

            // Calling debounce returns a new anonymous function
            return function () {
                // reference the context and args for the setTimeout function
                var context = this,
                    args = arguments;

                // Should the function be called now? If immediate is true
                //   and not already in a timeout then the answer is: Yes
                var callNow = immediate && !timeout;

                // This is the basic debounce behaviour where you can call this
                //   function several times, but it will only execute once
                //   [before or after imposing a delay].
                //   Each time the returned function is called, the timer starts over.
                clearTimeout(timeout);

                // Set the new timeout
                timeout = setTimeout(function () {

                    // Inside the timeout function, clear the timeout variable
                    // which will let the next execution run when in 'immediate' mode
                    timeout = null;

                    // Check if the function already ran with the immediate flag
                    if (!immediate) {
                        // Call the original function with apply
                        // apply lets you define the 'this' object as well as the arguments
                        //    (both captured before setTimeout)
                        func.apply(context, args);
                    }
                }, wait);

                // Immediate mode and no wait timer? Execute the function..
                if (callNow)
                    func.apply(context, args);
            };
        };
    $window.on('elementor/frontend/init', function () {
        var ModuleHandler = elementorModules.frontend.handlers.Base,
            AnimatedGradientBg;

        AnimatedGradientBg = ModuleHandler.extend({

            bindEvents: function () {
                this.run();
            },

            getDefaultSettings: function () {
                return {
                    direction: 'left-right',
                };
            },

            settings: function (key) {
                return this.getElementSettings('sa_agbg_' + key);
            },

            onElementChange: debounce(function (prop) {
                if (prop.indexOf('sa_agbg_') !== -1) {
                    if ($('#' + this.Granim).length) {
                        $('#' + this.Granim).remove();
                    }
                    this.run();
                }
            }, 400),

            run: function () {
                var options = this.getDefaultSettings(),
                    elementID = this.getID(),
                    elementContainer = $('.elementor-element-' + elementID),
                    element = 'sa-agbg-' + elementID;

                if (this.settings('enable') !== 'yes') {
                    return;
                }

                if ($(this.$element).hasClass('elementor-widget')) {
                    elementContainer = $('.elementor-element-' + elementID + ' > :first-child');
                    elementContainer.css({
                        'position': 'relative',
                        'overflow': 'hidden',
                    });
                }

                if ($(this.$element).hasClass('elementor-column')) {
                    elementContainer = $('.elementor-element-' + elementID).find('.elementor-column-wrap');
                    elementContainer.css({
                        // 'position' : 'relative',
                        'overflow': 'hidden',
                    });
                }

                var $color_list = this.settings('color_list');
                var gradients = [];

                $color_list.forEach(function (item) {
                    var start = resolveItemColor(item, 'sa_agbg_start_color', elementContainer);
                    var end = resolveItemColor(item, 'sa_agbg_end_color', elementContainer);
                    if (!start || !end) {
                        return;
                    }
                    var stops = [start];
                    var mid = resolveItemColor(item, 'sa_agbg_mid_color', elementContainer);
                    if (mid) {
                        stops.push(mid);
                    }
                    stops.push(end);
                    gradients.push(stops);
                });

                if (!gradients.length) {
                    return;
                }

                // Granim requires all gradients to have the same stop count
                var firstLen = gradients[0].length;
                if (gradients.some(function (g) { return g.length !== firstLen; })) {
                    gradients = gradients.map(function (g) { return [g[0], g[g.length - 1]]; });
                }

                elementContainer.prepend('<canvas id="' + element + '" class="sa-animated-gradient-bg sa-d-block sa-w-100 sa-h-100"></canvas>');

                $('#' + element).css({
                    'position': 'absolute',
                    'top': 0,
                    'right': 0,
                    'bottom': 0,
                    'left': 0,
                    'pointer-events': 'none',
                });

                options.element = '#' + element;
                options.isPausedWhenNotInView = this.settings('pause_on_scroll') !== 'no';

                if (this.settings('direction')) {
                    options.direction = this.settings('direction');
                }

                var transitionSpeed = this.settings('transition_speed.size') || 7000;

                options.states = {
                    'default-state': {
                        'gradients': gradients,
                        'transitionSpeed': transitionSpeed,
                    }
                };

                var loopCount = parseInt(this.settings('loop_count')) || 0;
                var granimInstance;

                if (loopCount > 0) {
                    var transitionCount = 0;
                    var maxTransitions = loopCount * gradients.length;
                    options.onGradientChange = function () {
                        transitionCount++;
                        if (transitionCount >= maxTransitions && granimInstance) {
                            granimInstance.pause();
                        }
                    };
                }

                granimInstance = new Granim(options);
                this.Granim = element;
            }
        });


        elementorFrontend.hooks.addAction('frontend/element_ready/section', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(AnimatedGradientBg, {
                $element: $scope
            });
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/container', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(AnimatedGradientBg, {
                $element: $scope
            });
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/column', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(AnimatedGradientBg, {
                $element: $scope
            });
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(AnimatedGradientBg, {
                $element: $scope
            });
        });

    });

}(jQuery));

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

;
(function ($) {
    var $window = $(window),
        debounce = function (func, wait, immediate) {
            // 'private' variable for instance
            // The returned function will be able to reference this due to closure.
            // Each call to the returned function will share this common timer.
            var timeout;

            // Calling debounce returns a new anonymous function
            return function () {
                // reference the context and args for the setTimeout function
                var context = this,
                    args = arguments;

                // Should the function be called now? If immediate is true
                //   and not already in a timeout then the answer is: Yes
                var callNow = immediate && !timeout;

                // This is the basic debounce behaviour where you can call this
                //   function several times, but it will only execute once
                //   [before or after imposing a delay].
                //   Each time the returned function is called, the timer starts over.
                clearTimeout(timeout);

                // Set the new timeout
                timeout = setTimeout(function () {

                    // Inside the timeout function, clear the timeout variable
                    // which will let the next execution run when in 'immediate' mode
                    timeout = null;

                    // Check if the function already ran with the immediate flag
                    if (!immediate) {
                        // Call the original function with apply
                        // apply lets you define the 'this' object as well as the arguments
                        //    (both captured before setTimeout)
                        func.apply(context, args);
                    }
                }, wait);

                // Immediate mode and no wait timer? Execute the function..
                if (callNow)
                    func.apply(context, args);
            };
        },
        // A slider handle the user cleared comes back as '' (or undefined when the control
        // was never saved). Anything else is a real number, including 0.
        isBlank = function (v) {
            return '' === v || null === v || undefined === v;
        },
        // anime.js 3.x resolves an easing name through its Penner table and calls
        // `.apply` on the result, so an unknown name (CSS names like 'ease-in-out'
        // from imported or hand-edited data) throws "Cannot read properties of
        // undefined (reading 'apply')" and kills every handler after it. Map the
        // CSS names, accept anything anime knows, fall back to the default otherwise.
        CSS_EASING_MAP = {
            'linear': 'linear',
            'ease': 'easeInOutSine',
            'ease-in': 'easeInSine',
            'ease-out': 'easeOutSine',
            'ease-in-out': 'easeInOutSine'
        },
        ANIME_EASING = /^(linear|spring|steps|cubicBezier|ease(In|Out|InOut|OutIn)(Quad|Cubic|Quart|Quint|Sine|Expo|Circ|Back|Bounce|Elastic))(\(.*\))?$/,
        normalizeEasing = function (value, fallback) {
            if (typeof value !== 'string' || '' === value) {
                return fallback;
            }
            if (CSS_EASING_MAP[value]) {
                return CSS_EASING_MAP[value];
            }
            return ANIME_EASING.test(value) ? value : fallback;
        };
    $window.on('elementor/frontend/init', function () {
        var ModuleHandler = elementorModules.frontend.handlers.Base,
            FloatingEffects;

        FloatingEffects = ModuleHandler.extend({

            bindEvents: function () {
                this.run();
            },

            getDefaultSettings: function () {
                return {
                    direction: 'alternate',
                    easing: 'easeInOutSine',
                    loop: true
                };
            },

            settings: function (key) {
                return this.getElementSettings('sa_floating_ef_' + key);
            },

            // Returns [from, to] for one transform axis, or null when the axis carries no
            // motion and should be left out of the anime options entirely.
            //
            // The guards this replaces read `settings('rotate_x.sizes.from').length !== 0`.
            // A slider value is a number, `.length` on it is undefined, and `undefined !== 0`
            // is always true — so every axis of an enabled group animated at its default.
            // Switching Rotate on for a small tilt gave a 45deg tumble across X, Y and Z.
            axis: function (key) {
                var val = this.settings(key);
                if (!val || !val.sizes) { return null; }

                var rawFrom = val.sizes.from,
                    rawTo   = val.size || val.sizes.to;

                // Both handles cleared — the "leave this axis alone" the old guards meant to catch.
                if (isBlank(rawFrom) && isBlank(rawTo)) { return null; }

                var from = parseFloat(rawFrom) || 0,
                    to   = parseFloat(rawTo) || 0;

                // Nothing to animate. This is what keeps an untouched axis inert now that the
                // non-primary defaults are a flat 0 -> 0.
                if (from === to) { return null; }

                return [from, to];
            },

            onElementChange: debounce(function (prop) {
                if (prop.indexOf('sa_floating') !== -1) {
                    this.anime && this.anime.restart();
                    this.run();
                }
            }, 400),

            run: function () {
                var options = this.getDefaultSettings(),
                    element = this.$element.get(0);

                options.targets = element;

                if (this.settings('enable') !== 'yes') {
                    return;
                }

                // Each group writes only the axes that actually move — see axis().
                var self = this,
                    addAxis = function (prop, key, group) {
                        var range = self.axis(key);
                        if (!range) { return; }
                        options[prop] = {
                            value: range,
                            duration: self.settings(group + '_duration.size'),
                            delay: self.settings(group + '_delay.size') || 0
                        };
                    };

                if (this.settings('translate_toggle')) {
                    addAxis('translateX', 'translate_x', 'translate');
                    addAxis('translateY', 'translate_y', 'translate');
                }

                if (this.settings('rotate_toggle')) {
                    addAxis('rotateX', 'rotate_x', 'rotate');
                    addAxis('rotateY', 'rotate_y', 'rotate');
                    addAxis('rotateZ', 'rotate_z', 'rotate');
                }

                if (this.settings('scale_toggle')) {
                    addAxis('scaleX', 'scale_x', 'scale');
                    addAxis('scaleY', 'scale_y', 'scale');
                }

                if (this.settings('skew_toggle')) {
                    addAxis('skewX', 'skew_x', 'skew');
                    addAxis('skewY', 'skew_y', 'skew');
                }

                options.easing = normalizeEasing(this.settings('easing'), options.easing);

                if (
                    this.settings('translate_toggle') ||
                    this.settings('rotate_toggle') ||
                    this.settings('scale_toggle') ||
                    this.settings('skew_toggle')
                ) {
                    this.anime = window.anime && window.anime(options);
                }

            }
        });


        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(FloatingEffects, {
                $element: $scope
            });
        });
    });

}(jQuery));

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

;jQuery('body').on('click', '.sa-element-link', function () {
    var timeout,
        $element = jQuery(this),
        data = $element.data('sa-element-link'),
        id = 'sa-element-link-' + $element.data('id'),
        idSelector = '#' + id;

    if (jQuery(idSelector).length === 0) {
        var options = {
            href: data.url,
            target: data.is_external ? '_blank' : '_self',
            class: 'sa-d-none',
            id: id,
            rel: data.nofollow ? 'nofollow noreferrer' : ''
        };

        jQuery('body').append(
            jQuery(document.createElement('a')).prop(options)
        );

        jQuery(idSelector)[0].click();

        timeout = setTimeout(function () {
            jQuery('body').find(idSelector).remove();
            clearTimeout(timeout);
        }, 1000);

    }

});
