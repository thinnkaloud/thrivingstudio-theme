<?php
/**
 * Google sign-in for front-end account access.
 */

function thrivingstudio_google_auth_get_constant_or_env($key) {
    if (defined($key)) {
        return trim((string) constant($key));
    }

    $value = getenv($key);

    return $value ? trim((string) $value) : '';
}

function thrivingstudio_google_auth_get_client_id() {
    return apply_filters(
        'thrivingstudio_google_client_id',
        thrivingstudio_google_auth_get_constant_or_env('THRIVINGSTUDIO_GOOGLE_CLIENT_ID')
    );
}

function thrivingstudio_google_auth_get_client_secret() {
    return apply_filters(
        'thrivingstudio_google_client_secret',
        thrivingstudio_google_auth_get_constant_or_env('THRIVINGSTUDIO_GOOGLE_CLIENT_SECRET')
    );
}

function thrivingstudio_google_auth_is_configured() {
    return thrivingstudio_google_auth_get_client_id() !== '' && thrivingstudio_google_auth_get_client_secret() !== '';
}

function thrivingstudio_google_auth_get_privileged_emails() {
    $raw_emails = thrivingstudio_google_auth_get_constant_or_env('THRIVINGSTUDIO_GOOGLE_PRIVILEGED_EMAILS');
    $emails = array_filter(array_map('trim', explode(',', strtolower($raw_emails))));

    return apply_filters('thrivingstudio_google_privileged_emails', $emails);
}

function thrivingstudio_google_auth_email_can_keep_privileges($email) {
    $email = strtolower((string) $email);

    return $email !== '' && in_array($email, thrivingstudio_google_auth_get_privileged_emails(), true);
}

function thrivingstudio_google_auth_get_redirect_uri() {
    return home_url('/ts-google-callback/');
}

function thrivingstudio_get_google_login_url($redirect_url = '') {
    $redirect_url = $redirect_url ?: home_url('/');
    $redirect_url = wp_validate_redirect($redirect_url, home_url('/'));

    return home_url('/ts-google-login/') . '?redirect_to=' . rawurlencode($redirect_url);
}

function thrivingstudio_google_auth_request_path_matches($slug) {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
    $home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');
    $slug = trim($slug, '/');

    if ($home_path !== '' && strpos($request_path, $home_path . '/') === 0) {
        $request_path = substr($request_path, strlen($home_path) + 1);
    }

    return trim($request_path, '/') === $slug;
}

function thrivingstudio_google_auth_sanitize_redirect($redirect_url = '') {
    if ($redirect_url === '' && isset($_GET['redirect_to'])) {
        $redirect_url = rawurldecode((string) wp_unslash($_GET['redirect_to']));
    }

    return wp_validate_redirect($redirect_url ?: home_url('/'), home_url('/'));
}

function thrivingstudio_google_auth_state_key($state) {
    return 'ts_google_oauth_' . hash('sha256', (string) $state);
}

function thrivingstudio_google_auth_cookie_value($state) {
    return hash_hmac('sha256', (string) $state, wp_salt('auth'));
}

