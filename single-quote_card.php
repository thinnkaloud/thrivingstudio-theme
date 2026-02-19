<?php get_header(); ?>

<main class="flex-1">
    <div class="container mx-auto px-4 py-1 mt-0">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('max-w-md mx-auto text-center'); ?>>
                <h1 class="text-2xl font-bold mb-4 mx-auto text-black" style="max-width:360px;"><?php the_title(); ?></h1>
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="mx-auto mb-6 bg-white border border-gray-200" style="max-width:360px; aspect-ratio:4/5; display:flex; align-items:center; justify-content:center; border-radius:0.75rem;">
                        <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" alt="<?php the_title_attribute(); ?>" style="max-width:100%; max-height:100%; object-fit:contain; border-radius:0.75rem;" />
                    </div>
                <?php endif; ?>
                <?php $author = get_post_meta(get_the_ID(), '_quote_card_author', true); ?>
                <?php if ($author) : ?>
                    <div class="text-base text-gray-500 mb-2 font-semibold italic">— <?php echo esc_html($author); ?></div>
                <?php endif; ?>
                <?php $caption = get_post_meta(get_the_ID(), '_quote_card_caption', true); ?>
                <?php if ($caption) : ?>
                    <div class="mt-4 text-gray-700 text-base text-left mx-auto" style="max-width: 36ch; line-height: 1.7;">
                        <?php echo nl2br(esc_html($caption)); ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?> 