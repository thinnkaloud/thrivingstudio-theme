<?php

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