function thrivingstudio_google_auth_set_cookie($name, $value, $expires) {
    $path = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
    $domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
    $secure = is_ssl();

    if (PHP_VERSION_ID >= 70300) {
        $options = [
            'expires'  => $expires,
            'path'     => $path,
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        if ($domain) {
            $options['domain'] = $domain;
        }

        setcookie($name, $value, $options);
    } else {
        setcookie($name, $value, $expires, $path . '; samesite=Lax', $domain, $secure, true);
    }

    if ($expires <= time()) {
        unset($_COOKIE[$name]);
    } else {
        $_COOKIE[$name] = $value;
    }
}

function thrivingstudio_google_auth_clear_state_cookie() {
    thrivingstudio_google_auth_set_cookie('thrivingstudio_google_oauth_state', '', time() - HOUR_IN_SECONDS);
}

function thrivingstudio_google_auth_start() {
    $redirect_url = thrivingstudio_google_auth_sanitize_redirect();

    if (is_user_logged_in()) {
        wp_safe_redirect($redirect_url);
        exit;
    }

    if (!thrivingstudio_google_auth_is_configured()) {
        thrivingstudio_google_auth_render_setup_page();
    }

    $state = wp_generate_password(40, false, false);
    $nonce = wp_generate_password(40, false, false);
    $state_payload = [
        'redirect_url' => $redirect_url,
        'nonce'        => $nonce,
        'created_at'   => time(),
    ];

    set_transient(thrivingstudio_google_auth_state_key($state), $state_payload, 10 * MINUTE_IN_SECONDS);
    thrivingstudio_google_auth_set_cookie(
        'thrivingstudio_google_oauth_state',
        thrivingstudio_google_auth_cookie_value($state),
        time() + (10 * MINUTE_IN_SECONDS)
    );

    $auth_url = add_query_arg(
        [
            'client_id'     => thrivingstudio_google_auth_get_client_id(),
            'redirect_uri'  => thrivingstudio_google_auth_get_redirect_uri(),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'nonce'         => $nonce,
            'prompt'        => 'select_account',
        ],
        'https://accounts.google.com/o/oauth2/v2/auth'
    );

    wp_redirect($auth_url);
    exit;
}

function thrivingstudio_google_auth_callback() {
    if (!thrivingstudio_google_auth_is_configured()) {
        thrivingstudio_google_auth_render_setup_page();
    }

    if (isset($_GET['error'])) {
        $error = sanitize_text_field((string) wp_unslash($_GET['error']));
        thrivingstudio_google_auth_render_message(
            __('Google sign-in was cancelled', 'thrivingstudio'),
            sprintf(__('Google returned this sign-in error: %s', 'thrivingstudio'), $error),
            400
        );
    }

    $code = isset($_GET['code']) ? sanitize_text_field((string) wp_unslash($_GET['code'])) : '';
    $state = isset($_GET['state']) ? sanitize_text_field((string) wp_unslash($_GET['state'])) : '';
    $cookie_state = isset($_COOKIE['thrivingstudio_google_oauth_state'])
        ? sanitize_text_field((string) wp_unslash($_COOKIE['thrivingstudio_google_oauth_state']))
        : '';

    if ($code === '' || $state === '' || $cookie_state === '') {
        thrivingstudio_google_auth_render_message(
            __('Google sign-in could not continue', 'thrivingstudio'),
            __('The sign-in response was missing required security details. Please try again.', 'thrivingstudio'),
            400
        );
    }

    if (!hash_equals(thrivingstudio_google_auth_cookie_value($state), $cookie_state)) {
        thrivingstudio_google_auth_render_message(
            __('Google sign-in could not continue', 'thrivingstudio'),
            __('The sign-in state did not match this browser session. Please try again.', 'thrivingstudio'),
            400
        );
    }

    $state_key = thrivingstudio_google_auth_state_key($state);
    $state_payload = get_transient($state_key);
    delete_transient($state_key);
    thrivingstudio_google_auth_clear_state_cookie();

    if (!is_array($state_payload) || empty($state_payload['nonce'])) {
        thrivingstudio_google_auth_render_message(
            __('Google sign-in expired', 'thrivingstudio'),
            __('Please start sign-in again.', 'thrivingstudio'),
            400
        );
    }

    $token_response = wp_remote_post(
        'https://oauth2.googleapis.com/token',
        [
            'timeout' => 15,
            'body'    => [
                'code'          => $code,
                'client_id'     => thrivingstudio_google_auth_get_client_id(),
                'client_secret' => thrivingstudio_google_auth_get_client_secret(),
                'redirect_uri'  => thrivingstudio_google_auth_get_redirect_uri(),
                'grant_type'    => 'authorization_code',
            ],
        ]
    );

    if (is_wp_error($token_response)) {
        thrivingstudio_google_auth_render_message(
            __('Google sign-in failed', 'thrivingstudio'),
            $token_response->get_error_message(),
            502
        );
    }

    $token_status = (int) wp_remote_retrieve_response_code($token_response);
    $token_body = json_decode((string) wp_remote_retrieve_body($token_response), true);

    if ($token_status < 200 || $token_status >= 300 || !is_array($token_body) || empty($token_body['id_token'])) {
        thrivingstudio_google_auth_render_message(
            __('Google sign-in failed', 'thrivingstudio'),
            __('Google did not return a valid identity token.', 'thrivingstudio'),
            502
        );
    }

    $claims = thrivingstudio_google_auth_decode_jwt_payload((string) $token_body['id_token']);

    if (!thrivingstudio_google_auth_claims_are_valid($claims, $state_payload)) {
        thrivingstudio_google_auth_render_message(
            __('Google sign-in failed', 'thrivingstudio'),
            __('Google returned identity details that could not be verified for this site.', 'thrivingstudio'),
            403
        );
    }

    $profile = thrivingstudio_google_auth_get_userinfo($token_body['access_token'] ?? '');
    $identity = thrivingstudio_google_auth_normalize_identity($claims, $profile);

    if (empty($identity['sub']) || empty($identity['email']) || empty($identity['email_verified'])) {
        thrivingstudio_google_auth_render_message(
            __('Google sign-in needs a verified email', 'thrivingstudio'),
            __('Please use a Google account with a verified email address.', 'thrivingstudio'),
            403
        );
    }

    $user_id = thrivingstudio_google_auth_get_or_create_user($identity);

    if (is_wp_error($user_id)) {
        thrivingstudio_google_auth_render_message(
            __('Google sign-in could not create your account', 'thrivingstudio'),
            $user_id->get_error_message(),
            500
        );
    }

    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true, is_ssl());

    $user = get_userdata($user_id);

    if ($user) {
        do_action('wp_login', $user->user_login, $user);
    }

    wp_safe_redirect(thrivingstudio_google_auth_sanitize_redirect($state_payload['redirect_url'] ?? home_url('/')));
    exit;
}

