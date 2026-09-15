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
