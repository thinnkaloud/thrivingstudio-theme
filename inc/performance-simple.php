<?php
/**
 * Simple Performance optimization module for Thriving Studio theme
 * Minimal optimizations that won't cause critical errors
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
 * Optimize JPEG image quality
 */
function optimize_image_quality() {
    return 85; // Balanced quality setting
}
add_filter('jpeg_quality', 'optimize_image_quality');
add_filter('wp_editor_set_quality', 'optimize_image_quality');

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
}
add_action('after_setup_theme', 'add_responsive_image_support');
?>
