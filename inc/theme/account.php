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
        __('Sign in', 'thrivingstudio')
    );
}

function thrivingstudio_get_account_login_url($redirect_url = '') {
    $redirect_url = $redirect_url ?: thrivingstudio_get_current_url();
    $login_url = thrivingstudio_get_account_sign_in_url($redirect_url);

    return apply_filters('thrivingstudio_account_login_url', $login_url, $redirect_url);
}

function thrivingstudio_get_account_sign_in_url($redirect_url = '') {
    $redirect_url = $redirect_url ?: thrivingstudio_get_current_url();
    $redirect_url = thrivingstudio_account_sanitize_redirect($redirect_url);

    return add_query_arg('redirect_to', $redirect_url, home_url('/sign-in/'));
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
        $profile_url = home_url('/profile/');
    }

    if (!$profile_url) {
        $profile_url = home_url('/');
    }

    return apply_filters('thrivingstudio_account_profile_url', $profile_url, $user_id);
}

function thrivingstudio_account_request_path_matches($slug) {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
    $home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');
    $slug = trim($slug, '/');

    if ($home_path !== '' && strpos($request_path, $home_path . '/') === 0) {
        $request_path = substr($request_path, strlen($home_path) + 1);
    }

    return trim($request_path, '/') === $slug;
}

function thrivingstudio_account_url_path_matches($url, $slug) {
    $path = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');
    $home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');
    $slug = trim($slug, '/');

    if ($home_path !== '' && strpos($path, $home_path . '/') === 0) {
        $path = substr($path, strlen($home_path) + 1);
    }

    return trim($path, '/') === $slug;
}

function thrivingstudio_account_sanitize_redirect($redirect_url = '') {
    if ($redirect_url === '' && isset($_REQUEST['redirect_to'])) {
        $raw_redirect = wp_unslash($_REQUEST['redirect_to']);
        $redirect_url = is_scalar($raw_redirect) ? rawurldecode((string) $raw_redirect) : '';
    }

    $redirect_url = wp_validate_redirect($redirect_url ?: home_url('/'), home_url('/'));

    if (thrivingstudio_account_url_path_matches($redirect_url, 'sign-in')) {
        return thrivingstudio_get_account_profile_url();
    }

    return $redirect_url;
}

function thrivingstudio_account_get_virtual_profile_post() {
    return new WP_Post((object) [
        'ID'                    => 0,
        'post_author'           => 0,
        'post_date'             => current_time('mysql'),
        'post_date_gmt'         => current_time('mysql', true),
        'post_content'          => '',
        'post_title'            => __('Profile', 'thrivingstudio'),
        'post_excerpt'          => '',
        'post_status'           => 'publish',
        'comment_status'        => 'closed',
        'ping_status'           => 'closed',
        'post_password'         => '',
        'post_name'             => 'profile',
        'to_ping'               => '',
        'pinged'                => '',
        'post_modified'         => current_time('mysql'),
        'post_modified_gmt'     => current_time('mysql', true),
        'post_content_filtered' => '',
        'post_parent'           => 0,
        'guid'                  => home_url('/profile/'),
        'menu_order'            => 0,
        'post_type'             => 'page',
        'post_mime_type'        => '',
        'comment_count'         => 0,
        'filter'                => 'raw',
    ]);
}

function thrivingstudio_account_get_virtual_sign_in_post() {
    return new WP_Post((object) [
        'ID'                    => 0,
        'post_author'           => 0,
        'post_date'             => current_time('mysql'),
        'post_date_gmt'         => current_time('mysql', true),
        'post_content'          => '',
        'post_title'            => __('Sign in', 'thrivingstudio'),
        'post_excerpt'          => '',
        'post_status'           => 'publish',
        'comment_status'        => 'closed',
        'ping_status'           => 'closed',
        'post_password'         => '',
        'post_name'             => 'sign-in',
        'to_ping'               => '',
        'pinged'                => '',
        'post_modified'         => current_time('mysql'),
        'post_modified_gmt'     => current_time('mysql', true),
        'post_content_filtered' => '',
        'post_parent'           => 0,
        'guid'                  => home_url('/sign-in/'),
        'menu_order'            => 0,
        'post_type'             => 'page',
        'post_mime_type'        => '',
        'comment_count'         => 0,
        'filter'                => 'raw',
    ]);
}

function thrivingstudio_account_get_virtual_route_post() {
    if (thrivingstudio_account_request_path_matches('profile')) {
        return thrivingstudio_account_get_virtual_profile_post();
    }

    if (thrivingstudio_account_request_path_matches('sign-in')) {
        return thrivingstudio_account_get_virtual_sign_in_post();
    }

    return null;
}

