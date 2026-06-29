<?php
/**
 * SEO Module for ThrivingStudio
 * Handles meta tags, Open Graph, Twitter Cards, structured data, and sitemap
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add SEO meta tags to head
 */
function thrivingstudio_seo_meta_tags() {
    // Skip if admin or feed
    if (is_admin() || is_feed()) {
        return;
    }

    $meta_tags = [];

    // Meta description
    $description = thrivingstudio_get_meta_description();
    if ($description) {
        $meta_tags[] = '<meta name="description" content="' . esc_attr($description) . '">';
    }

    // Canonical URL
    $canonical = thrivingstudio_get_canonical_url();
    if ($canonical) {
        $meta_tags[] = '<link rel="canonical" href="' . esc_url($canonical) . '">';
    }

    // Robots meta
    $robots = thrivingstudio_get_robots_meta();
    if ($robots) {
        $meta_tags[] = '<meta name="robots" content="' . esc_attr($robots) . '">';
    }

    // Open Graph tags
    $og_tags = thrivingstudio_get_open_graph_tags();
    $meta_tags = array_merge($meta_tags, $og_tags);

    // Twitter Card tags
    $twitter_tags = thrivingstudio_get_twitter_card_tags();
    $meta_tags = array_merge($meta_tags, $twitter_tags);

    // Output meta tags
    if (!empty($meta_tags)) {
        echo "\n<!-- SEO Meta Tags -->\n";
        echo implode("\n", $meta_tags) . "\n";
    }
}
add_action('wp_head', 'thrivingstudio_seo_meta_tags', 1);

/**
 * Get saved SEO options.
 *
 * @return array
 */
function thrivingstudio_get_seo_options() {
    $options = get_option('thrivingstudio_seo_options', []);

    return is_array($options) ? $options : [];
}

/**
 * Restrict robots choices to values the editor UI supports.
 *
 * @param string $robots_meta
 * @return string
 */
function thrivingstudio_sanitize_robots_meta($robots_meta) {
    $robots_meta = strtolower(sanitize_text_field((string) $robots_meta));
    $robots_meta = preg_replace('/\s*,\s*/', ',', $robots_meta);
    $robots_meta = trim($robots_meta, ', ');

    if ($robots_meta === '') {
        return '';
    }

    $allowed_tokens = [
        'index',
        'noindex',
        'follow',
        'nofollow',
        'noarchive',
        'nosnippet',
        'noimageindex',
        'max-snippet:-1',
        'max-image-preview:large',
        'max-video-preview:-1',
    ];
    $tokens = array_filter(explode(',', $robots_meta));
    $clean_tokens = [];

    foreach ($tokens as $token) {
        if (in_array($token, $allowed_tokens, true)) {
            $clean_tokens[] = $token;
        }
    }

    return implode(',', array_unique($clean_tokens));
}

/**
 * Register SEO fields so the block editor can save them through REST.
 */
function thrivingstudio_register_seo_post_meta() {
    $meta_fields = [
        '_thrivingstudio_seo_title'          => 'sanitize_text_field',
        '_thrivingstudio_meta_description'  => 'sanitize_textarea_field',
        '_thrivingstudio_focus_keyword'     => 'sanitize_text_field',
        '_thrivingstudio_canonical_url'     => 'esc_url_raw',
        '_thrivingstudio_robots_meta'       => 'thrivingstudio_sanitize_robots_meta',
        '_thrivingstudio_social_title'      => 'sanitize_text_field',
        '_thrivingstudio_social_description' => 'sanitize_textarea_field',
        '_thrivingstudio_social_image'      => 'esc_url_raw',
    ];

    foreach (['post', 'page'] as $post_type) {
        foreach ($meta_fields as $meta_key => $sanitize_callback) {
            register_post_meta(
                $post_type,
                $meta_key,
                [
                    'type'              => 'string',
                    'single'            => true,
                    'show_in_rest'      => true,
                    'sanitize_callback' => $sanitize_callback,
                    'auth_callback'     => function ($allowed = false, $meta_key = '', $post_id = 0) {
                        $post_id = (int) $post_id;

                        return $post_id > 0 ? current_user_can('edit_post', $post_id) : current_user_can('edit_posts');
                    },
                ]
            );
        }
    }
}
add_action('init', 'thrivingstudio_register_seo_post_meta');

/**
 * Load the SEO tab controls in the block editor.
 */
