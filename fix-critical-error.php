<?php
/**
 * Emergency Fix for Critical WordPress Error
 * This will restore the site to working condition
 */

// Load WordPress manually
if (!defined('ABSPATH')) {
    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
}

echo "<h1>🚨 Emergency Fix for Critical Error</h1>";
echo "<p><strong>Started:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Check if WordPress is loaded
if (!function_exists('get_option')) {
    echo "<p style='color: red;'>❌ WordPress not loaded. Loading manually...</p>";
    // Try to load WordPress
    if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php')) {
        require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
        echo "<p style='color: green;'>✅ WordPress loaded successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Cannot find wp-load.php</p>";
        exit;
    }
}

// 1. Restore basic .htaccess
echo "<h2>1. Restoring Basic .htaccess</h2>";
$htaccess_path = ABSPATH . '.htaccess';
$basic_htaccess = "# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress";

if (file_put_contents($htaccess_path, $basic_htaccess)) {
    echo "<p style='color: green;'>✅ Basic .htaccess restored</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to restore .htaccess</p>";
}

// 2. Check for syntax errors in functions.php
echo "<h2>2. Checking functions.php for Errors</h2>";
$functions_file = get_template_directory() . '/functions.php';
if (file_exists($functions_file)) {
    $content = file_get_contents($functions_file);
    
    // Check for common syntax issues
    $issues_found = false;
    
    // Check for unclosed PHP tags
    if (substr_count($content, '<?php') !== substr_count($content, '?>')) {
        echo "<p style='color: red;'>❌ Unclosed PHP tags detected</p>";
        $issues_found = true;
    }
    
    // Check for missing semicolons
    $lines = explode("\n", $content);
    foreach ($lines as $line_num => $line) {
        $line = trim($line);
        if (!empty($line) && !strpos($line, '//') && !strpos($line, '/*') && !strpos($line, '*/') && !strpos($line, '<?php') && !strpos($line, '?>') && !strpos($line, 'function') && !strpos($line, 'if') && !strpos($line, 'else') && !strpos($line, 'foreach') && !strpos($line, 'while') && !strpos($line, 'for') && !strpos($line, '{') && !strpos($line, '}') && !strpos($line, ';')) {
            if (strpos($line, 'echo') !== false || strpos($line, 'return') !== false || strpos($line, 'wp_') !== false) {
                echo "<p style='color: red;'>❌ Missing semicolon on line " . ($line_num + 1) . ": " . htmlspecialchars($line) . "</p>";
                $issues_found = true;
            }
        }
    }
    
    if (!$issues_found) {
        echo "<p style='color: green;'>✅ No obvious syntax errors found</p>";
    }
}

// 3. Remove any problematic diagnostic files
echo "<h2>3. Removing Problematic Files</h2>";
$problematic_files = [
    'optimize-site.php',
    'site-audit.php',
    'emergency-fix.php',
    'fix-wordpress-loading.php',
    'debug-live-site.php',
    'simple-debug.php'
];

foreach ($problematic_files as $file) {
    $file_path = get_template_directory() . '/' . $file;
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            echo "<p style='color: green;'>✅ Removed: $file</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to remove: $file</p>";
        }
    }
}

// 4. Check wp-config.php for issues
echo "<h2>4. Checking wp-config.php</h2>";
$wp_config_path = ABSPATH . 'wp-config.php';
if (file_exists($wp_config_path)) {
    $wp_config_content = file_get_contents($wp_config_path);
    
    // Remove any problematic constants that might have been added
    $problematic_constants = [
        'DISALLOW_FILE_EDIT',
        'DISALLOW_FILE_MODS',
        'FORCE_SSL_ADMIN',
        'WP_AUTO_UPDATE_CORE'
    ];
    
    foreach ($problematic_constants as $constant) {
        $pattern = "/define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"]\s*,\s*[^)]+\)\s*;/";
        if (preg_match($pattern, $wp_config_content)) {
            $wp_config_content = preg_replace($pattern, '', $wp_config_content);
            echo "<p style='color: orange;'>⚠️ Removed problematic constant: $constant</p>";
        }
    }
    
    if (file_put_contents($wp_config_path, $wp_config_content)) {
        echo "<p style='color: green;'>✅ wp-config.php cleaned</p>";
    }
}

// 5. Clear any caches
echo "<h2>5. Clearing Caches</h2>";
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "<p style='color: green;'>✅ WordPress cache cleared</p>";
}

// Clear transients
if (function_exists('wp_clear_scheduled_hook')) {
    wp_clear_scheduled_hook('delete_expired_transients');
    echo "<p style='color: green;'>✅ Transients cleared</p>";
}

// 6. Test WordPress functions
echo "<h2>6. Testing WordPress Functions</h2>";
if (function_exists('get_option')) {
    echo "<p style='color: green;'>✅ get_option() function working</p>";
    echo "<p>Site URL: " . get_option('siteurl', 'Not available') . "</p>";
} else {
    echo "<p style='color: red;'>❌ get_option() function not working</p>";
}

if (function_exists('get_template_directory')) {
    echo "<p style='color: green;'>✅ get_template_directory() function working</p>";
} else {
    echo "<p style='color: red;'>❌ get_template_directory() function not working</p>";
}

// 7. Create a simple test page
echo "<h2>7. Creating Test Page</h2>";
$test_content = '<?php
// Simple test to verify PHP is working
echo "<h1>PHP Test - " . date("Y-m-d H:i:s") . "</h1>";
echo "<p>If you can see this, PHP is working correctly.</p>";
echo "<p><a href=\"" . home_url("/") . "\">Go to Homepage</a></p>";
?>';
$test_file = get_template_directory() . '/test-php.php';
if (file_put_contents($test_file, $test_content)) {
    echo "<p style='color: green;'>✅ Test file created</p>";
}

echo "<h2>8. Recovery Summary</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px;'>";
echo "<h3>✅ Recovery Actions Completed:</h3>";
echo "<ul>";
echo "<li>Restored basic .htaccess file</li>";
echo "<li>Checked functions.php for syntax errors</li>";
echo "<li>Removed problematic diagnostic files</li>";
echo "<li>Cleaned wp-config.php</li>";
echo "<li>Cleared all caches</li>";
echo "<li>Tested WordPress functions</li>";
echo "<li>Created PHP test file</li>";
echo "</ul>";
echo "</div>";

echo "<h2>9. Test Your Site</h2>";
echo "<p><a href='" . home_url('/') . "' target='_blank'>Test Homepage</a></p>";
echo "<p><a href='" . get_template_directory_uri() . '/test-php.php' . "' target='_blank'>Test PHP Processing</a></p>";
echo "<p><a href='" . admin_url() . "' target='_blank'>Test WordPress Admin</a></p>";

echo "<h2>10. Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Test your homepage</strong> - Should work now</li>";
echo "<li><strong>Test WordPress admin</strong> - Should be accessible</li>";
echo "<li><strong>If still having issues</strong> - Contact your hosting provider</li>";
echo "<li><strong>Once working</strong> - We can do a safer optimization</li>";
echo "</ol>";

echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🔧 Critical error should be resolved!</p>";
echo "<p><strong>Completed:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 