function thrivingstudio_google_auth_decode_jwt_payload($id_token) {
    $parts = explode('.', $id_token);

    if (count($parts) < 2) {
        return [];
    }

    $payload = strtr($parts[1], '-_', '+/');
    $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
    $decoded = json_decode((string) base64_decode($payload), true);

    return is_array($decoded) ? $decoded : [];
}

function thrivingstudio_google_auth_claims_are_valid($claims, $state_payload) {
    if (!is_array($claims)) {
        return false;
    }

    $issuer = $claims['iss'] ?? '';
    $audience = $claims['aud'] ?? '';

    if (is_array($audience)) {
        $audience = reset($audience);
    }

    $expires = isset($claims['exp']) ? (int) $claims['exp'] : 0;
    $nonce = $claims['nonce'] ?? '';

    return in_array($issuer, ['https://accounts.google.com', 'accounts.google.com'], true)
        && hash_equals((string) thrivingstudio_google_auth_get_client_id(), (string) $audience)
        && $expires > time()
        && !empty($claims['sub'])
        && hash_equals((string) ($state_payload['nonce'] ?? ''), (string) $nonce);
}

function thrivingstudio_google_auth_get_userinfo($access_token) {
    if (!$access_token) {
        return [];
    }

    $response = wp_remote_get(
        'https://openidconnect.googleapis.com/v1/userinfo',
        [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
            ],
        ]
    );

    if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) >= 300) {
        return [];
    }

    $profile = json_decode((string) wp_remote_retrieve_body($response), true);

    return is_array($profile) ? $profile : [];
}

