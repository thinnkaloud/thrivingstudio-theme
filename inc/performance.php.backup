<?php
/**
 * Performance optimization module for Thriving Studio theme
 * Handles asset optimization, caching, and performance improvements
 */

/**
 * Remove unnecessary WordPress scripts and styles
 */
function remove_unnecessary_assets() {
    // Remove emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    
    // Remove embed script
    wp_deregister_script('wp-embed');
    
    // Remove jQuery migrate if not needed
    if (!is_admin() && !in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php'])) {
        wp_deregister_script('jquery-migrate');
    }
}
add_action('init', 'remove_unnecessary_assets');

/**
 * Conditionally load comments script only when needed
 */
function conditional_comments_script() {
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'conditional_comments_script');

/**
 * Remove width and height attributes from images, but preserve aspect ratios
 * Note: JPEG quality optimization is handled in functions.php
 */
function remove_image_dimensions($html) {
    // Only remove dimensions for non-post thumbnails to preserve layout
    if (strpos($html, 'wp-post-image') === false) {
        return preg_replace('/\s+(width|height)="[^"]*"/', '', $html);
    }
    return $html;
}

// Apply dimension removal to post thumbnails and editor images
add_filter('post_thumbnail_html', 'remove_image_dimensions', 10);
add_filter('image_send_to_editor', 'remove_image_dimensions', 10);

/**
 * Add preload tags for critical resources (simplified)
 */
function add_preload_tags() {
    // Only preload hero image on homepage if it exists
    if (is_front_page() && has_post_thumbnail()) {
        $hero_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'hero-large');
        if ($hero_image && strpos($hero_image[0], 'Untitled-design.jpg') === false) {
            echo '<link rel="preload" href="' . $hero_image[0] . '" as="image">';
        }
    }
    
    // Preload first blog post image on blog page for faster loading
    if (is_home() || is_archive()) {
        $first_post = get_posts(['numberposts' => 1, 'post_status' => 'publish']);
        if (!empty($first_post) && has_post_thumbnail($first_post[0]->ID)) {
            $first_image = wp_get_attachment_image_src(get_post_thumbnail_id($first_post[0]->ID), 'medium');
            if ($first_image) {
                echo '<link rel="preload" href="' . $first_image[0] . '" as="image" fetchpriority="high">';
            }
        }
    }
}
add_action('wp_head', 'add_preload_tags', 1);

/**
 * Add async/defer attributes to non-critical scripts
 */
