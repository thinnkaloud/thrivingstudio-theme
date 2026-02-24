<?php

/**
 * Ensure a Blog page exists and is set as the posts page on theme activation.
 */
function thrivingstudio_ensure_blog_page() {
    // Check if a posts page is already set
    $posts_page_id = get_option('page_for_posts');
    if ($posts_page_id && get_post($posts_page_id)) {
        return; // Already set
    }
    // Check if a page titled 'Blog' exists
    $blog_page = get_page_by_path('blog');
    if (!$blog_page) {
        // Create the Blog page
        $blog_page_id = wp_insert_post([
            'post_title'   => 'Blog',
            'post_name'    => 'blog',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ]);
    } else {
        $blog_page_id = $blog_page->ID;
    }
    // Set as posts page
    if ($blog_page_id) {
        update_option('page_for_posts', $blog_page_id);
    }
}
add_action('after_switch_theme', 'thrivingstudio_ensure_blog_page');

/**
 * Register the Quote Cards custom post type.
 */
function thrivingstudio_register_quote_cards_cpt() {
    $labels = [
        'name' => __('Quote Cards', 'thrivingstudio'),
        'singular_name' => __('Quote Card', 'thrivingstudio'),
        'add_new' => __('Add New', 'thrivingstudio'),
        'add_new_item' => __('Add New Quote Card', 'thrivingstudio'),
        'edit_item' => __('Edit Quote Card', 'thrivingstudio'),
        'new_item' => __('New Quote Card', 'thrivingstudio'),
        'view_item' => __('View Quote Card', 'thrivingstudio'),
        'search_items' => __('Search Quote Cards', 'thrivingstudio'),
        'not_found' => __('No quote cards found', 'thrivingstudio'),
        'not_found_in_trash' => __('No quote cards found in Trash', 'thrivingstudio'),
        'all_items' => __('All Quote Cards', 'thrivingstudio'),
        'menu_name' => __('Quote Cards', 'thrivingstudio'),
        'name_admin_bar' => __('Quote Card', 'thrivingstudio'),
    ];
    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'quotecards'],
        'menu_icon' => 'dashicons-format-image',
        'supports' => ['title', 'thumbnail'],
        'show_in_rest' => true,
    ];
    register_post_type('quote_card', $args);
}
add_action('init', 'thrivingstudio_register_quote_cards_cpt');

/**
 * Add custom meta boxes for Quote Cards (Author and Caption).
 */
function thrivingstudio_quote_card_meta_boxes() {
    add_meta_box(
        'quote_card_author',
        __('Quote Author', 'thrivingstudio'),
        'thrivingstudio_quote_card_author_box',
        'quote_card',
        'normal',
        'default'
    );
    add_meta_box(
        'quote_card_caption',
        __('Quote Caption', 'thrivingstudio'),
        'thrivingstudio_quote_card_caption_box',
        'quote_card',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'thrivingstudio_quote_card_meta_boxes');

/**
 * Render the Quote Card Author meta box.
 *
 * @param WP_Post $post
 */
function thrivingstudio_quote_card_author_box($post) {
    wp_nonce_field('save_quote_card_meta', 'quote_card_meta_nonce');
    $author = get_post_meta($post->ID, '_quote_card_author', true);
    echo '<input type="text" name="quote_card_author" value="' . esc_attr($author) . '" class="widefat" placeholder="e.g. Albert Einstein">';
}

/**
 * Render the Quote Card Caption meta box.
 *
 * @param WP_Post $post
 */
function thrivingstudio_quote_card_caption_box($post) {
    $caption = get_post_meta($post->ID, '_quote_card_caption', true);
    echo '<textarea name="quote_card_caption" class="widefat" rows="8" placeholder="Write a detailed caption or story about this quote...">' . esc_textarea($caption) . '</textarea>';
}

/**
 * Add admin CSS for the Quote Caption textarea.
 */
function thrivingstudio_admin_caption_textarea_css() {
    echo '<style>
    textarea[name="quote_card_caption"] { overflow-y: auto !important; resize: vertical !important; }
    </style>';
}
add_action('admin_head', 'thrivingstudio_admin_caption_textarea_css');

/**
 * Save Quote Card meta fields.
 *
 * @param int $post_id
 */
function thrivingstudio_save_quote_card_meta($post_id) {
    if (!isset($_POST['quote_card_meta_nonce']) || !wp_verify_nonce($_POST['quote_card_meta_nonce'], 'save_quote_card_meta')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (array_key_exists('quote_card_author', $_POST)) {
        update_post_meta($post_id, '_quote_card_author', sanitize_text_field($_POST['quote_card_author']));
    }
    if (array_key_exists('quote_card_caption', $_POST)) {
        update_post_meta($post_id, '_quote_card_caption', sanitize_textarea_field($_POST['quote_card_caption']));
    }
}
add_action('save_post_quote_card', 'thrivingstudio_save_quote_card_meta');

/**
 * Add MutationObserver-based auto-resize for Quote Caption textarea in admin.
 *
 * @param string $hook
 */
function thrivingstudio_admin_autoresize_caption_textarea($hook) {
    if ($hook === 'post-new.php' || $hook === 'post.php') {
        echo '<script>
        function attachAutoResize(textarea) {
            if (!textarea._autoResizeAttached) {
                function resize() {
                    textarea.style.height = "auto";
                    textarea.style.height = (textarea.scrollHeight) + "px";
                }
                textarea.addEventListener("input", resize);
                resize();
                textarea._autoResizeAttached = true;
            }
        }
        function observeTextarea() {
            var observer = new MutationObserver(function() {
                var textarea = document.querySelector("textarea[name=\\"quote_card_caption\\"]");
                if (textarea) {
                    attachAutoResize(textarea);
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
            // Initial attach
            var textarea = document.querySelector("textarea[name=\\"quote_card_caption\\"]");
            if (textarea) {
                attachAutoResize(textarea);
            }
        }
        document.addEventListener("DOMContentLoaded", observeTextarea);
        </script>';
    }
}
add_action('admin_footer', 'thrivingstudio_admin_autoresize_caption_textarea');
