<?php
/**
 * Optimize Existing Images Script for ThrivingStudio Theme
 * Converts large PNG images to WebP format to improve performance
 * 
 * Usage: Add to functions.php temporarily, then visit WordPress admin
 */

// Only run if user is admin
if (!current_user_can('manage_options')) {
    return;
}

// Add admin page for image optimization
add_action('admin_menu', 'thrivingstudio_add_image_optimization_menu');

function thrivingstudio_add_image_optimization_menu() {
    add_submenu_page(
        'performance-dashboard',
        'Image Optimization',
        'Image Optimization',
        'manage_options',
        'image-optimization',
        'thrivingstudio_image_optimization_page'
    );
}

// Handle image optimization form submission
add_action('admin_post_optimize_images', 'thrivingstudio_handle_image_optimization');

function thrivingstudio_handle_image_optimization() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $image_ids = $_POST['image_ids'] ?? [];
    $optimization_results = [];
    
    foreach ($image_ids as $image_id) {
        $result = optimize_single_image($image_id);
        $optimization_results[] = $result;
    }
    
    // Store results in transient for display
    set_transient('thrivingstudio_optimization_results', $optimization_results, 60);
    
    // Redirect back to optimization page
    wp_redirect(admin_url('admin.php?page=image-optimization&optimized=1'));
    exit;
}

