<?php get_template_part('template-parts/header'); ?>

<main class="container mx-auto px-4 py-24 flex-1 text-center mt-20">
  <h1 class="text-6xl font-extrabold text-gray-900 dark:text-white mb-4">404</h1>
  <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">Sorry, the page you're looking for can't be found.</p>
  <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700 transition-colors">Go Home</a>
</main>

<?php get_footer(); ?> 