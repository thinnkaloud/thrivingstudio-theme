<?php

function thrivingstudio_get_post_useful_count($post_id) {
    return max(0, (int) get_post_meta((int) $post_id, '_thrivingstudio_useful_count', true));
}

function thrivingstudio_enqueue_post_engagement_data() {
    if (!is_singular('post')) {
        return;
    }

    wp_localize_script(
        'thrivingstudio-js',
        'thrivingstudioPostEngagement',
        [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('thrivingstudio_post_engagement'),
            'strings' => [
                'thanks'       => __('Thanks for the signal.', 'thrivingstudio'),
                'removed'      => __('Signal removed.', 'thrivingstudio'),
                'saved'        => __('Saved on this device.', 'thrivingstudio'),
                'error'        => __('Could not save that signal. Please try again.', 'thrivingstudio'),
                'copied'       => __('Link copied.', 'thrivingstudio'),
                'copyError'    => __('Copy failed. Please try again.', 'thrivingstudio'),
                'shared'       => __('Share sheet opened.', 'thrivingstudio'),
                'shareFallback' => __('Link copied.', 'thrivingstudio'),
                'shareError'   => __('Sharing is not available here.', 'thrivingstudio'),
            ],
        ]
    );
}
add_action('wp_enqueue_scripts', 'thrivingstudio_enqueue_post_engagement_data', 20);

function thrivingstudio_ajax_toggle_post_useful() {
    check_ajax_referer('thrivingstudio_post_engagement', 'nonce');

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $selected_value = isset($_POST['selected']) ? sanitize_text_field((string) wp_unslash($_POST['selected'])) : '';
    $selected = in_array($selected_value, ['1', 'true', 'yes'], true);

    if (!$post_id || get_post_type($post_id) !== 'post' || get_post_status($post_id) !== 'publish') {
        wp_send_json_error(['message' => __('Invalid post.', 'thrivingstudio')], 400);
    }

    $count = thrivingstudio_get_post_useful_count($post_id);
    $count = $selected ? $count + 1 : max(0, $count - 1);

    update_post_meta($post_id, '_thrivingstudio_useful_count', $count);

    wp_send_json_success([
        'count'    => $count,
        'selected' => $selected,
    ]);
}
add_action('wp_ajax_thrivingstudio_toggle_post_useful', 'thrivingstudio_ajax_toggle_post_useful');
add_action('wp_ajax_nopriv_thrivingstudio_toggle_post_useful', 'thrivingstudio_ajax_toggle_post_useful');
