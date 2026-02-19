<?php
/**
 * Cleanup Orphaned Media Entries
 * Removes WordPress media entries that don't have corresponding files
 */

// Only run if user is admin
if (!current_user_can('manage_options')) {
    return;
}

// Add admin notice with cleanup option
add_action('admin_notices', 'thrivingstudio_orphaned_media_notice');

function thrivingstudio_orphaned_media_notice() {
    $orphaned_count = count_orphaned_media();
    
    if ($orphaned_count > 0) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<h3>🧹 Orphaned Media Entries Found</h3>';
        echo '<p>Found <strong>' . $orphaned_count . ' media entries</strong> in WordPress that don\'t have corresponding files.</p>';
        echo '<p>This can cause confusion with image optimization plugins.</p>';
        
        echo '<form method="post" style="margin: 15px 0;">';
        echo '<input type="hidden" name="action" value="cleanup_orphaned_media">';
        echo '<button type="submit" class="button button-primary">🧹 Clean Up Orphaned Media</button>';
        echo '<span style="margin-left: 10px; color: #666;">This will remove database entries for missing files</span>';
        echo '</form>';
        
        echo '</div>';
    }
}

// Handle cleanup
add_action('admin_post_cleanup_orphaned_media', 'thrivingstudio_handle_cleanup');

function thrivingstudio_handle_cleanup() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $results = cleanup_orphaned_media();
    
    // Store results for display
    set_transient('thrivingstudio_cleanup_results', $results, 300);
    
    // Redirect back
    wp_redirect(admin_url('admin.php?page=media&cleaned=1'));
    exit;
}

// Count orphaned media entries
function count_orphaned_media() {
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => array('image/jpeg', 'image/jpg', 'image/png', 'image/gif'),
        'post_status' => 'inherit',
        'posts_per_page' => -1
    );
    
    $attachments = get_posts($args);
    $orphaned_count = 0;
    
    foreach ($attachments as $attachment) {
        $file_path = get_attached_file($attachment->ID);
        
        if (!file_exists($file_path)) {
            $orphaned_count++;
        }
    }
    
    return $orphaned_count;
}

// Clean up orphaned media entries
function cleanup_orphaned_media() {
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => array('image/jpeg', 'image/jpg', 'image/png', 'image/gif'),
        'post_status' => 'inherit',
        'posts_per_page' => -1
    );
    
    $attachments = get_posts($args);
    $cleaned_count = 0;
    $errors = array();
    
    foreach ($attachments as $attachment) {
        $file_path = get_attached_file($attachment->ID);
        
        if (!file_exists($file_path)) {
            // Check if this media is used in any posts
            $usage = get_posts(array(
                'post_type' => array('post', 'page'),
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => '_thumbnail_id',
                        'value' => $attachment->ID,
                        'compare' => '='
                    )
                )
            ));
            
            // Also check if it's referenced in post content
            $content_usage = get_posts(array(
                'post_type' => array('post', 'page'),
                'post_status' => 'publish',
                's' => $attachment->post_title
            ));
            
            if (empty($usage) && empty($content_usage)) {
                // Safe to delete - not used anywhere
                $result = wp_delete_attachment($attachment->ID, true);
                
                if ($result) {
                    $cleaned_count++;
                } else {
                    $errors[] = 'Failed to delete attachment ID: ' . $attachment->ID;
                }
            }
        }
    }
    
    return array(
        'cleaned_count' => $cleaned_count,
        'errors' => $errors
    );
}

// Show cleanup results
add_action('admin_notices', 'thrivingstudio_show_cleanup_results');

function thrivingstudio_show_cleanup_results() {
    if (!isset($_GET['cleaned'])) {
        return;
    }
    
    $results = get_transient('thrivingstudio_cleanup_results');
    if (!$results) {
        return;
    }
    
    echo '<div class="notice notice-success is-dismissible">';
    echo '<h3>🎉 Orphaned Media Cleanup Complete!</h3>';
    echo '<p>Successfully removed <strong>' . $results['cleaned_count'] . ' orphaned media entries</strong>.</p>';
    
    if (!empty($results['errors'])) {
        echo '<p><strong>Errors encountered:</strong></p>';
        echo '<ul>';
        foreach ($results['errors'] as $error) {
            echo '<li>' . esc_html($error) . '</li>';
        }
        echo '</ul>';
    }
    
    echo '<p>Your media library should now be cleaner and more accurate.</p>';
    echo '</div>';
    
    delete_transient('thrivingstudio_cleanup_results');
}

