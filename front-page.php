<?php
// front-page.php: Custom homepage template for Thriving Studio
// Used when a static homepage is set in Settings > Reading
// https://developer.wordpress.org/themes/basics/template-hierarchy/
?>
<?php get_header(); ?>

<main class="flex-1 bg-white">
    <div class="site-content ts-front-page-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <!-- Hero Section (Aligned with header) -->
        <section class="hero-section ts-surface-card ts-section-spacing overflow-hidden mb-16 bg-white">
            <div class="text-center px-4 sm:px-8">
                <p class="text-sm sm:text-base font-semibold tracking-wide uppercase text-gray-600 mb-4">Creative Growth Journal</p>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold mb-6 text-black drop-shadow">
                    <?php echo wp_kses_post(get_theme_mod('thrivingstudio_home_hero_title', 'Welcome to <span class="text-black">Thriving Studio</span>')); ?>
                </h1>
                <p class="text-lg sm:text-xl md:text-2xl text-black mb-8">
                    <?php echo esc_html(get_theme_mod('thrivingstudio_home_hero_subtitle', 'Deep insights, visual storytelling, and timeless ideas for a thriving creative life.')); ?>
                </p>
                <?php $btn_text = get_theme_mod('thrivingstudio_home_hero_button_text', 'Learn More');
                $btn_link = get_theme_mod('thrivingstudio_home_hero_button_link', '#');
                if ($btn_text) : ?>
                    <a href="<?php echo esc_url($btn_link); ?>" class="inline-block px-8 py-3 font-bold rounded-lg shadow transition-colors duration-300 border border-black bg-white text-black hover:bg-gray-100">
                        <?php echo esc_html($btn_text); ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>



        <!-- Social Media Presence with Follower Counts -->
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-900">
            <?php echo esc_html(get_theme_mod('thrivingstudio_home_social_stats_title', __('Our Social Circle', 'thrivingstudio'))); ?>
        </h2>
        <p class="text-center text-gray-600 max-w-2xl mx-auto mb-6">Follow the platforms where we share practical ideas, writing, and visual storytelling.</p>
        <section class="social-section ts-surface-card ts-social-section mb-16 bg-white">
            <div class="ts-social-grid flex flex-col md:flex-row justify-center items-center gap-8 rounded-xl p-6 bg-white">
                <?php
                // Get follower counts from theme customizer
                $facebook_followers = trim((string) get_theme_mod('thrivingstudio_home_social_facebook_count', '1.2M+'));
                $instagram_followers = trim((string) get_theme_mod('thrivingstudio_home_social_instagram_count', '1.2K+'));
                $pinterest_followers = trim((string) get_theme_mod('thrivingstudio_home_social_pinterest_count', '150+'));
                $youtube_followers = trim((string) get_theme_mod('thrivingstudio_home_social_youtube_count', '99K+'));

                if ($facebook_followers === '') {
                    $facebook_followers = '1.2M+';
                }
                if ($instagram_followers === '') {
                    $instagram_followers = '1.2K+';
                }
                if ($pinterest_followers === '') {
                    $pinterest_followers = '150+';
                }
                if ($youtube_followers === '') {
                    $youtube_followers = '99K+';
                }
                ?>
                
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-bold ts-social-count ts-social-count-facebook">
                        <?php echo esc_html($facebook_followers); ?>
                    </span>
                    <span class="text-gray-700">Facebook Followers</span>
                </div>
                
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-bold ts-social-count ts-social-count-instagram">
                        <?php echo esc_html($instagram_followers); ?>
                    </span>
                    <span class="text-gray-700">Instagram Followers</span>
                </div>
                
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-bold ts-social-count ts-social-count-pinterest">
                        <?php echo esc_html($pinterest_followers); ?>
                    </span>
                    <span class="text-gray-700">Pinterest Followers</span>
                </div>
                
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-bold ts-social-count ts-social-count-youtube">
                        <?php echo esc_html($youtube_followers); ?>
                    </span>
                    <span class="text-gray-700">YouTube Followers</span>
                </div>
            </div>
        </section>

        <!-- Featured Categories (Dynamic) -->
        <section class="mb-16">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-900">Featured Categories</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-none mx-auto items-stretch">
                <?php
                for ($i = 1; $i <= 4; $i++) {
                    $cat_id = get_theme_mod("thrivingstudio_featured_category_{$i}");
                    $desc = get_theme_mod("thrivingstudio_featured_category_{$i}_desc");
                    $cat = ($cat_id && $cat_id != 0) ? get_category($cat_id) : false;
                    $gradient = 'from-blue-500/60 via-purple-500/60 to-pink-500/60'; // More vibrant gradient
                ?>
                <div class="h-full">
                    <?php if ($cat && !is_wp_error($cat)): ?>
                        <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                           class="category-card ts-surface-card block h-full overflow-hidden p-1 bg-white">
                            <div class="ts-card-inner p-6 h-full flex flex-col items-start justify-start bg-white">
                                <h3 class="text-xl font-bold mb-2 text-black truncate mt-0"><?php echo esc_html($cat->name); ?></h3>
                                <?php if (trim($desc)): ?>
                                    <p class="text-black text-base overflow-hidden text-ellipsis ts-line-clamp-4">
                                        <?php echo esc_html($desc); ?>
                                    </p>
                                <?php elseif ($cat->description): ?>
                                    <p class="text-black text-base overflow-hidden text-ellipsis ts-line-clamp-4">
                                        <?php echo esc_html($cat->description); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="category-card ts-surface-card block h-full overflow-hidden p-1 bg-white">
                            <div class="ts-card-inner p-6 h-full flex flex-col items-start justify-start bg-white">
                                <h3 class="text-xl font-bold mb-2 text-black truncate mt-0">No Category Selected</h3>
                                <p class="text-black text-base opacity-80">Please select a category in the Customizer.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php } ?>
            </div>
        </section>

        <!-- Latest Articles (Dynamic) -->
        <section class="mb-16">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-900">Latest Articles</h2>
            <p class="text-center text-gray-600 max-w-2xl mx-auto mb-6">Fresh posts on psychology, discipline, creativity, and practical systems for better work.</p>
            <div class="blog-grid ts-blog-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $latest_query = new WP_Query([
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                    'post_status' => 'publish',
                ]);
                if ($latest_query->have_posts()) :
                    while ($latest_query->have_posts()) : $latest_query->the_post(); ?>
                        <article <?php post_class('blog-card ts-blog-card bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="ts-blog-card-image-link">
                                    <?php the_post_thumbnail('medium_large', ['class' => 'blog-card-image ts-blog-card-image w-full', 'loading' => 'lazy']); ?>
                                </a>
                            <?php else : ?>
                                <div class="ts-blog-card-image-placeholder w-full h-48 bg-gray-100 flex items-center justify-center">
                                    <span class="text-gray-400 text-sm">No image available</span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Category Name -->
                            <div class="px-4 pt-4 ts-blog-card-meta">
                                <?php 
                                $categories = get_the_category();
                                if (!empty($categories)) {
                                    $primary_category = $categories[0]; // Get the first category
                                    echo '<span class="text-gray-600 text-xs font-medium ts-blog-card-category">' . esc_html($primary_category->name) . '</span>';
                                }
                                ?>
                            </div>
                            
                            <div class="px-4 pt-2 pb-4 ts-blog-card-body">
                                <h2 class="text-2xl font-bold mb-2 ts-blog-card-title">
                                    <a href="<?php the_permalink(); ?>" class="ts-blog-card-title-link hover:text-indigo-600"><?php the_title(); ?></a>
                                </h2>
                                <?php $manual_excerpt = thrivingstudio_get_manual_excerpt(); ?>
                                <?php if ($manual_excerpt !== '') : ?>
                                    <div class="text-gray-600 mb-4 ts-blog-card-excerpt">
                                        <p><?php echo esc_html($manual_excerpt); ?></p>
                                    </div>
                                <?php endif; ?>
                                <a href="<?php the_permalink(); ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold ts-blog-card-link"><?php esc_html_e('Read More', 'thrivingstudio'); ?> &rarr;</a>
                            </div>
                        </article>
                    <?php endwhile;
                    wp_reset_postdata();
                else : ?>
                    <p class="col-span-3 text-center text-gray-500">No articles found.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Featured Quote Cards Slider -->
        <section class="ts-quote-section mb-16" aria-labelledby="featured-quote-cards-title">
            <?php
            $quote_archive_link = get_post_type_archive_link('quote_card');
            $quote_query = new WP_Query([
                'post_type' => 'quote_card',
                'posts_per_page' => 6,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'ignore_sticky_posts' => true,
                'no_found_rows' => true,
                'meta_query' => [
                    [
                        'key' => '_thumbnail_id',
                        'compare' => 'EXISTS',
                    ],
                ],
            ]);
            $quote_total = (int) $quote_query->post_count;
            ?>

            <div class="ts-quote-header">
                <div class="ts-quote-heading">
                    <h2 id="featured-quote-cards-title" class="ts-quote-title">Featured Quote Cards</h2>
                </div>
                <?php if ($quote_archive_link) : ?>
                    <a class="ts-quote-archive-link" href="<?php echo esc_url($quote_archive_link); ?>">
                        <?php esc_html_e('View all', 'thrivingstudio'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($quote_query->have_posts()) : ?>
                <div class="ts-quote-carousel" data-quote-carousel>
                    <div class="ts-quote-track" data-quote-track role="list" tabindex="0" aria-label="<?php esc_attr_e('Featured quote cards', 'thrivingstudio'); ?>">
                        <?php
                        $quote_index = 0;
                        while ($quote_query->have_posts()) : $quote_query->the_post();
                            $quote_image_id = get_post_thumbnail_id();
                            ?>
                            <article <?php post_class('ts-quote-slide'); ?> role="listitem" data-quote-slide>
                                <a href="<?php the_permalink(); ?>" class="ts-quote-card-link" aria-label="<?php echo esc_attr(sprintf(__('Open quote card: %s', 'thrivingstudio'), get_the_title())); ?>">
                                    <span class="ts-quote-card-frame">
                                        <?php
                                        echo wp_get_attachment_image(
                                            $quote_image_id,
                                            'medium_large',
                                            false,
                                            [
                                                'class' => 'ts-quote-card-image',
                                                'loading' => 'lazy',
                                                'decoding' => 'async',
                                                'alt' => the_title_attribute(['echo' => false]),
                                                'sizes' => '(min-width: 1024px) 360px, (min-width: 768px) 32vw, 84vw',
                                            ]
                                        );
                                        ?>
                                    </span>
                                </a>
                            </article>
                            <?php
                            $quote_index++;
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>

                    <?php if ($quote_total > 1) : ?>
                        <div class="ts-quote-controls" aria-label="<?php esc_attr_e('Quote card carousel controls', 'thrivingstudio'); ?>">
                            <button class="ts-quote-nav-btn" type="button" data-quote-prev aria-label="<?php esc_attr_e('Previous quote card', 'thrivingstudio'); ?>">
                                <span aria-hidden="true">&lsaquo;</span>
                            </button>
                            <div class="ts-quote-dots" data-quote-dots>
                                <?php for ($i = 0; $i < $quote_total; $i++) : ?>
                                    <button
                                        class="ts-quote-dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
                                        type="button"
                                        data-quote-dot
                                        data-quote-index="<?php echo esc_attr($i); ?>"
                                        aria-label="<?php echo esc_attr(sprintf(__('Show quote card %d', 'thrivingstudio'), $i + 1)); ?>"
                                        <?php echo $i === 0 ? 'aria-current="true"' : ''; ?>
                                    ></button>
                                <?php endfor; ?>
                            </div>
                            <button class="ts-quote-nav-btn" type="button" data-quote-next aria-label="<?php esc_attr_e('Next quote card', 'thrivingstudio'); ?>">
                                <span aria-hidden="true">&rsaquo;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="ts-empty-state text-center">
                    <p class="ts-empty-state-text">No quote cards found. Add quote card images to feature them here.</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Subscribe Section -->
        <section id="subscribe" class="ts-home-subscribe">
            <div class="subscribe-section ts-subscribe-panel bg-[#f8fafc] rounded-xl p-10 text-center border border-gray-200 shadow-sm">
                <h2 class="text-2xl font-bold mb-4 text-gray-900">Stay Inspired!</h2>
                <p class="mb-6 text-gray-700">Subscribe to our newsletter for the latest articles, quotes, and creative tips.</p>
                <div class="ts-newsletter-form">
                    <?php if (shortcode_exists('sibwp_form')) : ?>
                        <?php echo do_shortcode('[sibwp_form id=2]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted Brevo plugin shortcode output. ?>
                    <?php else : ?>
                        <form class="ts-newsletter-preview" aria-label="<?php esc_attr_e('Newsletter signup preview', 'thrivingstudio'); ?>">
                            <label class="screen-reader-text" for="ts-newsletter-preview-email"><?php esc_html_e('Email address', 'thrivingstudio'); ?></label>
                            <input id="ts-newsletter-preview-email" type="email" placeholder="<?php esc_attr_e('Your email address', 'thrivingstudio'); ?>" disabled>
                            <button type="submit" disabled><?php esc_html_e('Subscribe', 'thrivingstudio'); ?></button>
                        </form>
                        <p class="ts-newsletter-form-notice"><?php esc_html_e('Preview only — newsletter signup is enabled on the live site.', 'thrivingstudio'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<?php get_footer(); ?> 
