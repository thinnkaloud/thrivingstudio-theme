<?php
/**
 * Template part for displaying comments
 *
 * @param array $args {
 *     Template part arguments.
 *     @type WP_Comment $comment The comment object.
 *     @type array     $args    The comment arguments.
 *     @type int       $depth   The comment depth.
 * }
 */

// Extract variables
$comment = $args['comment'];
$comment_args = $args['args'];
$depth = $args['depth'];
?>

<li <?php comment_class('flex space-x-4'); ?> id="comment-<?php comment_ID(); ?>">
    <div class="flex-shrink-0">
        <?php echo get_avatar($comment, $comment_args['avatar_size'], '', '', ['class' => 'rounded-full']); ?>
    </div>
    <div class="flex-1">
        <div class="flex items-center justify-between">
            <cite class="font-bold text-gray-900 not-italic"><?php comment_author_link(); ?></cite>
            <a href="<?php echo esc_url(get_comment_link($comment->comment_ID)); ?>" class="text-sm text-gray-500 hover:underline">
                <?php comment_date(); ?>
            </a>
        </div>
        <?php if ('0' == $comment->comment_approved) : ?>
            <p class="text-sm text-black">Your comment is awaiting moderation.</p>
        <?php endif; ?>
        <div class="prose prose-sm mt-2">
            <?php comment_text(); ?>
            <div class="not-prose mt-2">
                <?php comment_reply_link(array_merge($comment_args, [
                    'depth'     => $depth, 
                    'max_depth' => $comment_args['max_depth'],
                    'before'    => '<span class="text-sm font-semibold text-indigo-600 hover:underline">',
                    'after'     => '</span>'
                ])); ?>
            </div>
        </div>
    </div>
</li> 