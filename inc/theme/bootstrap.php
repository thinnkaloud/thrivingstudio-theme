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
    
    // Force blog layout fixes
    if (is_home() || is_archive()) {
        echo '<style>
        /* Force blog layout fixes */
        .blog-grid {
            gap: 1.5rem !important;
        }
        .blog-card {
            padding: 1rem !important;
        }
        .blog-card img {
            height: 200px !important;
            object-fit: cover !important;
            width: 100% !important;
        }
        .blog-card-image {
            height: 200px !important;
            object-fit: cover !important;
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

/**
 * Register theme customizer settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize
 */
function thrivingstudio_customize_register($wp_customize) {
    // Logo Section
    $wp_customize->add_section('thrivingstudio_logo_section', [
        'title' => __('Logo Settings', 'thrivingstudio'),
        'priority' => 30,
    ]);
    
    $wp_customize->add_setting('thrivingstudio_logo_text', [
        'default' => 'Thriving Studio',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('thrivingstudio_logo_text', [
        'label' => __('Logo Text', 'thrivingstudio'),
        'section' => 'thrivingstudio_logo_section',
        'type' => 'text',
    ]);
    
    // Colors Section
    $wp_customize->add_setting('thrivingstudio_primary_color', [
        'default' => '#3b82f6',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'thrivingstudio_primary_color', [
        'label' => __('Primary Color', 'thrivingstudio'),
        'section' => 'colors',
    ]));
    
    // Social Media Section
    $wp_customize->add_section('thrivingstudio_social_section', [
        'title' => __('Social Media', 'thrivingstudio'),
        'priority' => 40,
    ]);
    

    

    
    // Register a single setting for all social profiles (array of [platform, url])
    $wp_customize->add_setting('thrivingstudio_social_profiles', [
        'default' => json_encode([]),
        'sanitize_callback' => function($input) {
            $arr = json_decode($input, true);
            if (!is_array($arr)) return json_encode([]);
            // Validate each entry
            $valid = array_filter($arr, function($item) {
                return isset($item['platform'], $item['url']) && filter_var($item['url'], FILTER_VALIDATE_URL);
            });
            return json_encode(array_values($valid));
        },
        'transport' => 'refresh',
    ]);

    // Custom control class (JS will handle UI)
    if (class_exists('WP_Customize_Control')) {
        class ThrivingStudio_Sortable_Social_Profiles_Control extends WP_Customize_Control {
            public $type = 'sortable_social_profiles';
            public function render_content() {
                ?>
                <label><span class="customize-control-title"><?php echo esc_html($this->label); ?></span></label>
                <div id="sortable-social-profiles" data-setting="<?php echo esc_attr($this->id); ?>"></div>
                <?php
            }
        }
        $wp_customize->add_control(new ThrivingStudio_Sortable_Social_Profiles_Control($wp_customize, 'thrivingstudio_social_profiles', [
            'label' => __('Social Profiles', 'thrivingstudio'),
            'section' => 'thrivingstudio_social_section',
            'settings' => 'thrivingstudio_social_profiles',
        ]));
    }
    // Enqueue JS for the control
    add_action('customize_controls_enqueue_scripts', function() {
        wp_enqueue_script('thrivingstudio-customizer-social', get_template_directory_uri() . '/assets/js/customizer-social.js', ['jquery', 'jquery-ui-sortable'], THRIVINGSTUDIO_VERSION, true);
    });
    
    // Footer Section
    $wp_customize->add_section('thrivingstudio_footer_section', [
        'title' => __('Footer Settings', 'thrivingstudio'),
        'priority' => 50,
    ]);
    
    $wp_customize->add_setting('thrivingstudio_footer_text', [
        'default' => '© ' . date('Y') . ' Thriving Studio. All rights reserved.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    
    $wp_customize->add_control('thrivingstudio_footer_text', [
        'label' => __('Footer Text', 'thrivingstudio'),
        'section' => 'thrivingstudio_footer_section',
        'type' => 'textarea',
    ]);

    // Homepage Featured Categories Section
    $wp_customize->add_section('thrivingstudio_featured_categories_section', [
        'title' => __('Homepage Featured Categories', 'thrivingstudio'),
        'priority' => 35,
    ]);

    if (class_exists('WP_Customize_Control')) {
        class ThrivingStudio_Featured_Category_Dropdown_Control extends WP_Customize_Control {
            public $type = 'featured_category_dropdown';
            public function render_content() {
                $categories = get_categories(['hide_empty' => false]);
                ?>
                <label><span class="customize-control-title"><?php echo esc_html($this->label); ?></span></label>
                <select <?php $this->link(); ?> style="width:100%;max-width:300px;">
                    <option value="0">— None —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($this->value(), $cat->term_id); ?>><?php echo esc_html($cat->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php
            }
        }
        for ($i = 1; $i <= 4; $i++) {
            $wp_customize->add_setting("thrivingstudio_featured_category_$i", [
                'default' => '',
                'sanitize_callback' => 'absint',
            ]);
            $wp_customize->add_control(new ThrivingStudio_Featured_Category_Dropdown_Control($wp_customize, "thrivingstudio_featured_category_$i", [
                'label' => sprintf(__('Featured Category #%d', 'thrivingstudio'), $i),
                'section' => 'thrivingstudio_featured_categories_section',
                'settings' => "thrivingstudio_featured_category_$i",
            ]));
            // Add description field
            $wp_customize->add_setting("thrivingstudio_featured_category_{$i}_desc", [
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ]);
            $wp_customize->add_control("thrivingstudio_featured_category_{$i}_desc", [
                'label' => sprintf(__('Featured Category #%d Description', 'thrivingstudio'), $i),
                'section' => 'thrivingstudio_featured_categories_section',
                'type' => 'text',
            ]);
        }
    }



    // Homepage Social Stats Section (separate from social profiles)
    $wp_customize->add_section('thrivingstudio_homepage_social_stats_section', [
        'title' => __('Homepage Social Stats', 'thrivingstudio'),
        'priority' => 36,
    ]);
    
    // Social Stats Section Title
    $wp_customize->add_setting('thrivingstudio_home_social_stats_title', [
        'default'           => __('Our Social Circle', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_home_social_stats_title', [
        'label'    => __('Social Stats Section Title', 'thrivingstudio'),
        'section'  => 'thrivingstudio_homepage_social_stats_section',
        'type'     => 'text',
    ]);
    
    // Social Media Follower Counts
    $socials = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'pinterest' => 'Pinterest',
        'youtube' => 'YouTube',
    ];
    foreach ($socials as $key => $label) {
        $wp_customize->add_setting("thrivingstudio_home_social_{$key}_count", [
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control("thrivingstudio_home_social_{$key}_count", [
            'label' => sprintf(__('%s Followers', 'thrivingstudio'), $label),
            'section' => 'thrivingstudio_homepage_social_stats_section',
            'type' => 'text',
        ]);
    }

    // Homepage Quotes Section Title
    $wp_customize->add_section('thrivingstudio_home_quotes_section', [
        'title'    => __('Homepage Quotes Section', 'thrivingstudio'),
        'priority' => 37,
    ]);
    $wp_customize->add_setting('thrivingstudio_home_quotes_title', [
        'default'           => __('Inspirational Quotes', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_home_quotes_title', [
        'label'    => __('Quotes Section Title', 'thrivingstudio'),
        'section'  => 'thrivingstudio_home_quotes_section',
        'type'     => 'text',
    ]);

    // Header CTA Section for Get In Touch button
    $wp_customize->add_section('thrivingstudio_header_cta_section', [
        'title'    => __('Header CTA', 'thrivingstudio'),
        'priority' => 31,
    ]);
    $wp_customize->add_setting('thrivingstudio_header_cta_text', [
        'default'           => __('Get In Touch', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_header_cta_text', [
        'label'    => __('CTA Button Text', 'thrivingstudio'),
        'section'  => 'thrivingstudio_header_cta_section',
        'type'     => 'text',
    ]);
    $wp_customize->add_setting('thrivingstudio_header_cta_link', [
        'default'           => '#',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('thrivingstudio_header_cta_link', [
        'label'    => __('CTA Button Link', 'thrivingstudio'),
        'section'  => 'thrivingstudio_header_cta_section',
        'type'     => 'url',
    ]);

    // Homepage Hero Section
    $wp_customize->add_section('thrivingstudio_homepage_hero_section', [
        'title'    => __('Homepage Hero Section', 'thrivingstudio'),
        'priority' => 30,
    ]);
    $wp_customize->add_setting('thrivingstudio_home_hero_title', [
        'default'           => __('Welcome to <span class="text-black">Thriving Studio</span>', 'thrivingstudio'),
        'sanitize_callback' => 'wp_kses_post',
    ]);
    $wp_customize->add_control('thrivingstudio_home_hero_title', [
        'label'    => __('Hero Title (HTML allowed)', 'thrivingstudio'),
        'section'  => 'thrivingstudio_homepage_hero_section',
        'type'     => 'text',
    ]);
    $wp_customize->add_setting('thrivingstudio_home_hero_subtitle', [
        'default'           => __('Deep insights, visual storytelling, and timeless ideas for a thriving creative life.', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_home_hero_subtitle', [
        'label'    => __('Hero Subtitle', 'thrivingstudio'),
        'section'  => 'thrivingstudio_homepage_hero_section',
        'type'     => 'text',
    ]);
    $wp_customize->add_setting('thrivingstudio_home_hero_button_text', [
        'default'           => __('Learn More', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_home_hero_button_text', [
        'label'    => __('Hero Button Text', 'thrivingstudio'),
        'section'  => 'thrivingstudio_homepage_hero_section',
        'type'     => 'text',
    ]);
    $wp_customize->add_setting('thrivingstudio_home_hero_button_link', [
        'default'           => '#',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('thrivingstudio_home_hero_button_link', [
        'label'    => __('Hero Button Link', 'thrivingstudio'),
        'section'  => 'thrivingstudio_homepage_hero_section',
        'type'     => 'url',
    ]);

    // Top Bar Notification Section
    $wp_customize->add_section('thrivingstudio_topbar_section', [
        'title' => __('Top Bar Notification', 'thrivingstudio'),
        'priority' => 5,
    ]);
    $wp_customize->add_setting('thrivingstudio_topbar_message', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_topbar_message', [
        'label' => __('Top Bar Message', 'thrivingstudio'),
        'section' => 'thrivingstudio_topbar_section',
        'type' => 'text',
        'description' => __('This message will appear in a yellow bar above the header. Leave empty to hide.', 'thrivingstudio'),
    ]);
    $wp_customize->add_setting('thrivingstudio_topbar_show', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_topbar_show', [
        'label' => __('Show Top Bar', 'thrivingstudio'),
        'section' => 'thrivingstudio_topbar_section',
        'type' => 'checkbox',
        'description' => __('Toggle to show or hide the top bar notification section.', 'thrivingstudio'),
    ]);
}
add_action('customize_register', 'thrivingstudio_customize_register');

// Debug: Log when customizer settings are saved
function thrivingstudio_debug_customizer_save($wp_customize) {
    if (isset($_POST['customized'])) {
        $customized = json_decode(stripslashes($_POST['customized']), true);
        if (isset($customized['thrivingstudio_facebook_followers'])) {
            error_log('Facebook followers being saved: ' . $customized['thrivingstudio_facebook_followers']);
        }
    }
}
add_action('customize_save_after', 'thrivingstudio_debug_customizer_save');

/**
 * Ensure a Blog page exists and is set as the posts page on theme activation.
 */
function thrivingstudio_ensure_blog_page() {
    // Check if a posts page is already set
    $posts_page_id = get_option('page_for_posts');
    if ($posts_page_id && get_post($posts_page_id)) {
        return; // Already set
    }
    // Check if a page titled 'Blog' exists
    $blog_page = get_page_by_path('blog');
    if (!$blog_page) {
        // Create the Blog page
        $blog_page_id = wp_insert_post([
            'post_title'   => 'Blog',
            'post_name'    => 'blog',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ]);
    } else {
        $blog_page_id = $blog_page->ID;
    }
    // Set as posts page
    if ($blog_page_id) {
        update_option('page_for_posts', $blog_page_id);
    }
}
add_action('after_switch_theme', 'thrivingstudio_ensure_blog_page');

/**
 * Register the Quote Cards custom post type.
 */
function thrivingstudio_register_quote_cards_cpt() {
    $labels = [
        'name' => __('Quote Cards', 'thrivingstudio'),
        'singular_name' => __('Quote Card', 'thrivingstudio'),
        'add_new' => __('Add New', 'thrivingstudio'),
        'add_new_item' => __('Add New Quote Card', 'thrivingstudio'),
        'edit_item' => __('Edit Quote Card', 'thrivingstudio'),
        'new_item' => __('New Quote Card', 'thrivingstudio'),
        'view_item' => __('View Quote Card', 'thrivingstudio'),
        'search_items' => __('Search Quote Cards', 'thrivingstudio'),
        'not_found' => __('No quote cards found', 'thrivingstudio'),
        'not_found_in_trash' => __('No quote cards found in Trash', 'thrivingstudio'),
        'all_items' => __('All Quote Cards', 'thrivingstudio'),
        'menu_name' => __('Quote Cards', 'thrivingstudio'),
        'name_admin_bar' => __('Quote Card', 'thrivingstudio'),
    ];
    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'quotecards'],
        'menu_icon' => 'dashicons-format-image',
        'supports' => ['title', 'thumbnail'],
        'show_in_rest' => true,
    ];
    register_post_type('quote_card', $args);
}
add_action('init', 'thrivingstudio_register_quote_cards_cpt');

/**
 * Add custom meta boxes for Quote Cards (Author and Caption).
 */
function thrivingstudio_quote_card_meta_boxes() {
    add_meta_box(
        'quote_card_author',
        __('Quote Author', 'thrivingstudio'),
        'thrivingstudio_quote_card_author_box',
        'quote_card',
        'normal',
        'default'
    );
    add_meta_box(
        'quote_card_caption',
        __('Quote Caption', 'thrivingstudio'),
        'thrivingstudio_quote_card_caption_box',
        'quote_card',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'thrivingstudio_quote_card_meta_boxes');

/**
 * Render the Quote Card Author meta box.
 *
 * @param WP_Post $post
 */
function thrivingstudio_quote_card_author_box($post) {
    wp_nonce_field('save_quote_card_meta', 'quote_card_meta_nonce');
    $author = get_post_meta($post->ID, '_quote_card_author', true);
    echo '<input type="text" name="quote_card_author" value="' . esc_attr($author) . '" class="widefat" placeholder="e.g. Albert Einstein">';
}

/**
 * Render the Quote Card Caption meta box.
 *
 * @param WP_Post $post
 */
function thrivingstudio_quote_card_caption_box($post) {
    $caption = get_post_meta($post->ID, '_quote_card_caption', true);
    echo '<textarea name="quote_card_caption" class="widefat" rows="8" placeholder="Write a detailed caption or story about this quote...">' . esc_textarea($caption) . '</textarea>';
}

/**
 * Add admin CSS for the Quote Caption textarea.
 */
function thrivingstudio_admin_caption_textarea_css() {
    echo '<style>
    textarea[name="quote_card_caption"] { overflow-y: auto !important; resize: vertical !important; }
    </style>';
}
add_action('admin_head', 'thrivingstudio_admin_caption_textarea_css');

/**
 * Save Quote Card meta fields.
 *
 * @param int $post_id
 */
function thrivingstudio_save_quote_card_meta($post_id) {
    if (!isset($_POST['quote_card_meta_nonce']) || !wp_verify_nonce($_POST['quote_card_meta_nonce'], 'save_quote_card_meta')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (array_key_exists('quote_card_author', $_POST)) {
        update_post_meta($post_id, '_quote_card_author', sanitize_text_field($_POST['quote_card_author']));
    }
    if (array_key_exists('quote_card_caption', $_POST)) {
        update_post_meta($post_id, '_quote_card_caption', sanitize_textarea_field($_POST['quote_card_caption']));
    }
}
add_action('save_post_quote_card', 'thrivingstudio_save_quote_card_meta');

/**
 * Add MutationObserver-based auto-resize for Quote Caption textarea in admin.
 *
 * @param string $hook
 */
function thrivingstudio_admin_autoresize_caption_textarea($hook) {
    if ($hook === 'post-new.php' || $hook === 'post.php') {
        echo '<script>
        function attachAutoResize(textarea) {
            if (!textarea._autoResizeAttached) {
                function resize() {
                    textarea.style.height = "auto";
                    textarea.style.height = (textarea.scrollHeight) + "px";
                }
                textarea.addEventListener("input", resize);
                resize();
                textarea._autoResizeAttached = true;
            }
        }
        function observeTextarea() {
            var observer = new MutationObserver(function() {
                var textarea = document.querySelector("textarea[name=\\"quote_card_caption\\"]");
                if (textarea) {
                    attachAutoResize(textarea);
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
            // Initial attach
            var textarea = document.querySelector("textarea[name=\\"quote_card_caption\\"]");
            if (textarea) {
                attachAutoResize(textarea);
            }
        }
        document.addEventListener("DOMContentLoaded", observeTextarea);
        </script>';
    }
}
add_action('admin_footer', 'thrivingstudio_admin_autoresize_caption_textarea');

/**
 * Add custom hero fields to category add form.
 */
add_action('category_add_form_fields', function($taxonomy) {
    ?>
    <div class="form-field">
        <label for="hero_subtitle">Hero Subtitle</label>
        <input name="hero_subtitle" id="hero_subtitle" type="text" value="" />
        <p class="description">Optional. Subtitle for the hero section.</p>
    </div>
    <?php
});

/**
 * Add custom hero fields to category edit form.
 */
add_action('category_edit_form_fields', function($term) {
    $hero_subtitle = get_term_meta($term->term_id, 'hero_subtitle', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="hero_subtitle">Hero Subtitle</label></th>
        <td>
            <input name="hero_subtitle" id="hero_subtitle" type="text" value="<?php echo esc_attr($hero_subtitle); ?>" />
            <p class="description">Optional. Subtitle for the hero section.</p>
        </td>
    </tr>
    <?php
}, 10, 1);

/**
 * Save custom hero fields for categories.
 */
add_action('created_category', function($term_id) {
    if (isset($_POST['hero_subtitle'])) {
        update_term_meta($term_id, 'hero_subtitle', sanitize_text_field($_POST['hero_subtitle']));
    }
});
add_action('edited_category', function($term_id) {
    if (isset($_POST['hero_subtitle'])) {
        update_term_meta($term_id, 'hero_subtitle', sanitize_text_field($_POST['hero_subtitle']));
    }
}); 

/**
 * Add WebP fallback support
 */
function add_webp_fallback_support() {
    // Add WebP support to WordPress
    add_filter('upload_mimes', function($mimes) {
        $mimes['webp'] = 'image/webp';
        return $mimes;
    });
    
    // Add WebP to allowed image types
    add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
        if (strpos($filename, '.webp') !== false) {
            $data['type'] = 'image/webp';
            $data['proper_filename'] = $filename;
        }
        return $data;
    }, 10, 4);
}
add_action('init', 'add_webp_fallback_support');

/**
 * Add WebP picture element support
 */
function add_webp_picture_support($html, $attachment_id, $size, $icon, $attr) {
    // Only process if WebP is supported
    if (!function_exists('imagewebp')) {
        return $html;
    }
    
    $image_src = wp_get_attachment_image_src($attachment_id, $size);
    if (!$image_src) {
        return $html;
    }
    
    $file_path = get_attached_file($attachment_id);
    $file_info = pathinfo($file_path);
    $webp_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
    
    // Check if WebP version exists
    if (!file_exists($webp_path)) {
        return $html;
    }
    
    $webp_url = str_replace(ABSPATH, site_url('/'), $webp_path);
    
    // Create picture element with WebP fallback
    $picture_html = '<picture>';
    $picture_html .= '<source srcset="' . esc_url($webp_url) . '" type="image/webp">';
    $picture_html .= '<img src="' . esc_url($image_src[0]) . '" alt="' . esc_attr($attr['alt']) . '" width="' . $image_src[1] . '" height="' . $image_src[2] . '" loading="lazy" decoding="async">';
    $picture_html .= '</picture>';
    
    return $picture_html;
}
add_filter('wp_get_attachment_image', 'add_webp_picture_support', 10, 5); 

 
