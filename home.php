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
                <div id="category-row-1" class="flex justify-start gap-4 px-4 sm:px-0 overflow-hidden"></div>
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
                            <!-- Fallback for posts without featured images -->
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
        <?php else : ?>
            <p>No articles found.</p>
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
    
    function isTablet() {
        return getScreenSize() === 'tablet';
    }
    
    function getCategoryColor(categoryName) {
        console.log('Getting color for category:', categoryName);
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
    
    function createCategoryButton(category, isActive = false) {
        const button = document.createElement('button');
        const textSize = isMobile() ? 'text-xs' : 'text-sm'; // 20% smaller on mobile
        button.className = `category-filter-btn px-3 py-2 rounded-full ${textSize} font-medium transition-all duration-200 whitespace-nowrap flex-shrink-0`;
        button.setAttribute('data-category', category.slug);
        button.textContent = category.name;
        
        if (isActive) {
            const colors = getCategoryColor(category.name);
            button.style.backgroundColor = '#f3f4f6';
            button.style.color = '#374151';
            button.style.border = `1px solid ${colors.active}`;
        } else {
            const colors = getCategoryColor(category.name);
            button.style.backgroundColor = colors.bg;
            button.style.color = colors.text;
            button.style.border = '1px solid transparent';
            button.addEventListener('mouseenter', function() {
                this.style.backgroundColor = colors.hover;
            });
            button.addEventListener('mouseleave', function() {
                this.style.backgroundColor = colors.bg;
            });
        }
        
        console.log('Created button for:', category.name, 'with colors:', isActive ? 'black/white' : getCategoryColor(category.name));
        return button;
    }
    
    function createSeeMoreButton() {
        const button = document.createElement('button');
        const textSize = isMobile() ? 'text-xs' : 'text-sm'; // 20% smaller on mobile
        button.className = `category-filter-btn px-3 py-2 rounded-full ${textSize} font-medium transition-all duration-200 bg-blue-100 text-blue-700 hover:bg-blue-200 whitespace-nowrap flex-shrink-0`;
        button.textContent = 'See more...';
        button.addEventListener('click', showNextRow);
        return button;
    }
    
    function showNextRow() {
        currentRow++;
        const container = document.getElementById('category-container');
        const newRow = document.createElement('div');
        newRow.id = `category-row-${currentRow}`;
                newRow.className = 'flex justify-start gap-4 px-4 sm:px-0 overflow-hidden mt-4';
        
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
                    
                            // Update active button
                            document.querySelectorAll('.category-filter-btn').forEach(btn => {
                                if (btn.textContent !== 'See more...') {
                                    btn.classList.remove('active');
                                    // Restore original color for each button
                                    const categoryName = btn.textContent;
                                    if (categoryName !== 'All') {
                                        const colors = getCategoryColor(categoryName);
                                        btn.style.backgroundColor = colors.bg;
                                        btn.style.color = colors.text;
                                        btn.style.border = '1px solid transparent';
                                    } else {
                                        btn.style.backgroundColor = '#f3f4f6';
                                        btn.style.color = '#374151';
                                        btn.style.border = '1px solid #6b7280'; // Grey for "All"
                                    }
                                }
                            });
                            this.classList.add('active');
                            this.style.backgroundColor = '#f3f4f6';
                            this.style.color = '#374151';
                            // Use the category's specific active color
                            const categoryName = this.textContent;
                            const colors = getCategoryColor(categoryName);
                            this.style.border = `1px solid ${colors.active}`;
                    
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
            }
        });
    }
    
    // Initialize first row
    function initializeCategories() {
        console.log('Categories loaded:', categories);
        console.log('Window width:', window.innerWidth);
        console.log('Screen size:', getScreenSize());
        console.log('Is mobile:', isMobile());
        console.log('Is tablet:', isTablet());
        console.log('Max categories per row:', getMaxCategoriesPerRow());
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
    }
    
    initializeCategories();
    
    // Handle window resize for responsive behavior
    window.addEventListener('resize', function() {
        // Reinitialize categories when window is resized
        const container = document.getElementById('category-container');
        if (container) {
            container.innerHTML = '<div id="category-row-1" class="flex justify-start gap-4 px-4 sm:px-0 overflow-hidden"></div>';
            currentRow = 1;
            visibleCategories = 0;
            initializeCategories();
        }
    });
});
</script> 