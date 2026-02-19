<?php
/**
 * Regenerate Blog Images Script
 * Run this once to create the new blog-card image sizes for existing images
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

echo "<h2>🖼️ Regenerating Blog Image Sizes</h2>";

// Get all images
$images = get_posts([
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'numberposts' => -1,
    'post_status' => 'inherit'
]);

$count = 0;
$total = count($images);

echo "<p>Found {$total} images to process...</p>";

foreach ($images as $image) {
    $count++;
    $image_id = $image->ID;
    $image_path = get_attached_file($image_id);
    
    if ($image_path && file_exists($image_path)) {
        // Generate the new blog-card size
        $blog_card = image_make_intermediate_size($image_path, 400, 250, true);
        
        if ($blog_card) {
            echo "<p>✅ {$count}/{$total}: Generated blog-card size for " . basename($image_path) . "</p>";
        } else {
            echo "<p>⚠️ {$count}/{$total}: Failed to generate blog-card size for " . basename($image_path) . "</p>";
        }
    } else {
        echo "<p>❌ {$count}/{$total}: File not found for " . basename($image_path) . "</p>";
    }
    
    // Update progress every 10 images
    if ($count % 10 === 0) {
        echo "<p><strong>Progress: {$count}/{$total} images processed</strong></p>";
        flush();
    }
}

echo "<h3>🎉 Image regeneration complete!</h3>";
echo "<p>Your blog images are now optimized with the new blog-card size (400x250px).</p>";
echo "<p><a href='" . admin_url() . "'>← Back to Admin</a></p>";
?>