function thrivingstudio_account_prime_profile_query() {
    $virtual_post = thrivingstudio_account_get_virtual_route_post();

    if (!($virtual_post instanceof WP_Post)) {
        return;
    }

    global $post, $wp_query;

    if (!($wp_query instanceof WP_Query)) {
        return;
    }

    $post = $virtual_post;

    $wp_query->post = $post;
    $wp_query->posts = [$post];
    $wp_query->post_count = 1;
    $wp_query->found_posts = 1;
    $wp_query->max_num_pages = 1;
    $wp_query->queried_object = $post;
    $wp_query->queried_object_id = 0;
    $wp_query->is_page = true;
    $wp_query->is_singular = true;
    $wp_query->is_single = false;
    $wp_query->is_home = false;
    $wp_query->is_archive = false;
    $wp_query->is_search = false;
    $wp_query->is_404 = false;
}
add_action('wp', 'thrivingstudio_account_prime_profile_query', 0);

function thrivingstudio_account_prevent_profile_404($preempt, $wp_query) {
    if (!thrivingstudio_account_get_virtual_route_post()) {
        return $preempt;
    }

    if (is_object($wp_query)) {
        thrivingstudio_account_prime_profile_query();
        $wp_query->is_404 = false;
    }

    status_header(200);

    return true;
}
add_filter('pre_handle_404', 'thrivingstudio_account_prevent_profile_404', 10, 2);

function thrivingstudio_account_load_profile_template($template) {
    if (thrivingstudio_account_request_path_matches('profile')) {
        $profile_template = trailingslashit(get_template_directory()) . 'page-profile.php';

        return file_exists($profile_template) ? $profile_template : $template;
    }

    if (thrivingstudio_account_request_path_matches('sign-in')) {
        $sign_in_template = trailingslashit(get_template_directory()) . 'page-sign-in.php';

        return file_exists($sign_in_template) ? $sign_in_template : $template;
    }

    return $template;
}
add_filter('template_include', 'thrivingstudio_account_load_profile_template');

function thrivingstudio_account_profile_body_class($classes) {
    if (thrivingstudio_account_request_path_matches('profile')) {
        $classes[] = 'ts-profile-route';
    }

    if (thrivingstudio_account_request_path_matches('sign-in')) {
        $classes[] = 'ts-auth-route';
    }

    return $classes;
}
add_filter('body_class', 'thrivingstudio_account_profile_body_class');

function thrivingstudio_account_set_auth_error($message) {
    $GLOBALS['thrivingstudio_account_auth_error'] = (string) $message;
}

function thrivingstudio_account_get_auth_error() {
    return isset($GLOBALS['thrivingstudio_account_auth_error'])
        ? (string) $GLOBALS['thrivingstudio_account_auth_error']
        : '';
}

function thrivingstudio_account_get_unique_login_from_email($email) {
    $email_parts = explode('@', (string) $email);
    $base_login = sanitize_user($email_parts[0] ?? '', true);

    if ($base_login === '') {
        $base_login = 'reader';
    }

    $user_login = $base_login;
    $suffix = 2;

    while (username_exists($user_login)) {
        $user_login = $base_login . $suffix;
        $suffix++;
    }

    return $user_login;
}

function thrivingstudio_account_email_signup_is_enabled() {
    return (bool) apply_filters('thrivingstudio_email_signup_enabled', true);
}

function thrivingstudio_account_handle_email_login($redirect_url) {
    $nonce = isset($_POST['thrivingstudio_auth_nonce'])
        ? sanitize_text_field((string) wp_unslash($_POST['thrivingstudio_auth_nonce']))
        : '';

    if (!wp_verify_nonce($nonce, 'thrivingstudio_email_login')) {
        thrivingstudio_account_set_auth_error(__('We could not verify this sign-in. Please try again.', 'thrivingstudio'));
        return;
    }

    $email = isset($_POST['ts_auth_email'])
        ? sanitize_email((string) wp_unslash($_POST['ts_auth_email']))
        : '';
    $password = isset($_POST['ts_auth_password'])
        ? (string) wp_unslash($_POST['ts_auth_password'])
        : '';
    $remember = !empty($_POST['ts_auth_remember']);

    if ($email === '' || $password === '') {
        thrivingstudio_account_set_auth_error(__('Please enter your email and password.', 'thrivingstudio'));
        return;
    }

    $user = wp_signon([
        'user_login'    => $email,
        'user_password' => $password,
        'remember'      => $remember,
    ], is_ssl());

    if (is_wp_error($user)) {
        thrivingstudio_account_set_auth_error(__('We could not sign you in with those details.', 'thrivingstudio'));
        return;
    }

    wp_safe_redirect($redirect_url);
    exit;
}

