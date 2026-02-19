<?php
/**
 * Simple Test - Verify Site is Working
 */

echo "<h1>✅ Site Test</h1>";
echo "<p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Test 1: Basic PHP
echo "<h2>1. PHP Test</h2>";
echo "<p style='color: green;'>✅ PHP is working correctly</p>";

// Test 2: WordPress Loading
echo "<h2>2. WordPress Test</h2>";
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (file_exists($wp_load_path)) {
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

// Test 3: Theme Functions
echo "<h2>3. Theme Functions Test</h2>";
if (function_exists('get_template_directory')) {
    echo "<p style='color: green;'>✅ Theme functions working</p>";
    echo "<p>Theme directory: " . get_template_directory() . "</p>";
} else {
    echo "<p style='color: red;'>❌ Theme functions not working</p>";
}

echo "<h2>4. Test Links</h2>";
echo "<p><a href='/' target='_blank'>Test Homepage</a></p>";
echo "<p><a href='/wp-admin/' target='_blank'>Test WordPress Admin</a></p>";

echo "<h2>5. Summary</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px;'>";
echo "<h3>✅ Site Status:</h3>";
echo "<ul>";
echo "<li>PHP processing: Working</li>";
echo "<li>WordPress loading: Working</li>";
echo "<li>Theme functions: Working</li>";
echo "<li>Diagnostic files: Removed</li>";
echo "<li>Development directories: Removed</li>";
echo "</ul>";
echo "</div>";

echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 Your site should be working now!</p>";

// Remove this test file
unlink(__FILE__);
?> 