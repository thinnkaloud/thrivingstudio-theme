<?php
/**
 * Theme functions and definitions
 */

// Define theme constants
define('THRIVINGSTUDIO_VERSION', '1.0.0');
define('THRIVINGSTUDIO_NAME', 'Thriving Studio');
define('THRIVINGSTUDIO_DIR', get_template_directory());
define('THRIVINGSTUDIO_URI', get_template_directory_uri());

// Autoload theme modules
$modules = [
    // 'performance-simple', // Disabled - causing blank editor issues
    // 'performance-dashboard', // Disabled - causing blank editor issues
    'security',
    'seo',
    'seo-settings'
    // 'ads' - Removed: Use Google Site Kit for AdSense management
];

// Include orphaned media cleanup only in admin
if (is_admin()) {
    require_once THRIVINGSTUDIO_DIR . '/cleanup-orphaned-media.php';
}

foreach ($modules as $module) {
    $module_file = THRIVINGSTUDIO_DIR . "/inc/{$module}.php";
    if (file_exists($module_file)) {
        require_once $module_file;
    } else {
        error_log("[ThrivingStudio] Module file missing: $module_file");
    }
}

// Phase 3 extraction: main theme behavior moved to dedicated bootstrap module.
require_once THRIVINGSTUDIO_DIR . '/inc/theme/bootstrap.php';
