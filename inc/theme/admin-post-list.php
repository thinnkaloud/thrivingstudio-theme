<?php
/**
 * Admin post-list usability improvements.
 */

/**
 * Determine whether the current screen is the standard Posts list.
 *
 * @return bool
 */
function thrivingstudio_is_posts_list_screen() {
    $screen = get_current_screen();

    return $screen && 'edit-post' === $screen->id;
}

/**
 * Keep plugin-added columns from crushing the core Posts list columns.
 */
function thrivingstudio_posts_list_scroll_styles() {
    if (!thrivingstudio_is_posts_list_screen()) {
        return;
    }
    ?>
    <style id="thrivingstudio-posts-list-scroll-css">
        .ts-posts-list-scroll {
            width: 100%;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            -webkit-overflow-scrolling: touch;
        }

        #posts-filter .wp-list-table.posts {
            width: 2100px !important;
            min-width: 2100px !important;
            margin-top: 0;
            table-layout: auto;
        }

        #posts-filter {
            position: relative;
        }

        .ts-posts-list-scrollbar {
            position: absolute;
            right: 0;
            left: 0;
            z-index: 2;
            height: 8px;
            margin: 0;
            overflow-x: scroll;
            overflow-y: hidden;
            background: transparent;
            box-sizing: border-box;
            opacity: 0;
            transition: opacity 180ms ease;
        }

        .ts-posts-list-scrollbar.is-active,
        .ts-posts-list-scrollbar:hover,
        .ts-posts-list-scrollbar:focus-visible {
            opacity: 1;
        }

        .ts-posts-list-scrollbar-content {
            height: 1px;
        }

        .ts-posts-list-scrollbar::-webkit-scrollbar,
        .ts-posts-list-scroll::-webkit-scrollbar {
            height: 4px;
        }

        .ts-posts-list-scrollbar::-webkit-scrollbar-track,
        .ts-posts-list-scroll::-webkit-scrollbar-track {
            background: #e2e4e7;
            border-radius: 999px;
        }

        .ts-posts-list-scrollbar::-webkit-scrollbar-thumb,
        .ts-posts-list-scroll::-webkit-scrollbar-thumb {
            background: #8c8f94;
            border: 0;
            border-radius: 999px;
        }

        .ts-posts-list-scrollbar::-webkit-scrollbar-thumb:hover,
        .ts-posts-list-scroll::-webkit-scrollbar-thumb:hover {
            background: #50575e;
        }

        @media (prefers-reduced-motion: reduce) {
            .ts-posts-list-scrollbar {
                transition: none;
            }
        }

        #posts-filter .wp-list-table.posts .column-title {
            min-width: 340px;
        }

        #posts-filter .wp-list-table.posts .column-author {
            min-width: 130px;
        }

        #posts-filter .wp-list-table.posts .column-categories,
        #posts-filter .wp-list-table.posts .column-tags {
            min-width: 190px;
        }

        #posts-filter .wp-list-table.posts .column-comments {
            min-width: 54px;
        }

        #posts-filter .wp-list-table.posts .column-date {
            min-width: 170px;
        }

        #posts-filter .wp-list-table.posts .row-title,
        #posts-filter .wp-list-table.posts .column-categories,
        #posts-filter .wp-list-table.posts .column-tags {
            overflow-wrap: anywhere;
        }
    </style>
    <?php
}
add_action('admin_head-edit.php', 'thrivingstudio_posts_list_scroll_styles');

/**
 * Put the Posts table in a dedicated horizontal scroll container.
 */
function thrivingstudio_posts_list_scroll_script() {
    if (!thrivingstudio_is_posts_list_screen()) {
        return;
    }
    ?>
    <script id="thrivingstudio-posts-list-scroll-js">
        (function () {
            var table = document.querySelector('#posts-filter .wp-list-table.posts');

            if (!table || table.parentElement.classList.contains('ts-posts-list-scroll')) {
                return;
            }

            var scrollContainer = document.createElement('div');
            scrollContainer.className = 'ts-posts-list-scroll';
            scrollContainer.setAttribute('role', 'region');
            scrollContainer.setAttribute('aria-label', '<?php echo esc_js(__('Posts table', 'thrivingstudio')); ?>');
            scrollContainer.setAttribute('tabindex', '0');

            table.parentNode.insertBefore(scrollContainer, table);
            scrollContainer.appendChild(table);

            var topScrollbar = document.createElement('div');
            var topScrollbarContent = document.createElement('div');
            topScrollbar.className = 'ts-posts-list-scrollbar';
            topScrollbar.setAttribute('role', 'region');
            topScrollbar.setAttribute('aria-label', '<?php echo esc_js(__('Scroll posts table horizontally', 'thrivingstudio')); ?>');
            topScrollbar.setAttribute('tabindex', '0');
            topScrollbarContent.className = 'ts-posts-list-scrollbar-content';
            topScrollbar.appendChild(topScrollbarContent);
            scrollContainer.parentNode.insertBefore(topScrollbar, scrollContainer);

            var syncing = false;
            var hideScrollbarTimer;

            function showScrollbar() {
                topScrollbar.classList.add('is-active');
                window.clearTimeout(hideScrollbarTimer);
                hideScrollbarTimer = window.setTimeout(function () {
                    topScrollbar.classList.remove('is-active');
                }, 1000);
            }

            function setScrollbarWidth() {
                topScrollbarContent.style.width = table.scrollWidth + 'px';
                topScrollbar.style.top = Math.max(0, scrollContainer.offsetTop - topScrollbar.offsetHeight) + 'px';
            }

            function syncScroll(source, target) {
                if (syncing) {
                    return;
                }

                syncing = true;
                target.scrollLeft = source.scrollLeft;
                window.requestAnimationFrame(function () {
                    syncing = false;
                });
            }

            topScrollbar.addEventListener('scroll', function () {
                showScrollbar();
                syncScroll(topScrollbar, scrollContainer);
            });
            scrollContainer.addEventListener('scroll', function () {
                showScrollbar();
                syncScroll(scrollContainer, topScrollbar);
            });

            var postsFilter = document.getElementById('posts-filter');
            ['pointermove', 'wheel', 'touchstart', 'keydown'].forEach(function (eventName) {
                postsFilter.addEventListener(eventName, showScrollbar, { passive: true });
            });

            setScrollbarWidth();
            window.addEventListener('resize', setScrollbarWidth);
        }());
    </script>
    <?php
}
add_action('admin_footer-edit.php', 'thrivingstudio_posts_list_scroll_script');
