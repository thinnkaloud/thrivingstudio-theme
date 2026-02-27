<?php get_header(); ?>

<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <!-- Category Hero Section -->
        <?php
        $is_category = is_category();
        $term = $is_category ? get_queried_object() : null;
        $hero_subtitle = $term ? get_term_meta($term->term_id, 'hero_subtitle', true) : '';
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
                    <article id="post-<?php the_ID(); ?>" <?php post_class('blog-card ts-blog-card bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>" class="ts-blog-card-image-link">
                                <?php 
                                $thumbnail_id = get_post_thumbnail_id();
                                $image_url = wp_get_attachment_image_url($thumbnail_id, 'medium');
                                if ($image_url) {
                                    echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title()) . '" class="blog-card-image ts-blog-card-image w-full" loading="lazy" decoding="async">';
                                } else {
                                    echo '<div class="ts-blog-card-image-placeholder w-full h-48 bg-gray-100 flex items-center justify-center"><span class="text-gray-400 text-sm">Image not found</span></div>';
                                }
                                ?>
                            </a>
                        <?php else : ?>
                            <!-- Fallback for posts without featured images -->
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
                            <div class="text-gray-600 mb-4 ts-blog-card-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold ts-blog-card-link">
                                Read More &rarr;
                            </a>
                        </div>
                    </article>
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