function thrivingstudio_google_auth_normalize_identity($claims, $profile) {
    $source = array_merge(is_array($claims) ? $claims : [], is_array($profile) ? $profile : []);

    return [
        'sub'            => sanitize_text_field((string) ($source['sub'] ?? '')),
        'email'          => sanitize_email((string) ($source['email'] ?? '')),
        'email_verified' => filter_var($source['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'name'           => sanitize_text_field((string) ($source['name'] ?? '')),
        'given_name'     => sanitize_text_field((string) ($source['given_name'] ?? '')),
        'family_name'    => sanitize_text_field((string) ($source['family_name'] ?? '')),
        'picture'        => esc_url_raw((string) ($source['picture'] ?? '')),
    ];
}

function thrivingstudio_google_auth_get_or_create_user($identity) {
    $user_id = thrivingstudio_google_auth_find_user_by_sub($identity['sub']);
    $user_was_created_by_google = false;

    if (!$user_id) {
        $existing_user = get_user_by('email', $identity['email']);

        if ($existing_user) {
            if (
                thrivingstudio_google_auth_user_has_staff_capabilities((int) $existing_user->ID)
                && !thrivingstudio_google_auth_email_can_keep_privileges($identity['email'])
            ) {
                return new WP_Error(
                    'thrivingstudio_google_staff_account_blocked',
                    __('This email belongs to a staff account. Please use the private WordPress login for staff access.', 'thrivingstudio')
                );
            }

            $existing_sub = get_user_meta($existing_user->ID, 'thrivingstudio_google_sub', true);

            if ($existing_sub && !hash_equals((string) $existing_sub, (string) $identity['sub'])) {
                return new WP_Error(
                    'thrivingstudio_google_account_conflict',
                    __('That email is already linked to another Google account.', 'thrivingstudio')
                );
            }

            $user_id = (int) $existing_user->ID;
        }
    }

    if (!$user_id) {
        $user_id = thrivingstudio_google_auth_create_user($identity);
        $user_was_created_by_google = !is_wp_error($user_id);
    }

    if (is_wp_error($user_id)) {
        return $user_id;
    }

    if ($user_was_created_by_google) {
        update_user_meta((int) $user_id, 'thrivingstudio_google_auth_managed', '1');
    }

    thrivingstudio_google_auth_update_user_profile((int) $user_id, $identity);
    thrivingstudio_google_auth_enforce_subscriber_role((int) $user_id);

    return (int) $user_id;
}

function thrivingstudio_google_auth_find_user_by_sub($sub) {
    $users = get_users([
        'meta_key'   => 'thrivingstudio_google_sub',
        'meta_value' => $sub,
        'number'     => 1,
        'fields'     => 'ID',
    ]);

    return !empty($users[0]) ? (int) $users[0] : 0;
}

function thrivingstudio_google_auth_create_user($identity) {
    $email = $identity['email'];
    $email_parts = explode('@', $email);
    $base_login = sanitize_user($email_parts[0], true);

    if ($base_login === '') {
        $base_login = 'google_' . substr(md5($identity['sub']), 0, 10);
    }

    $user_login = $base_login;
    $suffix = 2;

    while (username_exists($user_login)) {
        $user_login = $base_login . $suffix;
        $suffix++;
    }

    return wp_insert_user([
        'user_login'   => $user_login,
        'user_pass'    => wp_generate_password(32, true, true),
        'user_email'   => $email,
        'display_name' => $identity['name'] ?: $email,
        'first_name'   => $identity['given_name'],
        'last_name'    => $identity['family_name'],
        'role'         => 'subscriber',
    ]);
}

function thrivingstudio_google_auth_user_has_staff_capabilities($user_id) {
    return user_can($user_id, 'edit_posts')
        || user_can($user_id, 'upload_files')
        || user_can($user_id, 'manage_options');
}

function thrivingstudio_google_auth_is_managed_frontend_user($user_id = 0) {
    $user_id = $user_id ?: get_current_user_id();

    if (!$user_id) {
        return false;
    }

    $google_sub = get_user_meta($user_id, 'thrivingstudio_google_sub', true);
    $managed = get_user_meta($user_id, 'thrivingstudio_google_auth_managed', true);
    $user = get_userdata($user_id);

    if (!$google_sub || !$user) {
        return false;
    }

    return $managed === '1' || !thrivingstudio_google_auth_email_can_keep_privileges($user->user_email);
}

function thrivingstudio_google_auth_enforce_subscriber_role($user_id = 0) {
    $user_id = $user_id ?: get_current_user_id();

    if (!$user_id || !thrivingstudio_google_auth_is_managed_frontend_user($user_id)) {
        return;
    }

    $user = get_userdata($user_id);

    if (!($user instanceof WP_User) || (in_array('subscriber', (array) $user->roles, true) && count((array) $user->roles) === 1)) {
        return;
    }

    $user->set_role('subscriber');
}

function thrivingstudio_google_auth_enforce_current_user_role() {
    if (!is_user_logged_in()) {
        return;
    }

    thrivingstudio_google_auth_enforce_subscriber_role(get_current_user_id());
}
add_action('init', 'thrivingstudio_google_auth_enforce_current_user_role', 20);

function thrivingstudio_google_auth_redirect_frontend_users_from_admin() {
    if (!is_user_logged_in()) {
        return;
    }

    if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
        return;
    }

    if (thrivingstudio_google_auth_is_managed_frontend_user(get_current_user_id()) && !current_user_can('edit_posts')) {
        wp_safe_redirect(function_exists('thrivingstudio_get_account_profile_url') ? thrivingstudio_get_account_profile_url() : home_url('/'));
        exit;
    }
}
add_action('admin_init', 'thrivingstudio_google_auth_redirect_frontend_users_from_admin', 1);

function thrivingstudio_google_auth_hide_admin_bar($show) {
    if (!is_user_logged_in()) {
        return $show;
    }

    if (thrivingstudio_google_auth_is_managed_frontend_user(get_current_user_id()) || !current_user_can('edit_posts')) {
        return false;
    }

    return $show;
}
add_filter('show_admin_bar', 'thrivingstudio_google_auth_hide_admin_bar');

function thrivingstudio_google_auth_update_user_profile($user_id, $identity) {
    update_user_meta($user_id, 'thrivingstudio_google_sub', $identity['sub']);

    if ($identity['picture'] && thrivingstudio_google_auth_is_google_picture_url($identity['picture'])) {
        update_user_meta($user_id, 'thrivingstudio_google_picture', $identity['picture']);
    }

    $profile_customized = get_user_meta($user_id, 'thrivingstudio_profile_customized', true) === '1';
    $updates = [
        'ID'         => $user_id,
        'first_name' => $identity['given_name'],
        'last_name'  => $identity['family_name'],
    ];

    if (!$profile_customized) {
        $updates['display_name'] = $identity['name'] ?: $identity['email'];
    }

    wp_update_user($updates);
}

function thrivingstudio_google_auth_is_google_picture_url($url) {
    if (!$url || !wp_http_validate_url($url)) {
        return false;
    }

    $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));

    return (bool) preg_match('/(^|\.)googleusercontent\.com$/', $host);
}

