<?php get_header(); ?>

<main id="primary" class="ts-quote-single-main">
    <div class="ts-quote-single">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <?php
            $post_id = get_the_ID();
            $quote_title = get_the_title();
            $quote_author = trim((string) get_post_meta($post_id, '_quote_card_author', true));
            $quote_caption = trim((string) get_post_meta($post_id, '_quote_card_caption', true));
            $verification_statuses = function_exists('thrivingstudio_quote_card_verification_status_options') ? thrivingstudio_quote_card_verification_status_options() : [
                '' => __('Not verified', 'thrivingstudio'),
                'verified' => __('Verified source', 'thrivingstudio'),
                'source_backed' => __('Source-backed', 'thrivingstudio'),
                'attributed' => __('Attributed', 'thrivingstudio'),
            ];
            $verification_status = sanitize_key((string) get_post_meta($post_id, '_quote_card_verification_status', true));
            $quote_source_title = trim((string) get_post_meta($post_id, '_quote_card_source_title', true));
            $quote_source_name = trim((string) get_post_meta($post_id, '_quote_card_source_name', true));
            $quote_source_url = esc_url_raw((string) get_post_meta($post_id, '_quote_card_source_url', true));
            $quote_verified_date = trim((string) get_post_meta($post_id, '_quote_card_verified_date', true));
            $quote_source_note = trim((string) get_post_meta($post_id, '_quote_card_source_note', true));
            $quote_source_host = $quote_source_url ? wp_parse_url($quote_source_url, PHP_URL_HOST) : '';
            $quote_has_source = $quote_source_title || $quote_source_name || $quote_source_url || $quote_verified_date || $quote_source_note;

            if (!array_key_exists($verification_status, $verification_statuses)) {
                $verification_status = '';
            }

            $show_quote_verification = $verification_status !== '' && $quote_has_source;
            $verification_label = $show_quote_verification ? $verification_statuses[$verification_status] : '';
            $verified_date_label = '';

            if ($quote_verified_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $quote_verified_date)) {
                $verified_date_label = date_i18n(get_option('date_format'), strtotime($quote_verified_date));
            }

            $permalink = get_permalink();
            $image_id = get_post_thumbnail_id();
            $image_full = $image_id ? wp_get_attachment_image_src($image_id, 'full') : false;
            $image_url = $image_full ? $image_full[0] : '';
            $image_alt = $image_id ? trim((string) get_post_meta($image_id, '_wp_attachment_image_alt', true)) : '';

            if ($image_alt === '') {
                $image_alt = $quote_author
                    ? sprintf(__('Quote card for "%1$s" by %2$s', 'thrivingstudio'), $quote_title, $quote_author)
                    : sprintf(__('Quote card for "%s"', 'thrivingstudio'), $quote_title);
            }

            $share_quote = $quote_author
                ? sprintf('"%1$s" - %2$s', $quote_title, $quote_author)
                : sprintf('"%s"', $quote_title);
            $share_text = sprintf('%1$s %2$s', $share_quote, $permalink);
            $encoded_url = rawurlencode($permalink);
            $encoded_text = rawurlencode($share_quote);
            $encoded_share_text = rawurlencode($share_text);
            $encoded_image = $image_url ? rawurlencode($image_url) : '';
            $download_extension = $image_url ? pathinfo((string) wp_parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION) : '';
            $download_name = sanitize_title($quote_title) . '-quote-card.' . ($download_extension ?: 'jpg');
            $previous_quote = get_previous_post();
            $next_quote = get_next_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class('ts-quote-single-article'); ?>>
                <header class="ts-quote-single-header">
                    <div class="ts-quote-eyebrow-row">
                        <p class="ts-quote-eyebrow"><?php esc_html_e('Quote Card', 'thrivingstudio'); ?></p>
                        <?php if ($show_quote_verification) : ?>
                            <a class="ts-quote-verified-badge ts-quote-verified-badge--<?php echo esc_attr(sanitize_html_class($verification_status)); ?>" href="#quote-source" aria-label="<?php echo esc_attr(sprintf(__('%s with supporting source details', 'thrivingstudio'), $verification_label)); ?>">
                                <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false">
                                    <path d="M12 2 4 5.4v5.9c0 5 3.4 9.7 8 10.7 4.6-1 8-5.7 8-10.7V5.4L12 2Zm3.7 8.3-4.4 4.4a1 1 0 0 1-1.4 0l-1.8-1.8a1 1 0 0 1 1.4-1.4l1.1 1.1 3.7-3.7a1 1 0 1 1 1.4 1.4Z" />
                                </svg>
                                <span><?php echo esc_html($verification_label); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                    <h1><?php echo esc_html($quote_title); ?></h1>
                    <?php if ($quote_author) : ?>
                        <p class="ts-quote-author-line">
                            <?php esc_html_e('By', 'thrivingstudio'); ?>
                            <span><?php echo esc_html($quote_author); ?></span>
                        </p>
                    <?php endif; ?>
                </header>

                <div class="ts-quote-experience">
                    <figure class="ts-quote-art-shell">
                        <?php if ($image_id && $image_url) : ?>
                            <button class="ts-quote-image-button" type="button" data-quote-lightbox-open aria-label="<?php echo esc_attr(sprintf(__('Open quote card image: %s', 'thrivingstudio'), $quote_title)); ?>">
                                <?php
                                echo wp_get_attachment_image(
                                    $image_id,
                                    'large',
                                    false,
                                    [
                                        'class' => 'ts-quote-single-image',
                                        'alt' => $image_alt,
                                        'loading' => 'eager',
                                        'decoding' => 'async',
                                        'fetchpriority' => 'high',
                                        'sizes' => '(max-width: 640px) calc(100vw - 2rem), 440px',
                                    ]
                                );
                                ?>
                                <span class="ts-quote-zoom-hint"><?php esc_html_e('Open image', 'thrivingstudio'); ?></span>
                            </button>
                        <?php else : ?>
                            <div class="ts-quote-missing-image">
                                <?php esc_html_e('Quote card image coming soon.', 'thrivingstudio'); ?>
                            </div>
                        <?php endif; ?>
                    </figure>

                    <aside class="ts-quote-action-panel" aria-label="<?php esc_attr_e('Quote card actions', 'thrivingstudio'); ?>">
                        <div class="ts-quote-panel-copy">
                            <span><?php esc_html_e('Ready to use', 'thrivingstudio'); ?></span>
                            <h2><?php esc_html_e('Save or share this card', 'thrivingstudio'); ?></h2>
                            <p><?php esc_html_e('Download the image, copy the quote, or send the page to someone who would enjoy it.', 'thrivingstudio'); ?></p>
                        </div>

                        <div
                            class="ts-quote-actions"
                            data-quote-actions
                            data-quote-text="<?php echo esc_attr($share_quote); ?>"
                            data-quote-url="<?php echo esc_url($permalink); ?>"
                        >
                            <?php if ($image_url) : ?>
                                <a class="ts-quote-action ts-quote-action-primary" href="<?php echo esc_url($image_url); ?>" download="<?php echo esc_attr($download_name); ?>">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false">
                                        <path d="M12 3a1 1 0 0 1 1 1v9.59l3.3-3.3a1 1 0 1 1 1.4 1.42l-5 5a1 1 0 0 1-1.4 0l-5-5a1 1 0 1 1 1.4-1.42l3.3 3.3V4a1 1 0 0 1 1-1Zm-7 16a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H6a1 1 0 0 1-1-1Z" />
                                    </svg>
                                    <span><?php esc_html_e('Download', 'thrivingstudio'); ?></span>
                                </a>
                            <?php endif; ?>
                            <button class="ts-quote-action" type="button" data-quote-copy="quote">
                                <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false">
                                    <path d="M8 7a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3h-1v1a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-6a3 3 0 0 1 3-3h1V7Zm2 1h3a3 3 0 0 1 3 3v3h1a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1h-6a1 1 0 0 0-1 1v1Zm-3 2a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1H7Z" />
                                </svg>
                                <span><?php esc_html_e('Copy Quote', 'thrivingstudio'); ?></span>
                            </button>
                            <button class="ts-quote-action" type="button" data-quote-copy="link">
                                <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false">
                                    <path d="M10.6 13.4a1 1 0 0 1 0-1.4l2.8-2.8a1 1 0 1 1 1.4 1.4L12 13.4a1 1 0 0 1-1.4 0ZM8.5 17.6a4 4 0 0 1-2.8-6.8l2.4-2.4a4 4 0 0 1 5.7 0 1 1 0 0 1-1.4 1.4 2 2 0 0 0-2.9 0l-2.4 2.4a2 2 0 0 0 2.9 2.9 1 1 0 1 1 1.4 1.4 4 4 0 0 1-2.9 1.1Zm4.9-2a1 1 0 0 1 0-1.4 2 2 0 0 0 2.9 0l2.4-2.4a2 2 0 0 0-2.9-2.9 1 1 0 1 1-1.4-1.4 4 4 0 0 1 5.7 5.7l-2.4 2.4a4 4 0 0 1-5.7 0Z" />
                                </svg>
                                <span><?php esc_html_e('Copy Link', 'thrivingstudio'); ?></span>
                            </button>
                            <button class="ts-quote-action" type="button" data-quote-share>
                                <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false">
                                    <path d="M18 16.1a3 3 0 0 0-2.4 1.2l-6.7-3.5a3.1 3.1 0 0 0 0-1.6l6.7-3.5A3 3 0 1 0 14.7 7L8 10.5a3 3 0 1 0 0 3l6.7 3.5a3 3 0 1 0 3.3-.9ZM6 13a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm12-6a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm0 12a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z" />
                                </svg>
                                <span><?php esc_html_e('Share', 'thrivingstudio'); ?></span>
                            </button>
                            <p class="ts-quote-action-status" data-quote-status aria-live="polite"></p>
                        </div>

                        <div class="ts-quote-social-links" aria-label="<?php esc_attr_e('Share on social platforms', 'thrivingstudio'); ?>">
                            <a href="<?php echo esc_url('https://api.whatsapp.com/send?text=' . $encoded_share_text); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php esc_html_e('WhatsApp', 'thrivingstudio'); ?></a>
                            <a href="<?php echo esc_url('https://twitter.com/intent/tweet?text=' . $encoded_text . '&url=' . $encoded_url); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php esc_html_e('X', 'thrivingstudio'); ?></a>
                            <a href="<?php echo esc_url('https://www.linkedin.com/sharing/share-offsite/?url=' . $encoded_url); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php esc_html_e('LinkedIn', 'thrivingstudio'); ?></a>
                            <?php if ($encoded_image) : ?>
                                <a href="<?php echo esc_url('https://www.pinterest.com/pin/create/button/?url=' . $encoded_url . '&media=' . $encoded_image . '&description=' . $encoded_text); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php esc_html_e('Pinterest', 'thrivingstudio'); ?></a>
                            <?php endif; ?>
                        </div>

                        <?php if ($show_quote_verification) : ?>
                            <section id="quote-source" class="ts-quote-source ts-quote-source--<?php echo esc_attr(sanitize_html_class($verification_status)); ?>" aria-labelledby="quote-source-title">
                                <div class="ts-quote-source-header">
                                    <span class="ts-quote-source-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" focusable="false">
                                            <path d="M12 2 4 5.4v5.9c0 5 3.4 9.7 8 10.7 4.6-1 8-5.7 8-10.7V5.4L12 2Zm3.7 8.3-4.4 4.4a1 1 0 0 1-1.4 0l-1.8-1.8a1 1 0 0 1 1.4-1.4l1.1 1.1 3.7-3.7a1 1 0 1 1 1.4 1.4Z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <span class="ts-quote-source-kicker"><?php echo esc_html($verification_label); ?></span>
                                        <h2 id="quote-source-title"><?php esc_html_e('Supporting source', 'thrivingstudio'); ?></h2>
                                    </div>
                                </div>

                                <dl class="ts-quote-source-list">
                                    <?php if ($quote_source_title) : ?>
                                        <div>
                                            <dt><?php esc_html_e('Source', 'thrivingstudio'); ?></dt>
                                            <dd><?php echo esc_html($quote_source_title); ?></dd>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($quote_source_name) : ?>
                                        <div>
                                            <dt><?php esc_html_e('Published in', 'thrivingstudio'); ?></dt>
                                            <dd><?php echo esc_html($quote_source_name); ?></dd>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($verified_date_label) : ?>
                                        <div>
                                            <dt><?php esc_html_e('Checked', 'thrivingstudio'); ?></dt>
                                            <dd><?php echo esc_html($verified_date_label); ?></dd>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($quote_source_url && !$quote_source_title && $quote_source_host) : ?>
                                        <div>
                                            <dt><?php esc_html_e('Source site', 'thrivingstudio'); ?></dt>
                                            <dd><?php echo esc_html($quote_source_host); ?></dd>
                                        </div>
                                    <?php endif; ?>
                                </dl>

                                <?php if ($quote_source_note) : ?>
                                    <p class="ts-quote-source-note"><?php echo nl2br(esc_html($quote_source_note)); ?></p>
                                <?php endif; ?>

                                <?php if ($quote_source_url) : ?>
                                    <a class="ts-quote-source-link" href="<?php echo esc_url($quote_source_url); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e('Open supporting source', 'thrivingstudio'); ?>
                                    </a>
                                <?php endif; ?>
                            </section>
                        <?php endif; ?>

                        <?php if ($quote_caption) : ?>
                            <div class="ts-quote-caption">
                                <h2><?php esc_html_e('Context', 'thrivingstudio'); ?></h2>
                                <p><?php echo nl2br(esc_html($quote_caption)); ?></p>
                            </div>
                        <?php endif; ?>
                    </aside>
                </div>

                <?php if ($previous_quote || $next_quote) : ?>
                    <nav class="ts-quote-adjacent" aria-label="<?php esc_attr_e('Adjacent quote cards', 'thrivingstudio'); ?>">
                        <?php if ($previous_quote) : ?>
                            <a class="ts-quote-adjacent-link" href="<?php echo esc_url(get_permalink($previous_quote)); ?>">
                                <span><?php esc_html_e('Previous', 'thrivingstudio'); ?></span>
                                <strong><?php echo esc_html(get_the_title($previous_quote)); ?></strong>
                            </a>
                        <?php endif; ?>
                        <?php if ($next_quote) : ?>
                            <a class="ts-quote-adjacent-link ts-quote-adjacent-link-next" href="<?php echo esc_url(get_permalink($next_quote)); ?>">
                                <span><?php esc_html_e('Next', 'thrivingstudio'); ?></span>
                                <strong><?php echo esc_html(get_the_title($next_quote)); ?></strong>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

                <?php
                $related_quotes = new WP_Query([
                    'post_type' => 'quote_card',
                    'posts_per_page' => 3,
                    'post__not_in' => [$post_id],
                    'ignore_sticky_posts' => true,
                    'no_found_rows' => true,
                ]);
                ?>

                <?php if ($related_quotes->have_posts()) : ?>
                    <section class="ts-quote-related" aria-labelledby="related-quote-cards-title">
                        <div class="ts-quote-related-header">
                            <h2 id="related-quote-cards-title"><?php esc_html_e('More Quote Cards', 'thrivingstudio'); ?></h2>
                            <a href="<?php echo esc_url(get_post_type_archive_link('quote_card')); ?>"><?php esc_html_e('View all', 'thrivingstudio'); ?></a>
                        </div>
                        <div class="ts-quote-related-grid">
                            <?php while ($related_quotes->have_posts()) : $related_quotes->the_post(); ?>
                                <?php
                                $related_id = get_the_ID();
                                $related_author = trim((string) get_post_meta($related_id, '_quote_card_author', true));
                                ?>
                                <article <?php post_class('ts-quote-related-card'); ?>>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <span class="ts-quote-related-image">
                                                <?php
                                                the_post_thumbnail(
                                                    'medium_large',
                                                    [
                                                        'loading' => 'lazy',
                                                        'decoding' => 'async',
                                                        'sizes' => '(max-width: 640px) 100vw, 260px',
                                                    ]
                                                );
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="ts-quote-related-title"><?php the_title(); ?></span>
                                        <?php if ($related_author) : ?>
                                            <span class="ts-quote-related-author"><?php echo esc_html($related_author); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </article>
                            <?php endwhile; ?>
                        </div>
                    </section>
                    <?php wp_reset_postdata(); ?>
                <?php endif; ?>

                <?php if ($image_url) : ?>
                    <div class="ts-quote-lightbox" data-quote-lightbox role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?php echo esc_attr(sprintf(__('Expanded quote card image: %s', 'thrivingstudio'), $quote_title)); ?>">
                        <button class="ts-quote-lightbox-backdrop" type="button" data-quote-lightbox-close aria-label="<?php esc_attr_e('Close image viewer', 'thrivingstudio'); ?>"></button>
                        <div class="ts-quote-lightbox-panel">
                            <button class="ts-quote-lightbox-close" type="button" data-quote-lightbox-close><?php esc_html_e('Close', 'thrivingstudio'); ?></button>
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>">
                            <a class="ts-quote-lightbox-download" href="<?php echo esc_url($image_url); ?>" download="<?php echo esc_attr($download_name); ?>"><?php esc_html_e('Download image', 'thrivingstudio'); ?></a>
                        </div>
                    </div>
                <?php endif; ?>
            </article>
        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
