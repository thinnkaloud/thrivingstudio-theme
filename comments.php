<?php
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area max-w-3xl mx-auto mt-12">

    <?php if (have_comments()) : ?>
        <h2 class="comments-title text-2xl font-bold text-gray-900 dark:text-white mb-6">
            <?php
            $comment_count = get_comments_number();
            if ('1' === $comment_count) {
                echo esc_html__('One comment', 'thrivingstudio');
            } else {
                printf(
                    esc_html__('%s comments', 'thrivingstudio'),
                    esc_html($comment_count)
                );
            }
            ?>
        </h2>

        <ol class="comment-list space-y-6">
            <?php
            wp_list_comments([
                'style'      => 'ol',
                'short_ping' => true,
                'avatar_size' => 56,
                'callback' => 'thrivingstudio_comment_callback' // We'll define this in functions.php
            ]);
            ?>
        </ol>

        <?php the_comments_navigation(); ?>

    <?php endif; ?>

    <?php
    if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) :
    ?>
        <p class="no-comments text-gray-600 dark:text-gray-400 mt-6"><?php esc_html_e('Comments are closed.', 'thrivingstudio'); ?></p>
    <?php endif; ?>

    <?php
    $commenter = wp_get_current_commenter();
    $req = get_option('require_name_email');
    $aria_req = ($req ? " aria-required='true'" : '');
    
    $fields = [
        'author' => '<p class="comment-form-author"><label for="author" class="text-gray-700 dark:text-gray-300">Name' . ($req ? ' <span class="required">*</span>' : '') . '</label> ' .
                    '<input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" size="30"' . $aria_req . ' class="mt-1 block w-full rounded-md bg-white border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" /></p>',
        'email'  => '<p class="comment-form-email"><label for="email" class="text-gray-700 dark:text-gray-300">Email' . ($req ? ' <span class="required">*</span>' : '') . '</label> ' .
                    '<input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email']) . '" size="30"' . $aria_req . ' class="mt-1 block w-full rounded-md bg-white border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" /></p>',
        'url'    => '<p class="comment-form-url"><label for="url" class="text-gray-700 dark:text-gray-300">Website</label>' .
                    '<input id="url" name="url" type="url" value="' . esc_attr($commenter['comment_author_url']) . '" size="30" class="mt-1 block w-full rounded-md bg-white border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" /></p>',
    ];

    comment_form([
        'title_reply'          => '<h3 class="text-xl font-bold text-gray-900 dark:text-white">' . esc_html__('Leave a Reply', 'thrivingstudio') . '</h3>',
        'title_reply_to'       => '<h3 class="text-xl font-bold text-gray-900 dark:text-white">' . esc_html__('Leave a Reply to %s', 'thrivingstudio') . '</h3>',
        'cancel_reply_link'    => esc_html__('Cancel Reply', 'thrivingstudio'),
        'comment_field'        => '<p class="comment-form-comment"><label for="comment" class="text-gray-700 dark:text-gray-300">' . esc_html__('Comment', 'thrivingstudio') . '</label><textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required="required" class="mt-1 block w-full rounded-md bg-white border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"></textarea></p>',
        'fields'               => $fields,
        'logged_in_as'         => '<p class="logged-in-as text-gray-600 dark:text-gray-300">' . 
            sprintf(
                __('Logged in as <a href="%1$s" class="hover:underline font-semibold">%2$s</a>. <a href="%3$s" class="hover:underline font-semibold">Log out?</a>', 'thrivingstudio'),
                get_edit_user_link(),
                wp_get_current_user()->display_name,
                wp_logout_url(apply_filters('the_permalink', get_permalink()))
            ) . 
            '</p>',
        'comment_notes_before' => '<p class="comment-notes text-sm text-gray-600 dark:text-gray-300">' .
                                  esc_html__('Your email address will not be published.', 'thrivingstudio') .
                                  ($req ? ' ' . esc_html__('Required fields are marked', 'thrivingstudio') . ' <span class="required">*</span>' : '') .
                                  '</p>',
        'class_submit'         => 'mt-4 px-6 py-3 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700 transition-colors',
        'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
        'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
    ]);
    ?>
</div> 