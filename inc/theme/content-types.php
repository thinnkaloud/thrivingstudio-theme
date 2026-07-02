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
 * Get Quote Card verification status labels.
 *
 * @return array<string, string>
 */
function thrivingstudio_quote_card_verification_status_options() {
    return [
        '' => __('Not verified', 'thrivingstudio'),
        'verified' => __('Verified source', 'thrivingstudio'),
        'source_backed' => __('Source-backed', 'thrivingstudio'),
        'attributed' => __('Attributed', 'thrivingstudio'),
    ];
}

/**
 * Get the server-side OpenAI API key for quote verification.
 *
 * Define THRIVINGSTUDIO_OPENAI_API_KEY in wp-config.php, or set OPENAI_API_KEY
 * / THRIVINGSTUDIO_OPENAI_API_KEY as an environment variable.
 */
function thrivingstudio_quote_card_ai_api_key() {
    if (defined('THRIVINGSTUDIO_OPENAI_API_KEY') && THRIVINGSTUDIO_OPENAI_API_KEY) {
        return trim((string) THRIVINGSTUDIO_OPENAI_API_KEY);
    }

    $api_key = getenv('THRIVINGSTUDIO_OPENAI_API_KEY');

    if (!$api_key) {
        $api_key = getenv('OPENAI_API_KEY');
    }

    return $api_key ? trim((string) $api_key) : '';
}

/**
 * Get the model used for AI-assisted quote verification.
 */
function thrivingstudio_quote_card_ai_model() {
    if (defined('THRIVINGSTUDIO_OPENAI_MODEL') && THRIVINGSTUDIO_OPENAI_MODEL) {
        return sanitize_text_field((string) THRIVINGSTUDIO_OPENAI_MODEL);
    }

    $model = getenv('THRIVINGSTUDIO_OPENAI_MODEL');

    if (!$model) {
        $model = 'gpt-5.5';
    }

    /**
     * Filter the model used for AI-assisted quote verification.
     *
     * @param string $model
     */
    return apply_filters('thrivingstudio_quote_card_ai_model', sanitize_text_field((string) $model));
}

/**
 * Add custom meta boxes for Quote Cards.
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
    add_meta_box(
        'quote_card_verification',
        __('Quote Verification', 'thrivingstudio'),
        'thrivingstudio_quote_card_verification_box',
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
 * Render the Quote Card Verification meta box.
 *
 * @param WP_Post $post
 */