function thrivingstudio_account_handle_email_signup($redirect_url) {
    if (!thrivingstudio_account_email_signup_is_enabled()) {
        thrivingstudio_account_set_auth_error(__('Email sign-up is not available right now.', 'thrivingstudio'));
        return;
    }

    $nonce = isset($_POST['thrivingstudio_auth_nonce'])
        ? sanitize_text_field((string) wp_unslash($_POST['thrivingstudio_auth_nonce']))
        : '';

    if (!wp_verify_nonce($nonce, 'thrivingstudio_email_signup')) {
        thrivingstudio_account_set_auth_error(__('We could not verify this sign-up. Please try again.', 'thrivingstudio'));
        return;
    }

    $honeypot = isset($_POST['ts_auth_company'])
        ? trim((string) wp_unslash($_POST['ts_auth_company']))
        : '';

    if ($honeypot !== '') {
        thrivingstudio_account_set_auth_error(__('We could not create this account. Please try again.', 'thrivingstudio'));
        return;
    }

    $display_name = isset($_POST['ts_auth_name'])
        ? sanitize_text_field((string) wp_unslash($_POST['ts_auth_name']))
        : '';
    $email = isset($_POST['ts_auth_email'])
        ? sanitize_email((string) wp_unslash($_POST['ts_auth_email']))
        : '';
    $password = isset($_POST['ts_auth_password'])
        ? (string) wp_unslash($_POST['ts_auth_password'])
        : '';
    $password_confirm = isset($_POST['ts_auth_password_confirm'])
        ? (string) wp_unslash($_POST['ts_auth_password_confirm'])
        : '';

    if ($display_name === '') {
        thrivingstudio_account_set_auth_error(__('Please add your name.', 'thrivingstudio'));
        return;
    }

    if ($email === '' || !is_email($email)) {
        thrivingstudio_account_set_auth_error(__('Please use a valid email address.', 'thrivingstudio'));
        return;
    }

    if (email_exists($email)) {
        thrivingstudio_account_set_auth_error(__('That email already has an account. Please sign in instead.', 'thrivingstudio'));
        return;
    }

    if (strlen($password) < 8) {
        thrivingstudio_account_set_auth_error(__('Please use a password with at least 8 characters.', 'thrivingstudio'));
        return;
    }

    if (!hash_equals($password, $password_confirm)) {
        thrivingstudio_account_set_auth_error(__('The passwords did not match.', 'thrivingstudio'));
        return;
    }

    $user_id = wp_insert_user([
        'user_login'   => thrivingstudio_account_get_unique_login_from_email($email),
        'user_email'   => $email,
        'user_pass'    => $password,
        'display_name' => $display_name,
        'nickname'     => $display_name,
        'role'         => 'subscriber',
    ]);

    if (is_wp_error($user_id)) {
        thrivingstudio_account_set_auth_error($user_id->get_error_message());
        return;
    }

    update_user_meta((int) $user_id, 'thrivingstudio_email_auth_managed', '1');
    update_user_meta((int) $user_id, 'thrivingstudio_profile_customized', '1');

    wp_set_current_user((int) $user_id);
    wp_set_auth_cookie((int) $user_id, true, is_ssl());

    $user = get_userdata((int) $user_id);

    if ($user) {
        do_action('wp_login', $user->user_login, $user);
    }

    wp_safe_redirect($redirect_url);
    exit;
}

function thrivingstudio_account_handle_sign_in_request() {
    if (!thrivingstudio_account_request_path_matches('sign-in')) {
        return;
    }

    $redirect_url = thrivingstudio_account_sanitize_redirect();

    if (is_user_logged_in()) {
        wp_safe_redirect($redirect_url);
        exit;
    }

    if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper((string) $_SERVER['REQUEST_METHOD']) !== 'POST') {
        return;
    }

    $action = isset($_POST['thrivingstudio_auth_action'])
        ? sanitize_key((string) wp_unslash($_POST['thrivingstudio_auth_action']))
        : '';

    if ($action === 'email_login') {
        thrivingstudio_account_handle_email_login($redirect_url);
        return;
    }

    if ($action === 'email_signup') {
        thrivingstudio_account_handle_email_signup($redirect_url);
        return;
    }

    thrivingstudio_account_set_auth_error(__('Please choose a sign-in option.', 'thrivingstudio'));
}
add_action('template_redirect', 'thrivingstudio_account_handle_sign_in_request', 0);

