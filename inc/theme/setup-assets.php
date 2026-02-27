<?php

function thrivingstudio_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ]);
    register_nav_menus([
        'primary' => __('Primary Menu', 'thrivingstudio'),
        'footer' => __('Footer Menu', 'thrivingstudio'),
        'category_menu' => __('Category Menu', 'thrivingstudio'),
    ]);
}
add_action('after_setup_theme', 'thrivingstudio_setup');

/**
 * Enqueue theme scripts and styles with optimized loading.
 */
function thrivingstudio_enqueue_scripts() {
    $frontend_path = THRIVINGSTUDIO_DIR . '/frontend';
    $frontend_uri = THRIVINGSTUDIO_URI . '/frontend';

    // Smart cache busting: Use file modification time for better cache control
    $css_source = $frontend_path . '/index.css';
    $css_build = $frontend_path . '/build.css';

    // Determine which CSS file to use (prefer build.css, fallback to index.css)
    if (file_exists($css_build)) {
        $css_file = 'build.css';
        // Force cache bust with timestamp to ensure fresh CSS loads
        $css_version = filemtime($css_build) ?: time();
    } elseif (file_exists($css_source)) {
        // Fallback to source if build doesn't exist (shouldn't happen in production, but prevents site breakage)
        $css_file = 'index.css';
        $css_version = filemtime($css_source) ?: time();
    } else {
        // Last resort fallback
        $css_file = 'build.css';
        $css_version = time();
    }

    // Add aggressive cache busting for CSS (force reload)
    $css_version = $css_version . '-' . time();

    $js_file = WP_DEBUG ? 'main.js' : 'main.min.js';
    $js_source = $frontend_path . '/main.js';
    $js_version = file_exists($js_source) ? filemtime($js_source) : time();

    // Enqueue main styles
    wp_enqueue_style(
        'thrivingstudio-style',
        "$frontend_uri/$css_file",
        [],
        $css_version
    );

    // Prevent LiteSpeed Cache from minifying/combining our CSS (it breaks Tailwind)
    if (defined('LSCWP_V')) {
        add_filter('litespeed_optimize_css_excludes', function($excludes) use ($css_file) {
            $excludes[] = $css_file;
            $excludes[] = 'build.css';
            $excludes[] = 'frontend/build.css';
            return $excludes;
        });
    }

    // Add data attribute to prevent Cloudflare Rocket Loader from interfering
    add_filter('style_loader_tag', function($tag, $handle) {
        if ($handle === 'thrivingstudio-style') {
            $tag = str_replace("rel='stylesheet'", "rel='stylesheet' data-cfasync='false'", $tag);
        }
        return $tag;
    }, 10, 2);

    // Enqueue scripts with defer
    wp_enqueue_script(
        'thrivingstudio-js',
        "$frontend_uri/$js_file",
        [],
        $js_version,
        true
    );
}
add_action('wp_enqueue_scripts', 'thrivingstudio_enqueue_scripts');

/**
 * Check if CSS needs rebuilding and show admin notice
 */
function thrivingstudio_check_css_build() {
    // Only show in admin area
    if (!is_admin()) {
        return;
    }

    $frontend_path = THRIVINGSTUDIO_DIR . '/frontend';
    $css_source = $frontend_path . '/index.css';
    $css_build = $frontend_path . '/build.css';

    // Check if build.css is missing (critical issue)
    if (!file_exists($css_build)) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>🚨 Critical:</strong> <code>build.css</code> is missing! Your site may appear broken. ';
            echo 'Please upload <code>wp-content/themes/thrivingstudio/frontend/build.css</code> to your server, ';
            echo 'or run: <code>cd wp-content/themes/thrivingstudio/frontend && npx tailwindcss -i ./index.css -o ./build.css --minify</code></p>';
            echo '</div>';
        });
        return; // Don't check for outdated build if it doesn't exist
    }

    // Check if source file exists and is newer than build
    if (file_exists($css_source) && file_exists($css_build)) {
        $source_time = filemtime($css_source);
        $build_time = filemtime($css_build);

        // If source is newer than build, show warning
        if ($source_time > $build_time) {
            add_action('admin_notices', function() use ($source_time, $build_time) {
                $diff = $source_time - $build_time;
                $minutes = round($diff / 60);
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p><strong>⚠️ CSS Needs Rebuilding:</strong> Your CSS source file is newer than the compiled build. ';
                echo 'Run <code>cd wp-content/themes/thrivingstudio/frontend && npx tailwindcss -i ./index.css -o ./build.css --minify</code> to rebuild. ';
                echo '(Source is ' . $minutes . ' minute' . ($minutes !== 1 ? 's' : '') . ' newer)</p>';
                echo '</div>';
            });
        }
    }
}
add_action('admin_init', 'thrivingstudio_check_css_build');

