<?php
/**
 * Emergency Restore Script
 * Fixes the critical error caused by optimization
 */

echo "<h1>🚨 Emergency Restore</h1>";
echo "<p><strong>Started:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. Restore basic .htaccess
echo "<h2>1. Restoring .htaccess</h2>";
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
    echo "<p style='color: green;'>✅ .htaccess restored to basic WordPress rules</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to restore .htaccess</p>";
}

// 2. Fix file permissions
echo "<h2>2. Fixing File Permissions</h2>";
$files_to_fix = [
    'wp-config.php' => '0644',
    '.htaccess' => '0644',
    'index.php' => '0644'
];

foreach ($files_to_fix as $file => $perms) {
    $file_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/' . $file;
    if (file_exists($file_path)) {
        chmod($file_path, octdec($perms));
        echo "<p style='color: green;'>✅ Fixed permissions for $file: $perms</p>";
    }
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
            echo "<p style='color: red;'>❌ WordPress functions not available</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error loading WordPress: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ wp-load.php not found</p>";
}

// 4. Check wp-config.php
echo "<h2>4. Checking wp-config.php</h2>";
$wp_config_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-config.php';
if (file_exists($wp_config_path)) {
    $wp_config_content = file_get_contents($wp_config_path);
    
    // Check for database connection
    if (strpos($wp_config_content, 'DB_NAME') !== false && 
        strpos($wp_config_content, 'DB_USER') !== false && 
        strpos($wp_config_content, 'DB_PASSWORD') !== false && 
        strpos($wp_config_content, 'DB_HOST') !== false) {
        echo "<p style='color: green;'>✅ Database configuration found</p>";
    } else {
        echo "<p style='color: red;'>❌ Database configuration missing</p>";
    }
    
    // Check for WordPress settings
    if (strpos($wp_config_content, 'WP_DEBUG') !== false) {
        echo "<p style='color: green;'>✅ WordPress settings found</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ WordPress settings not found</p>";
    }
} else {
    echo "<p style='color: red;'>❌ wp-config.php not found</p>";
}

// 5. Test database connection
echo "<h2>5. Testing Database Connection</h2>";
if (function_exists('get_option')) {
    try {
        global $wpdb;
        $result = $wpdb->get_var("SELECT 1");
        if ($result) {
            echo "<p style='color: green;'>✅ Database connection successful</p>";
        } else {
            echo "<p style='color: red;'>❌ Database connection failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Cannot test database - WordPress not loaded</p>";
}

echo "<h2>6. Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Test your site:</strong> <a href='../' target='_blank'>Go to homepage</a></li>";
echo "<li><strong>Test admin:</strong> <a href='../wp-admin/' target='_blank'>Go to admin</a></li>";
echo "<li><strong>If still broken:</strong> Check your hosting control panel</li>";
echo "<li><strong>Contact hosting:</strong> If database connection fails</li>";
echo "</ol>";

echo "<h2>7. What Went Wrong</h2>";
echo "<p>The optimization script likely:</p>";
echo "<ul>";
echo "<li>Changed .htaccess rules that broke WordPress routing</li>";
echo "<li>Set incorrect file permissions</li>";
echo "<li>Modified wp-config.php incorrectly</li>";
echo "</ul>";

echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🔧 Emergency restore completed!</p>";
echo "<p><strong>Completed:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 