<?php
/**
 * Temporary cleanup script to remove old ad options
 * Run this once, then delete the file
 */

// Only run if accessed directly
if (!defined('ABSPATH')) {
    // Load WordPress
    require_once('../../../wp-load.php');
}

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized access');
}

// Remove old ad options
delete_option('ts_ads_options');

echo '<h2>Cleanup Complete!</h2>';
echo '<p>The old ad options have been removed from the database.</p>';
echo '<p>You can now delete this file and use Site Kit for AdSense management.</p>';
echo '<p><a href="' . admin_url() . '">Return to WordPress Admin</a></p>';
?>
