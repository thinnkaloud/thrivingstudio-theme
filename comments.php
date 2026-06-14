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
    $required_attr = ($req ? " required='required' aria-required='true'" : '');
    
    $fields = [
        'author' => '<p class="comment-form-author"><label for="author" class="ts-comment-label">' . esc_html__('Name', 'thrivingstudio') . ($req ? ' <span class="required">*</span>' : '') . '</label>' .
                    '<input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" autocomplete="name" size="30"' . $required_attr . ' class="ts-comment-input" /></p>',
        'email'  => '<p class="comment-form-email"><label for="email" class="ts-comment-label">' . esc_html__('Email', 'thrivingstudio') . ($req ? ' <span class="required">*</span>' : '') . '</label>' .
                    '<input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email']) . '" autocomplete="email" size="30"' . $required_attr . ' class="ts-comment-input" /></p>',
        'url'    => '<p class="comment-form-url"><label for="url" class="ts-comment-label">' . esc_html__('Website', 'thrivingstudio') . '</label>' .
                    '<input id="url" name="url" type="url" value="' . esc_attr($commenter['comment_author_url']) . '" autocomplete="url" size="30" class="ts-comment-input" /></p>',
    ];

    comment_form([
        'title_reply_before'   => '<h2 id="reply-title" class="comment-reply-title">',
        'title_reply_after'    => '</h2>',
        'title_reply'          => esc_html__('Join the Conversation', 'thrivingstudio'),
        'title_reply_to'       => esc_html__('Reply to %s', 'thrivingstudio'),
        'cancel_reply_link'    => esc_html__('Cancel Reply', 'thrivingstudio'),
        'comment_field'        => '<p class="comment-form-comment"><label for="comment" class="ts-comment-label">' . esc_html__('Comment', 'thrivingstudio') . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="6" maxlength="65525" required="required" class="ts-comment-input ts-comment-textarea" placeholder="' . esc_attr__('Share a thoughtful response...', 'thrivingstudio') . '"></textarea></p>',
        'fields'               => $fields,
        'logged_in_as'         => '<p class="logged-in-as">' .
            sprintf(
                __('Logged in as <a href="%1$s" class="hover:underline font-semibold">%2$s</a>. <a href="%3$s" class="hover:underline font-semibold">Log out?</a>', 'thrivingstudio'),
                get_edit_user_link(),
                wp_get_current_user()->display_name,
                wp_logout_url(apply_filters('the_permalink', get_permalink()))
            ) . 
            '</p>',
        'comment_notes_before' => '<p class="comment-notes">' .
                                  esc_html__('Your email address stays private.', 'thrivingstudio') .
                                  ($req ? ' ' . esc_html__('Required fields are marked', 'thrivingstudio') . ' <span class="required">*</span>.' : '') .
                                  '</p>',
        'class_form'           => 'comment-form ts-comment-form',
        'class_submit'         => 'ts-comment-submit',
        'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
        'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
    ]);
    ?>
</div>