function thrivingstudio_enqueue_seo_editor_tab() {
    $script_path = THRIVINGSTUDIO_DIR . '/assets/js/editor-seo-tab.js';

    if (!file_exists($script_path)) {
        return;
    }

    wp_enqueue_script(
        'thrivingstudio-seo-editor-tab',
        THRIVINGSTUDIO_URI . '/assets/js/editor-seo-tab.js',
        [
            'wp-components',
            'wp-data',
            'wp-edit-post',
            'wp-element',
            'wp-i18n',
            'wp-plugins',
        ],
        filemtime($script_path),
        true
    );
}
add_action('enqueue_block_editor_assets', 'thrivingstudio_enqueue_seo_editor_tab');

/**
 * Brand-level SEO copy used when a page has no custom description.
 */
function thrivingstudio_get_default_meta_description() {
    $options = thrivingstudio_get_seo_options();
    $default_description = $options['default_description'] ?? '';

    if (!empty($default_description)) {
        return $default_description;
    }

    return 'Thriving Studio helps you cut through noise with clear, thoughtful ideas for inner growth, deeper understanding, and what truly matters.';
}

/**
 * Homepage title for search results and social previews.
 */
function thrivingstudio_get_homepage_title() {
    $options = thrivingstudio_get_seo_options();
    $homepage_title = $options['homepage_title'] ?? '';

    return $homepage_title ?: 'Thriving Studio | Clarity Over Noise';
}

/**
 * Homepage description for search results and social previews.
 */
function thrivingstudio_get_homepage_meta_description() {
    $options = thrivingstudio_get_seo_options();
    $homepage_description = $options['homepage_description'] ?? '';

    if (!empty($homepage_description)) {
        return $homepage_description;
    }

    $front_page_id = (int) get_option('page_on_front');
    $page_description = $front_page_id ? get_post_meta($front_page_id, '_thrivingstudio_meta_description', true) : '';

    return $page_description ?: thrivingstudio_get_default_meta_description();
}

/**
 * Optional alternate site name for structured data.
 */
function thrivingstudio_get_site_alternate_name() {
    $options = thrivingstudio_get_seo_options();
    $alternate_name = $options['site_alternate_name'] ?? '';

    return $alternate_name ?: 'ThrivingStudio';
}

/**
 * Normalize descriptions before outputting them into meta tags.
 *
 * @param string $description
 * @return string
 */
function thrivingstudio_normalize_meta_description($description) {
    $description = strip_shortcodes((string) $description);
    $description = wp_strip_all_tags($description);
    $description = preg_replace('/\s+/', ' ', $description);
    $description = trim($description);

    return wp_trim_words($description, 25, '...');
}

/**
 * Read the custom SEO description saved by the theme.
 *
 * @param int $post_id
 * @return string
 */
function thrivingstudio_get_post_meta_description($post_id) {
    $custom_description = get_post_meta($post_id, '_thrivingstudio_meta_description', true);
    if (!empty($custom_description)) {
        return $custom_description;
    }

    return '';
}

/**
 * Read the optional custom SEO title for a post or page.
 *
 * @param int $post_id
 * @return string
 */
function thrivingstudio_get_post_seo_title($post_id) {
    $seo_title = get_post_meta($post_id, '_thrivingstudio_seo_title', true);

    return trim(wp_strip_all_tags((string) $seo_title));
}

/**
 * Read an optional canonical URL override for a post or page.
 *
 * @param int $post_id
 * @return string
 */
function thrivingstudio_get_post_canonical_url($post_id) {
    $canonical_url = esc_url_raw((string) get_post_meta($post_id, '_thrivingstudio_canonical_url', true));

    return $canonical_url ?: '';
}

/**
 * Keep the static homepage title brand-forward instead of "Home".
 *
 * @param string $title
 * @return string
 */
function thrivingstudio_filter_document_title($title) {
    if (is_admin() || is_feed()) {
        return $title;
    }

    if (is_singular()) {
        $seo_title = thrivingstudio_get_post_seo_title(get_queried_object_id());

        if ($seo_title !== '') {
            return $seo_title;
        }
    }

    if (is_front_page()) {
        return thrivingstudio_get_homepage_title();
    }

    return $title;
}
add_filter('pre_get_document_title', 'thrivingstudio_filter_document_title', 20);

/**
 * Get meta description for current page
 */
