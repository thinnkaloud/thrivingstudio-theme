<?php
/**
 * Temporary debug file to check CSS loading
 * Access via: yoursite.com/wp-content/themes/thrivingstudio/DEBUG-CSS-LOADING.php
 * DELETE THIS FILE AFTER DEBUGGING
 */

// Load WordPress
require_once('../../../wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>CSS Loading Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #0073aa; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>CSS Loading Debug Information</h1>
    
    <?php
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();
    $frontend_path = $theme_dir . '/frontend';
    $frontend_uri = $theme_uri . '/frontend';
    
    $css_build = $frontend_path . '/build.css';
    $css_source = $frontend_path . '/index.css';
    
    echo '<div class="section">';
    echo '<h2>File Paths</h2>';
    echo '<p><strong>Theme Directory:</strong> ' . esc_html($theme_dir) . '</p>';
    echo '<p><strong>Theme URI:</strong> ' . esc_html($theme_uri) . '</p>';
    echo '<p><strong>Frontend Path:</strong> ' . esc_html($frontend_path) . '</p>';
    echo '<p><strong>Frontend URI:</strong> ' . esc_html($frontend_uri) . '</p>';
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>CSS Files Status</h2>';
    
    if (file_exists($css_build)) {
        $build_size = filesize($css_build);
        $build_time = filemtime($css_build);
        $build_url = $frontend_uri . '/build.css';
        echo '<p class="success">✓ build.css EXISTS</p>';
        echo '<p><strong>Path:</strong> ' . esc_html($css_build) . '</p>';
        echo '<p><strong>Size:</strong> ' . size_format($build_size) . '</p>';
        echo '<p><strong>Modified:</strong> ' . date('Y-m-d H:i:s', $build_time) . '</p>';
        echo '<p><strong>URL:</strong> <a href="' . esc_url($build_url) . '" target="_blank">' . esc_html($build_url) . '</a></p>';
        echo '<p><strong>Readable:</strong> ' . (is_readable($css_build) ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . '</p>';
    } else {
        echo '<p class="error">✗ build.css DOES NOT EXIST</p>';
        echo '<p><strong>Expected Path:</strong> ' . esc_html($css_build) . '</p>';
    }
    
    if (file_exists($css_source)) {
        $source_size = filesize($css_source);
        $source_time = filemtime($css_source);
        $source_url = $frontend_uri . '/index.css';
        echo '<p class="success">✓ index.css EXISTS</p>';
        echo '<p><strong>Path:</strong> ' . esc_html($css_source) . '</p>';
        echo '<p><strong>Size:</strong> ' . size_format($source_size) . '</p>';
        echo '<p><strong>Modified:</strong> ' . date('Y-m-d H:i:s', $source_time) . '</p>';
        echo '<p><strong>URL:</strong> <a href="' . esc_url($source_url) . '" target="_blank">' . esc_html($source_url) . '</a></p>';
    } else {
        echo '<p class="error">✗ index.css DOES NOT EXIST</p>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>What WordPress Will Load</h2>';
    if (file_exists($css_build)) {
        $css_file = 'build.css';
        $css_version = filemtime($css_build) ?: time();
        $css_version = $css_version . '-' . time();
    } elseif (file_exists($css_source)) {
        $css_file = 'index.css';
        $css_version = filemtime($css_source) ?: time();
        $css_version = $css_version . '-' . time();
    } else {
        $css_file = 'build.css';
        $css_version = time();
    }
    
    $css_url = $frontend_uri . '/' . $css_file . '?ver=' . $css_version;
    echo '<p><strong>File:</strong> ' . esc_html($css_file) . '</p>';
    echo '<p><strong>Version:</strong> ' . esc_html($css_version) . '</p>';
    echo '<p><strong>Full URL:</strong> <a href="' . esc_url($css_url) . '" target="_blank">' . esc_html($css_url) . '</a></p>';
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>Test CSS Access</h2>';
    echo '<p><a href="' . esc_url($frontend_uri . '/build.css') . '" target="_blank">Try to access build.css directly</a></p>';
    echo '<p><a href="' . esc_url($frontend_uri . '/index.css') . '" target="_blank">Try to access index.css directly</a></p>';
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>Active Plugins</h2>';
    $active_plugins = get_option('active_plugins');
    if (!empty($active_plugins)) {
        echo '<ul>';
        foreach ($active_plugins as $plugin) {
            echo '<li>' . esc_html($plugin) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No active plugins</p>';
    }
    echo '</div>';
    ?>
    
    <div class="section">
        <h2>Instructions</h2>
        <p><strong>1. Check if CSS URLs are accessible</strong> - Click the links above</p>
        <p><strong>2. Check browser console</strong> - Look for 404 errors on CSS file</p>
        <p><strong>3. Clear all caches:</strong></p>
        <ul>
            <li>WordPress cache plugins</li>
            <li>CDN cache (if using Cloudflare, etc.)</li>
            <li>Browser cache</li>
            <li>Server cache</li>
        </ul>
        <p><strong>4. DELETE THIS FILE</strong> after debugging for security</p>
    </div>
</body>
</html>

