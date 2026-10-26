<?php
/** The "Start here" page. Shows a guided introduction when the page has no authored content. */
get_header();

$first_reads = [];
foreach (['what-is-emotional-strength', 'how-to-pause-before-reacting'] as $first_read_slug) {
    $first_read = get_page_by_path($first_read_slug, OBJECT, 'post');
    if ($first_read && $first_read->post_status === 'publish') {
        $first_reads[] = $first_read;
    }
}
?>
<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <?php while (have_posts()) : the_post(); ?>
            <h1 class="text-4xl font-bold mb-6 text-gray-900"><?php the_title(); ?></h1>
            <?php if (trim(get_the_content()) !== '') : ?>
                <div class="prose"><?php the_content(); ?></div>
            <?php else : ?>
                <p class="text-xl text-gray-600 max-w-2xl"><?php esc_html_e('Thriving Studio is about clarity over noise: fewer, better ideas you can actually use. Everything here sits in one of three pillars. Pick the one that matters most to you right now.', 'thrivingstudio'); ?></p>
                <div class="ts-foundations-grid">
                    <?php foreach (thrivingstudio_get_pillars() as $pillar) : ?>
                        <section class="ts-foundation-card">
                            <h2 class="text-2xl font-bold mb-4"><?php echo esc_html($pillar['name']); ?></h2>
                            <p class="text-gray-600 mb-6"><?php echo esc_html($pillar['description']); ?></p>
                            <a href="<?php echo esc_url($pillar['url']); ?>">
                                <?php
                                /* translators: %s: pillar name */
                                printf(esc_html__('Explore %s', 'thrivingstudio'), esc_html($pillar['name']));
                                ?>
                            </a>
                        </section>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($first_reads)) : ?>
                    <section class="mt-12">
                        <h2 class="text-2xl font-bold mb-4 text-gray-900"><?php esc_html_e('Good first reads', 'thrivingstudio'); ?></h2>
                        <ul class="space-y-2">
                            <?php foreach ($first_reads as $first_read) : ?>
                                <li><a class="underline" href="<?php echo esc_url(get_permalink($first_read)); ?>"><?php echo esc_html(get_the_title($first_read)); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>
