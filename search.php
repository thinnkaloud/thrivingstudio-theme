<?php get_header(); ?>

<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <section class="ts-search-hero mb-12">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-6">
                <?php
                if (get_search_query() !== '') {
                    /* translators: %s: search terms */
                    printf(esc_html__('Results for "%s"', 'thrivingstudio'), esc_html(get_search_query()));
                } else {
                    esc_html_e('Search', 'thrivingstudio');
                }
                ?>
            </h1>
            <?php get_search_form(); ?>
        </section>

        <?php if (get_search_query() !== '' && have_posts()) : ?>
            <div class="blog-grid ts-blog-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/post-card'); ?>
                <?php endwhile; ?>
            </div>
            <nav class="ts-pagination-wrap" aria-label="<?php esc_attr_e('Search results pagination', 'thrivingstudio'); ?>">
                <?php
                the_posts_pagination([
                    'mid_size'  => 1,
                    'prev_text' => __('Previous', 'thrivingstudio'),
                    'next_text' => __('Next', 'thrivingstudio'),
                ]);
                ?>
            </nav>
        <?php elseif (get_search_query() !== '') : ?>
            <section class="ts-empty-state" aria-label="<?php esc_attr_e('No results', 'thrivingstudio'); ?>">
                <h2 class="ts-empty-state-title"><?php esc_html_e('Nothing matched that search', 'thrivingstudio'); ?></h2>
                <p class="ts-empty-state-text"><?php esc_html_e('Try a shorter phrase, or browse every article in the journal.', 'thrivingstudio'); ?></p>
                <a href="<?php echo esc_url(thrivingstudio_get_journal_url()); ?>" class="ts-empty-state-link">
                    <?php esc_html_e('Go to Blog Home', 'thrivingstudio'); ?>
                </a>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
