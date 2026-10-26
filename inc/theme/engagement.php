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

/**
 * Who is voting: the account for signed-in readers, otherwise the IP address.
 * Hosts that sit behind a proxy without restoring REMOTE_ADDR can supply the
 * real client IP through the `thrivingstudio_useful_visitor_ip` filter.
 */
function thrivingstudio_get_useful_vote_key($post_id) {
    $user_id = get_current_user_id();

    if ($user_id) {
        $visitor = 'user:' . $user_id;
    } else {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
        $ip = (string) apply_filters('thrivingstudio_useful_visitor_ip', $ip);
        $visitor = 'ip:' . $ip;
    }

    return 'ts_useful_' . md5(wp_salt('nonce') . '|' . (int) $post_id . '|' . $visitor);
}

/**
 * True when the browser says the request came from this site. Browsers set
 * Origin on POST requests and pages cannot forge it, so this blocks other
 * websites from casting votes through a visitor's browser.
 */
function thrivingstudio_useful_request_is_same_origin() {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        $source = (string) $_SERVER['HTTP_ORIGIN'];
    } else {
        $source = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
    }

    $source_host = wp_parse_url($source, PHP_URL_HOST);
    $site_host = wp_parse_url(home_url(), PHP_URL_HOST);

    return $source_host && $site_host && strtolower($source_host) === strtolower($site_host);
}

function thrivingstudio_ajax_toggle_post_useful() {
    // Anonymous visitors read cached pages, where a nonce goes stale within a
    // day. They are checked by origin instead, and the per-visitor vote
    // record below stops repeat votes.
    if (is_user_logged_in()) {
        check_ajax_referer('thrivingstudio_post_engagement', 'nonce');
    } elseif (!thrivingstudio_useful_request_is_same_origin()) {
        wp_send_json_error(['message' => __('Invalid request.', 'thrivingstudio')], 403);
    }

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $selected_value = isset($_POST['selected']) ? sanitize_text_field((string) wp_unslash($_POST['selected'])) : '';
    $selected = in_array($selected_value, ['1', 'true', 'yes'], true);

    if (!$post_id || get_post_type($post_id) !== 'post' || get_post_status($post_id) !== 'publish') {
        wp_send_json_error(['message' => __('Invalid post.', 'thrivingstudio')], 400);
    }

    $vote_key = thrivingstudio_get_useful_vote_key($post_id);
    $has_voted = (bool) get_transient($vote_key);
    $count = thrivingstudio_get_post_useful_count($post_id);

    // Each visitor can add one vote per post and can only take back their own.
    if ($selected && !$has_voted) {
        $count++;
        update_post_meta($post_id, '_thrivingstudio_useful_count', $count);
        set_transient($vote_key, 1, 90 * DAY_IN_SECONDS);
    } elseif (!$selected && $has_voted) {
        $count = max(0, $count - 1);
        update_post_meta($post_id, '_thrivingstudio_useful_count', $count);
        delete_transient($vote_key);
    }

    wp_send_json_success([
        'count'    => $count,
        'selected' => $selected,
    ]);
}
add_action('wp_ajax_thrivingstudio_toggle_post_useful', 'thrivingstudio_ajax_toggle_post_useful');
add_action('wp_ajax_nopriv_thrivingstudio_toggle_post_useful', 'thrivingstudio_ajax_toggle_post_useful');
