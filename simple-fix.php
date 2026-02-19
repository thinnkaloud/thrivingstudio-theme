<?php
/**
 * Simple Fix - Restore Site to Working Condition
 * This is a minimal fix that won't cause additional errors
 */

echo "<h1>🔧 Simple Fix</h1>";
echo "<p><strong>Started:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. Remove all problematic files
echo "<h2>1. Removing Problematic Files</h2>";
$files_to_remove = [
    'fix-critical-error.php',
    'fix-syntax-errors.php',
    'optimize-site.php',
    'site-audit.php',
    'emergency-fix.php',
    'debug-live-site.php',
    'simple-debug.php',
    'check-htaccess.php',
    'check-file-permissions.php',
    'check-database.php',
    'check-plugins.php',
    'find-test-code.php',
    'test-file.php',
    'php-test.php',
    'clear-cache.php',
    'cleanup.php'
];

$removed_count = 0;
foreach ($files_to_remove as $file) {
    $file_path = dirname(__FILE__) . '/' . $file;
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            echo "<p style='color: green;'>✅ Removed: $file</p>";
            $removed_count++;
        }
    }
}

echo "<p><strong>Removed $removed_count files</strong></p>";

// 2. Restore basic .htaccess
echo "<h2>2. Restoring Basic .htaccess</h2>";
$htaccess_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/.htaccess';
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

// 3. Check if WordPress can load
echo "<h2>3. Testing WordPress Loading</h2>";
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (file_exists($wp_load_path)) {
    echo "<p style='color: green;'>✅ wp-load.php exists</p>";
    
    // Try to load WordPress
    try {
        require_once($wp_load_path);
        if (function_exists('get_option')) {
            echo "<p style='color: green;'>✅ WordPress loaded successfully</p>";
            echo "<p>Site URL: " . get_option('siteurl', 'Not available') . "</p>";
        } else {
            echo "<p style='color: red;'>❌ WordPress loaded but functions not available</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error loading WordPress: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ wp-load.php not found</p>";
}

// 4. Create a simple test
echo "<h2>4. Creating Simple Test</h2>";
$test_content = '<?php
echo "<h1>PHP Test - " . date("Y-m-d H:i:s") . "</h1>";
echo "<p>PHP is working correctly.</p>";
echo "<p><a href=\"/\">Go to Homepage</a></p>";
?>';
$test_file = dirname(__FILE__) . '/test.php';
if (file_put_contents($test_file, $test_content)) {
    echo "<p style='color: green;'>✅ Test file created</p>";
}

echo "<h2>5. Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Test your homepage:</strong> <a href=\"/\" target=\"_blank\">Click here</a></li>";
echo "<li><strong>Test WordPress admin:</strong> <a href=\"/wp-admin/\" target=\"_blank\">Click here</a></li>";
echo "<li><strong>Test PHP processing:</strong> <a href=\"/wp-content/themes/thrivingstudio/test.php\" target=\"_blank\">Click here</a></li>";
echo "</ol>";

echo "<h2>6. If Still Not Working</h2>";
echo "<p>If you're still seeing errors:</p>";
echo "<ol>";
echo "<li>Contact your hosting provider</li>";
echo "<li>Ask them to check PHP configuration</li>";
echo "<li>Request them to restore from backup if needed</li>";
echo "</ol>";

echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🔧 Simple fix completed!</p>";
echo "<p><strong>Completed:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Remove this script after completion
$current_file = __FILE__;
unlink($current_file);
?> 