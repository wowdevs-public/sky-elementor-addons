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