function add_async_defer_attributes($tag, $handle) {
    // Add async to non-critical scripts
    $async_scripts = ['thrivingstudio-js'];
    
    // Don't defer WPForms scripts as they need to load immediately
    $wpforms_scripts = ['wpforms', 'wpforms-utils', 'wpforms-frontend'];
    
    if (in_array($handle, $async_scripts) && !in_array($handle, $wpforms_scripts)) {
        // Use defer instead of async for better performance
        return str_replace(' src', ' defer src', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'add_async_defer_attributes', 10, 2);

/**
 * Optimize JPEG image quality
 */
function optimize_image_quality() {
    return 85; // Balanced quality setting
}
add_filter('jpeg_quality', 'optimize_image_quality');
add_filter('wp_editor_set_quality', 'optimize_image_quality');

/**
 * Add WebP support
 */
function add_webp_support($mimes) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter('upload_mimes', 'add_webp_support');

/**
 * Add responsive image support
 */
function add_responsive_image_support() {
    add_theme_support('responsive-embeds');
    add_theme_support('post-thumbnails');
    
    // Add custom image sizes
    add_image_size('hero-large', 1920, 1080, true);
    add_image_size('hero-medium', 1200, 675, true);
    add_image_size('hero-small', 768, 432, true);
    add_image_size('card-large', 600, 400, true);
    add_image_size('card-medium', 400, 267, true);
    add_image_size('card-small', 300, 200, true);
    
    // Blog-specific optimized sizes
    add_image_size('blog-card', 400, 250, true);  // Perfect for blog cards
    add_image_size('blog-hero', 800, 400, true);  // For featured blog images
    add_image_size('blog-thumb', 300, 200, true); // For small thumbnails
}
add_action('after_setup_theme', 'add_responsive_image_support');

/**
 * Add lazy loading to images
 */
function add_lazy_loading_to_images($attr, $attachment, $size) {
    if (!is_admin()) {
        $attr['loading'] = 'lazy';
        $attr['decoding'] = 'async';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'add_lazy_loading_to_images', 10, 3); 

/**
 * Optimize font loading
 */
function optimize_font_loading() {
    if (is_admin()) return;
    
    // Single optimized font loading approach
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
    echo '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"></noscript>';
}
add_action('wp_head', 'optimize_font_loading', 2);

/**
 * Add resource hints for performance
 */
function add_resource_hints($hints, $relation_type) {
    if ($relation_type === 'dns-prefetch') {
        // Add DNS prefetch for external domains if needed
        // $hints[] = '//fonts.googleapis.com';
    }
    return $hints;
}
add_filter('wp_resource_hints', 'add_resource_hints', 10, 2);

/**
 * Add Web Vitals tracking
 */
function add_web_vitals_tracking() {
    if (is_admin()) return;
    
    ?>
    <script>
    // Core Web Vitals tracking
    function sendToAnalytics(metric) {
        const body = JSON.stringify(metric);
        const url = '<?php echo admin_url('admin-ajax.php'); ?>';
        
        // Use navigator.sendBeacon if available, fallback to fetch
        if (navigator.sendBeacon) {
            navigator.sendBeacon(url, body);
        } else {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: body
            });
        }
    }

    // Track Largest Contentful Paint (LCP)
    new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
            sendToAnalytics({
                name: 'LCP',
                value: entry.startTime,
                id: entry.id,
                action: 'web_vitals'
            });
        }
    }).observe({entryTypes: ['largest-contentful-paint']});

    // Track First Input Delay (FID)
    new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
            sendToAnalytics({
                name: 'FID',
                value: entry.processingStart - entry.startTime,
                id: entry.id,
                action: 'web_vitals'
            });
        }
    }).observe({entryTypes: ['first-input']});

    // Track Cumulative Layout Shift (CLS)
    let clsValue = 0;
    let clsEntries = [];

    new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
            if (!entry.hadRecentInput) {
                clsValue += entry.value;
                clsEntries.push(entry);
            }
        }
        
        sendToAnalytics({
            name: 'CLS',
            value: clsValue,
            id: clsEntries[clsEntries.length - 1]?.id,
            action: 'web_vitals'
        });
    }).observe({entryTypes: ['layout-shift']});

    // Track Time to First Byte (TTFB)
    new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
            if (entry.entryType === 'navigation') {
                sendToAnalytics({
                    name: 'TTFB',
                    value: entry.responseStart - entry.requestStart,
                    id: entry.id,
                    action: 'web_vitals'
                });
            }
        }
    }).observe({entryTypes: ['navigation']});
    </script>
    <?php
}
add_action('wp_head', 'add_web_vitals_tracking', 20);

/**
 * Handle Web Vitals AJAX requests
 */
function handle_web_vitals_ajax() {
    if (!isset($_POST['action']) || $_POST['action'] !== 'web_vitals') {
        return;
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data) {
        // Log Web Vitals data
        error_log('Web Vitals: ' . json_encode($data));
        
        // Store in database for analysis
        $web_vitals = get_option('thrivingstudio_web_vitals', []);
        $web_vitals[] = [
            'metric' => $data['name'],
            'value' => $data['value'],
            'timestamp' => current_time('mysql'),
            'page' => $_SERVER['HTTP_REFERER'] ?? ''
        ];
        
        // Keep only last 1000 entries
        if (count($web_vitals) > 1000) {
            $web_vitals = array_slice($web_vitals, -1000);
        }
        
        update_option('thrivingstudio_web_vitals', $web_vitals);
    }
    
    wp_die();
}
add_action('wp_ajax_web_vitals', 'handle_web_vitals_ajax');
add_action('wp_ajax_nopriv_web_vitals', 'handle_web_vitals_ajax'); 

/**
 * Performance budget monitoring
 */
function add_performance_budget_monitoring() {
    if (is_admin()) return;
    
    ?>
    <script>
    // Performance budget thresholds
    const PERFORMANCE_BUDGETS = {
        LCP: 2500, // 2.5 seconds
        FID: 100,  // 100 milliseconds
        CLS: 0.1,  // 0.1
        TTFB: 800, // 800 milliseconds
        JS_SIZE: 100 * 1024, // 100KB
        CSS_SIZE: 50 * 1024, // 50KB
        IMAGE_SIZE: 500 * 1024 // 500KB
    };

    // Monitor performance budgets
    function checkPerformanceBudget(metric, value) {
        const budget = PERFORMANCE_BUDGETS[metric];
        if (budget && value > budget) {
            console.warn(`Performance budget exceeded: ${metric} = ${value} (budget: ${budget})`);
            
            // Send budget violation to analytics
            sendToAnalytics({
                name: 'BUDGET_VIOLATION',
                metric: metric,
                value: value,
                budget: budget,
                action: 'performance_budget'
            });
        }
    }

    // Monitor resource sizes
    function monitorResourceSizes() {
        const resources = performance.getEntriesByType('resource');
        let totalJS = 0;
        let totalCSS = 0;
        let totalImages = 0;

        resources.forEach(resource => {
            const size = resource.transferSize || 0;
            if (resource.name.includes('.js')) {
                totalJS += size;
            } else if (resource.name.includes('.css')) {
                totalCSS += size;
            } else if (resource.name.match(/\.(jpg|jpeg|png|gif|webp|svg)$/)) {
                totalImages += size;
            }
        });

        checkPerformanceBudget('JS_SIZE', totalJS);
        checkPerformanceBudget('CSS_SIZE', totalCSS);
        checkPerformanceBudget('IMAGE_SIZE', totalImages);
    }

    // Run monitoring after page load
    window.addEventListener('load', () => {
        setTimeout(monitorResourceSizes, 1000);
    });

    // Enhanced Web Vitals tracking with budget monitoring
    const originalSendToAnalytics = window.sendToAnalytics;
    window.sendToAnalytics = function(metric) {
        if (originalSendToAnalytics) {
            originalSendToAnalytics(metric);
        }
        
        // Check performance budget
        if (PERFORMANCE_BUDGETS[metric.name]) {
            checkPerformanceBudget(metric.name, metric.value);
        }
    };
    </script>
    <?php
}
add_action('wp_head', 'add_performance_budget_monitoring', 21); 

