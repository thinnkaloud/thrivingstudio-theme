<?php
// home.php: Blog index template for Thriving Studio
// Used when a Posts page is set in Settings > Reading
// https://developer.wordpress.org/themes/basics/template-hierarchy/
?>
<?php get_header(); ?>

<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <!-- Blog Hero Section -->
        <section class="mb-12 rounded-xl overflow-hidden" style="background:#fff; color:#000; border: 1px solid #bbb; border-color: #bbb; box-shadow: 0 4px 24px 0 rgba(0,0,0,0.04); padding: 3.5rem 0; border-radius: 1rem;">
            <div class="text-center px-4 sm:px-8">
                <h1 class="text-5xl font-extrabold mb-4 text-black drop-shadow">Welcome to the Blog</h1>
                <p class="text-xl text-black mb-2 max-w-2xl mx-auto">
                    Explore deep insights, creative ideas, and timeless stories curated for your growth and inspiration.
                </p>
            </div>
        </section>
        
        <!-- Category Filter Buttons -->
        <div class="mb-12">
            <div id="category-container">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all categories from PHP
    const categories = <?php
        $categories = get_categories(array(
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC'
        ));
        $category_data = array();
        foreach ($categories as $category) {
            $category_data[] = array(
                'slug' => $category->slug,
                'name' => $category->name
            );
        }
        echo json_encode($category_data);
        ?>;
    
    const blogCards = document.querySelectorAll('.blog-card');
    const resultsStatus = document.getElementById('filter-results-status');
    let currentRow = 1;
    let visibleCategories = 0;
    const maxCategoriesPerRow = 8; // Limit categories per row for desktop
    const maxCategoriesPerRowTablet = 5; // Limit categories per row for tablet
    const maxCategoriesPerRowMobile = 3; // Limit categories per row for mobile
    
    function getScreenSize() {
        const width = window.innerWidth;
        if (width <= 640) return 'mobile'; // sm breakpoint
        if (width <= 1024) return 'tablet'; // lg breakpoint
        return 'desktop';
    }
    
    function getMaxCategoriesPerRow() {
        const screenSize = getScreenSize();
        switch(screenSize) {
            case 'mobile': return maxCategoriesPerRowMobile;
            case 'tablet': return maxCategoriesPerRowTablet;
            case 'desktop': return maxCategoriesPerRow;
            default: return maxCategoriesPerRow;
        }
    }
    
    function isMobile() {
        return getScreenSize() === 'mobile';
    }
    
    function getCategoryColor(categoryName) {
        const colorMap = {
            'Uncategorized': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#6b7280' },
            'Self Improvement': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#059669' },
            'Signals Of Progress': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#dc2626' },
            'Awareness': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#7c3aed' },
            'Lorem Ipsum': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#0891b2' },
            'Lorem Ipsum 2': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#ca8a04' },
            'Lorem Ipsum 3': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#dc2626' },
            'Lorem Ipsum 4': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#059669' },
            'Lorem Ipsum 5': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#7c3aed' },
            'Lorem Ipsum 6': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#0891b2' },
            'Lorem Ipsum 7': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#ca8a04' },
            'Psychology': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#7c3aed' },
            'Mental Health': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#dc2626' },
            'Health and Fitness': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#059669' },
            'Discipline & Design': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#0891b2' },
            'Consciousness': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#7c3aed' },
            'Inspiring': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#ca8a04' },
            'The Good Thread': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#059669' },
            'Wildlife': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#16a34a' },
            'Art & Creativity': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#dc2626' },
            'Innovation': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#0891b2' },
            'Nutrition': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#16a34a' },
            'Personal Growth Mindset': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#7c3aed' },
            'Wellness': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#059669' },
            'Trending': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#dc2626' },
            'Blogging': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#0891b2' },
            'Miscellaneous': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#6b7280' },
            'True Hero Files': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#ca8a04' },
            'Yoga & Spirituality': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#7c3aed' },
            'Social Media': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#0891b2' },
            'Instagram': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#dc2626' },
            'AI & Tools': { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#7c3aed' }
        };
        
        return colorMap[categoryName] || { bg: '#f3f4f6', text: '#374151', hover: '#e5e7eb', active: '#6b7280' };
    }

    function setCategoryButtonDefault(button) {
        if (button.textContent === 'See more...') {
            return;
        }
        const colors = getCategoryColor(button.textContent);
        button.classList.remove('active');
        button.setAttribute('aria-pressed', 'false');
        button.style.backgroundColor = colors.bg;
        button.style.color = colors.text;
        button.style.border = '1px solid transparent';
    }

    function setCategoryButtonActive(button) {
        const colors = getCategoryColor(button.textContent);
        button.classList.add('active');
        button.setAttribute('aria-pressed', 'true');
        button.style.backgroundColor = '#f3f4f6';
        button.style.color = '#374151';
        button.style.border = `1px solid ${colors.active}`;
    }

    function updateFilterStatus(selectedCategory) {
        if (!resultsStatus) {
            return;
        }
        let visibleCount = 0;
        blogCards.forEach((card) => {
            if (!card.hidden) {
                visibleCount++;
            }
        });

        const label = selectedCategory === 'all'
            ? 'all categories'
            : `"${selectedCategory.replace(/-/g, ' ')}"`;
        const noun = visibleCount === 1 ? 'post' : 'posts';
        resultsStatus.textContent = `Showing ${visibleCount} ${noun} for ${label}.`;
    }

    function applyFilter(selectedCategory) {
        blogCards.forEach((card) => {
            if (selectedCategory === 'all') {
                card.hidden = false;
                return;
            }
            const cardCategories = card.getAttribute('data-categories') || '';
            card.hidden = !cardCategories.includes(selectedCategory);
        });
        updateFilterStatus(selectedCategory);
    }
    
    function createCategoryButton(category, isActive = false) {
        const button = document.createElement('button');
        const textSize = isMobile() ? 'text-xs' : 'text-sm'; // 20% smaller on mobile
        button.type = 'button';
        button.className = `category-filter-btn ts-category-filter-btn px-3 py-2 rounded-full ${textSize} font-medium transition-all duration-200 whitespace-nowrap flex-shrink-0`;
        button.setAttribute('data-category', category.slug);
        button.setAttribute('aria-controls', 'blog-post-grid');
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        button.textContent = category.name;

        setCategoryButtonDefault(button);
        if (isActive) {
            setCategoryButtonActive(button);
        }
        return button;
    }
    
    function createSeeMoreButton() {
        const button = document.createElement('button');
        const textSize = isMobile() ? 'text-xs' : 'text-sm'; // 20% smaller on mobile
        button.type = 'button';
        button.className = `category-filter-btn ts-see-more-btn px-3 py-2 rounded-full ${textSize} font-medium transition-all duration-200 whitespace-nowrap flex-shrink-0`;
        button.textContent = 'See more...';
        button.addEventListener('click', showNextRow);
        return button;
    }
    
    function showNextRow() {
        currentRow++;
        const container = document.getElementById('category-container');
        const newRow = document.createElement('div');
        newRow.id = `category-row-${currentRow}`;
        newRow.className = 'ts-category-row flex justify-start gap-3 px-4 sm:px-0 overflow-x-auto pb-1 mt-4';
        
        // Add categories to new row
        let categoriesInRow = 0;
        while (visibleCategories < categories.length && categoriesInRow < getMaxCategoriesPerRow()) {
            const category = categories[visibleCategories];
            const button = createCategoryButton(category);
            newRow.appendChild(button);
            visibleCategories++;
            categoriesInRow++;
        }
        
        // Add See more button if there are more categories
        if (visibleCategories < categories.length) {
            const seeMoreButton = createSeeMoreButton();
            newRow.appendChild(seeMoreButton);
        }
        
        container.appendChild(newRow);
        
        // Remove the See more button from previous row
        const prevRow = document.getElementById(`category-row-${currentRow - 1}`);
        const prevSeeMoreButton = prevRow.querySelector('button:last-child');
        if (prevSeeMoreButton && prevSeeMoreButton.textContent === 'See more...') {
            prevSeeMoreButton.remove();
        }
        
        // Add event listeners to new buttons
        addEventListenersToRow(newRow);
    }
    
    function addEventListenersToRow(row) {
        const buttons = row.querySelectorAll('.category-filter-btn');
        buttons.forEach(button => {
            if (button.textContent !== 'See more...') {
                button.addEventListener('click', function() {
                    const selectedCategory = this.getAttribute('data-category');

                    document.querySelectorAll('.category-filter-btn').forEach((btn) => {
                        setCategoryButtonDefault(btn);
                    });
                    setCategoryButtonActive(this);
                    applyFilter(selectedCategory);
                });
            }
        });
    }
    
    // Initialize first row
    function initializeCategories() {
        const firstRow = document.getElementById('category-row-1');
        
        // Add "All" button
        const allButton = createCategoryButton({slug: 'all', name: 'All'}, true);
        firstRow.appendChild(allButton);
        visibleCategories = 0; // Start counting from 0 since "All" is not in categories array
        
        // Add categories to first row
        let categoriesInRow = 0;
        const maxInFirstRow = getMaxCategoriesPerRow() - 1; // -1 for "All" button
        while (visibleCategories < categories.length && categoriesInRow < maxInFirstRow) {
            const category = categories[visibleCategories];
            const button = createCategoryButton(category);
            firstRow.appendChild(button);
            visibleCategories++;
            categoriesInRow++;
        }
        
        // On mobile, show 2 rows by default, on desktop show 1 row
        if (isMobile()) {
            // Add See more button if there are more categories after first row
            if (visibleCategories < categories.length) {
                const seeMoreButton = createSeeMoreButton();
                firstRow.appendChild(seeMoreButton);
            }
            
            // Create second row for mobile if there are more categories
            if (visibleCategories < categories.length) {
                showNextRow();
            }
        } else {
            // Desktop: Add See more button if there are more categories
            if (visibleCategories < categories.length) {
                const seeMoreButton = createSeeMoreButton();
                firstRow.appendChild(seeMoreButton);
            }
        }
        
        // Add event listeners
        addEventListenersToRow(firstRow);
        applyFilter('all');
    }
    
    initializeCategories();
    
    // Handle window resize for responsive behavior
    window.addEventListener('resize', function() {
        // Reinitialize categories when window is resized
        const container = document.getElementById('category-container');
        if (container) {
            container.innerHTML = '<div id="category-row-1" class="ts-category-row flex justify-start gap-3 px-4 sm:px-0 overflow-x-auto pb-1"></div>';
            currentRow = 1;
            visibleCategories = 0;
            initializeCategories();
        }
    });
});
</script> 
