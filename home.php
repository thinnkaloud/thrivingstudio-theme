<?php
// home.php: Blog index template for Thriving Studio
// Used when a Posts page is set in Settings > Reading
// https://developer.wordpress.org/themes/basics/template-hierarchy/
?>
<?php get_header(); ?>

<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <!-- Blog Hero Section -->
        <section class="ts-blog-hero mb-12 rounded-xl overflow-hidden">
            <div class="text-center px-4 sm:px-8">
                <h1 class="text-5xl font-extrabold mb-4 text-black drop-shadow"><?php echo esc_html(thrivingstudio_copy('blog_title')); ?></h1>
                <p class="text-xl text-black mb-2 max-w-2xl mx-auto">
                    <?php echo esc_html(thrivingstudio_copy('blog_intro')); ?>
                </p>
            </div>
        </section>
        
        <nav class="ts-category-browse mb-8" aria-label="Browse articles by category">
            <span class="ts-category-browse-label">Browse by topic</span>
            <div class="ts-category-browse-links">
                <a href="<?php echo esc_url(thrivingstudio_get_journal_url()); ?>" aria-current="page">All articles</a>
                <?php foreach (get_categories(['hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC']) as $category) : ?>
                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>"><?php echo esc_html($category->name); ?></a>
                <?php endforeach; ?>
            </div>
        </nav>

        <?php if ( have_posts() ) : ?>
            <div id="blog-post-grid" class="blog-grid ts-blog-grid grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part('template-parts/post-card'); ?>
                <?php endwhile; ?>
            </div>
            <nav class="ts-pagination-wrap" aria-label="Blog pagination">
                <?php
                the_posts_pagination([
                    'mid_size'  => 1,
                    'prev_text' => __('Previous', 'thrivingstudio'),
                    'next_text' => __('Next', 'thrivingstudio'),
                ]);
                ?>
            </nav>
        <?php else : ?>
            <section class="ts-empty-state" aria-label="No articles found">
                <h2 class="ts-empty-state-title"><?php esc_html_e('No articles found', 'thrivingstudio'); ?></h2>
                <p class="ts-empty-state-text"><?php esc_html_e('Try another category or return to the full blog feed.', 'thrivingstudio'); ?></p>
                <a href="<?php echo esc_url(thrivingstudio_get_journal_url()); ?>" class="ts-empty-state-link">
                    <?php esc_html_e('Go to Blog Home', 'thrivingstudio'); ?>
                </a>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
