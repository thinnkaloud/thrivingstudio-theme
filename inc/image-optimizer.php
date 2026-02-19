<?php
/**
 * Image Optimizer Plugin for ThrivingStudio
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ThrivingStudioImageOptimizer {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_notices', array($this, 'show_notice'));
    }
    
    public function add_menu() {
        add_menu_page(
            'Image Optimizer',
            'Image Optimizer',
            'manage_options',
            'image-optimizer',
            array($this, 'admin_page'),
            'dashicons-images-alt2',
            30
        );
    }
    
    public function show_notice() {
        $large_images = $this->get_large_images();
        
        if (empty($large_images)) {
            return;
        }
        
        $total_size = array_sum(array_column($large_images, 'size'));
        $total_size_mb = round($total_size / (1024 * 1024), 2);
        
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<h3>🚨 Large Images Detected!</h3>';
        echo '<p>Found <strong>' . count($large_images) . ' large images</strong> totaling <strong>' . $total_size_mb . ' MB</strong>.</p>';
        echo '<p><a href="' . admin_url('admin.php?page=image-optimizer') . '" class="button button-primary">🖼️ Optimize Now</a></p>';
        echo '</div>';
    }
    
    private function get_large_images() {
        $upload_dir = wp_upload_dir();
        $large_images = array();
        
        $this->scan_directory($upload_dir['basedir'], $large_images);
        
        return $large_images;
    }
    
    private function get_total_image_count() {
        $upload_dir = wp_upload_dir();
        $count = 0;
        
        $this->count_images_recursive($upload_dir['basedir'], $count);
        
        return $count;
    }
    
    private function count_images_recursive($dir, &$count) {
        if (!is_dir($dir)) return;
        
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $file_path = $dir . '/' . $file;
            
            if (is_dir($file_path)) {
                $this->count_images_recursive($file_path, $count);
            } else {
                $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $count++;
                }
            }
        }
    }
    
    private function scan_directory($dir, &$large_images) {
        if (!is_dir($dir)) return;
        
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $file_path = $dir . '/' . $file;
            
            if (is_dir($file_path)) {
                $this->scan_directory($file_path, $large_images);
            } else {
                $this->check_image($file_path, $large_images);
            }
        }
    }
    
    private function check_image($file_path, &$large_images) {
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) return;
        
        // Skip WordPress thumbnails (files with dimensions in name)
        $filename = basename($file_path);
        if (preg_match('/-\d+x\d+\./', $filename)) {
            return; // Skip thumbnails
        }
        
        // Skip cropped images
        if (strpos($filename, 'cropped-') === 0) {
            return; // Skip cropped versions
        }
        
        $size = filesize($file_path);
        $size_mb = $size / (1024 * 1024);
        
        if ($size_mb > 0.5) {
            $relative_path = str_replace(wp_upload_dir()['basedir'] . '/', '', $file_path);
            
            $large_images[] = array(
                'path' => $file_path,
                'relative_path' => $relative_path,
                'size' => $size,
                'size_mb' => $size_mb,
                'extension' => $ext
            );
        }
    }
    
    public function admin_page() {
        $large_images = $this->get_large_images();
        $total_size = array_sum(array_column($large_images, 'size'));
        $total_size_mb = round($total_size / (1024 * 1024), 2);
        
        // Get total image count for comparison
        $total_images = $this->get_total_image_count();
        
        ?>
        <div class="wrap">
            <h1>🖼️ Image Optimizer</h1>
            
            <div class="card">
                <h2>📊 Status</h2>
                <p>Found <strong><?php echo count($large_images); ?> large original images</strong> totaling <strong><?php echo $total_size_mb; ?> MB</strong>.</p>
                <p><small>Total images in uploads: <?php echo $total_images; ?> (including thumbnails)</small></p>
                
                <?php if (count($large_images) > 0): ?>
                    <p><strong>Recommendation:</strong> Use a professional image optimization service like:</p>
                    <ul>
                        <li><strong>ShortPixel</strong> - Excellent WebP conversion</li>
                        <li><strong>TinyPNG</strong> - Great compression</li>
                        <li><strong>Imagify</strong> - WordPress-specific</li>
                    </ul>
                    
                    <p><a href="https://wordpress.org/plugins/shortpixel-adaptive-images/" target="_blank" class="button button-primary">🚀 Install ShortPixel (Recommended)</a></p>
                <?php else: ?>
                    <p>🎉 No large images found! Your website is optimized.</p>
                <?php endif; ?>
            </div>
            
            <?php if (count($large_images) > 0): ?>
                <div class="card">
                    <h2>📋 Large Images</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Size</th>
                                <th>Path</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($large_images as $image): ?>
                                <tr>
                                    <td><?php echo esc_html(basename($image['relative_path'])); ?></td>
                                    <td><?php echo round($image['size_mb'], 2); ?> MB</td>
                                    <td><code><?php echo esc_html($image['relative_path']); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

new ThrivingStudioImageOptimizer();
