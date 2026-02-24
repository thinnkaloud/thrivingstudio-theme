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
 * Get meta description for current page
 */
function thrivingstudio_get_meta_description() {
    $description = '';

    if (is_singular()) {
        // Check for custom meta description
        $custom_description = get_post_meta(get_the_ID(), '_thrivingstudio_meta_description', true);
        if ($custom_description) {
            $description = $custom_description;
        } else {
            // Use excerpt or generate from content
            $description = get_the_excerpt();
            if (!$description) {
                $content = get_the_content();
                $description = wp_trim_words(strip_tags($content), 25, '...');
            }
        }
    } elseif (is_home()) {
        $description = get_bloginfo('description');
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

    return wp_trim_words($description, 25, '...');
}

/**
 * Get canonical URL for current page
 */
function thrivingstudio_get_canonical_url() {
    global $wp;

    if (is_home()) {
        return home_url('/');
    } elseif (is_singular()) {
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
    $og_tags[] = '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">';
    $og_tags[] = '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">';
    $og_tags[] = '<meta property="og:url" content="' . esc_url(thrivingstudio_get_canonical_url()) . '">';

    // Title
    $title = is_singular() ? get_the_title() : get_bloginfo('name');
    $og_tags[] = '<meta property="og:title" content="' . esc_attr($title) . '">';

    // Description
    $description = thrivingstudio_get_meta_description();
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
    $twitter_tags[] = '<meta name="twitter:site" content="@' . esc_attr(get_bloginfo('name')) . '">';

    // Title
    $title = is_singular() ? get_the_title() : get_bloginfo('name');
    $twitter_tags[] = '<meta name="twitter:title" content="' . esc_attr($title) . '">';

    // Description
    $description = thrivingstudio_get_meta_description();
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
 * Get Open Graph image
 */
function thrivingstudio_get_og_image() {
    if (is_singular() && has_post_thumbnail()) {
        $image_id = get_post_thumbnail_id();
        $image_url = wp_get_attachment_image_src($image_id, 'large');
        return $image_url[0];
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
    $logo_path = get_template_directory() . '/assets/images/logo.webp';
    if (file_exists($logo_path)) {
        return get_template_directory_uri() . '/assets/images/logo.webp';
    }
    
    $logo_path_png = get_template_directory() . '/assets/images/logo.png';
    if (file_exists($logo_path_png)) {
        return get_template_directory_uri() . '/assets/images/logo.png';
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

    // Website schema
    $structured_data[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
        'description' => get_bloginfo('description'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => home_url('/?s={search_term_string}'),
            'query-input' => 'required name=search_term_string'
        ]
    ];

    // Organization schema
    $logo_url = thrivingstudio_get_logo_url();
    $structured_data[] = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
        'logo' => $logo_url ?: (file_exists(get_template_directory() . '/screenshot.webp') ? get_template_directory_uri() . '/screenshot.webp' : get_template_directory_uri() . '/screenshot.png')
    ];

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
    if (file_exists($assets_path . '/favicon.ico')) {
        echo '<link rel="icon" type="image/x-icon" href="' . $theme_uri . '/assets/images/favicon.ico">' . "\n";
    }
    
    // WebP favicons with PNG fallbacks
    if (file_exists($assets_path . '/favicon-32x32.webp')) {
        echo '<link rel="icon" type="image/webp" sizes="32x32" href="' . $theme_uri . '/assets/images/favicon-32x32.webp">' . "\n";
    } elseif (file_exists($assets_path . '/favicon-32x32.png')) {
        echo '<link rel="icon" type="image/png" sizes="32x32" href="' . $theme_uri . '/assets/images/favicon-32x32.png">' . "\n";
    }
    
    if (file_exists($assets_path . '/favicon-16x16.webp')) {
        echo '<link rel="icon" type="image/webp" sizes="16x16" href="' . $theme_uri . '/assets/images/favicon-16x16.webp">' . "\n";
    } elseif (file_exists($assets_path . '/favicon-16x16.png')) {
        echo '<link rel="icon" type="image/png" sizes="16x16" href="' . $theme_uri . '/assets/images/favicon-16x16.png">' . "\n";
    }
    
    // Apple touch icon with WebP support
    if (file_exists($assets_path . '/apple-touch-icon.webp')) {
        echo '<link rel="apple-touch-icon" sizes="180x180" href="' . $theme_uri . '/assets/images/apple-touch-icon.webp">' . "\n";
    } elseif (file_exists($assets_path . '/apple-touch-icon.png')) {
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
function thrivingstudio_generate_sitemap() {
    $sitemap_file = ABSPATH . 'sitemap.xml';
    
    // Only generate if file doesn't exist or is older than 24 hours
    if (file_exists($sitemap_file) && (time() - filemtime($sitemap_file)) < 86400) {
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
    $pages = get_pages();
    foreach ($pages as $page) {
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

    $meta_description = get_post_meta($post->ID, '_thrivingstudio_meta_description', true);
    $robots_meta = get_post_meta($post->ID, '_thrivingstudio_robots_meta', true);
    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="thrivingstudio_meta_description">Meta Description</label>
            </th>
            <td>
                <textarea id="thrivingstudio_meta_description" name="thrivingstudio_meta_description" rows="3" cols="50" style="width: 100%;"><?php echo esc_textarea($meta_description); ?></textarea>
                <p class="description">Leave empty to auto-generate from content. Maximum 160 characters recommended.</p>
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

    if (isset($_POST['thrivingstudio_meta_description'])) {
        update_post_meta($post_id, '_thrivingstudio_meta_description', sanitize_textarea_field($_POST['thrivingstudio_meta_description']));
    }

    if (isset($_POST['thrivingstudio_robots_meta'])) {
        update_post_meta($post_id, '_thrivingstudio_robots_meta', sanitize_text_field($_POST['thrivingstudio_robots_meta']));
    }
}
add_action('save_post', 'thrivingstudio_save_seo_meta_box_data'); 