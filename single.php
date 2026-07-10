<?php get_header(); ?>

<main class="flex-1" id="main-content" role="main">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <?php
            $raw_content = get_the_content();
            $rendered_content = apply_filters('the_content', $raw_content);
            $reading_time = max(1, (int) ceil(str_word_count(wp_strip_all_tags($raw_content)) / 200));
            $post_id = get_the_ID();
            $post_permalink = get_permalink($post_id);
            $post_title_plain = wp_strip_all_tags(get_the_title($post_id));
            $author_id = get_the_author_meta('ID');
            $author_name = get_the_author();
            $published_iso = get_the_date('c');
            $published_label = get_the_date('M j, Y');
            $modified_iso = get_the_modified_date('c');
            $modified_label = get_the_modified_date('M j, Y');
            $show_modified = get_the_modified_date('Y-m-d') !== get_the_date('Y-m-d');
            $post_summary = thrivingstudio_get_manual_excerpt();
            $toc_items = [];
            $has_featured_image = has_post_thumbnail();
            $single_layout = get_theme_mod('thrivingstudio_single_layout', 'content_rail');
            $featured_image_position = get_theme_mod('thrivingstudio_single_featured_image_position', 'below_header');
            $allowed_single_layouts = ['content_rail', 'centered', 'wide'];
            $allowed_featured_image_positions = ['below_header', 'above_title', 'hidden'];

            if (!in_array($single_layout, $allowed_single_layouts, true)) {
                $single_layout = 'content_rail';
            }

            if (!in_array($featured_image_position, $allowed_featured_image_positions, true)) {
                $featured_image_position = 'below_header';
            }

            $content_width = max(38, min(72, (int) get_theme_mod('thrivingstudio_single_content_width', 48)));
            $title_size = max(30, min(64, (int) get_theme_mod('thrivingstudio_single_title_size', 44)));
            $excerpt_size = max(14, min(24, (int) get_theme_mod('thrivingstudio_single_excerpt_size', 16)));
            $body_size = max(15, min(22, (int) get_theme_mod('thrivingstudio_single_body_size', 16)));
            $body_line_height = max(145, min(190, (int) get_theme_mod('thrivingstudio_single_body_line_height', 166))) / 100;
            $show_category = (bool) get_theme_mod('thrivingstudio_single_show_category', true);
            $show_excerpt = (bool) get_theme_mod('thrivingstudio_single_show_excerpt', true);
            $show_author_avatar = (bool) get_theme_mod('thrivingstudio_single_show_author_avatar', true);
            $show_author_name = (bool) get_theme_mod('thrivingstudio_single_show_author_name', true);
            $show_published_date = (bool) get_theme_mod('thrivingstudio_single_show_published_date', true);
            $show_reading_time = (bool) get_theme_mod('thrivingstudio_single_show_reading_time', true);
            $show_updated_date = (bool) get_theme_mod('thrivingstudio_single_show_updated_date', true);
            $show_featured_image = $has_featured_image && $featured_image_position !== 'hidden';
            $show_post_cta = (bool) get_theme_mod('thrivingstudio_single_show_cta', true);
            $post_cta_title = trim((string) get_theme_mod('thrivingstudio_single_cta_title', __('Want More Practical Insights?', 'thrivingstudio')));
            $post_cta_text = trim((string) get_theme_mod('thrivingstudio_single_cta_text', __('Get focused ideas on psychology, discipline, and creative growth delivered to your inbox.', 'thrivingstudio')));
            $post_cta_primary_label = trim((string) get_theme_mod('thrivingstudio_single_cta_primary_label', __('Subscribe', 'thrivingstudio')));
            $post_cta_primary_link = trim((string) get_theme_mod('thrivingstudio_single_cta_primary_link', home_url('/#subscribe')));
            $post_cta_secondary_label = trim((string) get_theme_mod('thrivingstudio_single_cta_secondary_label', __('Get in touch', 'thrivingstudio')));
            $post_cta_secondary_link = trim((string) get_theme_mod('thrivingstudio_single_cta_secondary_link', home_url('/contact')));
            $show_related_posts = (bool) get_theme_mod('thrivingstudio_single_show_related_posts', true);
            $related_posts_count = max(2, min(6, (int) get_theme_mod('thrivingstudio_single_related_posts_count', 3)));
            $show_post_engagement = get_post_type($post_id) === 'post';
            $post_reaction_count = function_exists('thrivingstudio_get_post_useful_count') ? thrivingstudio_get_post_useful_count($post_id) : max(0, (int) get_post_meta($post_id, '_thrivingstudio_useful_count', true));
            $post_discussion_url = (comments_open($post_id) || get_comments_number($post_id)) ? $post_permalink . '#comments' : home_url('/contact/');
            $post_discussion_label = comments_open($post_id) || get_comments_number($post_id) ? __('Discuss', 'thrivingstudio') : __('Talk to us', 'thrivingstudio');
            $post_share_menu_id = 'ts-post-share-menu-' . $post_id;
            $post_external_share_url = $post_permalink;
            $post_permalink_host = (string) wp_parse_url($post_permalink, PHP_URL_HOST);
            $post_permalink_path = (string) wp_parse_url($post_permalink, PHP_URL_PATH);
            $post_permalink_query = (string) wp_parse_url($post_permalink, PHP_URL_QUERY);
            $post_local_hosts = ['localhost', '127.0.0.1', '::1'];
            $post_is_local_share_url = in_array($post_permalink_host, $post_local_hosts, true) || substr($post_permalink_host, -6) === '.local';

            if ($post_is_local_share_url && $post_permalink_path !== '') {
                $public_share_base_url = apply_filters('thrivingstudio_public_share_base_url', 'https://thrivingstudio.xyz');
                $post_external_share_url = trailingslashit(untrailingslashit((string) $public_share_base_url)) . ltrim($post_permalink_path, '/');

                if ($post_permalink_query !== '') {
                    $post_external_share_url .= '?' . $post_permalink_query;
                }
            }

            $post_share_text = trim($post_title_plain . ' ' . $post_external_share_url);

            if (class_exists('DOMDocument')) {
                $dom = new DOMDocument();
                $loaded = false;
                $used_ids = [];

                libxml_use_internal_errors(true);
                $loaded = $dom->loadHTML(
                    '<?xml encoding="utf-8" ?><div id="ts-content-root">' . $rendered_content . '</div>',
                    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
                );
                libxml_clear_errors();

                if ($loaded) {
                    $xpath = new DOMXPath($dom);
                    $headings = $xpath->query('//h2|//h3');

                    if ($headings instanceof DOMNodeList) {
                        foreach ($headings as $heading) {
                            $text = trim($heading->textContent);
                            if ($text === '') {
                                continue;
                            }

                            $id = $heading->getAttribute('id');
                            if ($id === '') {
                                $base_id = sanitize_title($text);
                                $id = $base_id !== '' ? $base_id : 'section';
                            }

                            $unique_id = $id;
                            $suffix = 2;
                            while (in_array($unique_id, $used_ids, true)) {
                                $unique_id = $id . '-' . $suffix;
                                $suffix++;
                            }
                            $id = $unique_id;

                            $heading->setAttribute('id', $id);
                            $used_ids[] = $id;

                            $heading_level = strtolower($heading->nodeName);
                            if ($heading_level === 'h2') {
                                $toc_items[] = [
                                    'id' => $id,
                                    'text' => $text,
                                    'level' => $heading_level,
                                ];
                            }
                        }
                    }

                    $root = $dom->getElementById('ts-content-root');
                    if ($root) {
                        $html = '';
                        foreach ($root->childNodes as $child) {
                            $html .= $dom->saveHTML($child);
                        }
                        if ($html !== '') {
                            $rendered_content = $html;
                        }
                    }
                }
            }

            $layout_supports_right_rail = $single_layout === 'content_rail';
            $has_toc = count($toc_items) >= 2;
            $has_right_rail_widgets = $layout_supports_right_rail && (bool) get_theme_mod('thrivingstudio_single_show_rail_widgets', true) && is_active_sidebar('single-post-right-rail');
            $show_right_rail_widgets_mobile = (bool) get_theme_mod('thrivingstudio_single_rail_show_mobile', false);
            $right_rail_widget_shell_classes = 'ts-single-rail-widgets-shell';
            $right_rail_widget_classes = 'ts-single-rail-widgets';
            $toc_shell_classes = 'ts-single-toc-shell';
            $article_classes = 'ts-single-article ts-single-layout-' . str_replace('_', '-', $single_layout);
            $article_style = sprintf(
                '--ts-single-content-width:%drem;--ts-single-title-size:%dpx;--ts-single-excerpt-size:%dpx;--ts-single-body-size:%dpx;--ts-single-body-line-height:%s;',
                $content_width,
                $title_size,
                $excerpt_size,
                $body_size,
                number_format($body_line_height, 2, '.', '')
            );
            $meta_items = [];
            $featured_image_markup = '';

            if ($show_published_date) {
                $meta_items[] = sprintf(
                    '<time datetime="%s">%s</time>',
                    esc_attr($published_iso),
                    esc_html($published_label)
                );
            }

            if ($show_reading_time) {
                $meta_items[] = sprintf(
                    '<span>%s</span>',
                    esc_html(sprintf(_n('%d min read', '%d min read', $reading_time, 'thrivingstudio'), $reading_time))
                );
            }

            if ($show_updated_date && $show_modified) {
                $meta_items[] = sprintf(
                    '<span>%s <time datetime="%s">%s</time></span>',
                    esc_html__('Updated', 'thrivingstudio'),
                    esc_attr($modified_iso),
                    esc_html($modified_label)
                );
            }

            $has_byline_body = $show_author_name || !empty($meta_items);
            $show_byline = $show_author_avatar || $has_byline_body;

            if ($show_featured_image) {
                $thumbnail_id = get_post_thumbnail_id();
                $thumbnail_alt = trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true));
                $thumbnail_caption = trim((string) wp_get_attachment_caption($thumbnail_id));
                $featured_wrap_classes = 'ts-single-featured-wrap';

                if ($featured_image_position === 'above_title') {
                    $featured_wrap_classes .= ' ts-single-featured-wrap-in-hero';
                }

                if ($thumbnail_alt === '') {
                    $thumbnail_alt = get_the_title();
                }

                ob_start();
                ?>
                <figure class="<?php echo esc_attr($featured_wrap_classes); ?>">
                    <?php
                    echo wp_get_attachment_image($thumbnail_id, 'full', false, [
                        'class' => 'ts-single-featured-image',
                        'loading' => 'eager',
                        'fetchpriority' => 'high',
                        'decoding' => 'async',
                        'alt' => $thumbnail_alt,
                    ]);
                    ?>
                    <?php if ($thumbnail_caption !== '') : ?>
                        <figcaption class="ts-single-featured-caption"><?php echo wp_kses_post($thumbnail_caption); ?></figcaption>
                    <?php endif; ?>
                </figure>
                <?php
                $featured_image_markup = ob_get_clean();
            }

            $featured_image_has_grid_row = $show_featured_image && $featured_image_position === 'below_header';
            $right_rail_widget_shell_classes .= $featured_image_has_grid_row ? ' ts-single-rail-widgets-shell-with-media' : ' ts-single-rail-widgets-shell-no-media';
            $toc_shell_classes .= $featured_image_has_grid_row ? ' ts-single-toc-shell-after-media' : ' ts-single-toc-shell-after-hero';
            $article_classes .= $show_featured_image ? ' ts-single-has-featured-image' : ' ts-single-no-featured-image';

            if ($has_right_rail_widgets) {
                $article_classes .= ' ts-single-article-has-rail-widgets';
            }

            if (!$show_right_rail_widgets_mobile) {
                $right_rail_widget_shell_classes .= ' ts-single-rail-widgets-shell-hide-mobile';
            }
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class($article_classes); ?> style="<?php echo esc_attr($article_style); ?>" aria-labelledby="ts-post-title-<?php the_ID(); ?>">
                <header class="ts-single-hero">
                    <?php if ($featured_image_position === 'above_title' && $featured_image_markup !== '') : ?>
                        <?php echo $featured_image_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php endif; ?>

                    <?php if ($show_category) : ?>
                        <div class="ts-single-category-row" aria-label="<?php esc_attr_e('Post categories', 'thrivingstudio'); ?>">
                            <?php
                            $categories = get_the_category();
                            if ( ! empty( $categories ) ) {
                                // Separate parent and child categories
                                $parent_categories = [];
                                $child_categories = [];

                                foreach( $categories as $category ) {
                                    if ( $category->parent == 0 ) {
                                        $parent_categories[] = $category;
                                    } else {
                                        $child_categories[] = $category;
                                    }
                                }

                                // Display parent category first, then child category with separator
                                $category_parts = [];

                                if ( ! empty( $parent_categories ) ) {
                                    $parent = $parent_categories[0]; // Use first parent category
                                    $category_parts[] = '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '" class="ts-single-category-link ts-single-category-parent">' . esc_html( $parent->name ) . '</a>';
                                }

                                if ( ! empty( $child_categories ) ) {
                                    $child = $child_categories[0]; // Use first child category
                                    $category_parts[] = '<a href="' . esc_url( get_category_link( $child->term_id ) ) . '" class="ts-single-category-link ts-single-category-child">' . esc_html( $child->name ) . '</a>';
                                }

                                echo '<span class="ts-single-category-breadcrumb">' . implode( ' <span class="ts-single-category-sep">/</span> ', $category_parts ) . '</span>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <h1 id="ts-post-title-<?php the_ID(); ?>" class="ts-single-title"><?php the_title(); ?></h1>
                    <?php if ($show_excerpt && $post_summary !== '') : ?>
                        <p class="ts-single-excerpt">
                            <?php echo esc_html(wp_strip_all_tags($post_summary)); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($show_byline) : ?>
                        <div class="ts-single-byline">
                            <?php if ($show_author_avatar) : ?>
                                <a class="ts-single-byline-avatar" href="<?php echo esc_url(get_author_posts_url($author_id)); ?>" aria-label="<?php echo esc_attr(sprintf(__('View posts by %s', 'thrivingstudio'), $author_name)); ?>">
                                    <?php echo get_avatar($author_id, 44, '', $author_name, ['class' => 'ts-single-byline-avatar-img']); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($has_byline_body) : ?>
                                <div class="ts-single-byline-body">
                                    <?php if ($show_author_name) : ?>
                                        <p class="ts-single-byline-author">
                                            <?php esc_html_e('By', 'thrivingstudio'); ?>
                                            <a href="<?php echo esc_url(get_author_posts_url($author_id)); ?>"><?php echo esc_html($author_name); ?></a>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($meta_items)) : ?>
                                        <div class="ts-single-meta">
                                            <?php foreach ($meta_items as $index => $meta_item) : ?>
                                                <?php if ($index > 0) : ?>
                                                    <span class="ts-meta-sep">•</span>
                                                <?php endif; ?>
                                                <?php echo $meta_item; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </header>

                <?php if ($has_right_rail_widgets) : ?>
                    <aside class="<?php echo esc_attr($right_rail_widget_shell_classes); ?>" aria-label="<?php esc_attr_e('Article sidebar modules', 'thrivingstudio'); ?>">
                        <div class="<?php echo esc_attr($right_rail_widget_classes); ?>">
                            <?php dynamic_sidebar('single-post-right-rail'); ?>
                        </div>
                    </aside>
                <?php endif; ?>

                <?php if ($has_toc) : ?>
                    <aside class="<?php echo esc_attr($toc_shell_classes); ?>">
                        <nav class="ts-single-toc" aria-label="<?php esc_attr_e('In this article', 'thrivingstudio'); ?>">
                            <p class="ts-single-toc-title"><?php esc_html_e('In this article', 'thrivingstudio'); ?></p>
                            <ul class="ts-single-toc-list">
                                <?php foreach ($toc_items as $item) : ?>
                                    <?php
                                    $toc_item_classes = [
                                        'ts-single-toc-item',
                                        'ts-single-toc-item-' . $item['level'],
                                    ];

                                    if ($item['level'] === 'h3') {
                                        $toc_item_classes[] = 'ts-single-toc-subitem';
                                    }
                                    ?>
                                    <li class="<?php echo esc_attr(implode(' ', $toc_item_classes)); ?>">
                                        <a href="#<?php echo esc_attr($item['id']); ?>">
                                            <?php echo esc_html($item['text']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    </aside>
                <?php endif; ?>

                <?php if ($featured_image_position === 'below_header' && $featured_image_markup !== '') : ?>
                    <?php echo $featured_image_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php endif; ?>

                <div class="ts-single-body-shell">
                    <div class="prose prose-lg ts-single-content ts-single-reading-column">
                        <?php echo $rendered_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                </div>

                <?php if ($show_post_engagement) : ?>
                    <section
                        class="ts-single-engagement"
                        data-post-engagement
                        data-post-id="<?php echo esc_attr((string) $post_id); ?>"
                        data-post-url="<?php echo esc_url($post_permalink); ?>"
                        data-post-share-url="<?php echo esc_url($post_external_share_url); ?>"
                        data-post-title="<?php echo esc_attr($post_title_plain); ?>"
                        aria-label="<?php esc_attr_e('Post engagement', 'thrivingstudio'); ?>"
                    >
                        <div class="ts-single-engagement-copy">
                            <p class="ts-single-engagement-eyebrow"><?php esc_html_e('Was this useful?', 'thrivingstudio'); ?></p>
                            <p class="ts-single-engagement-text"><?php esc_html_e('Send a small signal, save the link, or keep the conversation going.', 'thrivingstudio'); ?></p>
                        </div>
                        <div class="ts-single-engagement-actions">
                            <div class="ts-single-engagement-action-wrap">
                                <button class="ts-single-engagement-action ts-single-engagement-useful" type="button" data-post-useful aria-pressed="false">
                                    <span><?php esc_html_e('Useful', 'thrivingstudio'); ?></span>
                                    <span class="ts-single-engagement-count<?php echo $post_reaction_count > 0 ? '' : ' is-empty'; ?>" data-useful-count><?php echo esc_html(number_format_i18n($post_reaction_count)); ?></span>
                                </button>
                                <p class="ts-single-engagement-action-status" data-post-useful-status aria-live="polite"></p>
                            </div>
                            <div class="ts-single-engagement-action-wrap">
                                <button class="ts-single-engagement-action" type="button" data-post-copy>
                                    <?php esc_html_e('Copy link', 'thrivingstudio'); ?>
                                </button>
                                <p class="ts-single-engagement-action-status" data-post-copy-status aria-live="polite"></p>
                            </div>
                            <div class="ts-single-engagement-action-wrap">
                                <button class="ts-single-engagement-action" type="button" data-post-share aria-expanded="false" aria-controls="<?php echo esc_attr($post_share_menu_id); ?>">
                                    <?php esc_html_e('Share', 'thrivingstudio'); ?>
                                </button>
                                <p class="ts-single-engagement-action-status" data-post-share-status aria-live="polite"></p>
                                <div id="<?php echo esc_attr($post_share_menu_id); ?>" class="ts-single-share-menu" data-post-share-menu hidden>
                                    <a href="<?php echo esc_url('https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($post_external_share_url)); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php esc_html_e('LinkedIn', 'thrivingstudio'); ?></a>
                                    <a href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($post_external_share_url) . '&quote=' . rawurlencode($post_title_plain)); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php esc_html_e('Facebook', 'thrivingstudio'); ?></a>
                                    <a href="<?php echo esc_url('https://api.whatsapp.com/send?text=' . rawurlencode($post_share_text)); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php esc_html_e('WhatsApp', 'thrivingstudio'); ?></a>
                                    <a href="<?php echo esc_url('mailto:?subject=' . rawurlencode($post_title_plain) . '&body=' . rawurlencode($post_external_share_url)); ?>"><?php esc_html_e('Email', 'thrivingstudio'); ?></a>
                                </div>
                            </div>
                            <div class="ts-single-engagement-action-wrap">
                                <a class="ts-single-engagement-action ts-single-engagement-link" href="<?php echo esc_url($post_discussion_url); ?>">
                                    <?php echo esc_html($post_discussion_label); ?>
                                </a>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

                <?php
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
                ?>

                <?php if ($show_post_cta && ($post_cta_title !== '' || $post_cta_text !== '' || ($post_cta_primary_label !== '' && $post_cta_primary_link !== '') || ($post_cta_secondary_label !== '' && $post_cta_secondary_link !== ''))) : ?>
                    <section class="ts-single-post-cta" aria-label="<?php echo esc_attr($post_cta_title !== '' ? $post_cta_title : __('Post call to action', 'thrivingstudio')); ?>">
                        <?php if ($post_cta_title !== '') : ?>
                            <h2 class="ts-single-post-cta-title"><?php echo esc_html($post_cta_title); ?></h2>
                        <?php endif; ?>
                        <?php if ($post_cta_text !== '') : ?>
                            <p class="ts-single-post-cta-text"><?php echo esc_html($post_cta_text); ?></p>
                        <?php endif; ?>
                        <?php if (($post_cta_primary_label !== '' && $post_cta_primary_link !== '') || ($post_cta_secondary_label !== '' && $post_cta_secondary_link !== '')) : ?>
                            <div class="ts-single-post-cta-actions">
                                <?php if ($post_cta_primary_label !== '' && $post_cta_primary_link !== '') : ?>
                                    <a href="<?php echo esc_url($post_cta_primary_link); ?>" class="ts-single-post-cta-btn" aria-label="<?php echo esc_attr($post_cta_primary_label); ?>"><?php echo esc_html($post_cta_primary_label); ?></a>
                                <?php endif; ?>
                                <?php if ($post_cta_secondary_label !== '' && $post_cta_secondary_link !== '') : ?>
                                    <a href="<?php echo esc_url($post_cta_secondary_link); ?>" class="ts-single-post-cta-link" aria-label="<?php echo esc_attr($post_cta_secondary_label); ?>"><?php echo esc_html($post_cta_secondary_label); ?></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php
                $author_bio = trim(get_the_author_meta('description', $author_id));
                ?>
                <section class="ts-single-author-card" aria-label="Author information">
                    <div class="ts-single-author-avatar">
                        <?php echo get_avatar($author_id, 84, '', get_the_author(), ['class' => 'ts-single-author-avatar-img']); ?>
                    </div>
                    <div class="ts-single-author-body">
                        <p class="ts-single-author-label">Written by</p>
                        <h2 class="ts-single-author-name"><?php the_author(); ?></h2>
                        <p class="ts-single-author-bio">
                            <?php
                            if ($author_bio !== '') {
                                echo esc_html($author_bio);
                            } else {
                                echo esc_html__('Sharing practical ideas on mindset, creativity, and better work.', 'thrivingstudio');
                            }
                            ?>
                        </p>
                    </div>
                </section>

                <?php
                $prev_post = get_previous_post();
                $next_post = get_next_post();
                if ($prev_post || $next_post) :
                ?>
                    <nav class="ts-single-post-nav" aria-label="Post navigation">
                        <?php if ($prev_post) : ?>
                            <a class="ts-single-post-nav-card ts-single-post-nav-prev" href="<?php echo esc_url(get_permalink($prev_post)); ?>" aria-label="<?php echo esc_attr(sprintf(__('Previous post: %s', 'thrivingstudio'), get_the_title($prev_post))); ?>">
                                <span class="ts-single-post-nav-kicker">Previous</span>
                                <span class="ts-single-post-nav-title"><?php echo esc_html(get_the_title($prev_post)); ?></span>
                            </a>
                        <?php endif; ?>
                        <?php if ($next_post) : ?>
                            <a class="ts-single-post-nav-card ts-single-post-nav-next" href="<?php echo esc_url(get_permalink($next_post)); ?>" aria-label="<?php echo esc_attr(sprintf(__('Next post: %s', 'thrivingstudio'), get_the_title($next_post))); ?>">
                                <span class="ts-single-post-nav-kicker">Next</span>
                                <span class="ts-single-post-nav-title"><?php echo esc_html(get_the_title($next_post)); ?></span>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

                <?php
                $related_categories = wp_get_post_categories(get_the_ID());
                if ($show_related_posts && !empty($related_categories)) :
                    $related_query = new WP_Query([
                        'post_type' => 'post',
                        'post_status' => 'publish',
                        'posts_per_page' => $related_posts_count,
                        'post__not_in' => [get_the_ID()],
                        'category__in' => $related_categories,
                        'ignore_sticky_posts' => true,
                    ]);
                    if ($related_query->have_posts()) :
                ?>
                    <section class="ts-related-posts" aria-label="Related posts">
                        <h2 class="ts-related-posts-title">Related Articles</h2>
                        <div class="ts-related-posts-grid">
                            <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                                <article class="ts-related-post-card">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php
                                        $thumb_id = get_post_thumbnail_id();
                                        $thumb_alt = trim((string) get_post_meta($thumb_id, '_wp_attachment_image_alt', true));
                                        if ($thumb_alt === '') {
                                            $thumb_alt = get_the_title();
                                        }
                                        ?>
                                        <a href="<?php the_permalink(); ?>" class="ts-related-post-thumb-link">
                                            <?php the_post_thumbnail('medium', ['class' => 'ts-related-post-thumb', 'loading' => 'lazy', 'alt' => $thumb_alt]); ?>
                                        </a>
                                    <?php endif; ?>
                                    <div class="ts-related-post-card-body">
                                        <h3 class="ts-related-post-card-title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h3>
                                        <p class="ts-related-post-card-meta"><?php echo esc_html(get_the_date(get_option('date_format'))); ?></p>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        </div>
                    </section>
                <?php
                    endif;
                    wp_reset_postdata();
                endif;
                ?>

            </article>
        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
