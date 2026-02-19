<?php get_header(); ?>

<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <?php if (is_page('blog')) : ?>
                <!-- Custom Blog Page Template -->
                <section class="mb-12 rounded-xl overflow-hidden" style="background:#fff; color:#000; border: 1px solid #bbb; border-color: #bbb; box-shadow: 0 4px 24px 0 rgba(0,0,0,0.04); padding: 3.5rem 0; border-radius: 1rem;">
                    <div class="text-center px-4 sm:px-8">
                        <h1 class="text-5xl font-extrabold mb-4 text-black drop-shadow">Welcome to the Blog</h1>
                        <p class="text-xl text-black mb-2 max-w-2xl mx-auto">
                            Explore deep insights, creative ideas, and timeless stories curated for your growth and inspiration.
                        </p>
                    </div>
                </section>
                
                <!-- Category Filter Buttons -->
                <div class="mb-8 text-center">
                    <div class="flex flex-wrap justify-center gap-3 mb-4">
                        <button class="category-filter-btn active px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 bg-black text-white hover:bg-gray-800" data-category="all">
                            All
                        </button>
                        <?php
                        $categories = get_categories(array(
                            'hide_empty' => true,
                            'orderby' => 'name',
                            'order' => 'ASC'
                        ));
                        foreach ($categories as $category) {
                            echo '<button class="category-filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200" data-category="' . esc_attr($category->slug) . '">';
                            echo esc_html($category->name);
                            echo '</button>';
                        }
                        ?>
                    </div>
                </div>
                
                <?php if ( have_posts() ) : ?>
                    <div class="blog-grid grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <?php while ( have_posts() ) : the_post(); 
                            $post_categories = get_the_category();
                            $category_slugs = array();
                            foreach ($post_categories as $cat) {
                                $category_slugs[] = $cat->slug;
                            }
                            $category_data = implode(' ', $category_slugs);
                        ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class('blog-card bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300'); ?> data-categories="<?php echo esc_attr($category_data); ?>">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php 
                                        $thumbnail_id = get_post_thumbnail_id();
                                        $image_url = wp_get_attachment_image_url($thumbnail_id, 'medium');
                                        if ($image_url) {
                                            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title()) . '" class="blog-card-image w-full rounded-lg" loading="lazy" decoding="async" style="height: 200px; object-fit: cover;">';
                                        } else {
                                            echo '<div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center"><span class="text-gray-400 text-sm">Image not found</span></div>';
                                        }
                                        ?>
                                    </a>
                                <?php else : ?>
                                    <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <span class="text-gray-400 text-sm">No image available</span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Category Name -->
                                <div class="px-4 pt-4">
                                    <?php 
                                    $categories = get_the_category();
                                    if (!empty($categories)) {
                                        $primary_category = $categories[0]; // Get the first category
                                        echo '<span class="text-gray-600 text-xs font-medium">' . esc_html($primary_category->name) . '</span>';
                                    }
                                    ?>
                                </div>
                                
                                <div class="px-4 pt-2 pb-4">
                                    <h2 class="text-2xl font-bold mb-2">
                                        <a href="<?php the_permalink(); ?>" class="hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400"><?php the_title(); ?></a>
                                    </h2>
                                    <div class="text-gray-600 dark:text-gray-300 mb-4">
                                        <?php the_excerpt(); ?>
                                    </div>
                                    <a href="<?php the_permalink(); ?>" class="text-indigo-600 hover:text-indigo-800 dark:hover:text-indigo-400 font-semibold">
                                        Read More &rarr;
                                    </a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <!-- Regular Page Template -->
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <h1 class="text-4xl font-bold mb-6 text-gray-900"><?php the_title(); ?></h1>
                    <div class="prose">
                        <?php the_content(); ?>
                    </div>
                </article>
            <?php endif; ?>
        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.category-filter-btn');
    const blogCards = document.querySelectorAll('.blog-card');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const selectedCategory = this.getAttribute('data-category');
            
            // Update active button
            filterButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-black', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });
            this.classList.add('active', 'bg-black', 'text-white');
            this.classList.remove('bg-gray-100', 'text-gray-700');
            
            // Filter blog cards
            blogCards.forEach(card => {
                if (selectedCategory === 'all') {
                    card.style.display = 'block';
                } else {
                    const cardCategories = card.getAttribute('data-categories') || '';
                    if (cardCategories.includes(selectedCategory)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    });
});
</script> 