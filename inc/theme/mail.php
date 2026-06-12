<?php

/**
 * Optional SMTP mail configuration.
 *
 * Values are read from wp-config.php constants first, then environment variables.
 * If THRIVINGSTUDIO_SMTP_HOST is not set, WordPress keeps using its default mailer.
 */
function thrivingstudio_get_mail_config($key, $default = '') {
    $constant = 'THRIVINGSTUDIO_' . $key;

    if (defined($constant)) {
        return constant($constant);
    }

    $value = getenv($constant);
    return $value !== false ? $value : $default;
}

/**
 * Configure PHPMailer for SMTP when credentials are available.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer
 */
function thrivingstudio_configure_smtp($phpmailer) {
    $host = trim((string) thrivingstudio_get_mail_config('SMTP_HOST'));

    if ($host === '') {
        return;
    }

    $port = (int) thrivingstudio_get_mail_config('SMTP_PORT', 587);
    $secure = strtolower((string) thrivingstudio_get_mail_config('SMTP_SECURE', 'tls'));
    $username = (string) thrivingstudio_get_mail_config('SMTP_USERNAME');
    $password = (string) thrivingstudio_get_mail_config('SMTP_PASSWORD');

    $phpmailer->isSMTP();
    $phpmailer->Host = $host;
    $phpmailer->Port = $port > 0 ? $port : 587;
    $phpmailer->SMTPAuth = $username !== '' || $password !== '';
    $phpmailer->Username = $username;
    $phpmailer->Password = $password;

    if (in_array($secure, ['ssl', 'tls'], true)) {
        $phpmailer->SMTPSecure = $secure;
    }
}
add_action('phpmailer_init', 'thrivingstudio_configure_smtp');

/**
 * Set a domain-aligned sender address when configured.
 *
 * @param string $email
 * @return string
 */
function thrivingstudio_mail_from($email) {
    $from = sanitize_email(thrivingstudio_get_mail_config('MAIL_FROM'));
    return is_email($from) ? $from : $email;
}
add_filter('wp_mail_from', 'thrivingstudio_mail_from');

/**
 * Set a sender name when configured.
 *
 * @param string $name
 * @return string
 */
function thrivingstudio_mail_from_name($name) {
    $from_name = sanitize_text_field(thrivingstudio_get_mail_config('MAIL_FROM_NAME'));
    return $from_name !== '' ? $from_name : $name;
}
add_filter('wp_mail_from_name', 'thrivingstudio_mail_from_name');

/**
 * Allow production to route contact mail to a dedicated inbox.
 *
 * @param string $recipient
 * @return string
 */
function thrivingstudio_contact_recipient($recipient) {
    $configured = sanitize_email(thrivingstudio_get_mail_config('CONTACT_RECIPIENT'));
    if (is_email($configured)) {
        return $configured;
    }

    $public_inbox = 'hello@thrivingstudio.xyz';
    return is_email($public_inbox) ? $public_inbox : $recipient;
}
add_filter('thrivingstudio_contact_recipient', 'thrivingstudio_contact_recipient');
