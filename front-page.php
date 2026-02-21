<?php
// front-page.php: Custom homepage template for Thriving Studio
// Used when a static homepage is set in Settings > Reading
// https://developer.wordpress.org/themes/basics/template-hierarchy/
?>
<?php get_header(); ?>

<main class="flex-1 bg-white">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <!-- Hero Section (Aligned with header) -->
        <section class="hero-section ts-surface-card ts-section-spacing overflow-hidden mb-16 bg-white">
            <div class="text-center px-4 sm:px-8">
                <p class="text-sm sm:text-base font-semibold tracking-wide uppercase text-gray-600 mb-4">Creative Growth Journal</p>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold mb-6 text-black drop-shadow">
                    <?php echo wp_kses_post(get_theme_mod('thrivingstudio_home_hero_title', 'Welcome to <span class="text-black">Thriving Studio</span>')); ?>
                </h1>
                <p class="text-lg sm:text-xl md:text-2xl text-black mb-8">
                    <?php echo esc_html(get_theme_mod('thrivingstudio_home_hero_subtitle', 'Deep insights, visual storytelling, and timeless ideas for a thriving creative life.')); ?>
                </p>
                <?php $btn_text = get_theme_mod('thrivingstudio_home_hero_button_text', 'Learn More');
                $btn_link = get_theme_mod('thrivingstudio_home_hero_button_link', '#');
                if ($btn_text) : ?>
                    <a href="<?php echo esc_url($btn_link); ?>" class="inline-block px-8 py-3 font-bold rounded-lg shadow transition-colors duration-300 border border-black bg-white text-black hover:bg-gray-100">
                        <?php echo esc_html($btn_text); ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>



        <!-- Social Media Presence with Follower Counts -->
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-900">
            <?php echo esc_html(get_theme_mod('thrivingstudio_home_social_stats_title', __('Our Social Circle', 'thrivingstudio'))); ?>
        </h2>
        <p class="text-center text-gray-600 max-w-2xl mx-auto mb-6">Follow the platforms where we share practical ideas, writing, and visual storytelling.</p>
        <section class="social-section ts-surface-card ts-social-section mb-16 bg-white">
            <div class="ts-social-grid flex flex-col md:flex-row justify-center items-center gap-8 rounded-xl p-6 bg-white">
                <?php
                // Get follower counts from theme customizer
                $facebook_followers = get_theme_mod('thrivingstudio_home_social_facebook_count', '1.2M+');
                $instagram_followers = get_theme_mod('thrivingstudio_home_social_instagram_count', '1.2K+');
                $pinterest_followers = get_theme_mod('thrivingstudio_home_social_pinterest_count', '150+');
                $youtube_followers = get_theme_mod('thrivingstudio_home_social_youtube_count', '99K+');
                ?>
                
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-bold ts-social-count ts-social-count-facebook">
                        <?php echo esc_html($facebook_followers); ?>
                    </span>
                    <span class="text-gray-700">Facebook Followers</span>
                </div>
                
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-bold ts-social-count ts-social-count-instagram">
                        <?php echo esc_html($instagram_followers); ?>
                    </span>
                    <span class="text-gray-700">Instagram Followers</span>
                </div>
                
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-bold ts-social-count ts-social-count-pinterest">
                        <?php echo esc_html($pinterest_followers); ?>
                    </span>
                    <span class="text-gray-700">Pinterest Followers</span>
                </div>
                
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-bold ts-social-count ts-social-count-youtube">
                        <?php echo esc_html($youtube_followers); ?>
                    </span>
                    <span class="text-gray-700">YouTube Followers</span>
                </div>
            </div>
        </section>

        <!-- Featured Categories (Dynamic) -->
        <section class="mb-16">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-900">Featured Categories</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-none mx-auto items-stretch">
                <?php
                for ($i = 1; $i <= 4; $i++) {
                    $cat_id = get_theme_mod("thrivingstudio_featured_category_{$i}");
                    $desc = get_theme_mod("thrivingstudio_featured_category_{$i}_desc");
                    $cat = ($cat_id && $cat_id != 0) ? get_category($cat_id) : false;
                    $gradient = 'from-blue-500/60 via-purple-500/60 to-pink-500/60'; // More vibrant gradient
                ?>
                <div class="h-full">
                    <?php if ($cat && !is_wp_error($cat)): ?>
                        <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                           class="category-card ts-surface-card block h-full overflow-hidden p-1 bg-white">
                            <div class="ts-card-inner p-6 h-full flex flex-col items-start justify-start bg-white">
                                <h3 class="text-xl font-bold mb-2 text-black truncate mt-0"><?php echo esc_html($cat->name); ?></h3>
                                <?php if (trim($desc)): ?>
                                    <p class="text-black text-base overflow-hidden text-ellipsis ts-line-clamp-4">
                                        <?php echo esc_html($desc); ?>
                                    </p>
                                <?php elseif ($cat->description): ?>
                                    <p class="text-black text-base overflow-hidden text-ellipsis ts-line-clamp-4">
                                        <?php echo esc_html($cat->description); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="category-card ts-surface-card block h-full overflow-hidden p-1 bg-white">
                            <div class="ts-card-inner p-6 h-full flex flex-col items-start justify-start bg-white">
                                <h3 class="text-xl font-bold mb-2 text-black truncate mt-0">No Category Selected</h3>
                                <p class="text-black text-base opacity-80">Please select a category in the Customizer.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php } ?>
            </div>
        </section>

        <!-- Latest Articles (Dynamic) -->
        <section class="mb-16">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-900">Latest Articles</h2>
            <p class="text-center text-gray-600 max-w-2xl mx-auto mb-6">Fresh posts on psychology, discipline, creativity, and practical systems for better work.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $latest_query = new WP_Query([
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                    'post_status' => 'publish',
                ]);
                if ($latest_query->have_posts()) :
                    while ($latest_query->have_posts()) : $latest_query->the_post(); ?>
                        <article class="bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300">
                            <?php if (has_post_thumbnail()) : ?>
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
                                <h2 class="text-xl font-bold mb-2">
                                    <a href="<?php the_permalink(); ?>" class="hover:text-indigo-600"><?php the_title(); ?></a>
                                </h2>
                                <div class="text-gray-600 mb-4">
                                    <?php the_excerpt(); ?>
                                </div>
                                <a href="<?php the_permalink(); ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold"><?php esc_html_e('Read More', 'thrivingstudio'); ?> &rarr;</a>
                            </div>
                        </article>
                    <?php endwhile;
                    wp_reset_postdata();
                else : ?>
                    <p class="col-span-3 text-center text-gray-500">No articles found.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Featured Quote Cards Slider -->
        <section class="mb-16">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-black">Featured Quote Cards</h2>
            </div>
            
            <!-- Desktop Slider (3 cards) -->
            <div class="quote-desktop-track hidden md:flex md:justify-between">
                    <?php
                    $quote_query = new WP_Query([
                        'post_type' => 'quote_card',
                        'posts_per_page' => 3,
                        'post_status' => 'publish',
                        'orderby' => 'date',
                        'order' => 'DESC',
                    ]);
                    
                    if ($quote_query->have_posts()) :
                        while ($quote_query->have_posts()) : $quote_query->the_post();
                            if (has_post_thumbnail()) : ?>
                                <div class="rounded-xl shadow-lg overflow-hidden transform hover:-translate-y-2 transition-all duration-300">
                                    <a href="<?php the_permalink(); ?>" class="block">
                                        <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" 
                                             alt="<?php the_title_attribute(); ?>" 
                                             class="w-full h-auto object-contain">

                                    </a>
                                </div>
                            <?php endif;
                        endwhile;
                        wp_reset_postdata();
                    else : ?>
                        <div class="text-center py-12">
                            <p class="text-gray-500">No quote cards found. Add some quote cards to see them here!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile Slider (Single card with navigation) -->
                <div class="md:hidden">
                    <div class="relative">
                        <div class="overflow-hidden">
                            <div id="mobile-quote-slider" class="flex transition-transform duration-300">
                                <?php
                                $mobile_quote_query = new WP_Query([
                                    'post_type' => 'quote_card',
                                    'posts_per_page' => 6,
                                    'post_status' => 'publish',
                                    'orderby' => 'date',
                                    'order' => 'DESC',
                                ]);
                                
                                if ($mobile_quote_query->have_posts()) :
                                    while ($mobile_quote_query->have_posts()) : $mobile_quote_query->the_post();
                                        if (has_post_thumbnail()) : ?>
                                            <div class="w-full flex-shrink-0 px-2">
                                                <div class="rounded-xl shadow-lg overflow-hidden w-full">
                                                    <a href="<?php the_permalink(); ?>" class="block">
                                                        <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" 
                                                             alt="<?php the_title_attribute(); ?>" 
                                                             class="w-full h-auto object-contain">

                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif;
                                    endwhile;
                                    wp_reset_postdata();
                                endif; ?>
                            </div>
                        </div>
                        
                        <!-- Mobile Navigation Dots -->
                        <?php 
                        $total_slides = $mobile_quote_query->found_posts;
                        if ($total_slides > 0) : ?>
                            <div class="flex justify-center mt-6 space-x-2">
                                <?php for ($i = 0; $i < min($total_slides, 6); $i++) : ?>
                                    <button class="w-3 h-3 rounded-full bg-gray-300 quote-slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>"></button>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
        </section>

        <!-- Subscribe Section -->
        <section id="subscribe" class="mb-8">
            <div class="subscribe-section ts-subscribe-panel bg-[#f8fafc] rounded-xl p-10 text-center border border-gray-200 shadow-sm">
                <h2 class="text-2xl font-bold mb-4 text-gray-900">Stay Inspired!</h2>
                <p class="mb-6 text-gray-700">Subscribe to our newsletter for the latest articles, quotes, and creative tips.</p>
                <form class="flex flex-col md:flex-row justify-center gap-4">
                    <input type="email" placeholder="Your email address" class="px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white text-gray-900" required>
                    <button type="submit" class="px-8 py-3 font-bold rounded-lg shadow transition-colors duration-300 border border-black bg-white text-black hover:bg-gray-100">Subscribe</button>
                </form>
            </div>
        </section>
    </div>
</main>

<script>
    // Mobile Quote Slider Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.getElementById('mobile-quote-slider');
        const dots = document.querySelectorAll('.quote-slider-dot');
        
        if (slider && dots.length > 0) {
            let currentSlide = 0;
            const totalSlides = dots.length;

            function goToSlide(slideIndex) {
                currentSlide = slideIndex;
                slider.style.transform = `translateX(-${slideIndex * 100}%)`;
                
                // Update dots
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === slideIndex);
                    dot.classList.toggle('bg-gray-300', index !== slideIndex);
                    dot.classList.toggle('bg-gray-600', index === slideIndex);
                });
            }

            // Dot navigation
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => goToSlide(index));
            });

            // Auto-advance slider removed - now only manual navigation
        }
    });
</script>



<?php get_footer(); ?> 
