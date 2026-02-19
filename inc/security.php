<?php
/**
 * Security enhancements for the theme
 */

namespace ThrivingStudio\Security;

/**
 * Add security headers to HTTP responses.
 *
 * @param array $headers
 * @return array
 * Security headers are enforced here.
 */
function add_security_headers($headers) {
    $headers['X-Frame-Options'] = 'SAMEORIGIN';
    $headers['X-Content-Type-Options'] = 'nosniff';
    $headers['X-XSS-Protection'] = '1; mode=block';
    $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
    $headers['Permissions-Policy'] = 'geolocation=(), microphone=(), camera=()';
    
    // Only add HSTS header if SSL is enabled
    if (is_ssl()) {
        $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
    }
    
    $headers['Content-Security-Policy'] = "default-src 'self'; script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://www.google-analytics.com https://stats.g.doubleclick.net https://pagead2.googlesyndication.com https://googleads.g.doubleclick.net https://www.googletagservices.com https://tpc.googlesyndication.com https://adservice.google.com https://adservice.google.co.in https://fundingchoicesmessages.google.com https://static.cloudflareinsights.com https://analytics.ahrefs.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https://www.google.com https://www.google-analytics.com https://www.googletagmanager.com https://pagead2.googlesyndication.com https://tpc.googlesyndication.com https://googleads.g.doubleclick.net https://i.ytimg.com https://secure.gravatar.com; connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com https://stats.g.doubleclick.net https://googleads.g.doubleclick.net https://pagead2.googlesyndication.com https://tpc.googlesyndication.com https://adservice.google.com https://adservice.google.co.in https://fundingchoicesmessages.google.com https://analytics.ahrefs.com https://ep1.adtrafficquality.google; font-src 'self' data: https://fonts.gstatic.com; frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://googleads.g.doubleclick.net https://tpc.googlesyndication.com https://fundingchoicesmessages.google.com; worker-src 'self' blob:; object-src 'none'; frame-ancestors 'self'; base-uri 'self'; form-action 'self';";
    
    return $headers;
}

/**
 * Disable XML-RPC functionality for security.
 *
 * XML-RPC is disabled here to prevent brute force and DDoS attacks.
 */
function disable_xmlrpc() {
    add_filter('xmlrpc_enabled', '__return_false');
    add_filter('wp_headers', function($headers) {
        unset($headers['X-Pingback']);
        return $headers;
    });
}

/**
 * Remove WordPress version info from the site.
 *
 * @return string
 */
function remove_version_info() {
    return '';
}

/**
 * Disable file editing in the WordPress admin.
 */
function disable_file_editing() {
    // Temporarily disabled for development
    // if (!defined('DISALLOW_FILE_EDIT')) {
    //     define('DISALLOW_FILE_EDIT', true);
    // }
}

/**
 * Block suspicious queries for non-admin users.
 *
 * Suspicious admin/login queries are blocked here for non-admins.
 */
function block_suspicious_queries() {
    global $user_ID;
    
    if ($user_ID) {
        if (!current_user_can('administrator')) {
            if (
                strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false ||
                strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false
            ) {
                $ref = wp_get_referer();
                if (!$ref) {
                    wp_die('Access denied.', 'Access Denied', ['response' => 403]);
                }
            }
        }
    }
}

/**
 * Initialize all security features for the theme.
 */
function init() {
    add_filter('wp_headers', __NAMESPACE__ . '\\add_security_headers');
    add_action('init', __NAMESPACE__ . '\\disable_xmlrpc');
    add_filter('the_generator', __NAMESPACE__ . '\\remove_version_info');
    add_action('init', __NAMESPACE__ . '\\disable_file_editing');
    add_action('init', __NAMESPACE__ . '\\block_suspicious_queries');
}

init(); 