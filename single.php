<?php get_header(); ?>

<main class="flex-1" id="main-content" role="main">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <?php
            $raw_content = get_the_content();
            $rendered_content = apply_filters('the_content', $raw_content);
            $reading_time = max(1, (int) ceil(str_word_count(wp_strip_all_tags($raw_content)) / 200));
            $author_id = get_the_author_meta('ID');
            $author_name = get_the_author();
            $published_iso = get_the_date('c');
            $published_label = get_the_date('M j, Y');
            $modified_iso = get_the_modified_date('c');
            $modified_label = get_the_modified_date('M j, Y');
            $show_modified = get_the_modified_date('Y-m-d') !== get_the_date('Y-m-d');
            $has_custom_excerpt = has_excerpt();
            $post_summary = $has_custom_excerpt ? get_the_excerpt() : '';
            $toc_items = [];

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
                            $toc_items[] = [
                                'id' => $id,
                                'text' => $text,
                                'level' => strtolower($heading->nodeName),
                            ];
                        }
                    }

                    if (!$has_custom_excerpt && $post_summary === '') {
                        $paragraphs = $xpath->query('//p');

                        if ($paragraphs instanceof DOMNodeList) {
                            $summary_parts = [];
                            $summary_word_count = 0;

                            foreach ($paragraphs as $paragraph) {
                                $summary_text = trim(preg_replace('/\s+/', ' ', $paragraph->textContent));

                                if ($summary_text !== '') {
                                    $summary_parts[] = $summary_text;
                                    $summary_word_count += str_word_count($summary_text);

                                    if ($summary_word_count >= 34) {
                                        break;
                                    }
                                }
                            }

                            if (!empty($summary_parts)) {
                                $post_summary = wp_trim_words(implode(' ', $summary_parts), 34, '...');
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

            if ($post_summary === '') {
                $post_summary = wp_trim_words(wp_strip_all_tags(strip_shortcodes($raw_content)), 34, '...');
            }
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('ts-single-article'); ?> aria-labelledby="ts-post-title-<?php the_ID(); ?>">
                <header class="ts-single-hero">
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
                    <h1 id="ts-post-title-<?php the_ID(); ?>" class="ts-single-title"><?php the_title(); ?></h1>
                    <?php if ($post_summary !== '') : ?>
                        <p class="ts-single-excerpt">
                            <?php echo esc_html(wp_strip_all_tags($post_summary)); ?>
                        </p>
                    <?php endif; ?>

                    <div class="ts-single-byline">
                        <a class="ts-single-byline-avatar" href="<?php echo esc_url(get_author_posts_url($author_id)); ?>" aria-label="<?php echo esc_attr(sprintf(__('View posts by %s', 'thrivingstudio'), $author_name)); ?>">
                            <?php echo get_avatar($author_id, 44, '', $author_name, ['class' => 'ts-single-byline-avatar-img']); ?>
                        </a>
                        <div class="ts-single-byline-body">
                            <p class="ts-single-byline-author">
                                <?php esc_html_e('By', 'thrivingstudio'); ?>
                                <a href="<?php echo esc_url(get_author_posts_url($author_id)); ?>"><?php echo esc_html($author_name); ?></a>
                            </p>
                            <div class="ts-single-meta">
                                <time datetime="<?php echo esc_attr($published_iso); ?>"><?php echo esc_html($published_label); ?></time>
                                <span class="ts-meta-sep">•</span>
                                <span><?php echo esc_html($reading_time); ?> <?php esc_html_e('min read', 'thrivingstudio'); ?></span>
                                <?php if ($show_modified) : ?>
                                    <span class="ts-meta-sep">•</span>
                                    <span><?php esc_html_e('Updated', 'thrivingstudio'); ?> <time datetime="<?php echo esc_attr($modified_iso); ?>"><?php echo esc_html($modified_label); ?></time></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </header>

                <?php if (count($toc_items) >= 2) : ?>
                    <aside class="ts-single-toc-shell">
                        <nav class="ts-single-toc" aria-label="<?php esc_attr_e('In this article', 'thrivingstudio'); ?>">
                            <p class="ts-single-toc-title"><?php esc_html_e('In this article', 'thrivingstudio'); ?></p>
                            <ul class="ts-single-toc-list">
                                <?php foreach ($toc_items as $item) : ?>
                                    <li class="<?php echo $item['level'] === 'h3' ? 'ts-single-toc-subitem' : ''; ?>">
                                        <a href="#<?php echo esc_attr($item['id']); ?>">
                                            <?php echo esc_html($item['text']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    </aside>
                <?php endif; ?>

                <?php if ( has_post_thumbnail() ) : ?>
                    <?php
                    $thumbnail_id = get_post_thumbnail_id();
                    $thumbnail_alt = trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true));
                    $thumbnail_caption = trim((string) wp_get_attachment_caption($thumbnail_id));

                    if ($thumbnail_alt === '') {
                        $thumbnail_alt = get_the_title();
                    }
                    ?>
                    <figure class="ts-single-featured-wrap">
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
                <?php endif; ?>

                <div class="ts-single-body-shell">
                    <div class="prose prose-lg ts-single-content ts-single-reading-column">
                        <?php echo $rendered_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                </div>

                <section class="ts-single-post-cta" aria-label="Post call to action">
                    <h2 class="ts-single-post-cta-title">Want More Practical Insights?</h2>
                    <p class="ts-single-post-cta-text">Get focused ideas on psychology, discipline, and creative growth delivered to your inbox.</p>
                    <div class="ts-single-post-cta-actions">
                        <a href="<?php echo esc_url(home_url('/#subscribe')); ?>" class="ts-single-post-cta-btn" aria-label="Subscribe for more insights">Subscribe</a>
                        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ts-single-post-cta-link" aria-label="Contact us">Get in touch</a>
                    </div>
                </section>

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
                if (!empty($related_categories)) :
                    $related_query = new WP_Query([
                        'post_type' => 'post',
                        'post_status' => 'publish',
                        'posts_per_page' => 3,
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

                <?php
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
                ?>
            </article>
        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?> 