/**
 * Add custom CSS for specific pages
 */
function thrivingstudio_custom_css() {
    if (is_post_type_archive('quote_card')) {
        echo '<style>
        /* Minimal spacing for quote card archive */
        main.flex-1 {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        main.flex-1 .container {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }
        main.flex-1 .mb-4 {
            margin-bottom: 0 !important;
        }
        main.flex-1 h1 {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        </style>';
    }

    // Fix gap between category and title on single posts
    if (is_single()) {
        echo '<style>
        /* Fix gap between category and title - Override prose margins */
        .prose.prose-lg > div.mb-1 + h1,
        .prose > div.mb-1 + h1 {
            margin-top: 0.25rem !important;
        }
        .prose.prose-lg h1:first-of-type {
            margin-top: 0.25rem !important;
        }
        </style>';
    }

}
add_action('wp_head', 'thrivingstudio_custom_css');

/**
 * Add margin class to primary menu items.
 *
 * @param array $classes
 * @param WP_Post $item
 * @param stdClass $args
 * @return array
 */
function add_additional_class_on_li($classes, $item, $args) {
    if (isset($args->theme_location) && $args->theme_location == 'primary') {
        $classes[] = 'mr-6';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'add_additional_class_on_li', 1, 3);

/**
 * Add custom attributes to mobile menu links.
 *
 * @param array $atts
 * @param WP_Post $item
 * @param stdClass $args
 * @param int $depth
 * @return array
 */
function thrivingstudio_nav_menu_link_attributes($atts, $item, $args, $depth) {
    if (isset($args->is_mobile) && $args->is_mobile) {
        $atts['class'] = 'block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700';
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'thrivingstudio_nav_menu_link_attributes', 10, 4);

/**
 * Custom callback for displaying comments.
 *
 * @param WP_Comment $comment
 * @param array $args
 * @param int $depth
 */
function thrivingstudio_comment_callback($comment, $args, $depth) {
    get_template_part('template-parts/comment', null, [
        'comment' => $comment,
        'args' => $args,
        'depth' => $depth
    ]);
}

/**
 * Add custom body class for homepage.
 *
 * @param array $classes
 * @return array
 */
function add_home_body_class($classes) {
    if (is_front_page()) {
        $classes[] = 'is-homepage';
    }
    return $classes;
}
add_filter('body_class', 'add_home_body_class');

/**
 * Optimize JPEG image quality.
 *
 * @return int
 */
function thrivingstudio_optimize_image_quality() {
    return 85; // Balanced quality setting
}
add_filter('jpeg_quality', 'thrivingstudio_optimize_image_quality');
add_filter('wp_editor_set_quality', 'thrivingstudio_optimize_image_quality');

// Preload and async functionality moved to inc/performance.php to avoid duplication

/**
 * Check if WooCommerce is active.
 *
 * @return bool
 */
function thrivingstudio_is_woocommerce_active() {
    return class_exists('WooCommerce');
}

// Placeholder: Critical CSS inlining should be implemented in the build process for above-the-fold content.
// Placeholder: Set up automated testing (e.g., PHPUnit) for theme functions and templates.
