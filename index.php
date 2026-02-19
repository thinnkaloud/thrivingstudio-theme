<?php get_header(); ?>



<main class="container mx-auto px-4 py-8 flex-1 relative">
    

    <section class="mb-12 text-center py-12 shadow-lg bg-white border border-gray-200 rounded-xl" style="background: var(--tw-bg-opacity, 1) rgb(255 255 255); color: var(--tw-text-opacity, 1) rgb(17 24 39);">
        <h1 class="text-5xl font-extrabold mb-4 text-gray-900 drop-shadow"><?php echo sprintf( esc_html__('Welcome to %s', 'thrivingstudio'), '<span class="text-gray-900">Thriving Studio</span>' ); ?></h1>
        <p class="text-xl text-gray-600 mb-6"><?php esc_html_e('Deep insights, visual storytelling, and timeless ideas for a thriving creative life.', 'thrivingstudio'); ?></p>
    </section>
    <div class="mt-20">
    <?php if ( have_posts() ) : ?>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300'); ?>>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('medium_large', ['class' => 'w-full rounded-lg', 'loading' => 'lazy']); ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Category Name -->
                    <div class="px-6 pt-4">
                        <?php 
                        $categories = get_the_category();
                        if (!empty($categories)) {
                            $primary_category = $categories[0]; // Get the first category
                            echo '<span class="text-gray-600 text-xs font-medium">' . esc_html($primary_category->name) . '</span>';
                        }
                        ?>
                    </div>
                    
                    <div class="px-6 pt-2 pb-6">
                        <h2 class="text-2xl font-bold mb-2">
                            <a href="<?php the_permalink(); ?>" class="hover:text-indigo-600"><?php the_title(); ?></a>
                        </h2>
                        <div class="text-gray-600 mb-4">
                            <?php the_excerpt(); ?>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold"><?php esc_html_e('Read More', 'thrivingstudio'); ?> &rarr;</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p><?php esc_html_e('No posts found.', 'thrivingstudio'); ?></p>
    <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?> 