function thrivingstudio_get_meta_description() {
    $description = '';

    if (is_front_page()) {
        $description = thrivingstudio_get_homepage_meta_description();
    } elseif (is_singular()) {
        $post_id = get_the_ID();
        $description = thrivingstudio_get_post_meta_description($post_id);
    } elseif (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');
        $description = $posts_page_id ? thrivingstudio_get_post_meta_description($posts_page_id) : '';
        if (!$description) {
            $description = get_bloginfo('description');
        }
    } elseif (is_category() || is_tag()) {
        $description = category_description();
        if (!$description) {
            $description = single_term_title('', false) . ' - ' . get_bloginfo('name');
        }
    } elseif (is_author()) {
        $author = get_queried_object();
        $description = get_the_author_meta('description', $author->ID);
    } elseif (is_search()) {
        $description = 'Search results for: ' . get_search_query();
    } elseif (is_404()) {
        $description = 'Page not found - ' . get_bloginfo('name');
    }

    return thrivingstudio_normalize_meta_description($description);
}

/**
 * Get canonical URL for current page
 */
function thrivingstudio_get_canonical_url() {
    global $wp;

    if (is_front_page()) {
        return home_url('/');
    } elseif (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');
        return $posts_page_id ? get_permalink($posts_page_id) : home_url('/');
    } elseif (is_singular()) {
        $custom_canonical = thrivingstudio_get_post_canonical_url(get_queried_object_id());

        if ($custom_canonical !== '') {
            return $custom_canonical;
        }

        return get_permalink();
    } elseif (is_category() || is_tag()) {
        return get_term_link(get_queried_object());
    } elseif (is_author()) {
        return get_author_posts_url(get_queried_object()->ID);
    } elseif (is_search()) {
        return home_url('/?s=' . urlencode(get_search_query()));
    }

    return home_url($wp->request);
}

/**
 * Redirect the front-page permalink, such as /home/, back to the canonical root.
 */
function thrivingstudio_redirect_front_page_permalink() {
    if (is_admin() || is_feed() || wp_doing_ajax()) {
        return;
    }

    $front_page_id = (int) get_option('page_on_front');
    if (!$front_page_id || !is_page($front_page_id)) {
        return;
    }

    $front_page_path = trim((string) wp_parse_url(get_permalink($front_page_id), PHP_URL_PATH), '/');
    $home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');
    $request_path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

    if ($home_path !== '' && strpos($request_path, $home_path . '/') === 0) {
        $request_path = substr($request_path, strlen($home_path) + 1);
        $front_page_path = preg_replace('#^' . preg_quote($home_path, '#') . '/?#', '', $front_page_path);
    }

    if ($front_page_path !== '' && untrailingslashit($request_path) === untrailingslashit($front_page_path)) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}
add_action('template_redirect', 'thrivingstudio_redirect_front_page_permalink', 1);

/**
 * Get robots meta content
 */
function thrivingstudio_get_robots_meta() {
    $robots = [];

    // Default: index, follow
    $robots[] = 'index';
    $robots[] = 'follow';

    // Noindex for specific pages
    if (is_404() || is_search()) {
        $robots = ['noindex', 'nofollow'];
    }

    // Check for custom robots meta
    if (is_singular()) {
        $custom_robots = get_post_meta(get_the_ID(), '_thrivingstudio_robots_meta', true);
        if ($custom_robots) {
            $robots = explode(',', $custom_robots);
        }
    }

    return implode(',', array_map('trim', $robots));
}

/**
 * Get Open Graph tags
 */
function thrivingstudio_get_open_graph_tags() {
    $og_tags = [];

    // Basic OG tags
    $og_tags[] = '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">';
    $og_tags[] = '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">';
    $og_tags[] = '<meta property="og:url" content="' . esc_url(thrivingstudio_get_canonical_url()) . '">';

    // Title
    $title = thrivingstudio_get_social_title();
    $og_tags[] = '<meta property="og:title" content="' . esc_attr($title) . '">';

    // Description
    $description = thrivingstudio_get_social_description();
    if ($description) {
        $og_tags[] = '<meta property="og:description" content="' . esc_attr($description) . '">';
    }

    // Image
    $image = thrivingstudio_get_og_image();
    if ($image) {
        $og_tags[] = '<meta property="og:image" content="' . esc_url($image) . '">';
        $og_tags[] = '<meta property="og:image:width" content="1200">';
        $og_tags[] = '<meta property="og:image:height" content="630">';
    }

    // Article specific tags
    if (is_singular('post')) {
        $og_tags[] = '<meta property="article:published_time" content="' . get_the_date('c') . '">';
        $og_tags[] = '<meta property="article:modified_time" content="' . get_the_modified_date('c') . '">';
        $og_tags[] = '<meta property="article:author" content="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">';
        
        // Categories
        $categories = get_the_category();
        if ($categories) {
            foreach ($categories as $category) {
                $og_tags[] = '<meta property="article:section" content="' . esc_attr($category->name) . '">';
            }
        }
    }

    return $og_tags;
}