// Function to optimize a single image
function optimize_single_image($image_id) {
    $attachment = get_post($image_id);
    if (!$attachment || $attachment->post_type !== 'attachment') {
        return ['success' => false, 'message' => 'Invalid image ID'];
    }
    
    $file_path = get_attached_file($image_id);
    if (!file_exists($file_path)) {
        return ['success' => false, 'message' => 'File not found'];
    }
    
    $file_info = pathinfo($file_path);
    $extension = strtolower($file_info['extension']);
    
    // Only process PNG and JPEG images
    if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
        return ['success' => false, 'message' => 'Unsupported format: ' . $extension];
    }
    
    // Create WebP version
    $webp_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
    
    try {
        // Use WordPress image editor if available
        if (function_exists('wp_get_image_editor')) {
            $editor = wp_get_image_editor($file_path);
            if (!is_wp_error($editor)) {
                $result = $editor->save($webp_path, 'image/webp');
                if (!is_wp_error($result)) {
                    // Update attachment metadata to include WebP
                    $metadata = wp_get_attachment_metadata($image_id);
                    $metadata['webp_file'] = basename($webp_path);
                    wp_update_attachment_metadata($image_id, $metadata);
                    
                    // Get file sizes for comparison
                    $original_size = filesize($file_path);
                    $webp_size = filesize($webp_path);
                    $savings = $original_size - $webp_size;
                    $savings_percent = round(($savings / $original_size) * 100);
                    
                    return [
                        'success' => true,
                        'image_id' => $image_id,
                        'title' => $attachment->post_title,
                        'original_size' => $original_size,
                        'webp_size' => $webp_size,
                        'savings' => $savings,
                        'savings_percent' => $savings_percent,
                        'webp_path' => $webp_path
                    ];
                }
            }
        }
        
        // Fallback: try using GD or Imagick
        if (extension_loaded('gd') || extension_loaded('imagick')) {
            $image_data = file_get_contents($file_path);
            $image_info = getimagesizefromstring($image_data);
            
            if ($image_info) {
                $width = $image_info[0];
                $height = $image_info[1];
                $mime_type = $image_info['mime'];
                
                if (extension_loaded('imagick')) {
                    $imagick = new Imagick();
                    $imagick->readImageBlob($image_data);
                    $imagick->setImageFormat('webp');
                    $imagick->setImageCompressionQuality(85);
                    $imagick->writeImage($webp_path);
                    $imagick->clear();
                    $imagick->destroy();
                    
                    // Get file sizes
                    $original_size = filesize($file_path);
                    $webp_size = filesize($webp_path);
                    $savings = $original_size - $webp_size;
                    $savings_percent = round(($savings / $original_size) * 100);
                    
                    return [
                        'success' => true,
                        'image_id' => $image_id,
                        'title' => $attachment->post_title,
                        'original_size' => $original_size,
                        'webp_size' => $webp_size,
                        'savings' => $savings,
                        'savings_percent' => $savings_percent,
                        'webp_path' => $webp_path
                    ];
                }
            }
        }
        
        return ['success' => false, 'message' => 'No suitable image processing library available'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Render the optimization page
function thrivingstudio_image_optimization_page() {
    $analysis = analyze_wordpress_images();
    $optimization_results = get_transient('thrivingstudio_optimization_results');
    
    ?>
    <div class="wrap">
        <h1>🖼️ Image Optimization</h1>
        
        <?php if (isset($_GET['optimized']) && $optimization_results): ?>
            <div class="notice notice-success is-dismissible">
                <h3>✅ Optimization Complete!</h3>
                <p>Successfully processed <?php echo count(array_filter($optimization_results, fn($r) => $r['success'])); ?> images.</p>
            </div>
            
            <h3>📊 Optimization Results:</h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Original Size</th>
                        <th>WebP Size</th>
                        <th>Savings</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($optimization_results as $result): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($result['title'] ?? 'Unknown'); ?></strong>
                                <?php if (isset($result['image_id'])): ?>
                                    <br><small>ID: <?php echo $result['image_id']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo isset($result['original_size']) ? format_file_size($result['original_size']) : '-'; ?></td>
                            <td><?php echo isset($result['webp_size']) ? format_file_size($result['webp_size']) : '-'; ?></td>
                            <td>
                                <?php if (isset($result['savings'])): ?>
                                    <span style="color: #28a745; font-weight: bold;">
                                        <?php echo format_file_size($result['savings']); ?> (<?php echo $result['savings_percent']; ?>%)
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($result['success']): ?>
                                    <span style="color: #28a745;">✅ Success</span>
                                <?php else: ?>
                                    <span style="color: #dc3545;">❌ <?php echo esc_html($result['message']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php
            $total_savings = array_sum(array_column(array_filter($optimization_results, fn($r) => $r['success']), 'savings'));
            if ($total_savings > 0): ?>
                <div class="notice notice-info">
                    <h4>🎉 Total Savings: <?php echo format_file_size($total_savings); ?></h4>
                    <p>This should significantly improve your website performance!</p>
                </div>
            <?php endif; ?>
            
            <hr style="margin: 30px 0;">
        <?php endif; ?>
        
        <div class="card">
            <h2>📊 Current Image Status</h2>
            <p>Your website contains <strong><?php echo count($analysis['large_images']); ?> large images</strong> that are significantly slowing down page load times.</p>
            
            <?php if ($analysis['total_size'] > 5 * 1024 * 1024): ?>
                <div class="notice notice-error">
                    <h3>🚨 CRITICAL PERFORMANCE ISSUE</h3>
                    <p>Total image size is <strong><?php echo format_file_size($analysis['total_size']); ?></strong> - this will severely impact performance!</p>
                </div>
            <?php endif; ?>
            
            <h3>🔴 Large Images (>500KB):</h3>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="optimize_images">
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Image</th>
                            <th>Size</th>
                            <th>Expected Savings</th>
                            <th>Preview</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analysis['large_images'] as $image): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="image_ids[]" value="<?php echo $image['id']; ?>">
                                </td>
                                <td>
                                    <strong><?php echo esc_html($image['title']); ?></strong><br>
                                    <small>ID: <?php echo $image['id']; ?></small><br>
                                    <code><?php echo esc_url($image['url']); ?></code>
                                </td>
                                <td>
                                    <span style="color: #dc3545; font-weight: bold;">
                                        <?php echo format_file_size($image['size']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $expected_savings = round($image['size'] * 0.6 / 1024);
                                    echo "<span style='color: #28a745; font-weight: bold;'>~{$expected_savings} KB</span>";
                                    ?>
                                </td>
                                <td>
                                    <img src="<?php echo esc_url($image['url']); ?>" 
                                         style="max-width: 100px; max-height: 100px; object-fit: cover; border: 1px solid #ddd;"
                                         alt="<?php echo esc_attr($image['title']); ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large">
                        🚀 Optimize Selected Images
                    </button>
                    <span class="description">
                        This will convert selected images to WebP format for better performance.
                    </span>
                </p>
            </form>
        </div>
        
        <div class="card">
            <h2>💡 Optimization Tips</h2>
            <ul>
                <li><strong>Start with the largest images first</strong> - they provide the biggest performance gains</li>
                <li><strong>WebP format</strong> typically provides 60-80% file size reduction</li>
                <li><strong>Test your website</strong> after optimization to see performance improvements</li>
                <li><strong>Consider a CDN</strong> for even better image delivery performance</li>
            </ul>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Select all checkbox functionality
        $('#select-all').change(function() {
            $('input[name="image_ids[]"]').prop('checked', this.checked);
        });
        
        // Update select all checkbox state
        $('input[name="image_ids[]"]').change(function() {
            var total = $('input[name="image_ids[]"]').length;
            var checked = $('input[name="image_ids[]"]:checked').length;
            $('#select-all').prop('indeterminate', checked > 0 && checked < total);
            $('#select-all').prop('checked', checked === total);
        });
    });
    </script>
    <?php
}

// Helper function to format file size
function format_file_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}