function thrivingstudio_account_redirect_non_staff_from_admin() {
    if (!is_user_logged_in() || current_user_can('edit_posts')) {
        return;
    }

    if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
        return;
    }

    wp_safe_redirect(thrivingstudio_get_account_profile_url());
    exit;
}
add_action('admin_init', 'thrivingstudio_account_redirect_non_staff_from_admin', 2);

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
            <a
                class="ts-account-profile"
                href="<?php echo esc_url($profile_url); ?>"
                aria-label="<?php echo esc_attr(sprintf(__('Open profile for %s', 'thrivingstudio'), $display_name)); ?>"
                title="<?php echo esc_attr($display_name); ?>"
            >
                <?php echo get_avatar($user->ID, 40, '', $display_name, ['class' => 'ts-account-avatar']); ?>
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

function thrivingstudio_render_admin_bar_avatar_only($wp_admin_bar) {
    if (is_admin() || !is_user_logged_in() || !is_object($wp_admin_bar) || !method_exists($wp_admin_bar, 'get_node')) {
        return;
    }

    $account_node = $wp_admin_bar->get_node('my-account');

    if (!$account_node || !method_exists($wp_admin_bar, 'add_node')) {
        return;
    }

    $user = wp_get_current_user();
    $display_name = $user->display_name ?: $user->user_login;
    $meta = isset($account_node->meta) && is_array($account_node->meta) ? $account_node->meta : [];
    $meta['class'] = trim(($meta['class'] ?? '') . ' ts-admin-bar-avatar-only');

    $wp_admin_bar->add_node([
        'id'     => 'my-account',
        'parent' => $account_node->parent ?? false,
        'href'   => $account_node->href ?? thrivingstudio_get_account_profile_url($user->ID),
        'group'  => $account_node->group ?? false,
        'title'  => get_avatar($user->ID, 26, '', $display_name) .
            '<span class="screen-reader-text">' .
            esc_html(sprintf(__('Account: %s', 'thrivingstudio'), $display_name)) .
            '</span>',
        'meta'   => $meta,
    ]);
}
add_action('admin_bar_menu', 'thrivingstudio_render_admin_bar_avatar_only', 999);

function thrivingstudio_require_google_account_for_comments($requires_login) {
    if (is_admin()) {
        return $requires_login;
    }

    return true;
}
add_filter('option_comment_registration', 'thrivingstudio_require_google_account_for_comments');

function thrivingstudio_render_comment_login_prompt($redirect_url = '') {
    if (is_user_logged_in()) {
        return;
    }

    $redirect_url = $redirect_url ?: thrivingstudio_get_current_url();
    ?>
    <section class="ts-comment-login-prompt" aria-labelledby="ts-comment-login-title">
        <span class="ts-comment-login-avatar" aria-hidden="true">G</span>
        <div class="ts-comment-login-copy">
            <span class="ts-comment-login-eyebrow"><?php esc_html_e('Join the conversation', 'thrivingstudio'); ?></span>
            <h2 id="ts-comment-login-title" class="ts-comment-login-title"><?php esc_html_e('Sign in to comment', 'thrivingstudio'); ?></h2>
            <p><?php esc_html_e('Use Google or email to keep your name and replies connected.', 'thrivingstudio'); ?></p>
        </div>
        <a class="ts-account-login ts-account-login--comment" href="<?php echo esc_url(thrivingstudio_get_account_login_url($redirect_url)); ?>">
            <?php esc_html_e('Sign in', 'thrivingstudio'); ?>
        </a>
    </section>
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

function thrivingstudio_get_comment_composer_identity($redirect_url = '') {
    if (!is_user_logged_in()) {
        return '';
    }

    $user = wp_get_current_user();
    $display_name = $user->display_name ?: $user->user_login;
    $profile_url = thrivingstudio_get_account_profile_url($user->ID);
    $redirect_url = $redirect_url ?: thrivingstudio_get_current_url();

    ob_start();
    ?>
    <div class="ts-comment-composer-meta">
        <span class="ts-comment-composer-kicker"><?php esc_html_e('Commenting as', 'thrivingstudio'); ?></span>
        <a class="ts-comment-composer-name" href="<?php echo esc_url($profile_url); ?>"><?php echo esc_html($display_name); ?></a>
        <a class="ts-comment-composer-logout" href="<?php echo esc_url(thrivingstudio_get_account_logout_url($redirect_url)); ?>">
            <?php esc_html_e('Log out', 'thrivingstudio'); ?>
        </a>
    </div>
    <?php

    return ob_get_clean();
}

function thrivingstudio_get_comment_must_log_in($redirect_url = '') {
    $redirect_url = $redirect_url ?: thrivingstudio_get_current_url();

    return sprintf(
        '<p class="must-log-in ts-comment-must-login"><a href="%1$s">%2$s</a></p>',
        esc_url(thrivingstudio_get_account_login_url($redirect_url)),
        esc_html__('Sign in to comment.', 'thrivingstudio')
    );
}