function thrivingstudio_quote_card_verification_box($post) {
    wp_nonce_field('save_quote_card_meta', 'quote_card_meta_nonce');

    $statuses = thrivingstudio_quote_card_verification_status_options();
    $status = (string) get_post_meta($post->ID, '_quote_card_verification_status', true);
    $source_title = get_post_meta($post->ID, '_quote_card_source_title', true);
    $source_name = get_post_meta($post->ID, '_quote_card_source_name', true);
    $source_url = get_post_meta($post->ID, '_quote_card_source_url', true);
    $verified_date = get_post_meta($post->ID, '_quote_card_verified_date', true);
    $source_note = get_post_meta($post->ID, '_quote_card_source_note', true);
    $ai_available = thrivingstudio_quote_card_ai_api_key() !== '';
    $ai_nonce = wp_create_nonce('thrivingstudio_verify_quote_card_' . $post->ID);
    ?>
    <div class="ts-quote-ai-verifier">
        <button
            type="button"
            class="button button-secondary"
            data-quote-ai-verify
            data-post-id="<?php echo esc_attr($post->ID); ?>"
            data-nonce="<?php echo esc_attr($ai_nonce); ?>"
            <?php disabled(!$ai_available); ?>
        >
            <?php esc_html_e('Verify with AI', 'thrivingstudio'); ?>
        </button>
        <p class="description">
            <?php esc_html_e('AI drafts the source fields below. Review the evidence, then click Update to save and publish the badge.', 'thrivingstudio'); ?>
        </p>
        <?php if (!$ai_available) : ?>
            <p class="description ts-quote-ai-config-note">
                <?php esc_html_e('To enable this button, define THRIVINGSTUDIO_OPENAI_API_KEY in wp-config.php or set OPENAI_API_KEY on the server.', 'thrivingstudio'); ?>
            </p>
        <?php endif; ?>
        <div id="quote-card-ai-result" class="ts-quote-ai-result" aria-live="polite" hidden></div>
    </div>
    <p>
        <label for="quote_card_verification_status"><strong><?php esc_html_e('Verification status', 'thrivingstudio'); ?></strong></label>
        <select id="quote_card_verification_status" name="quote_card_verification_status" class="widefat">
            <?php foreach ($statuses as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($status, $value); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p class="description"><?php esc_html_e('Use Verified only when the quote text has been checked against an original or authoritative source.', 'thrivingstudio'); ?></p>
    <p>
        <label for="quote_card_source_title"><strong><?php esc_html_e('Source title', 'thrivingstudio'); ?></strong></label>
        <input type="text" id="quote_card_source_title" name="quote_card_source_title" value="<?php echo esc_attr($source_title); ?>" class="widefat" placeholder="<?php esc_attr_e('e.g. Interview, book, speech, article, or archive title', 'thrivingstudio'); ?>">
    </p>
    <p>
        <label for="quote_card_source_name"><strong><?php esc_html_e('Publication or source name', 'thrivingstudio'); ?></strong></label>
        <input type="text" id="quote_card_source_name" name="quote_card_source_name" value="<?php echo esc_attr($source_name); ?>" class="widefat" placeholder="<?php esc_attr_e('e.g. Book title, journal, website, or event', 'thrivingstudio'); ?>">
    </p>
    <p>
        <label for="quote_card_source_url"><strong><?php esc_html_e('Source URL', 'thrivingstudio'); ?></strong></label>
        <input type="url" id="quote_card_source_url" name="quote_card_source_url" value="<?php echo esc_attr($source_url); ?>" class="widefat" placeholder="https://">
    </p>
    <p>
        <label for="quote_card_verified_date"><strong><?php esc_html_e('Verified date', 'thrivingstudio'); ?></strong></label>
        <input type="date" id="quote_card_verified_date" name="quote_card_verified_date" value="<?php echo esc_attr($verified_date); ?>">
    </p>
    <p>
        <label for="quote_card_source_note"><strong><?php esc_html_e('Verification note', 'thrivingstudio'); ?></strong></label>
        <textarea id="quote_card_source_note" name="quote_card_source_note" class="widefat" rows="4" placeholder="<?php esc_attr_e('Briefly explain what was checked and any attribution limits.', 'thrivingstudio'); ?>"><?php echo esc_textarea($source_note); ?></textarea>
    </p>
    <?php
}

/**
 * Add admin CSS for the Quote Caption textarea.
 */
function thrivingstudio_admin_caption_textarea_css() {
    echo '<style>
    textarea[name="quote_card_caption"] { overflow-y: auto !important; resize: vertical !important; }
    .ts-quote-ai-verifier { padding: 12px; border: 1px solid #dcdcde; border-radius: 4px; background: #f6f7f7; }
    .ts-quote-ai-verifier .button { margin-bottom: 6px; }
    .ts-quote-ai-config-note { color: #b45309; }
    .ts-quote-ai-result { margin-top: 10px; padding: 10px; border-left: 4px solid #72aee6; background: #fff; }
    .ts-quote-ai-result.is-error { border-left-color: #d63638; }
    .ts-quote-ai-result.is-success { border-left-color: #00a32a; }
    .ts-quote-ai-result p { margin: 0 0 8px; }
    .ts-quote-ai-result ul { margin: 8px 0 0 18px; list-style: disc; }
    .ts-quote-ai-result li { margin-bottom: 6px; }
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

    $statuses = thrivingstudio_quote_card_verification_status_options();
    $status = isset($_POST['quote_card_verification_status']) ? sanitize_key(wp_unslash($_POST['quote_card_verification_status'])) : '';
    if (!array_key_exists($status, $statuses)) {
        $status = '';
    }
    update_post_meta($post_id, '_quote_card_verification_status', $status);

    if (array_key_exists('quote_card_source_title', $_POST)) {
        update_post_meta($post_id, '_quote_card_source_title', sanitize_text_field(wp_unslash($_POST['quote_card_source_title'])));
    }
    if (array_key_exists('quote_card_source_name', $_POST)) {
        update_post_meta($post_id, '_quote_card_source_name', sanitize_text_field(wp_unslash($_POST['quote_card_source_name'])));
    }
    if (array_key_exists('quote_card_source_url', $_POST)) {
        update_post_meta($post_id, '_quote_card_source_url', esc_url_raw(wp_unslash($_POST['quote_card_source_url'])));
    }
    if (array_key_exists('quote_card_verified_date', $_POST)) {
        $verified_date = sanitize_text_field(wp_unslash($_POST['quote_card_verified_date']));
        update_post_meta($post_id, '_quote_card_verified_date', preg_match('/^\d{4}-\d{2}-\d{2}$/', $verified_date) ? $verified_date : '');
    }
    if (array_key_exists('quote_card_source_note', $_POST)) {
        update_post_meta($post_id, '_quote_card_source_note', sanitize_textarea_field(wp_unslash($_POST['quote_card_source_note'])));
    }
}
add_action('save_post_quote_card', 'thrivingstudio_save_quote_card_meta');

/**
 * Build the AI verification prompt for a quote card.
 *
 * @param int $post_id
 * @return string
 */
function thrivingstudio_quote_card_ai_prompt($post_id) {
    $quote = get_the_title($post_id);
    $author = trim((string) get_post_meta($post_id, '_quote_card_author', true));
    $caption = trim((string) get_post_meta($post_id, '_quote_card_caption', true));

    return sprintf(
        "You are an editorial quote-verification assistant for Thriving Studio.\n\n" .
        "Use web search to find supporting evidence for the quote. Prefer original, primary, archival, publisher, transcript, interview, book, speech, court, institutional, or author-controlled sources. Treat quote-aggregator pages as weak evidence.\n\n" .
        "Do not overclaim. Use status \"verified\" only when the exact wording, or a clearly equivalent original wording, is supported by an authoritative source. Use \"source_backed\" when a reputable secondary source supports the quote but the original source is not available. Use \"attributed\" when the attribution is common but weak. Use \"unverified\" when no reliable source is found. Use \"disputed\" when evidence suggests the quote is false, misattributed, or materially altered.\n\n" .
        "Return only a valid JSON object with these keys: status, source_title, source_name, source_url, verified_date, note, confidence, evidence, needs_editor_review.\n" .
        "The evidence key must be an array of up to 4 objects with title, url, and why_it_matters. The verified_date must be today's date in YYYY-MM-DD format. The note must be concise and editor-facing.\n\n" .
        "Quote: %s\nAuthor: %s\nExisting caption/context: %s\nToday: %s",
        $quote,
        $author ?: 'Unknown',
        $caption ?: 'None',
        current_time('Y-m-d')
    );
}

/**
 * Extract text from a Responses API payload.
 *
 * @param array<string, mixed> $response_data
 * @return string
 */
function thrivingstudio_quote_card_ai_extract_output_text($response_data) {
    if (!empty($response_data['output_text']) && is_string($response_data['output_text'])) {
        return $response_data['output_text'];
    }

    $text = '';
    $output = $response_data['output'] ?? [];

    if (!is_array($output)) {
        return '';
    }

    foreach ($output as $item) {
        if (!is_array($item) || empty($item['content']) || !is_array($item['content'])) {
            continue;
        }

        foreach ($item['content'] as $content) {
            if (!is_array($content)) {
                continue;
            }

            if (!empty($content['text']) && is_string($content['text'])) {
                $text .= $content['text'];
            } elseif (!empty($content['value']) && is_string($content['value'])) {
                $text .= $content['value'];
            }
        }
    }

    return trim($text);
}

/**
 * Extract a JSON object from model output text.
 *
 * @param string $text
 * @return array<string, mixed>|null
 */
function thrivingstudio_quote_card_ai_decode_json($text) {
    $text = trim($text);
    $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
    $text = preg_replace('/\s*```$/', '', (string) $text);

    $decoded = json_decode($text, true);
    if (is_array($decoded)) {
        return $decoded;
    }

    $start = strpos($text, '{');
    $end = strrpos($text, '}');

    if ($start === false || $end === false || $end <= $start) {
        return null;
    }

    $decoded = json_decode(substr($text, $start, $end - $start + 1), true);

    return is_array($decoded) ? $decoded : null;
}

/**
 * Normalize an AI verification response for the admin UI.
 *
 * @param array<string, mixed> $result
 * @return array<string, mixed>
 */
function thrivingstudio_quote_card_ai_normalize_result($result) {
    $status = sanitize_key((string) ($result['status'] ?? ''));
    $public_statuses = thrivingstudio_quote_card_verification_status_options();
    $source_url = esc_url_raw((string) ($result['source_url'] ?? ''));

    if (!array_key_exists($status, $public_statuses) || !$source_url) {
        $status = '';
    }

    $verified_date = sanitize_text_field((string) ($result['verified_date'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $verified_date)) {
        $verified_date = current_time('Y-m-d');
    }

    $evidence = [];
    $raw_evidence = is_array($result['evidence'] ?? null) ? $result['evidence'] : [];

    foreach (array_slice($raw_evidence, 0, 4) as $item) {
        if (!is_array($item)) {
            continue;
        }

        $url = esc_url_raw((string) ($item['url'] ?? ''));
        if (!$url) {
            continue;
        }

        $evidence[] = [
            'title' => sanitize_text_field((string) ($item['title'] ?? '')),
            'url' => $url,
            'why_it_matters' => sanitize_textarea_field((string) ($item['why_it_matters'] ?? '')),
        ];
    }

    return [
        'status' => $status,
        'raw_status' => sanitize_key((string) ($result['status'] ?? '')),
        'source_title' => sanitize_text_field((string) ($result['source_title'] ?? '')),
        'source_name' => sanitize_text_field((string) ($result['source_name'] ?? '')),
        'source_url' => $source_url,
        'verified_date' => $verified_date,
        'note' => sanitize_textarea_field((string) ($result['note'] ?? '')),
        'confidence' => max(0, min(100, absint($result['confidence'] ?? 0))),
        'evidence' => $evidence,
        'needs_editor_review' => true,
    ];
}

/**
 * AJAX endpoint for AI-assisted quote verification.
 */
function thrivingstudio_ajax_verify_quote_card() {
    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

    if (!$post_id || get_post_type($post_id) !== 'quote_card') {
        wp_send_json_error(['message' => __('Invalid quote card.', 'thrivingstudio')], 400);
    }

    check_ajax_referer('thrivingstudio_verify_quote_card_' . $post_id, 'nonce');

    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(['message' => __('You do not have permission to verify this quote card.', 'thrivingstudio')], 403);
    }

    $api_key = thrivingstudio_quote_card_ai_api_key();
    if (!$api_key) {
        wp_send_json_error(['message' => __('OpenAI API key is not configured on the server.', 'thrivingstudio')], 400);
    }

    $payload = [
        'model' => thrivingstudio_quote_card_ai_model(),
        'tools' => [
            ['type' => 'web_search'],
        ],
        'input' => thrivingstudio_quote_card_ai_prompt($post_id),
        'store' => false,
    ];

    $response = wp_remote_post(
        'https://api.openai.com/v1/responses',
        [
            'timeout' => 75,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($payload),
        ]
    );

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $response_data = json_decode($body, true);

    if ($status_code < 200 || $status_code >= 300) {
        $message = __('AI verification request failed.', 'thrivingstudio');
        if (is_array($response_data) && !empty($response_data['error']['message'])) {
            $message = sanitize_text_field((string) $response_data['error']['message']);
        }

        wp_send_json_error(['message' => $message], $status_code ?: 500);
    }

    if (!is_array($response_data)) {
        wp_send_json_error(['message' => __('AI verification returned an unreadable response.', 'thrivingstudio')], 500);
    }

    $output_text = thrivingstudio_quote_card_ai_extract_output_text($response_data);
    $decoded = thrivingstudio_quote_card_ai_decode_json($output_text);

    if (!$decoded) {
        wp_send_json_error(
            [
                'message' => __('AI verification did not return valid structured evidence.', 'thrivingstudio'),
                'raw_excerpt' => wp_html_excerpt($output_text, 500, '...'),
            ],
            500
        );
    }

    wp_send_json_success(thrivingstudio_quote_card_ai_normalize_result($decoded));
}
add_action('wp_ajax_thrivingstudio_verify_quote_card', 'thrivingstudio_ajax_verify_quote_card');

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

/**
 * Add the AI quote verification admin workflow.
 *
 * @param string $hook
 */
function thrivingstudio_admin_quote_card_ai_verifier_script($hook) {
    if ($hook !== 'post-new.php' && $hook !== 'post.php') {
        return;
    }

    $screen = get_current_screen();

    if (!$screen || $screen->post_type !== 'quote_card') {
        return;
    }
    ?>
    <script>
    (function() {
        function setValue(id, value) {
            var field = document.getElementById(id);
            if (field) {
                field.value = value || "";
                field.dispatchEvent(new Event("change", { bubbles: true }));
            }
        }

        function appendText(parent, text) {
            parent.appendChild(document.createTextNode(text || ""));
        }

        function showResult(box, type, message, data) {
            box.hidden = false;
            box.className = "ts-quote-ai-result" + (type ? " is-" + type : "");
            box.innerHTML = "";

            var messageEl = document.createElement("p");
            appendText(messageEl, message);
            box.appendChild(messageEl);

            if (!data) {
                return;
            }

            var meta = document.createElement("p");
            appendText(meta, "Suggested status: " + (data.raw_status || data.status || "not verified") + " | Confidence: " + (data.confidence || 0) + "%");
            box.appendChild(meta);

            if (data.note) {
                var note = document.createElement("p");
                appendText(note, data.note);
                box.appendChild(note);
            }

            if (data.evidence && data.evidence.length) {
                var list = document.createElement("ul");

                data.evidence.forEach(function(item) {
                    var li = document.createElement("li");
                    var link = document.createElement("a");
                    link.href = item.url;
                    link.target = "_blank";
                    link.rel = "noopener noreferrer";
                    appendText(link, item.title || item.url);
                    li.appendChild(link);

                    if (item.why_it_matters) {
                        appendText(li, " - " + item.why_it_matters);
                    }

                    list.appendChild(li);
                });

                box.appendChild(list);
            }
        }

        function applyDraft(data) {
            setValue("quote_card_verification_status", data.status || "");
            setValue("quote_card_source_title", data.source_title || "");
            setValue("quote_card_source_name", data.source_name || "");
            setValue("quote_card_source_url", data.source_url || "");
            setValue("quote_card_verified_date", data.verified_date || "");
            setValue("quote_card_source_note", data.note || "");
        }

        document.addEventListener("DOMContentLoaded", function() {
            var button = document.querySelector("[data-quote-ai-verify]");
            var resultBox = document.getElementById("quote-card-ai-result");

            if (!button || !resultBox) {
                return;
            }

            button.addEventListener("click", function() {
                var formData = new FormData();
                formData.append("action", "thrivingstudio_verify_quote_card");
                formData.append("post_id", button.getAttribute("data-post-id") || "");
                formData.append("nonce", button.getAttribute("data-nonce") || "");

                button.disabled = true;
                showResult(resultBox, "", "Searching for supporting sources...");

                fetch(window.ajaxurl, {
                    method: "POST",
                    credentials: "same-origin",
                    body: formData
                })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(json) {
                        if (!json || !json.success) {
                            throw new Error(json && json.data && json.data.message ? json.data.message : "AI verification failed.");
                        }

                        applyDraft(json.data);
                        showResult(resultBox, "success", "AI verification draft applied. Review the evidence and click Update to save.", json.data);
                    })
                    .catch(function(error) {
                        showResult(resultBox, "error", error.message || "AI verification failed.");
                    })
                    .finally(function() {
                        button.disabled = false;
                    });
            });
        });
    })();
    </script>
    <?php
}
add_action('admin_footer', 'thrivingstudio_admin_quote_card_ai_verifier_script');
