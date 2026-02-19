<?php get_header(); ?>

<main class="flex-1">
    <div class="container mx-auto px-4 py-0 mt-0">
        <div class="mb-4">
            <?php the_archive_title('<h1 class="text-4xl font-extrabold text-gray-900">', '</h1>'); ?>
            <?php if (get_the_archive_description()) : ?>
                <div class="mt-4 text-lg text-gray-600">
                    <?php the_archive_description(); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (have_posts()) : ?>
            <div class="grid gap-y-8 gap-x-1 justify-center" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); max-width: 1600px; margin: 0 auto;">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 overflow-hidden flex flex-col items-center border border-gray-100'); ?> style="width:320px;">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="quote-img-wrapper bg-white border-b border-gray-100" style="width:100%; aspect-ratio:4/5; display:flex;align-items:center;justify-content:center; padding: 1rem;">
                                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>" alt="<?php the_title_attribute(); ?>" style="max-width:100%; max-height:100%; object-fit:contain; border-radius: 0.5rem;" />
                            </div>
                        <?php endif; ?>
                        <div class="p-6 flex-1 flex flex-col justify-between w-full">
                            <h2 class="text-lg font-bold mb-4 text-center leading-tight">
                                <a href="<?php the_permalink(); ?>" class="hover:text-indigo-600 transition-colors duration-200"><?php the_title(); ?></a>
                            </h2>
                            <?php if (has_excerpt()) : ?>
                                <div class="text-sm text-gray-500 mb-4 text-center italic leading-relaxed">
                                    <?php echo get_the_excerpt(); ?>
                                </div>
                            <?php endif; ?>
                            <div class="text-xs text-gray-400 text-center mt-auto pt-4 border-t border-gray-100">
                                <?php echo get_the_date('j M Y'); ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p class="text-gray-600">No quote cards found.</p>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?> 