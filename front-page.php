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
                <p class="text-sm sm:text-base font-semibold tracking-wide uppercase text-gray-600 mb-4"><?php echo esc_html(thrivingstudio_copy('home_eyebrow')); ?></p>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold mb-6 text-black drop-shadow">
                    <?php echo wp_kses_post(get_theme_mod('thrivingstudio_home_hero_title', __('Thriving Studio', 'thrivingstudio'))); ?>
                </h1>
                <p class="text-lg sm:text-xl md:text-2xl text-black mb-8 max-w-3xl mx-auto">
                    <?php echo esc_html(get_theme_mod('thrivingstudio_home_hero_subtitle', __('Practical writing on a steadier mind, a disciplined life, and a world worth being curious about.', 'thrivingstudio'))); ?>
                </p>
                <?php
                $btn_text = get_theme_mod('thrivingstudio_home_hero_button_text', __('Start here', 'thrivingstudio'));
                $btn_link = get_theme_mod('thrivingstudio_home_hero_button_link', '#');
                // Without a custom destination, send new readers to the Foundations page when it exists.
                if ($btn_link === '#' || $btn_link === '' || $btn_link === home_url('/#latest-articles')) {
                    $foundations_page = get_page_by_path('foundations');
                    $btn_link = $foundations_page ? get_permalink($foundations_page) : '#latest-articles';
                }
                $secondary_text = thrivingstudio_copy('home_hero_secondary_label');
                ?>
                <div class="ts-hero-actions">
                    <?php if ($btn_text) : ?>
                        <a href="<?php echo esc_url($btn_link); ?>" class="inline-block px-8 py-3 font-bold rounded-lg shadow transition-colors duration-300 border border-black bg-black text-white hover:bg-gray-800">
                            <?php echo esc_html($btn_text); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($secondary_text) : ?>
                        <a href="#subscribe" class="inline-block px-8 py-3 font-bold rounded-lg transition-colors duration-300 border border-black bg-white text-black hover:bg-gray-100">
                            <?php echo esc_html($secondary_text); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Content Pillars -->
        <?php $pillars = thrivingstudio_get_pillars(); ?>
        <?php if (!empty($pillars)) : ?>
            <section class="mb-16" aria-labelledby="pillars-title">
                <h2 id="pillars-title" class="text-2xl font-bold mb-6 text-center text-gray-900"><?php echo esc_html(thrivingstudio_copy('home_pillars_title')); ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full max-w-none mx-auto items-stretch">
                    <?php foreach ($pillars as $pillar) : ?>
                        <a href="<?php echo esc_url($pillar['url']); ?>" class="category-card ts-surface-card block h-full overflow-hidden p-1 bg-white">
                            <div class="ts-card-inner p-6 h-full flex flex-col items-start justify-start bg-white">
                                <h3 class="text-xl font-bold mb-2 text-black mt-0"><?php echo esc_html($pillar['name']); ?></h3>
                                <p class="text-black text-base ts-line-clamp-4"><?php echo esc_html($pillar['description']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php elseif (current_user_can('edit_theme_options')) : ?>
            <p class="ts-empty-state mb-16"><?php esc_html_e('Only you can see this: create the categories inner-strength, discipline and curious-world (by slug) to show the three pillars here.', 'thrivingstudio'); ?></p>
        <?php endif; ?>

        <!-- Latest Articles (Dynamic) -->
        <section class="mb-16">
            <h2 id="latest-articles" class="text-2xl font-bold mb-6 text-center text-gray-900"><?php echo esc_html(thrivingstudio_copy('home_latest_title')); ?></h2>
            <p class="text-center text-gray-600 max-w-2xl mx-auto mb-6"><?php echo esc_html(thrivingstudio_copy('home_latest_intro')); ?></p>
            <div class="blog-grid ts-blog-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $latest_query = new WP_Query([
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                    'post_status' => 'publish',
                ]);
                if ($latest_query->have_posts()) :
                    while ($latest_query->have_posts()) : $latest_query->the_post(); ?>
                        <?php get_template_part('template-parts/post-card', null, ['heading_level' => 'h3']); ?>
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

        <!-- Social profiles: only counts the owner has entered are shown. -->
        <?php
        $social_stats = [
            'facebook' => __('Facebook followers', 'thrivingstudio'),
            'instagram' => __('Instagram followers', 'thrivingstudio'),
            'pinterest' => __('Pinterest followers', 'thrivingstudio'),
            'youtube' => __('YouTube subscribers', 'thrivingstudio'),
        ];
        foreach ($social_stats as $network => $label) {
            $count = trim((string) get_theme_mod("thrivingstudio_home_social_{$network}_count", ''));
            if ($count === '') {
                unset($social_stats[$network]);
            } else {
                $social_stats[$network] = ['count' => $count, 'label' => $label];
            }
        }
        ?>
        <?php if (!empty($social_stats)) : ?>
            <section class="mb-16" aria-labelledby="social-stats-title">
                <h2 id="social-stats-title" class="text-2xl font-bold mb-6 text-center text-gray-900">
                    <?php echo esc_html(get_theme_mod('thrivingstudio_home_social_stats_title', __('Follow along', 'thrivingstudio'))); ?>
                </h2>
                <p class="text-center text-gray-600 max-w-2xl mx-auto mb-6"><?php echo esc_html(thrivingstudio_copy('home_social_intro')); ?></p>
                <div class="social-section ts-surface-card ts-social-section bg-white">
                    <div class="ts-social-grid flex flex-col md:flex-row justify-center items-center gap-8 rounded-xl p-6 bg-white">
                        <?php foreach ($social_stats as $network => $stat) : ?>
                            <div class="flex flex-col items-center">
                                <span class="text-3xl font-bold ts-social-count ts-social-count-<?php echo esc_attr($network); ?>"><?php echo esc_html($stat['count']); ?></span>
                                <span class="text-gray-700"><?php echo esc_html($stat['label']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Subscribe Section -->
        <section id="subscribe" class="ts-home-subscribe">
            <div class="subscribe-section ts-subscribe-panel bg-[#f8fafc] rounded-xl p-10 text-center border border-gray-200 shadow-sm">
                <h2 class="text-2xl font-bold mb-4 text-gray-900"><?php echo esc_html(thrivingstudio_copy('newsletter_title')); ?></h2>
                <p class="mb-6 text-gray-700 max-w-2xl mx-auto"><?php echo esc_html(thrivingstudio_copy('newsletter_text')); ?></p>
                <div class="ts-newsletter-form">
                    <?php if (shortcode_exists('sibwp_form')) : ?>
                        <?php
                        $newsletter_form = do_shortcode('[sibwp_form id=2]');

                        if (class_exists('WP_HTML_Tag_Processor')) {
                            $newsletter_form_processor = new WP_HTML_Tag_Processor($newsletter_form);

                            while ($newsletter_form_processor->next_tag('input')) {
                                if ($newsletter_form_processor->get_attribute('name') === 'email') {
                                    $newsletter_form_processor->set_attribute('placeholder', __('Your email address', 'thrivingstudio'));
                                    $newsletter_form_processor->set_attribute('aria-label', __('Email address', 'thrivingstudio'));
                                    break;
                                }
                            }

                            $newsletter_form = $newsletter_form_processor->get_updated_html();
                        }

                        echo $newsletter_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted Brevo plugin shortcode output.
                        ?>
                    <?php else : ?>
                        <form class="ts-newsletter-preview" aria-label="<?php esc_attr_e('Newsletter signup preview', 'thrivingstudio'); ?>">
                            <label class="screen-reader-text" for="ts-newsletter-preview-email"><?php esc_html_e('Email address', 'thrivingstudio'); ?></label>
                            <input id="ts-newsletter-preview-email" type="email" placeholder="<?php esc_attr_e('Your email address', 'thrivingstudio'); ?>" disabled>
                            <button type="submit" disabled><?php esc_html_e('Subscribe', 'thrivingstudio'); ?></button>
                        </form>
                        <p class="ts-newsletter-form-notice"><?php esc_html_e('Preview only — newsletter signup is enabled on the live site.', 'thrivingstudio'); ?></p>
                    <?php endif; ?>
                </div>
                <p class="mt-4 text-sm text-gray-600">Unsubscribe using the link in any newsletter. <a class="underline" href="<?php echo esc_url(get_privacy_policy_url() ?: home_url('/privacy-policy/')); ?>">Read our privacy policy</a>.</p>
            </div>
        </section>
    </div>
</main>

<?php get_footer(); ?> 
