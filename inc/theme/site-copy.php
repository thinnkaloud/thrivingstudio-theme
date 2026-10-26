<?php
/**
 * Site-wide wording and the three content pillars.
 *
 * Every string here has a default that matches the brand positioning and can
 * be overridden under Appearance > Customize > Site Copy.
 */

/**
 * The three pillars, in display order. Each maps to a category by slug.
 */
function thrivingstudio_pillar_definitions() {
    return [
        'inner-strength' => [
            'name' => __('Inner Strength', 'thrivingstudio'),
            'description' => __('Emotional strength, awareness, and how the mind and human behaviour really work.', 'thrivingstudio'),
        ],
        'discipline' => [
            'name' => __('Discipline', 'thrivingstudio'),
            'description' => __('Habits, focus, fitness, and practical tools for growing steadily in life.', 'thrivingstudio'),
        ],
        'curious-world' => [
            'name' => __('Curious World', 'thrivingstudio'),
            'description' => __('Science and technology explained simply, and good news from around the world.', 'thrivingstudio'),
        ],
    ];
}

/**
 * Pillars that exist as categories, with their link and description.
 * A description set in Site Copy wins over the category's own description,
 * which wins over the built-in default.
 */
function thrivingstudio_get_pillars() {
    $pillars = [];

    foreach (thrivingstudio_pillar_definitions() as $slug => $pillar) {
        $category = get_category_by_slug($slug);
        if (!$category) {
            continue;
        }

        $description = trim((string) get_theme_mod('thrivingstudio_copy_pillar_' . str_replace('-', '_', $slug), ''));
        if ($description === '') {
            $description = trim(wp_strip_all_tags(category_description($category->term_id)));
        }
        if ($description === '') {
            $description = $pillar['description'];
        }

        $pillars[] = [
            'slug' => $slug,
            'name' => $category->name,
            'description' => $description,
            'url' => get_category_link($category->term_id),
        ];
    }

    return $pillars;
}

/**
 * The journal (posts page) URL. get_permalink(0) returns the current post's
 * link, so the posts-page setting must be checked before resolving it.
 */
function thrivingstudio_get_journal_url() {
    $posts_page_id = (int) get_option('page_for_posts');

    return $posts_page_id ? get_permalink($posts_page_id) : home_url('/blog/');
}

function thrivingstudio_site_copy_fields() {
    return [
        'home_eyebrow' => [
            'label' => __('Homepage: line above the title', 'thrivingstudio'),
            'default' => __('Clarity Over Noise', 'thrivingstudio'),
        ],
        'home_hero_secondary_label' => [
            'label' => __('Homepage: second hero button', 'thrivingstudio'),
            'default' => __('Get the weekly letter', 'thrivingstudio'),
        ],
        'home_pillars_title' => [
            'label' => __('Homepage: pillars heading', 'thrivingstudio'),
            'default' => __('What we write about', 'thrivingstudio'),
        ],
        'home_latest_title' => [
            'label' => __('Homepage: latest articles heading', 'thrivingstudio'),
            'default' => __('Latest from the journal', 'thrivingstudio'),
        ],
        'home_latest_intro' => [
            'label' => __('Homepage: latest articles intro', 'thrivingstudio'),
            'default' => __('Researched, practical pieces. Fewer posts, more clarity.', 'thrivingstudio'),
            'type' => 'textarea',
        ],
        'home_social_intro' => [
            'label' => __('Homepage: social section intro', 'thrivingstudio'),
            'default' => __('Short ideas, quote cards, and good news, shared where you already spend time.', 'thrivingstudio'),
            'type' => 'textarea',
        ],
        'newsletter_title' => [
            'label' => __('Newsletter: heading', 'thrivingstudio'),
            'default' => __('The Weekly Letter', 'thrivingstudio'),
        ],
        'newsletter_text' => [
            'label' => __('Newsletter: pitch', 'thrivingstudio'),
            'default' => __('One clear idea each week on inner strength, discipline, and the world worth being curious about. Free, and no noise.', 'thrivingstudio'),
            'type' => 'textarea',
        ],
        'blog_title' => [
            'label' => __('Blog page: title', 'thrivingstudio'),
            'default' => __('The Journal', 'thrivingstudio'),
        ],
        'blog_intro' => [
            'label' => __('Blog page: intro', 'thrivingstudio'),
            'default' => __('Practical writing on a steadier mind, a disciplined life, and a world worth being curious about.', 'thrivingstudio'),
            'type' => 'textarea',
        ],
        'book_eyebrow' => [
            'label' => __('Book page: label', 'thrivingstudio'),
            'default' => __('The Thriving Studio book', 'thrivingstudio'),
        ],
        'book_title' => [
            'label' => __('Book page: title', 'thrivingstudio'),
            'default' => __('A book is taking shape', 'thrivingstudio'),
        ],
        'book_text' => [
            'label' => __('Book page: text', 'thrivingstudio'),
            'default' => __('A book on building inner strength and steady discipline in a noisy world is taking shape. Join the weekly letter to read early chapters and hear first when it is ready.', 'thrivingstudio'),
            'type' => 'textarea',
        ],
        'pillar_inner_strength' => [
            'label' => __('Inner Strength: description', 'thrivingstudio'),
            'default' => '',
            'type' => 'textarea',
            'description' => __('Leave empty to use the category description.', 'thrivingstudio'),
        ],
        'pillar_discipline' => [
            'label' => __('Discipline: description', 'thrivingstudio'),
            'default' => '',
            'type' => 'textarea',
            'description' => __('Leave empty to use the category description.', 'thrivingstudio'),
        ],
        'pillar_curious_world' => [
            'label' => __('Curious World: description', 'thrivingstudio'),
            'default' => '',
            'type' => 'textarea',
            'description' => __('Leave empty to use the category description.', 'thrivingstudio'),
        ],
    ];
}

/**
 * A Site Copy string, falling back to its default when left empty.
 */
function thrivingstudio_copy($key) {
    $fields = thrivingstudio_site_copy_fields();
    $default = isset($fields[$key]) ? $fields[$key]['default'] : '';
    $value = trim((string) get_theme_mod('thrivingstudio_copy_' . $key, ''));

    return $value !== '' ? $value : $default;
}

function thrivingstudio_site_copy_customize_register($wp_customize) {
    $wp_customize->add_section('thrivingstudio_site_copy_section', [
        'title' => __('Site Copy', 'thrivingstudio'),
        'description' => __('Wording used across the site. Leave a field empty to use the default text.', 'thrivingstudio'),
        'priority' => 29,
    ]);

    foreach (thrivingstudio_site_copy_fields() as $key => $field) {
        $type = isset($field['type']) ? $field['type'] : 'text';

        $wp_customize->add_setting('thrivingstudio_copy_' . $key, [
            'default' => '',
            'sanitize_callback' => $type === 'textarea' ? 'sanitize_textarea_field' : 'sanitize_text_field',
        ]);
        $wp_customize->add_control('thrivingstudio_copy_' . $key, [
            'label' => $field['label'],
            'description' => isset($field['description']) ? $field['description'] : sprintf(__('Default: %s', 'thrivingstudio'), $field['default']),
            'section' => 'thrivingstudio_site_copy_section',
            'type' => $type,
        ]);
    }
}
add_action('customize_register', 'thrivingstudio_site_copy_customize_register');