/**
 * Disable Cloudflare Rocket Loader for better performance
 */
function disable_cloudflare_rocket_loader() {
    if (!is_admin()) {
        echo '<script>window.addEventListener("load", function() { if (window.rocketLoader) { window.rocketLoader.disabled = true; } });</script>';
    }
}
add_action('wp_head', 'disable_cloudflare_rocket_loader', 1); 

// Removed conflicting CSS loading functions - using standard WordPress enqueue instead

/**
 * Conditional JavaScript loading based on page type
 */
function conditional_js_loading() {
    if (is_admin()) return;
    
    $js_file = WP_DEBUG ? 'main.js' : 'main.min.js';
    $js_version = filemtime(get_template_directory() . '/frontend/' . $js_file);
    $js_url = get_template_directory_uri() . '/frontend/' . $js_file;
    
    // Load core JavaScript immediately
    wp_enqueue_script(
        'thrivingstudio-core', 
        $js_url, 
        [], 
        $js_version, 
        false // Load in head for critical functionality
    );
    
    // Load page-specific JavaScript conditionally
    if (is_singular() && comments_open()) {
        wp_enqueue_script(
            'thrivingstudio-comments',
            $js_url,
            ['thrivingstudio-core'],
            $js_version,
            true
        );
    }
    
    if (is_front_page()) {
        wp_enqueue_script(
            'thrivingstudio-homepage',
            $js_url,
            ['thrivingstudio-core'],
            $js_version,
            true
        );
    }
    
    if (is_archive()) {
        wp_enqueue_script(
            'thrivingstudio-archive',
            $js_url,
            ['thrivingstudio-core'],
            $js_version,
            true
        );
    }
}
// Disabled to avoid duplicate loading of core JS which can cause inconsistent behavior on mobile
// add_action('wp_enqueue_scripts', 'conditional_js_loading', 20);

/**
 * Add module/nomodule pattern for modern browsers
 */
function add_modern_js_support() {
    if (is_admin()) return;
    
    $js_file = WP_DEBUG ? 'main.js' : 'main.min.js';
    $js_version = filemtime(get_template_directory() . '/frontend/' . $js_file);
    $js_url = get_template_directory_uri() . '/frontend/' . $js_file;
    
    // Modern browsers (ES6+)
    echo '<script type="module" src="' . $js_url . '?v=' . $js_version . '"></script>';
    
    // Legacy browsers (ES5)
    echo '<script nomodule src="' . $js_url . '?v=' . $js_version . '"></script>';
}
// Disabled to avoid duplicate loading (module/nomodule) of the same script
// add_action('wp_head', 'add_modern_js_support', 15);

/**
 * Enhanced lazy loading with Intersection Observer
 */
function add_enhanced_lazy_loading() {
    if (is_admin()) return;
    
    ?>
    <script>
    // Enhanced lazy loading with Intersection Observer
    document.addEventListener('DOMContentLoaded', function() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const src = img.dataset.src;
                        const srcset = img.dataset.srcset;
                        
                        if (src) {
                            img.src = src;
                            img.removeAttribute('data-src');
                        }
                        
                        if (srcset) {
                            img.srcset = srcset;
                            img.removeAttribute('data-srcset');
                        }
                        
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });
            
            // Observe all lazy images
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for older browsers
            document.querySelectorAll('img[data-src]').forEach(img => {
                img.src = img.dataset.src;
                if (img.dataset.srcset) {
                    img.srcset = img.dataset.srcset;
                }
            });
        }
    });
    </script>
    <?php
}
add_action('wp_head', 'add_enhanced_lazy_loading', 25);

