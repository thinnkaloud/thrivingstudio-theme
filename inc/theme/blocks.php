<?php

/**
 * Custom editor blocks for Thriving Studio.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default editable rows for the FAQ accordion block.
 *
 * @return array<int, array<string, string>>
 */
function thrivingstudio_faq_block_default_items() {
    return [
        [
            'question' => '',
            'answer' => '',
        ],
        [
            'question' => '',
            'answer' => '',
        ],
        [
            'question' => '',
            'answer' => '',
        ],
    ];
}

/**
 * Register the FAQ / related questions accordion block.
 */
function thrivingstudio_register_faq_block() {
    $script_path = THRIVINGSTUDIO_DIR . '/assets/js/editor-faq-block.js';

    if (file_exists($script_path)) {
        wp_register_script(
            'thrivingstudio-faq-block-editor',
            THRIVINGSTUDIO_URI . '/assets/js/editor-faq-block.js',
            [
                'wp-block-editor',
                'wp-blocks',
                'wp-components',
                'wp-element',
                'wp-i18n',
            ],
            filemtime($script_path),
            true
        );
    }

    register_block_type(
        'thrivingstudio/faq-accordion',
        [
            'api_version'     => 2,
            'editor_script'   => file_exists($script_path) ? 'thrivingstudio-faq-block-editor' : null,
            'render_callback' => 'thrivingstudio_render_faq_block',
            'attributes'      => [
                'heading'   => [
                    'type'    => 'string',
                    'default' => __('Frequently Asked Questions', 'thrivingstudio'),
                ],
                'items'     => [
                    'type'    => 'array',
                    'default' => thrivingstudio_faq_block_default_items(),
                ],
                'firstOpen' => [
                    'type'    => 'boolean',
                    'default' => false,
                ],
            ],
            'supports'        => [
                'anchor'   => true,
                'html'     => false,
                'reusable' => true,
            ],
        ]
    );
}
add_action('init', 'thrivingstudio_register_faq_block');

/**
 * Load the inline citation format in the block editor.
 */
function thrivingstudio_enqueue_citation_format() {
    $script_path = THRIVINGSTUDIO_DIR . '/assets/js/editor-citation-format.js';

    if (!file_exists($script_path)) {
        return;
    }

    wp_enqueue_script(
        'thrivingstudio-citation-format',
        THRIVINGSTUDIO_URI . '/assets/js/editor-citation-format.js',
        [
            'wp-block-editor',
            'wp-components',
            'wp-element',
            'wp-i18n',
            'wp-rich-text',
        ],
        filemtime($script_path),
        true
    );
}
add_action('enqueue_block_editor_assets', 'thrivingstudio_enqueue_citation_format');

/**
 * Normalize and sanitize accordion rows from block attributes.
 *
 * @param mixed $items Raw block attribute data.
 * @return array<int, array<string, string>>
 */
function thrivingstudio_get_faq_block_items($items) {
    if (!is_array($items)) {
        return [];
    }

    $normalized_items = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $question = isset($item['question']) ? trim(wp_strip_all_tags((string) $item['question'])) : '';
        $answer = isset($item['answer']) ? trim(wp_kses_post((string) $item['answer'])) : '';

        if ($question === '' || $answer === '') {
            continue;
        }

        $normalized_items[] = [
            'question' => $question,
            'answer'   => $answer,
        ];
    }

    return $normalized_items;
}

/**
 * Format an FAQ answer as readable article content.
 *
 * @param string $answer Sanitized answer HTML.
 * @return string
 */
function thrivingstudio_format_faq_answer($answer) {
    $answer = trim($answer);

    if ($answer === '') {
        return '';
    }

    $has_block_markup = (bool) preg_match('/<(p|ul|ol|li|blockquote|pre|table|h[2-6]|div)\b/i', $answer);

    return $has_block_markup ? $answer : wpautop($answer);
}

/**
 * Render the FAQ accordion block on the front end.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function thrivingstudio_render_faq_block($attributes) {
    $items = thrivingstudio_get_faq_block_items($attributes['items'] ?? []);

    if (empty($items)) {
        return '';
    }

    $heading = isset($attributes['heading']) ? trim(wp_strip_all_tags((string) $attributes['heading'])) : '';
    $first_open = !empty($attributes['firstOpen']);
    $heading_id = wp_unique_id('ts-faq-block-title-');
    $section_attributes = [
        'class' => 'ts-faq-block not-prose',
    ];

    if ($heading !== '') {
        $section_attributes['aria-labelledby'] = $heading_id;
    } else {
        $section_attributes['aria-label'] = __('Frequently Asked Questions', 'thrivingstudio');
    }

    $output = '<section ' . get_block_wrapper_attributes($section_attributes) . '>';

    if ($heading !== '') {
        $output .= '<h2 id="' . esc_attr($heading_id) . '" class="ts-faq-block-title">' . esc_html($heading) . '</h2>';
    }

    $output .= '<div class="ts-faq-list">';
    $schema_entities = [];

    foreach ($items as $index => $item) {
        $answer_html = thrivingstudio_format_faq_answer($item['answer']);

        if ($answer_html === '') {
            continue;
        }

        $output .= '<details class="ts-faq-item"' . ($first_open && $index === 0 ? ' open' : '') . '>';
        $output .= '<summary class="ts-faq-question">';
        $output .= '<span class="ts-faq-question-text">' . esc_html($item['question']) . '</span>';
        $output .= '<span class="ts-faq-icon" aria-hidden="true"></span>';
        $output .= '</summary>';
        $output .= '<div class="ts-faq-answer">' . $answer_html . '</div>';
        $output .= '</details>';

        $schema_entities[] = [
            '@type'          => 'Question',
            'name'           => $item['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => trim(wp_strip_all_tags($item['answer'])),
            ],
        ];
    }

    $output .= '</div>';

    if (!empty($schema_entities)) {
        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $schema_entities,
        ];

        $output .= '<script type="application/ld+json" class="ts-faq-schema">';
        $output .= wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $output .= '</script>';
    }

    $output .= '</section>';

    return $output;
}
