<?php
/**
 * Account helpers for WordPress login, profile, and comment UI.
 */

function thrivingstudio_get_current_url() {
    if (is_singular()) {
        $permalink = get_permalink();

        if ($permalink) {
            return $permalink;
        }
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $request_uri = '/' . ltrim($request_uri, '/');

    return wp_validate_redirect(home_url($request_uri), home_url('/'));
}

function thrivingstudio_get_account_login_label() {
    return apply_filters(
        'thrivingstudio_account_login_label',
        __('Continue with Google', 'thrivingstudio')
    );
}

function thrivingstudio_get_account_login_url($redirect_url = '') {
    $redirect_url = $redirect_url ?: thrivingstudio_get_current_url();
    $login_url = wp_login_url($redirect_url);

    return apply_filters('thrivingstudio_account_login_url', $login_url, $redirect_url);
}

function thrivingstudio_get_account_logout_url($redirect_url = '') {
    $redirect_url = $redirect_url ?: home_url('/');

    return wp_logout_url($redirect_url);
}

function thrivingstudio_get_account_profile_url($user_id = 0) {
    $user_id = $user_id ?: get_current_user_id();
    $profile_page = get_page_by_path('profile');

    if ($profile_page instanceof WP_Post && 'publish' === get_post_status($profile_page)) {
        $profile_url = get_permalink($profile_page);
    } else {
        $profile_url = get_edit_user_link($user_id);
    }

    if (!$profile_url) {
        $profile_url = admin_url('profile.php');
    }

    return apply_filters('thrivingstudio_account_profile_url', $profile_url, $user_id);
}

function thrivingstudio_render_account_nav($context = 'desktop') {
    $context = sanitize_html_class($context ?: 'desktop');
    $classes = 'ts-account-nav ts-account-nav--' . $context;
    $redirect_url = thrivingstudio_get_current_url();

    if (is_user_logged_in()) :
        $user = wp_get_current_user();
        $display_name = $user->display_name ?: $user->user_login;
        $profile_url = thrivingstudio_get_account_profile_url($user->ID);
        ?>
        <div class="<?php echo esc_attr($classes); ?>" aria-label="<?php esc_attr_e('Account', 'thrivingstudio'); ?>">
            <a class="ts-account-profile" href="<?php echo esc_url($profile_url); ?>">
                <?php echo get_avatar($user->ID, 32, '', $display_name, ['class' => 'ts-account-avatar']); ?>
                <span class="ts-account-name"><?php echo esc_html($display_name); ?></span>
            </a>
            <a class="ts-account-logout" href="<?php echo esc_url(thrivingstudio_get_account_logout_url(home_url('/'))); ?>">
                <?php esc_html_e('Log out', 'thrivingstudio'); ?>
            </a>
        </div>
        <?php
        return;
    endif;
    ?>
    <div class="<?php echo esc_attr($classes); ?>" aria-label="<?php esc_attr_e('Account', 'thrivingstudio'); ?>">
        <a class="ts-account-login" href="<?php echo esc_url(thrivingstudio_get_account_login_url($redirect_url)); ?>">
            <?php echo esc_html(thrivingstudio_get_account_login_label()); ?>
        </a>
    </div>
    <?php
}

function thrivingstudio_render_comment_login_prompt($redirect_url = '') {
    if (is_user_logged_in()) {
        return;
    }

    $redirect_url = $redirect_url ?: thrivingstudio_get_current_url();
    ?>
    <div class="ts-comment-login-prompt">
        <p class="ts-comment-login-copy">
            <strong><?php esc_html_e('Comment with your profile.', 'thrivingstudio'); ?></strong>
            <?php esc_html_e('Use Google login to keep your name and avatar attached to replies.', 'thrivingstudio'); ?>
        </p>
        <a class="ts-account-login ts-account-login--comment" href="<?php echo esc_url(thrivingstudio_get_account_login_url($redirect_url)); ?>">
            <?php echo esc_html(thrivingstudio_get_account_login_label()); ?>
        </a>
    </div>
    <?php
}

function thrivingstudio_get_comment_logged_in_as($redirect_url = '') {
    if (!is_user_logged_in()) {
        return '';
    }

    $user = wp_get_current_user();
    $display_name = $user->display_name ?: $user->user_login;
    $profile_url = thrivingstudio_get_account_profile_url($user->ID);
    $redirect_url = $redirect_url ?: thrivingstudio_get_current_url();

    ob_start();
    ?>
    <div class="ts-comment-user-card">
        <?php echo get_avatar($user->ID, 44, '', $display_name, ['class' => 'ts-comment-user-avatar']); ?>
        <div class="ts-comment-user-summary">
            <span class="ts-comment-user-label"><?php esc_html_e('Commenting as', 'thrivingstudio'); ?></span>
            <a class="ts-comment-user-name" href="<?php echo esc_url($profile_url); ?>"><?php echo esc_html($display_name); ?></a>
            <span class="ts-comment-user-note"><?php esc_html_e('Your profile details will be used for this reply.', 'thrivingstudio'); ?></span>
        </div>
        <a class="ts-comment-user-logout" href="<?php echo esc_url(thrivingstudio_get_account_logout_url($redirect_url)); ?>">
            <?php esc_html_e('Log out', 'thrivingstudio'); ?>
        </a>
    </div>
    <?php

    return ob_get_clean();
}

function thrivingstudio_get_comment_must_log_in($redirect_url = '') {
    $redirect_url = $redirect_url ?: thrivingstudio_get_current_url();

    return sprintf(
        '<p class="must-log-in ts-comment-must-login">%1$s <a href="%2$s">%3$s</a> %4$s</p>',
        esc_html__('Please', 'thrivingstudio'),
        esc_url(thrivingstudio_get_account_login_url($redirect_url)),
        esc_html(thrivingstudio_get_account_login_label()),
        esc_html__('to comment.', 'thrivingstudio')
    );
}
