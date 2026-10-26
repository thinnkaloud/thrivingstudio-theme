<?php
/** Archive titles must describe the requested page, not the first loop post. */
function thrivingstudio_archive_document_title($title) {
    if (is_home()) {
        $page_id = (int) get_option('page_for_posts');
        $label = $page_id ? get_the_title($page_id) : __('Blog', 'thrivingstudio');
    } elseif (is_category()) {
        $term = get_queried_object();
        $label = $term instanceof WP_Term ? $term->name : '';
    } else {
        return $title;
    }

    if (empty($label)) {
        return $title;
    }

    $parts = [$label];
    $page = max(1, (int) get_query_var('paged'));
    if ($page > 1) {
        $parts[] = sprintf(__('Page %d', 'thrivingstudio'), $page);
    }
    $parts[] = get_bloginfo('name');
    return implode(' — ', $parts);
}
add_filter('pre_get_document_title', 'thrivingstudio_archive_document_title', 99);
// Use the same context when an SEO plugin owns the title output.
add_filter('wpseo_title', 'thrivingstudio_archive_document_title', 99);
add_filter('rank_math/frontend/title', 'thrivingstudio_archive_document_title', 99);
