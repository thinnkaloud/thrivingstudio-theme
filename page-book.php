<?php
/**
 * Template Name: Book Page
 * Description: A custom template for showcasing an upcoming book with pre-order functionality
 */

get_header(); ?>

<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <!-- Book Hero Section -->
        <section class="mb-12 rounded-xl overflow-hidden" style="background:#fff; color:#000; border: 1px solid #bbb; border-color: #bbb; box-shadow: 0 4px 24px 0 rgba(0,0,0,0.04); padding: 3.5rem 0; border-radius: 1rem;">
            <div class="text-center px-4 sm:px-8">
                <div class="modern-coming-soon bg-black text-white" style="margin-top:0; margin-bottom:1.5rem; box-shadow:none; animation:none;">
                    Stay tuned
                </div>
                <h1 class="font-bold text-gray-900 mb-4" style="font-size:2.25rem; white-space:nowrap;">
                    Something Amazing Is On The Way
                </h1>
                <p class="text-xl text-gray-600 mb-6">
                    We're working on a life-changing project. Stay tuned for the big reveal.
                </p>
            </div>
        </section>
    </div>
</main>

<?php get_footer(); ?> 