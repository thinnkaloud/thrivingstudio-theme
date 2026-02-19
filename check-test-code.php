<?php
/**
 * Check and Remove Test Code Script
 * This script will help identify and remove any test code that shouldn't be on the live site
 */

// Prevent direct access in production
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    die('Test code checking disabled in production');
}

echo "<h2>Checking for Test Code...</h2>";

// Files to check for test code
$files_to_check = [
    'header.php',
    'footer.php', 
    'functions.php',
    'front-page.php',
    'index.php'
];

$test_patterns = [
    'SFTP.*Test',
    'Plugin.*Test', 
    'Test.*Successful',
    '2025-08-03',
    '14:25:50',
    'front-page\.php',
    'console\.log.*test',
    'echo.*test',
    'print.*test'
];

$found_test_code = false;

foreach ($files_to_check as $file) {
    $file_path = get_template_directory() . '/' . $file;
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        
        foreach ($test_patterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $content)) {
                echo "<p style='color: red;'>⚠️ Found test code in: <strong>$file</strong> (pattern: $pattern)</p>";
                $found_test_code = true;
                
                // Show the problematic line
                $lines = explode("\n", $content);
                foreach ($lines as $line_num => $line) {
                    if (preg_match('/' . $pattern . '/i', $line)) {
                        echo "<p style='background: #ffe6e6; padding: 5px; margin: 2px 0;'>Line " . ($line_num + 1) . ": " . htmlspecialchars(trim($line)) . "</p>";
                    }
                }
            }
        }
    }
}

if (!$found_test_code) {
    echo "<p style='color: green;'>✅ No test code found in theme files.</p>";
    echo "<p>The test message might be coming from:</p>";
    echo "<ul>";
    echo "<li>Server-side caching</li>";
    echo "<li>CDN cache</li>";
    echo "<li>WordPress caching plugins</li>";
    echo "<li>Browser cache</li>";
    echo "</ul>";
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Clear your browser cache (Cmd+Shift+R on Mac, Ctrl+Shift+R on Windows)</li>";
echo "<li>Clear server cache from your hosting control panel</li>";
echo "<li>If using a CDN, clear CDN cache</li>";
echo "<li>Check WordPress caching plugins and clear their cache</li>";
echo "</ol>";

echo "<p><a href='" . home_url('/?cache_cleared=1') . "'>Click here to force cache clearing</a></p>";
?> 