/**
 * Get Twitter Card tags
 */
function thrivingstudio_get_twitter_card_tags() {
    $twitter_tags = [];

    $twitter_tags[] = '<meta name="twitter:card" content="summary_large_image">';

    $twitter_handle = thrivingstudio_get_twitter_handle();
    if ($twitter_handle) {
        $twitter_tags[] = '<meta name="twitter:site" content="' . esc_attr($twitter_handle) . '">';
    }

    // Title
    $title = thrivingstudio_get_social_title();
    $twitter_tags[] = '<meta name="twitter:title" content="' . esc_attr($title) . '">';

    // Description
    $description = thrivingstudio_get_social_description();
    if ($description) {
        $twitter_tags[] = '<meta name="twitter:description" content="' . esc_attr($description) . '">';
    }

    // Image
    $image = thrivingstudio_get_og_image();
    if ($image) {
        $twitter_tags[] = '<meta name="twitter:image" content="' . esc_url($image) . '">';
    }

    return $twitter_tags;
}

/**
 * Title used for social previews.
 */
function thrivingstudio_get_social_title() {
    if (is_singular()) {
        $post_id = get_queried_object_id();
        $social_title = trim(wp_strip_all_tags((string) get_post_meta($post_id, '_thrivingstudio_social_title', true)));

        if ($social_title !== '') {
            return $social_title;
        }

        $seo_title = thrivingstudio_get_post_seo_title($post_id);

        if ($seo_title !== '') {
            return $seo_title;
        }

        if (is_front_page()) {
            $options = thrivingstudio_get_seo_options();
            $homepage_social_title = $options['social_title'] ?? '';

            return $homepage_social_title ?: thrivingstudio_get_homepage_title();
        }

        return get_the_title($post_id);
    }

    if (is_front_page()) {
        $options = thrivingstudio_get_seo_options();
        $social_title = $options['social_title'] ?? '';

        return $social_title ?: thrivingstudio_get_homepage_title();
    }

    return get_bloginfo('name');
}

/**
 * Description used for social previews.
 */
function thrivingstudio_get_social_description() {
    if (is_singular()) {
        $social_description = get_post_meta(get_queried_object_id(), '_thrivingstudio_social_description', true);

        if (!empty($social_description)) {
            return thrivingstudio_normalize_meta_description($social_description);
        }

        if (is_front_page()) {
            $options = thrivingstudio_get_seo_options();
            $homepage_social_description = $options['social_description'] ?? '';

            return thrivingstudio_normalize_meta_description($homepage_social_description ?: thrivingstudio_get_homepage_meta_description());
        }
    }

    if (is_front_page()) {
        $options = thrivingstudio_get_seo_options();
        $social_description = $options['social_description'] ?? '';

        return thrivingstudio_normalize_meta_description($social_description ?: thrivingstudio_get_homepage_meta_description());
    }

    return thrivingstudio_get_meta_description();
}

/**
 * Get the configured X/Twitter handle, normalized for Twitter Card output.
 */
function thrivingstudio_get_twitter_handle() {
    $options = thrivingstudio_get_seo_options();
    $twitter = $options['social_media']['twitter'] ?? '';
    $twitter = trim((string) $twitter);

    if ($twitter === '') {
        return '';
    }

    $twitter = ltrim($twitter, '@');
    $twitter = preg_replace('/[^A-Za-z0-9_]/', '', $twitter);

    return $twitter ? '@' . $twitter : '';
}

/**
 * Get Open Graph image
 */
