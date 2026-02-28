<?php
// home.php: Blog index template for Thriving Studio
// Used when a Posts page is set in Settings > Reading
// https://developer.wordpress.org/themes/basics/template-hierarchy/
?>
<?php get_header(); ?>
<?php
$home_filter_categories = get_categories([
    'hide_empty' => true,
    'orderby' => 'count',
    'order' => 'DESC',
]);
$home_filter_categories_data = [];
foreach ($home_filter_categories as $category) {
    $home_filter_categories_data[] = [
        'slug' => $category->slug,
        'name' => $category->name,
    ];
}
?>

<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <!-- Blog Hero Section -->
        <section class="ts-blog-hero mb-12 rounded-xl overflow-hidden">
            <div class="text-center px-4 sm:px-8">
                <h1 class="text-5xl font-extrabold mb-4 text-black drop-shadow">Welcome to the Blog</h1>
                <p class="text-xl text-black mb-2 max-w-2xl mx-auto">
                    Explore deep insights, creative ideas, and timeless stories curated for your growth and inspiration.
                </p>
            </div>
        </section>
        
        <!-- Category Filter Buttons -->
        <div class="mb-12">
            <div id="category-container" data-categories="<?php echo esc_attr(wp_json_encode($home_filter_categories_data)); ?>">
                <div id="category-row-1" class="ts-category-row flex justify-start gap-3 px-4 sm:px-0 overflow-x-auto pb-1"></div>
            </div>
            <p id="filter-results-status" class="ts-filter-status px-4 sm:px-0 text-sm text-gray-600" aria-live="polite"></p>
        </div>
        
        <?php if ( have_posts() ) : ?>
            <div id="blog-post-grid" class="blog-grid ts-blog-grid grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php while ( have_posts() ) : the_post(); 
                    $post_categories = get_the_category();
                    $category_slugs = array();
                    foreach ($post_categories as $cat) {
                        $category_slugs[] = $cat->slug;
                    }
                    $category_data = implode(' ', $category_slugs);
                ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('blog-card ts-blog-card bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300'); ?> data-categories="<?php echo esc_attr($category_data); ?>">
                        <?php if ( has_post_thumbnail() ) : ?>
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
                                <a href="<?php the_permalink(); ?>" class="ts-blog-card-title-link hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400"><?php the_title(); ?></a>
                            </h2>
                            <div class="text-gray-600 dark:text-gray-300 mb-4 ts-blog-card-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="text-indigo-600 hover:text-indigo-800 dark:hover:text-indigo-400 font-semibold ts-blog-card-link">
                                Read More &rarr;
                            </a>
                        </div>
                    </article>
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
                <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/blog')); ?>" class="ts-empty-state-link">
                    <?php esc_html_e('Go to Blog Home', 'thrivingstudio'); ?>
                </a>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