function thrivingstudio_google_auth_avatar_url($url, $id_or_email, $args) {
    $user = false;

    if ($id_or_email instanceof WP_User) {
        $user = $id_or_email;
    } elseif (is_numeric($id_or_email)) {
        $user = get_userdata((int) $id_or_email);
    } elseif ($id_or_email instanceof WP_Comment && !empty($id_or_email->user_id)) {
        $user = get_userdata((int) $id_or_email->user_id);
    } elseif (is_string($id_or_email) && is_email($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
    }

    if (!$user) {
        return $url;
    }

    $picture = get_user_meta($user->ID, 'thrivingstudio_google_picture', true);

    return thrivingstudio_google_auth_is_google_picture_url($picture) ? $picture : $url;
}
add_filter('get_avatar_url', 'thrivingstudio_google_auth_avatar_url', 10, 3);

function thrivingstudio_google_auth_render_setup_page() {
    thrivingstudio_google_auth_render_message(
        __('Google sign-in is almost ready', 'thrivingstudio'),
        sprintf(
            '%1$s<br><br><strong>%2$s</strong><br><code>%3$s</code><br><br><strong>%4$s</strong><br><code>define(\'THRIVINGSTUDIO_GOOGLE_CLIENT_ID\', \'your-client-id.apps.googleusercontent.com\');<br>define(\'THRIVINGSTUDIO_GOOGLE_CLIENT_SECRET\', \'your-client-secret\');</code>',
            esc_html__('Create a Google OAuth web client, add this authorized redirect URI, then add the client credentials to wp-config.php.', 'thrivingstudio'),
            esc_html__('Authorized redirect URI:', 'thrivingstudio'),
            esc_html(thrivingstudio_google_auth_get_redirect_uri()),
            esc_html__('wp-config.php:', 'thrivingstudio')
        ),
        503
    );
}

function thrivingstudio_google_auth_render_message($title, $message, $response = 200) {
    status_header($response);
    nocache_headers();
    ?>
    <!doctype html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo esc_html($title); ?></title>
        <style>
            body { margin: 0; background: #f3f4f6; color: #111827; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
            .ts-auth-page { min-height: 100vh; display: grid; place-items: center; padding: 2rem; }
            .ts-auth-panel { width: min(100%, 34rem); padding: 1.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; background: #fff; box-shadow: 0 16px 40px rgba(17, 24, 39, 0.08); }
            .ts-auth-panel h1 { margin: 0 0 0.75rem; font-size: 1.65rem; line-height: 1.2; }
            .ts-auth-panel p { margin: 0; color: #4b5563; line-height: 1.6; }
            .ts-auth-panel code { display: block; margin-top: 0.4rem; padding: 0.75rem; border-radius: 0.4rem; background: #f9fafb; color: #111827; overflow-x: auto; white-space: nowrap; }
            .ts-auth-panel a { display: inline-flex; margin-top: 1rem; color: #111827; font-weight: 800; text-decoration: underline; text-underline-offset: 3px; }
        </style>
    </head>
    <body>
        <main class="ts-auth-page">
            <section class="ts-auth-panel" aria-labelledby="ts-auth-title">
                <h1 id="ts-auth-title"><?php echo esc_html($title); ?></h1>
                <p><?php echo wp_kses_post($message); ?></p>
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back to Thriving Studio', 'thrivingstudio'); ?></a>
            </section>
        </main>
    </body>
    </html>
    <?php
    exit;
}

function thrivingstudio_google_auth_handle_routes() {
    if (thrivingstudio_google_auth_request_path_matches('ts-google-login')) {
        thrivingstudio_google_auth_start();
    }

    if (thrivingstudio_google_auth_request_path_matches('ts-google-callback')) {
        thrivingstudio_google_auth_callback();
    }
}
add_action('template_redirect', 'thrivingstudio_google_auth_handle_routes', 0);
