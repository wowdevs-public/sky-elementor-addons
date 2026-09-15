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
