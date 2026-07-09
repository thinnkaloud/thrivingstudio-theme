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
    $post_meta_fields = [
        '_thrivingstudio_article_subtitle' => 'sanitize_textarea_field',
    ];

    foreach (['post', 'page', 'quote_card'] as $post_type) {
        $post_type_meta_fields = $post_type === 'post' ? array_merge($post_meta_fields, $meta_fields) : $meta_fields;

        foreach ($post_type_meta_fields as $meta_key => $sanitize_callback) {
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
 * Build a useful fallback description for individual quote card pages.
 *
 * @param int $post_id
 * @return string
 */
function thrivingstudio_get_quote_card_meta_description($post_id) {
    $caption = get_post_meta($post_id, '_quote_card_caption', true);

    if (!empty($caption)) {
        return $caption;
    }

    $title = get_the_title($post_id);
    $author = trim((string) get_post_meta($post_id, '_quote_card_author', true));

    if ($author !== '') {
        return sprintf('Quote card featuring "%1$s" by %2$s from %3$s.', $title, $author, get_bloginfo('name'));
    }

    return sprintf('Quote card featuring "%1$s" from %2$s.', $title, get_bloginfo('name'));
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
 * Append the site name to a route-level title when it is not already present.
 *
 * @param string $title
 * @return string
 */
function thrivingstudio_append_site_name_to_title($title) {
    $title = trim(wp_strip_all_tags((string) $title));
    $site_name = trim((string) get_bloginfo('name'));

    if ($title === '' || $site_name === '') {
        return $title ?: $site_name;
    }

    if (stripos($title, $site_name) !== false) {
        return $title;
    }

    return sprintf('%1$s | %2$s', $title, $site_name);
}

/**
 * Remove WordPress archive prefixes from titles before using them in SEO copy.
 *
 * @param string $title
 * @return string
 */
function thrivingstudio_clean_archive_title($title) {
    $title = wp_strip_all_tags((string) $title);
    $title = html_entity_decode($title, ENT_QUOTES, get_bloginfo('charset'));
    $title = preg_replace('/^\s*(Category|Tag|Author|Year|Month|Day|Archives):\s*/i', '', $title);

    return trim((string) $title);
}

/**
 * SEO copy for virtual account routes that do not have saved post metadata.
 *
 * @return array<string, string>
 */
function thrivingstudio_get_account_route_seo_copy() {
    if (!function_exists('thrivingstudio_account_request_path_matches')) {
        return [];
    }

    if (thrivingstudio_account_request_path_matches('profile')) {
        return [
            'title'       => __('Profile', 'thrivingstudio'),
            'description' => __('Manage your Thriving Studio reader profile, public identity, comment history, and account settings from one focused place.', 'thrivingstudio'),
            'canonical'   => home_url('/profile/'),
        ];
    }

    if (thrivingstudio_account_request_path_matches('sign-in')) {
        return [
            'title'       => __('Sign in', 'thrivingstudio'),
            'description' => __('Sign in or create a Thriving Studio reader profile to keep your avatar, comments, and account access connected across the site.', 'thrivingstudio'),
            'canonical'   => home_url('/sign-in/'),
        ];
    }

    return [];
}

/**
 * Human-readable label for date archives.
 *
 * @return string
 */
function thrivingstudio_get_date_archive_label() {
    if (is_year()) {
        return (string) get_query_var('year');
    }

    if (is_month()) {
        $year = (int) get_query_var('year');
        $month = (int) get_query_var('monthnum');

        if ($year && $month) {
            return date_i18n('F Y', mktime(0, 0, 0, $month, 1, $year));
        }
    }

    if (is_day()) {
        $year = (int) get_query_var('year');
        $month = (int) get_query_var('monthnum');
        $day = (int) get_query_var('day');

        if ($year && $month && $day) {
            return date_i18n(get_option('date_format'), mktime(0, 0, 0, $month, $day, $year));
        }
    }

    return thrivingstudio_clean_archive_title(get_the_archive_title());
}

/**
 * Build a sensible route-level title for non-singular pages and virtual routes.
 *
 * @param bool $include_site_name
 * @return string
 */
function thrivingstudio_get_contextual_seo_title($include_site_name = false) {
    $account_copy = thrivingstudio_get_account_route_seo_copy();

    if (!empty($account_copy['title'])) {
        $title = $account_copy['title'];

        return $include_site_name ? thrivingstudio_append_site_name_to_title($title) : $title;
    }

    if (is_front_page()) {
        return thrivingstudio_get_homepage_title();
    }

    if (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');
        $title = $posts_page_id ? thrivingstudio_get_post_seo_title($posts_page_id) : '';

        if ($title === '' && $posts_page_id) {
            $title = get_the_title($posts_page_id);
        }

        $title = $title ?: __('Blog', 'thrivingstudio');

        return $include_site_name ? thrivingstudio_append_site_name_to_title($title) : $title;
    }

    if (is_post_type_archive('quote_card')) {
        $title = __('Quote Cards', 'thrivingstudio');

        return $include_site_name ? thrivingstudio_append_site_name_to_title($title) : $title;
    }

    if (is_post_type_archive()) {
        $title = thrivingstudio_clean_archive_title(post_type_archive_title('', false));

        return $include_site_name ? thrivingstudio_append_site_name_to_title($title) : $title;
    }

    if (is_category() || is_tag() || is_tax()) {
        $title = thrivingstudio_clean_archive_title(single_term_title('', false));

        return $include_site_name ? thrivingstudio_append_site_name_to_title($title) : $title;
    }

    if (is_author()) {
        $author = get_queried_object();
        $title = $author instanceof WP_User ? $author->display_name : get_the_author_meta('display_name');

        return $include_site_name ? thrivingstudio_append_site_name_to_title($title) : $title;
    }

    if (is_search()) {
        $query = trim((string) get_search_query());
        $title = $query !== ''
            ? sprintf(__('Search Results for "%s"', 'thrivingstudio'), $query)
            : __('Search Results', 'thrivingstudio');

        return $include_site_name ? thrivingstudio_append_site_name_to_title($title) : $title;
    }

    if (is_404()) {
        $title = __('Page Not Found', 'thrivingstudio');

        return $include_site_name ? thrivingstudio_append_site_name_to_title($title) : $title;
    }

    if (is_date()) {
        $title = thrivingstudio_get_date_archive_label();

        return $include_site_name ? thrivingstudio_append_site_name_to_title($title) : $title;
    }

    if (is_archive()) {
        $title = thrivingstudio_clean_archive_title(get_the_archive_title());

        return $include_site_name ? thrivingstudio_append_site_name_to_title($title) : $title;
    }

    return '';
}

/**
 * Build a strong fallback description for taxonomy archives.
 *
 * @param WP_Term|null $term
 * @return string
 */
function thrivingstudio_get_term_archive_meta_description($term = null) {
    if (!($term instanceof WP_Term)) {
        $term = get_queried_object();
    }

    if (!($term instanceof WP_Term)) {
        return '';
    }

    $term_description = term_description($term);

    if (!empty($term_description)) {
        return $term_description;
    }

    $term_name = thrivingstudio_clean_archive_title($term->name);

    if ($term->taxonomy === 'post_tag') {
        return sprintf(
            __('Browse Thriving Studio articles tagged %s, gathering related ideas on clarity, growth, creativity, and thoughtful living.', 'thrivingstudio'),
            $term_name
        );
    }

    return sprintf(
        __('Explore %s articles from Thriving Studio, with thoughtful stories, research, and practical ideas for clearer thinking, deeper growth, and creative living.', 'thrivingstudio'),
        $term_name
    );
}

/**
 * Build route-level descriptions for archives, virtual pages, and utility routes.
 *
 * @return string
 */
function thrivingstudio_get_contextual_meta_description() {
    $account_copy = thrivingstudio_get_account_route_seo_copy();

    if (!empty($account_copy['description'])) {
        return $account_copy['description'];
    }

    if (is_post_type_archive('quote_card')) {
        return __('Browse Thriving Studio quote cards: visual excerpts, clear attribution where available, and shareable ideas for reflection, creativity, and growth.', 'thrivingstudio');
    }

    if (is_post_type_archive()) {
        $title = thrivingstudio_get_contextual_seo_title(false);

        return sprintf(
            __('Browse %s from Thriving Studio, with thoughtful ideas, stories, and research for clearer thinking and meaningful growth.', 'thrivingstudio'),
            $title
        );
    }

    if (is_category() || is_tag() || is_tax()) {
        return thrivingstudio_get_term_archive_meta_description();
    }

    if (is_author()) {
        $author = get_queried_object();
        $author_name = $author instanceof WP_User ? $author->display_name : get_the_author_meta('display_name');
        $author_description = $author instanceof WP_User ? get_the_author_meta('description', $author->ID) : '';

        if (!empty($author_description)) {
            return $author_description;
        }

        return sprintf(
            __('Read articles by %s on Thriving Studio, covering clarity, personal growth, creativity, research, and stories worth returning to.', 'thrivingstudio'),
            $author_name ?: get_bloginfo('name')
        );
    }

    if (is_date()) {
        return sprintf(
            __('Browse Thriving Studio articles from %s, with essays and stories on clarity, growth, creativity, wellbeing, and meaningful progress.', 'thrivingstudio'),
            thrivingstudio_get_date_archive_label()
        );
    }

    if (is_search()) {
        $query = trim((string) get_search_query());

        if ($query !== '') {
            return sprintf(
                __('Search results for "%s" on Thriving Studio, with related essays, ideas, and stories from the archive.', 'thrivingstudio'),
                $query
            );
        }

        return __('Search Thriving Studio for essays, ideas, and stories on clarity, growth, creativity, wellbeing, and meaningful progress.', 'thrivingstudio');
    }

    if (is_404()) {
        return __('This Thriving Studio page could not be found. Search or browse essays on clarity, growth, creativity, wellbeing, and meaningful progress.', 'thrivingstudio');
    }

    if (is_archive()) {
        $title = thrivingstudio_get_contextual_seo_title(false);

        return sprintf(
            __('Browse %s on Thriving Studio, with thoughtful ideas, stories, and research for clearer thinking and meaningful growth.', 'thrivingstudio'),
            $title ?: __('the archive', 'thrivingstudio')
        );
    }

    return '';
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

    $account_copy = thrivingstudio_get_account_route_seo_copy();

    if (!empty($account_copy['title'])) {
        return thrivingstudio_append_site_name_to_title($account_copy['title']);
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
    $contextual_description = thrivingstudio_get_contextual_meta_description();

    if ($contextual_description !== '') {
        $description = $contextual_description;
    } elseif (is_front_page()) {
        $description = thrivingstudio_get_homepage_meta_description();
    } elseif (is_singular()) {
        $post_id = get_the_ID();
        $description = thrivingstudio_get_post_meta_description($post_id);

        if (!$description && is_singular('quote_card')) {
            $description = thrivingstudio_get_quote_card_meta_description($post_id);
        }
    } elseif (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');
        $description = $posts_page_id ? thrivingstudio_get_post_meta_description($posts_page_id) : '';

        if (!$description) {
            $description = __('Read the latest Thriving Studio essays on clarity, personal growth, psychology, creativity, and progress, curated to help you think and live with intention.', 'thrivingstudio');
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

    $account_copy = thrivingstudio_get_account_route_seo_copy();

    if (!empty($account_copy['canonical'])) {
        return $account_copy['canonical'];
    }

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

    $request_path = isset($wp->request) ? trim((string) $wp->request, '/') : '';

    return $request_path !== '' ? home_url(user_trailingslashit($request_path)) : home_url('/');
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
    $og_tags[] = '<meta property="og:type" content="' . (is_singular(['post', 'quote_card']) ? 'article' : 'website') . '">';
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
        $image_dimensions = thrivingstudio_get_og_image_dimensions();
        $og_tags[] = '<meta property="og:image:width" content="' . esc_attr($image_dimensions['width'] ?? 1200) . '">';
        $og_tags[] = '<meta property="og:image:height" content="' . esc_attr($image_dimensions['height'] ?? 630) . '">';
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
    $account_copy = thrivingstudio_get_account_route_seo_copy();

    if (!empty($account_copy['title'])) {
        return thrivingstudio_append_site_name_to_title($account_copy['title']);
    }

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

        if (get_post_type($post_id) === 'quote_card') {
            $quote_author = trim((string) get_post_meta($post_id, '_quote_card_author', true));

            if ($quote_author !== '') {
                return sprintf('%1$s - %2$s', get_the_title($post_id), $quote_author);
            }
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

    $contextual_title = thrivingstudio_get_contextual_seo_title(true);

    if ($contextual_title !== '') {
        return $contextual_title;
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

        if (is_singular('quote_card')) {
            return thrivingstudio_normalize_meta_description(thrivingstudio_get_quote_card_meta_description(get_queried_object_id()));
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
 * Get Open Graph image dimensions when the image comes from the current post thumbnail.
 *
 * @return array
 */
function thrivingstudio_get_og_image_dimensions() {
    if (is_singular() && has_post_thumbnail()) {
        $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');

        if ($image && !empty($image[1]) && !empty($image[2])) {
            return [
                'width' => (int) $image[1],
                'height' => (int) $image[2],
            ];
        }
    }

    return [];
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

    // CreativeWork schema for individual quote cards
    if (is_singular('quote_card')) {
        $post_id = get_queried_object_id();
        $quote_author = trim((string) get_post_meta($post_id, '_quote_card_author', true));
        $quote_description = thrivingstudio_normalize_meta_description(thrivingstudio_get_quote_card_meta_description($post_id));
        $quote_source_title = trim((string) get_post_meta($post_id, '_quote_card_source_title', true));
        $quote_source_name = trim((string) get_post_meta($post_id, '_quote_card_source_name', true));
        $quote_source_url = esc_url_raw((string) get_post_meta($post_id, '_quote_card_source_url', true));

        $quote_data = [
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'headline' => get_the_title($post_id),
            'text' => get_the_title($post_id),
            'description' => $quote_description,
            'url' => get_permalink($post_id),
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            'publisher' => [
                '@id' => home_url('/#organization')
            ],
        ];

        if ($quote_author !== '') {
            $quote_data['author'] = [
                '@type' => 'Person',
                'name' => $quote_author,
            ];
        }

        if (has_post_thumbnail($post_id)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');

            if ($image) {
                $quote_data['image'] = [
                    '@type' => 'ImageObject',
                    'url' => $image[0],
                    'width' => (int) $image[1],
                    'height' => (int) $image[2],
                ];
            }
        }

        if ($quote_source_url) {
            $quote_data['citation'] = [
                '@type' => 'CreativeWork',
                'name' => $quote_source_title ?: ($quote_source_name ?: $quote_source_url),
                'url' => $quote_source_url,
            ];

            if ($quote_source_name) {
                $quote_data['citation']['publisher'] = [
                    '@type' => 'Organization',
                    'name' => $quote_source_name,
                ];
            }
        }

        $structured_data[] = $quote_data;
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
        } elseif (is_singular('quote_card')) {
            $quote_archive_link = get_post_type_archive_link('quote_card') ?: home_url('/quotecards/');

            $breadcrumb_data['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => 'Quote Cards',
                'item' => $quote_archive_link
            ];

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
    $article_subtitle = get_post_meta($post->ID, '_thrivingstudio_article_subtitle', true);
    if ($article_subtitle === '' && !metadata_exists('post', $post->ID, '_thrivingstudio_article_subtitle')) {
        $article_subtitle = trim((string) $post->post_excerpt);
    }
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
            $empty_meta_description_notice = __('The curated blog index description will be used unless this field is filled.', 'thrivingstudio');
        } else {
            $empty_meta_description_notice = __('No meta description will be output for this post/page until this field is filled.', 'thrivingstudio');
        }
    }
    ?>
    <table class="form-table">
        <?php if ($post->post_type === 'post') : ?>
            <tr>
                <th scope="row">
                    <label for="thrivingstudio_article_subtitle">Subtitle Under Title</label>
                </th>
                <td>
                    <textarea id="thrivingstudio_article_subtitle" name="thrivingstudio_article_subtitle" rows="3" cols="50" style="width: 100%;"><?php echo esc_textarea($article_subtitle); ?></textarea>
                    <p class="description">Shown below the H1 on the article page and reused on blog cards.</p>
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <th scope="row">
                <label for="thrivingstudio_seo_title">Meta Title (SEO Title)</label>
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

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $field_map = [
        'thrivingstudio_article_subtitle'   => ['_thrivingstudio_article_subtitle', 'sanitize_textarea_field'],
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
