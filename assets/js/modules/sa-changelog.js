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