function thrivingstudio_get_og_image() {
    if (is_singular()) {
        $post_id = get_queried_object_id();
        $social_image = esc_url_raw((string) get_post_meta($post_id, '_thrivingstudio_social_image', true));

        if ($social_image !== '') {
            return $social_image;
        }
    }

    if (is_singular() && has_post_thumbnail()) {
        $image_id = get_post_thumbnail_id();
        $image_url = wp_get_attachment_image_src($image_id, 'large');
        return $image_url[0];
    }

    $options = thrivingstudio_get_seo_options();
    $social_image = $options['social_image'] ?? '';
    if (!empty($social_image)) {
        return $social_image;
    }

    // Check if default OG image exists, otherwise use a placeholder
    $default_og_image = get_template_directory() . '/assets/images/default-og-image.jpg';
    if (file_exists($default_og_image)) {
        return get_template_directory_uri() . '/assets/images/default-og-image.jpg';
    }
    
    // Fallback to a placeholder service or site logo
    $screenshot_webp = get_template_directory() . '/screenshot.webp';
    if (file_exists($screenshot_webp)) {
        return get_template_directory_uri() . '/screenshot.webp';
    }
    return get_template_directory_uri() . '/screenshot.png';
}

/**
 * Get logo URL with fallback
 */
function thrivingstudio_get_logo_url() {
    $options = thrivingstudio_get_seo_options();
    $structured_options = $options['structured_data'] ?? [];
    $custom_logo_url = $structured_options['logo_url'] ?? '';

    if (!empty($custom_logo_url)) {
        return $custom_logo_url;
    }

    $logo_path_png = get_template_directory() . '/assets/images/logo.png';
    if (file_exists($logo_path_png)) {
        return get_template_directory_uri() . '/assets/images/logo.png';
    }

    $logo_path_webp = get_template_directory() . '/assets/images/webp/logo.webp';
    if (file_exists($logo_path_webp)) {
        return get_template_directory_uri() . '/assets/images/webp/logo.webp';
    }
    
    // Fallback to site name as text logo
    return '';
}

/**
 * Add structured data (JSON-LD)
 */
function thrivingstudio_add_structured_data() {
    if (is_admin() || is_feed()) {
        return;
    }

    $structured_data = [];
    $site_description = thrivingstudio_normalize_meta_description(thrivingstudio_get_homepage_meta_description());
    $logo_url = thrivingstudio_get_logo_url();
    $options = thrivingstudio_get_seo_options();
    $structured_options = $options['structured_data'] ?? [];
    $organization_type = $structured_options['organization_type'] ?? 'Organization';
    $alternate_name = thrivingstudio_get_site_alternate_name();

    // Website schema
    $structured_data[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => home_url('/#website'),
        'name' => get_bloginfo('name'),
        'alternateName' => $alternate_name,
        'url' => home_url('/'),
        'description' => $site_description,
        'publisher' => [
            '@id' => home_url('/#organization')
        ],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => home_url('/?s={search_term_string}'),
            'query-input' => 'required name=search_term_string'
        ]
    ];

    // Organization schema
    $organization_data = [
        '@context' => 'https://schema.org',
        '@type' => $organization_type,
        '@id' => home_url('/#organization'),
        'name' => get_bloginfo('name'),
        'alternateName' => $alternate_name,
        'url' => home_url('/'),
        'description' => $site_description,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logo_url ?: (file_exists(get_template_directory() . '/screenshot.webp') ? get_template_directory_uri() . '/screenshot.webp' : get_template_directory_uri() . '/screenshot.png')
        ]
    ];

    if (!empty($structured_options['contact_email'])) {
        $organization_data['email'] = $structured_options['contact_email'];
    }

    if (!empty($structured_options['contact_phone'])) {
        $organization_data['telephone'] = $structured_options['contact_phone'];
    }

    $structured_data[] = $organization_data;

    // Article schema for single posts
    if (is_singular('post')) {
        $article_data = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title(),
            'url' => get_permalink(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author()
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $logo_url ?: (file_exists(get_template_directory() . '/screenshot.webp') ? get_template_directory_uri() . '/screenshot.webp' : get_template_directory_uri() . '/screenshot.png')
                ]
            ]
        ];

        // Add featured image
        if (has_post_thumbnail()) {
            $image_id = get_post_thumbnail_id();
            $image_url = wp_get_attachment_image_src($image_id, 'large');
            $article_data['image'] = $image_url[0];
        }

        // Add categories
        $categories = get_the_category();
        if ($categories) {
            $article_data['articleSection'] = $categories[0]->name;
        }

        $structured_data[] = $article_data;
    }

    // Add breadcrumb schema for better SEO
    if (is_singular() || is_category() || is_tag()) {
        $breadcrumb_data = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        $position = 1;
        
        // Home
        $breadcrumb_data['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => home_url('/')
        ];

        if (is_singular('post')) {
            // Category
            $categories = get_the_category();
            if ($categories) {
                $breadcrumb_data['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $categories[0]->name,
                    'item' => get_category_link($categories[0]->term_id)
                ];
            }
            
            // Post
            $breadcrumb_data['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => get_the_title(),
                'item' => get_permalink()
            ];
        } elseif (is_category()) {
            $category = get_queried_object();
            $breadcrumb_data['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $category->name,
                'item' => get_category_link($category->term_id)
            ];
        } elseif (is_tag()) {
            $tag = get_queried_object();
            $breadcrumb_data['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $tag->name,
                'item' => get_tag_link($tag->term_id)
            ];
        }

        $structured_data[] = $breadcrumb_data;
    }

    // Output structured data
    if (!empty($structured_data)) {
        echo "\n<!-- Structured Data -->\n";
        foreach ($structured_data as $data) {
            echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        }
    }
}
add_action('wp_head', 'thrivingstudio_add_structured_data', 2);

