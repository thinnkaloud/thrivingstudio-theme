<?php get_header(); ?>

<main class="flex-1" id="main-content" role="main">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <?php
            $raw_content = get_the_content();
            $rendered_content = apply_filters('the_content', $raw_content);
            $reading_time = max(1, (int) ceil(str_word_count(wp_strip_all_tags($raw_content)) / 200));
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
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('max-w-3xl mx-auto ts-single-article'); ?> aria-labelledby="ts-post-title-<?php the_ID(); ?>">
                <div class="prose prose-lg mx-auto ts-single-content">
                    <!-- Category first -->
                    <div class="mb-1 ts-single-category-row">
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
                                $category_parts[] = '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '" class="text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors duration-200 no-underline">' . esc_html( $parent->name ) . '</a>';
                            }
                            
                            if ( ! empty( $child_categories ) ) {
                                $child = $child_categories[0]; // Use first child category
                                $category_parts[] = '<a href="' . esc_url( get_category_link( $child->term_id ) ) . '" class="text-xs font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200 no-underline">' . esc_html( $child->name ) . '</a>';
                            }
                            
                            // Join with a separator (bullet or dash)
                            echo implode( ' <span class="text-gray-400 mx-1">•</span> ', $category_parts );
                        }
                        ?>
                    </div>
                    <!-- Title second -->
                    <h1 id="ts-post-title-<?php the_ID(); ?>" class="text-4xl font-bold mb-0 ts-single-title"><?php the_title(); ?></h1>
                    <!-- Custom excerpt -->
                    <?php if (has_excerpt()) : ?>
                        <div class="text-lg text-gray-600 mb-1 leading-relaxed ts-single-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>

                    <div class="text-base text-gray-500 mb-6 ts-single-meta">
                        <span><?php echo esc_html($reading_time); ?> min read</span>
                        <span class="ts-meta-sep">•</span>
                        <span>Published <?php echo esc_html(get_the_date(get_option('date_format'))); ?></span>
                        <?php if (get_the_modified_time('U') > get_the_time('U')) : ?>
                            <span class="ts-meta-sep">•</span>
                            <span>Updated <?php echo esc_html(get_the_modified_date(get_option('date_format'))); ?></span>
                        <?php endif; ?>
                        <span class="ts-meta-sep">•</span>
                        <span>By <?php the_author(); ?></span>
                    </div>

                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="mb-4 overflow-auto ts-single-featured-wrap">
                            <?php the_post_thumbnail('full', [
                                'class' => 'w-full rounded-lg ts-single-featured-image',
                                'loading' => 'lazy'
                            ]); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (count($toc_items) >= 2) : ?>
                        <nav class="ts-single-toc" aria-label="In this article">
                            <p class="ts-single-toc-title">In this article</p>
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
                    <?php endif; ?>
                    <?php echo $rendered_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
                $author_id = get_the_author_meta('ID');
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