/**
 * Add WebP support with fallback
 */
function add_enhanced_webp_picture_support($html, $attachment_id, $size, $icon, $attr) {
    if (is_admin()) return $html;
    
    $image_url = wp_get_attachment_image_url($attachment_id, $size);
    $webp_url = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $image_url);
    
    // Check if WebP version exists
    $webp_path = str_replace(get_site_url(), ABSPATH, $webp_url);
    if (!file_exists($webp_path)) {
        return $html;
    }
    
    // Create picture element with WebP and fallback
    $picture_html = '<picture>';
    $picture_html .= '<source srcset="' . $webp_url . '" type="image/webp">';
    $picture_html .= $html;
    $picture_html .= '</picture>';
    
    return $picture_html;
}
add_filter('wp_get_attachment_image', 'add_enhanced_webp_picture_support', 10, 5);

/**
 * Optimize blog page image loading with fallback
 */
function optimize_blog_image_loading($html, $attachment_id, $size, $icon, $attr) {
    // Only optimize on blog/archive pages
    if (!is_home() && !is_archive()) {
        return $html;
    }
    
    // Add responsive image attributes for blog cards
    if ($size === 'blog-card') {
        $attr['sizes'] = '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw';
        $attr['loading'] = 'lazy';
        $attr['decoding'] = 'async';
        $attr['fetchpriority'] = 'low';
    }
    
    return $html;
}
add_filter('wp_get_attachment_image_attributes', 'optimize_blog_image_loading', 10, 4);

// Removed problematic smart_blog_image_fallback function that was causing critical errors

/**
 * Add responsive image sizes
 */
function add_responsive_image_sizes() {
    // Add more granular image sizes for better performance
    add_image_size('thumbnail-small', 150, 150, true);
    add_image_size('thumbnail-medium', 300, 300, true);
    add_image_size('thumbnail-large', 600, 600, true);
    add_image_size('hero-mobile', 768, 432, true);
    add_image_size('hero-tablet', 1024, 576, true);
    add_image_size('hero-desktop', 1920, 1080, true);
}
add_action('after_setup_theme', 'add_responsive_image_sizes'); 

/**
 * Optimize database queries
 */
function optimize_database_queries() {
    // Remove unnecessary queries
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    
    // Optimize post queries
    add_filter('posts_pre_query', function($posts, $query) {
        if (!$query->is_main_query()) {
            return $posts;
        }
        
        // Add query optimization hints
        $query->set('no_found_rows', true);
        $query->set('update_post_meta_cache', false);
        $query->set('update_post_term_cache', false);
        
        return $posts;
    }, 10, 2);
}
add_action('init', 'optimize_database_queries');

/**
 * Add object caching support
 */
function add_object_caching() {
    // Check if Redis is available
    if (class_exists('Redis') && function_exists('wp_cache_add')) {
        // Configure Redis caching
        wp_cache_add_global_groups(['users', 'userlogins', 'usermeta', 'user_meta', 'site-transient', 'site-options', 'site-lookup', 'blog-lookup', 'blog-details', 'rss']);
        wp_cache_add_non_persistent_groups(['comment', 'counts', 'plugins']);
    }
    
    // Add transients for expensive operations
    add_action('wp_head', function() {
        $cache_key = 'thrivingstudio_page_' . get_the_ID();
        $cached_content = wp_cache_get($cache_key);
        
        if (false === $cached_content) {
            // Cache expensive operations for 1 hour
            wp_cache_set($cache_key, 'cached', '', HOUR_IN_SECONDS);
        }
    });
}
add_action('init', 'add_object_caching');

/**
 * Optimize WordPress queries
 */
function optimize_wp_queries() {
    // Disable emoji queries
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    
    // Remove unnecessary REST API endpoints
    add_filter('rest_endpoints', function($endpoints) {
        if (!is_user_logged_in()) {
            unset($endpoints['/wp/v2/users']);
            unset($endpoints['/wp/v2/posts']);
            unset($endpoints['/wp/v2/pages']);
        }
        return $endpoints;
    });
}
add_action('init', 'optimize_wp_queries');

// Cleanup: removed temporary no-cache headers and debug header

/**
 * Add query monitoring for development
 */
function add_query_monitoring() {
    if (WP_DEBUG && current_user_can('manage_options')) {
        add_action('wp_footer', function() {
            global $wpdb;
            $query_count = is_array($wpdb->queries) ? count($wpdb->queries) : 0;
            echo '<!-- Queries: ' . $query_count . ' -->';
            echo '<!-- Query time: ' . timer_stop() . 's -->';
        });
    }
}
add_action('init', 'add_query_monitoring'); 

// AdSense mobile bootstrap function removed - using Site Kit for AdSense management