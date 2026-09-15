function skyAddonsObserver(target, callback) {
    var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
    // Set the rootMargin to trigger when the target is 10% past the viewport
    options.rootMargin = options.rootMargin || '10% 0px 0px 0px';
    var observer = new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                callback(entry);

                if (!options.loop)
                    observer.unobserve(entry.target); // Unobserve after the first intersection
            }
        });
    }, options);
    observer.observe(target);
}

/**
 * Plyr options that keep the player off cdn.plyr.io and off noembed.com.
 *
 * Plyr defaults `iconUrl` and `blankVideo` to its own CDN. Both files ship with the
 * plugin instead, and their URLs are localized onto the `plyr` handle as
 * `skyAddonsPlyr`. Every `new Plyr()` call must merge this in:
 *
 *     new Plyr(el, skyAddonsPlyrOptions({ controls: [...] }));
 */
function skyAddonsPlyrOptions(options) {
    var opts = options || {};
    var urls = window.skyAddonsPlyr || {};

    if (urls.iconUrl) {
        opts.iconUrl = urls.iconUrl;
    }
    if (urls.blankVideo) {
        opts.blankVideo = urls.blankVideo;
    }

    // Every YouTube player asks noembed.com for the video title — a free third-party
    // endpoint with no SLA, sitting in the render path of every page view, costing
    // seconds when it is slow and hanging the player when it is down. The title only
    // labels the play button. Point Plyr at an inline `null` document instead: no
    // network, the request still resolves, and Plyr continues its normal setup.
    // Plyr deep-merges config, so this replaces the URL and nothing else.
    opts.urls = opts.urls || {};
    opts.urls.youtube = opts.urls.youtube || {};
    if (!opts.urls.youtube.api) {
        opts.urls.youtube.api = 'data:application/json,null';
    }

    return opts;
}

var widgetGlobalCarousel = function ($scope, $) {

    var $carousel = $scope.find('.sa-swiper-global-carousel'),
        $carouselContainer = $carousel.find('.swiper'),
        $settings = $carousel.data('settings');

    if (!$carousel.length) {
        return;
    }

    const Swiper = elementorFrontend.utils.swiper;
    initSwiper();
    async function initSwiper() {
        var swiper = await new Swiper($carouselContainer, $settings);
        if ($settings.pauseOnHover) {
            $($carouselContainer).hover(function () {
                (this).swiper.autoplay.stop();
            }, function () {
                (this).swiper.autoplay.start();
            });
        }

    };

};

/**
 * Skyaddons debounce function.
 * 
 * @param {function} func
 * @param {number} wait
 * @param {boolean} immediate
 * @returns {function}
 */
skyAddonsDebounce = function (func, wait, immediate) {
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