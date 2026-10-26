<?php get_header(); ?>

<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <!-- Category Hero Section -->
        <?php
        $is_category = is_category();
        $term = $is_category ? get_queried_object() : null;
        $hero_subtitle = $term ? get_term_meta($term->term_id, 'hero_subtitle', true) : '';
        if ($term && $term->slug === 'artificial-intelligence-and-tools') {
            $hero_subtitle = 'Stories about artificial intelligence and emerging technology, and how they shape everyday life.';
        }
        ?>
        <section class="mb-12 rounded-xl overflow-hidden" style="background:#fff; color:#000; border: 1px solid #bbb; border-color: #bbb; box-shadow: 0 4px 24px 0 rgba(0,0,0,0.04); padding: 3.5rem 0; border-radius: 1rem;">
            <div class="text-center px-4 sm:px-8">
                <?php if ($is_category): ?>
                    <h1 class="text-5xl font-extrabold mb-6 text-gray-900 drop-shadow-sm"><?php single_cat_title(); ?></h1>
                    <?php if ($hero_subtitle): ?>
                        <p class="text-xl text-gray-600 mb-4 max-w-3xl mx-auto leading-relaxed"><?php echo esc_html($hero_subtitle); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <h1 class="text-4xl font-extrabold text-gray-900 drop-shadow-sm text-center mb-0"><?php the_archive_title(); ?></h1>
                <?php endif; ?>
            </div>
        </section>

        <?php if (have_posts()) : ?>
            <div class="blog-grid ts-blog-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/post-card'); ?>
                <?php endwhile; ?>
            </div>
            <nav class="ts-pagination-wrap" aria-label="Archive pagination">
                <?php
                the_posts_pagination([
                    'mid_size'  => 1,
                    'prev_text' => __('Previous', 'thrivingstudio'),
                    'next_text' => __('Next', 'thrivingstudio'),
                ]);
                ?>
            </nav>
        <?php else : ?>
            <section class="ts-empty-state" aria-label="No posts in this archive">
                <h2 class="ts-empty-state-title"><?php esc_html_e('No posts in this section yet', 'thrivingstudio'); ?></h2>
                <p class="ts-empty-state-text"><?php esc_html_e('Browse other categories or return to the full blog listing.', 'thrivingstudio'); ?></p>
                <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/blog')); ?>" class="ts-empty-state-link">
                    <?php esc_html_e('Go to Blog Home', 'thrivingstudio'); ?>
                </a>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?> 
