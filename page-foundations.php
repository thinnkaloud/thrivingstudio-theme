<?php
/** A useful starting point when the Foundations page has no authored content. */
get_header();
?>
<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <?php while (have_posts()) : the_post(); ?>
            <h1 class="text-4xl font-bold mb-6 text-gray-900"><?php the_title(); ?></h1>
            <?php if (trim(get_the_content()) !== '') : ?>
                <div class="prose"><?php the_content(); ?></div>
            <?php else : ?>
                <p class="text-xl text-gray-600 max-w-2xl">Start with the ideas that support everyday growth: understanding your emotions, finding clarity, and building intentional habits.</p>
                <div class="ts-foundations-grid">
                    <section class="ts-foundation-card">
                        <h2 class="text-2xl font-bold mb-4">Understand your emotions</h2>
                        <p class="text-gray-600 mb-6">Explore what emotional strength means and how to create space before you react.</p>
                        <a href="<?php echo esc_url(home_url('/what-is-emotional-strength/')); ?>">Start with emotional strength</a>
                    </section>
                    <section class="ts-foundation-card">
                        <h2 class="text-2xl font-bold mb-4">Create a thoughtful pause</h2>
                        <p class="text-gray-600 mb-6">Practice noticing a feeling, slowing down, and choosing your next step.</p>
                        <a href="<?php echo esc_url(home_url('/how-to-pause-before-reacting/')); ?>">Learn how to pause</a>
                    </section>
                    <section class="ts-foundation-card">
                        <h2 class="text-2xl font-bold mb-4">Keep exploring</h2>
                        <p class="text-gray-600 mb-6">Find more perspectives on psychology, discipline, creativity, and meaningful progress.</p>
                        <a href="<?php echo esc_url(home_url('/blog/')); ?>">Browse the journal</a>
                    </section>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>
