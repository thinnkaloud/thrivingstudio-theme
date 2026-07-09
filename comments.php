<?php
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area max-w-3xl mx-auto mt-12">

    <?php if (comments_open()) : ?>
        <?php
        $comment_redirect_url = get_permalink();
        $comment_redirect_url = $comment_redirect_url ? $comment_redirect_url . '#comments' : home_url('/#comments');

        if (!is_user_logged_in() && function_exists('thrivingstudio_render_comment_login_prompt')) {
            thrivingstudio_render_comment_login_prompt($comment_redirect_url);
        } else {
            $comment_avatar = '';
            $comment_identity = function_exists('thrivingstudio_get_comment_composer_identity')
                ? thrivingstudio_get_comment_composer_identity($comment_redirect_url)
                : '';

            if (is_user_logged_in()) {
                $comment_user = wp_get_current_user();
                $comment_display_name = $comment_user->display_name ?: $comment_user->user_login;
                $comment_avatar = get_avatar($comment_user->ID, 44, '', $comment_display_name, ['class' => 'ts-comment-composer-avatar']);
            }

            comment_form([
                'title_reply_before'   => '<h2 id="reply-title" class="comment-reply-title">',
                'title_reply_after'    => '</h2>',
                'title_reply'          => esc_html__('Join the conversation', 'thrivingstudio'),
                'title_reply_to'       => esc_html__('Reply to %s', 'thrivingstudio'),
                'cancel_reply_link'    => esc_html__('Cancel Reply', 'thrivingstudio'),
                'comment_field'        => '<div class="ts-comment-composer">' . $comment_avatar . '<div class="ts-comment-composer-body">' . $comment_identity . '<p class="comment-form-comment"><label for="comment" class="ts-comment-label ts-comment-label--sr">' . esc_html__('Comment', 'thrivingstudio') . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="3" maxlength="65525" required="required" class="ts-comment-input ts-comment-textarea" placeholder="' . esc_attr__('What do you think?', 'thrivingstudio') . '"></textarea></p></div></div>',
                'fields'               => [],
                'logged_in_as'         => '',
                'must_log_in'          => function_exists('thrivingstudio_get_comment_must_log_in') ? thrivingstudio_get_comment_must_log_in($comment_redirect_url) : '',
                'comment_notes_before' => '',
                'class_form'           => 'comment-form ts-comment-form',
                'class_submit'         => 'ts-comment-submit',
                'label_submit'         => esc_html__('Post', 'thrivingstudio'),
                'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
                'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
            ]);
        }
        ?>
    <?php endif; ?>

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
</div>
