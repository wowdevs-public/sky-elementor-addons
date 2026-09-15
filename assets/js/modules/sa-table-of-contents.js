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
