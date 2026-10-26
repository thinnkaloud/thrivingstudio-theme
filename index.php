<?php get_header(); ?>



<main class="container mx-auto px-4 py-8 flex-1 relative">
    

    <section class="mb-12 text-center py-12 shadow-lg bg-white border border-gray-200 rounded-xl" style="background: var(--tw-bg-opacity, 1) rgb(255 255 255); color: var(--tw-text-opacity, 1) rgb(17 24 39);">
        <h1 class="text-5xl font-extrabold mb-4 text-gray-900 drop-shadow"><?php echo sprintf( esc_html__('Welcome to %s', 'thrivingstudio'), '<span class="text-gray-900">Thriving Studio</span>' ); ?></h1>
        <p class="text-xl text-gray-600 mb-6"><?php esc_html_e('Deep insights, visual storytelling, and timeless ideas for a thriving creative life.', 'thrivingstudio'); ?></p>
    </section>
    <div class="mt-20">
    <?php if ( have_posts() ) : ?>
        <div class="blog-grid ts-blog-grid grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part('template-parts/post-card'); ?>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p><?php esc_html_e('No posts found.', 'thrivingstudio'); ?></p>
    <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
