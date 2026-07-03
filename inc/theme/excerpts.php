<?php

/**
 * Get the editorial subtitle shown beneath article titles.
 *
 * New articles use the dedicated subtitle meta field. Older articles may still
 * have this copy in the manual WordPress excerpt, so keep that as a fallback.
 * This intentionally avoids generated excerpts from the post body.
 *
 * @param int|WP_Post|null $post_id Optional post ID or post object.
 * @return string
 */
function thrivingstudio_get_manual_excerpt($post_id = null) {
    $post = get_post($post_id ?: get_the_ID());

    if (!$post instanceof WP_Post) {
        return '';
    }

    $excerpt = get_post_meta($post->ID, '_thrivingstudio_article_subtitle', true);

    if ($excerpt === '' && !metadata_exists('post', $post->ID, '_thrivingstudio_article_subtitle')) {
        $excerpt = $post->post_excerpt;
    }

    $excerpt = trim((string) $excerpt);

    if ($excerpt === '') {
        return '';
    }

    $excerpt = strip_shortcodes($excerpt);
    $excerpt = wp_strip_all_tags($excerpt);
    $excerpt = preg_replace('/\s+/', ' ', $excerpt);

    return trim($excerpt);
}