/**
 * Add favicon and app icons with fallbacks
 */
function thrivingstudio_add_favicon() {
    $theme_uri = get_template_directory_uri();
    $assets_path = get_template_directory() . '/assets/images';
    
    // Favicon
    if (file_exists($assets_path . '/favicon-48x48.png')) {
        echo '<link rel="icon" type="image/png" sizes="48x48" href="' . $theme_uri . '/assets/images/favicon-48x48.png">' . "\n";
    }

    if (file_exists($assets_path . '/favicon.ico')) {
        echo '<link rel="icon" type="image/x-icon" href="' . $theme_uri . '/assets/images/favicon.ico">' . "\n";
    }
    
    if (file_exists($assets_path . '/favicon-32x32.png')) {
        echo '<link rel="icon" type="image/png" sizes="32x32" href="' . $theme_uri . '/assets/images/favicon-32x32.png">' . "\n";
    }
    
    if (file_exists($assets_path . '/favicon-16x16.png')) {
        echo '<link rel="icon" type="image/png" sizes="16x16" href="' . $theme_uri . '/assets/images/favicon-16x16.png">' . "\n";
    }
    
    if (file_exists($assets_path . '/apple-touch-icon.png')) {
        echo '<link rel="apple-touch-icon" sizes="180x180" href="' . $theme_uri . '/assets/images/apple-touch-icon.png">' . "\n";
    }
    
    // Web manifest
    if (file_exists($assets_path . '/../site.webmanifest')) {
        echo '<link rel="manifest" href="' . $theme_uri . '/assets/site.webmanifest">' . "\n";
    }
}
add_action('wp_head', 'thrivingstudio_add_favicon', 1);

/**
 * Generate XML sitemap
 */
function thrivingstudio_generate_sitemap($force = false) {
    $sitemap_file = ABSPATH . 'sitemap.xml';
    $force = true === $force;
    
    // Only generate if file doesn't exist or is older than 24 hours
    if (!$force && file_exists($sitemap_file) && (time() - filemtime($sitemap_file)) < 86400) {
        return;
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Homepage
    $xml .= "\t<url>\n";
    $xml .= "\t\t<loc>" . home_url('/') . "</loc>\n";
    $xml .= "\t\t<lastmod>" . date('c') . "</lastmod>\n";
    $xml .= "\t\t<changefreq>daily</changefreq>\n";
    $xml .= "\t\t<priority>1.0</priority>\n";
    $xml .= "\t</url>\n";

    // Posts
    $posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => -1
    ]);

    foreach ($posts as $post) {
        $xml .= "\t<url>\n";
        $xml .= "\t\t<loc>" . get_permalink($post->ID) . "</loc>\n";
        $xml .= "\t\t<lastmod>" . get_the_modified_date('c', $post->ID) . "</lastmod>\n";
        $xml .= "\t\t<changefreq>weekly</changefreq>\n";
        $xml .= "\t\t<priority>0.8</priority>\n";
        $xml .= "\t</url>\n";
    }

    // Pages
    $front_page_id = (int) get_option('page_on_front');
    $pages = get_pages();
    foreach ($pages as $page) {
        if ((int) $page->ID === $front_page_id) {
            continue;
        }

        $xml .= "\t<url>\n";
        $xml .= "\t\t<loc>" . get_permalink($page->ID) . "</loc>\n";
        $xml .= "\t\t<lastmod>" . get_the_modified_date('c', $page->ID) . "</lastmod>\n";
        $xml .= "\t\t<changefreq>monthly</changefreq>\n";
        $xml .= "\t\t<priority>0.6</priority>\n";
        $xml .= "\t</url>\n";
    }

    // Categories
    $categories = get_categories();
    foreach ($categories as $category) {
        $xml .= "\t<url>\n";
        $xml .= "\t\t<loc>" . get_category_link($category->term_id) . "</loc>\n";
        $xml .= "\t\t<lastmod>" . date('c') . "</lastmod>\n";
        $xml .= "\t\t<changefreq>weekly</changefreq>\n";
        $xml .= "\t\t<priority>0.5</priority>\n";
        $xml .= "\t</url>\n";
    }

    $xml .= '</urlset>';

    file_put_contents($sitemap_file, $xml);
}

