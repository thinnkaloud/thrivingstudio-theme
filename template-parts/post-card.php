<?php
/**
 * Article card used by every post listing.
 *
 * Args:
 * - heading_level: 'h2' (default) or 'h3' when the grid sits under a section heading.
 */
$heading_tag = (isset($args['heading_level']) && $args['heading_level'] === 'h3') ? 'h3' : 'h2';
$categories = get_the_category();
$primary_category = !empty($categories) ? $categories[0] : null;
$manual_excerpt = thrivingstudio_get_manual_excerpt();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('blog-card ts-blog-card bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300'); ?>>
    <?php if (has_post_thumbnail()) : ?>
        <?php // The title link below carries the accessible name, so the image link is skipped by keyboards and screen readers. ?>
        <a href="<?php the_permalink(); ?>" class="ts-blog-card-image-link" tabindex="-1" aria-hidden="true">
            <?php
            echo wp_get_attachment_image(
                get_post_thumbnail_id(),
                'medium_large',
                false,
                [
                    'class' => 'ts-blog-card-image w-full',
                    'alt' => '',
                    'loading' => 'lazy',
                    'decoding' => 'async',
                    'sizes' => '(min-width: 1024px) 380px, (min-width: 768px) 50vw, 100vw',
                ]
            );
            ?>
        </a>
    <?php else : ?>
        <div class="ts-blog-card-image-placeholder w-full h-48 bg-gray-100" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="px-4 pt-4 ts-blog-card-meta">
        <?php if ($primary_category) : ?>
            <span class="text-gray-600 text-xs font-medium ts-blog-card-category"><?php echo esc_html($primary_category->name); ?></span>
        <?php endif; ?>
    </div>

    <div class="px-4 pt-2 pb-4 ts-blog-card-body">
        <<?php echo $heading_tag; ?> class="text-2xl font-bold mb-2 ts-blog-card-title">
            <a href="<?php the_permalink(); ?>" class="ts-blog-card-title-link hover:text-indigo-600"><?php the_title(); ?></a>
        </<?php echo $heading_tag; ?>>
        <?php if ($manual_excerpt !== '') : ?>
            <div class="text-gray-600 mb-4 ts-blog-card-excerpt">
                <p><?php echo esc_html($manual_excerpt); ?></p>
            </div>
        <?php endif; ?>
        <a href="<?php the_permalink(); ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold ts-blog-card-link" tabindex="-1" aria-hidden="true"><?php esc_html_e('Read More', 'thrivingstudio'); ?> &rarr;</a>
    </div>
</article>
