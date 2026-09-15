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