// Generate sitemap on post publish/update
add_action('publish_post', 'thrivingstudio_generate_sitemap');
add_action('publish_page', 'thrivingstudio_generate_sitemap');

/**
 * Refresh the sitemap once after SEO URL handling changes are deployed.
 */
function thrivingstudio_maybe_refresh_seo_sitemap() {
    $sitemap_version = 'homepage-canonical-v1';

    if (get_option('thrivingstudio_sitemap_version') === $sitemap_version) {
        return;
    }

    thrivingstudio_generate_sitemap(true);
    update_option('thrivingstudio_sitemap_version', $sitemap_version, false);
}
add_action('init', 'thrivingstudio_maybe_refresh_seo_sitemap', 20);

/**
 * Add meta boxes for SEO
 */
function thrivingstudio_add_seo_meta_boxes() {
    add_meta_box(
        'thrivingstudio-seo-meta-box',
        'SEO Settings',
        'thrivingstudio_seo_meta_box_callback',
        ['post', 'page'],
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'thrivingstudio_add_seo_meta_boxes');

/**
 * SEO meta box callback
 */
function thrivingstudio_seo_meta_box_callback($post) {
    if (!current_user_can('edit_post', $post->ID)) {
        wp_die('Insufficient permissions');
    }
    wp_nonce_field('thrivingstudio_seo_meta_box', 'thrivingstudio_seo_meta_box_nonce');

    $seo_title = get_post_meta($post->ID, '_thrivingstudio_seo_title', true);
    $meta_description = get_post_meta($post->ID, '_thrivingstudio_meta_description', true);
    $focus_keyword = get_post_meta($post->ID, '_thrivingstudio_focus_keyword', true);
    $canonical_url = get_post_meta($post->ID, '_thrivingstudio_canonical_url', true);
    $robots_meta = get_post_meta($post->ID, '_thrivingstudio_robots_meta', true);
    $social_title = get_post_meta($post->ID, '_thrivingstudio_social_title', true);
    $social_description = get_post_meta($post->ID, '_thrivingstudio_social_description', true);
    $social_image = get_post_meta($post->ID, '_thrivingstudio_social_image', true);
    $has_meta_description = trim((string) $meta_description) !== '';
    $front_page_id = (int) get_option('page_on_front');
    $posts_page_id = (int) get_option('page_for_posts');
    $empty_meta_description_notice = '';

    if (!$has_meta_description) {
        if ($front_page_id === (int) $post->ID) {
            $empty_meta_description_notice = __('Homepage SEO settings or the default site description will be used unless this field is filled.', 'thrivingstudio');
        } elseif ($posts_page_id === (int) $post->ID) {
            $empty_meta_description_notice = __('The site tagline may be used for the blog index unless this field is filled.', 'thrivingstudio');
        } else {
            $empty_meta_description_notice = __('No meta description will be output for this post/page until this field is filled.', 'thrivingstudio');
        }
    }
    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="thrivingstudio_seo_title">SEO Title</label>
            </th>
            <td>
                <input type="text" id="thrivingstudio_seo_title" name="thrivingstudio_seo_title" value="<?php echo esc_attr($seo_title); ?>" class="regular-text" />
                <p class="description">Optional search title. Leave empty to use the post title.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="thrivingstudio_meta_description">Meta Description</label>
            </th>
            <td>
                <textarea id="thrivingstudio_meta_description" name="thrivingstudio_meta_description" rows="3" cols="50" style="width: 100%;"><?php echo esc_textarea($meta_description); ?></textarea>
                <p class="description">Manual field only. Leave empty to output no meta description for this post/page. Maximum 160 characters recommended.</p>
                <?php if ($empty_meta_description_notice !== '') : ?>
                    <p class="description" style="color: #8a5a00;"><strong><?php esc_html_e('Heads up:', 'thrivingstudio'); ?></strong> <?php echo esc_html($empty_meta_description_notice); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="thrivingstudio_focus_keyword">Focus Keyword</label>
            </th>
            <td>
                <input type="text" id="thrivingstudio_focus_keyword" name="thrivingstudio_focus_keyword" value="<?php echo esc_attr($focus_keyword); ?>" class="regular-text" />
                <p class="description">Editorial reference only. This is not output as a meta keywords tag.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="thrivingstudio_canonical_url">Canonical URL</label>
            </th>
            <td>
                <input type="url" id="thrivingstudio_canonical_url" name="thrivingstudio_canonical_url" value="<?php echo esc_attr($canonical_url); ?>" class="regular-text" />
                <p class="description">Optional override. Leave empty to use the post permalink.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="thrivingstudio_robots_meta">Robots Meta</label>
            </th>
            <td>
                <input type="text" id="thrivingstudio_robots_meta" name="thrivingstudio_robots_meta" value="<?php echo esc_attr($robots_meta); ?>" class="regular-text" />
                <p class="description">e.g., noindex,nofollow or index,follow (default)</p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="thrivingstudio_social_title">Social Title</label>
            </th>
            <td>
                <input type="text" id="thrivingstudio_social_title" name="thrivingstudio_social_title" value="<?php echo esc_attr($social_title); ?>" class="regular-text" />
                <p class="description">Optional Open Graph and Twitter title. Leave empty to use the SEO title or post title.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="thrivingstudio_social_description">Social Description</label>
            </th>
            <td>
                <textarea id="thrivingstudio_social_description" name="thrivingstudio_social_description" rows="3" cols="50" style="width: 100%;"><?php echo esc_textarea($social_description); ?></textarea>
                <p class="description">Optional Open Graph and Twitter description. Leave empty to use the meta description.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="thrivingstudio_social_image">Social Image URL</label>
            </th>
            <td>
                <input type="url" id="thrivingstudio_social_image" name="thrivingstudio_social_image" value="<?php echo esc_attr($social_image); ?>" class="regular-text" />
                <p class="description">Optional Open Graph and Twitter image. Leave empty to use the featured image or site default.</p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Save SEO meta box data
 */
function thrivingstudio_save_seo_meta_box_data($post_id) {
    if (!isset($_POST['thrivingstudio_seo_meta_box_nonce']) || !wp_verify_nonce($_POST['thrivingstudio_seo_meta_box_nonce'], 'thrivingstudio_seo_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $field_map = [
        'thrivingstudio_seo_title'          => ['_thrivingstudio_seo_title', 'sanitize_text_field'],
        'thrivingstudio_meta_description'  => ['_thrivingstudio_meta_description', 'sanitize_textarea_field'],
        'thrivingstudio_focus_keyword'     => ['_thrivingstudio_focus_keyword', 'sanitize_text_field'],
        'thrivingstudio_canonical_url'     => ['_thrivingstudio_canonical_url', 'esc_url_raw'],
        'thrivingstudio_robots_meta'       => ['_thrivingstudio_robots_meta', 'thrivingstudio_sanitize_robots_meta'],
        'thrivingstudio_social_title'      => ['_thrivingstudio_social_title', 'sanitize_text_field'],
        'thrivingstudio_social_description' => ['_thrivingstudio_social_description', 'sanitize_textarea_field'],
        'thrivingstudio_social_image'      => ['_thrivingstudio_social_image', 'esc_url_raw'],
    ];

    foreach ($field_map as $field_name => $field_config) {
        if (!isset($_POST[$field_name])) {
            continue;
        }

        $meta_key = $field_config[0];
        $sanitize_callback = $field_config[1];

        update_post_meta($post_id, $meta_key, call_user_func($sanitize_callback, wp_unslash($_POST[$field_name])));
    }
}
add_action('save_post', 'thrivingstudio_save_seo_meta_box_data');
