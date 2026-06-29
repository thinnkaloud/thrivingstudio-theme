<?php

/**
 * Get only the manually entered WordPress excerpt.
 *
 * WordPress can generate excerpts from post content. This helper intentionally
 * avoids that fallback so editorial subtitles stay separate from the body copy.
 *
 * @param int|WP_Post|null $post_id Optional post ID or post object.
 * @return string
 */
function thrivingstudio_get_manual_excerpt($post_id = null) {
    $post = get_post($post_id ?: get_the_ID());

    if (!$post instanceof WP_Post) {
        return '';
    }

    $excerpt = trim((string) $post->post_excerpt);

    if ($excerpt === '') {
        return '';
    }

    $excerpt = strip_shortcodes($excerpt);
    $excerpt = wp_strip_all_tags($excerpt);
    $excerpt = preg_replace('/\s+/', ' ', $excerpt);

    return trim($excerpt);
}
