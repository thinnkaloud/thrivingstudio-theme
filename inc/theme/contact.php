<?php

/**
 * Handle contact form submissions from the contact page template.
 */
function thrivingstudio_handle_contact_form() {
    if (
        !isset($_POST['thrivingstudio_contact_nonce']) ||
        !wp_verify_nonce($_POST['thrivingstudio_contact_nonce'], 'thrivingstudio_contact_form')
    ) {
        thrivingstudio_redirect_contact_form('error');
    }

    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $website = isset($_POST['website']) ? trim((string) wp_unslash($_POST['website'])) : '';

    if ($website !== '' || $name === '' || $message === '' || !is_email($email)) {
        thrivingstudio_redirect_contact_form('error');
    }

    $mail_subject = $subject !== '' ? $subject : sprintf(__('New contact message from %s', 'thrivingstudio'), $name);
    $mail_body = sprintf(
        "Name: %s\nEmail: %s\n\nMessage:\n%s",
        $name,
        $email,
        $message
    );
    $headers = [
        'Reply-To: ' . $name . ' <' . $email . '>',
    ];

    $recipient = apply_filters('thrivingstudio_contact_recipient', get_option('admin_email'));
    $sent = wp_mail($recipient, $mail_subject, $mail_body, $headers);

    thrivingstudio_redirect_contact_form($sent ? 'success' : 'error');
}
add_action('admin_post_thrivingstudio_contact_submit', 'thrivingstudio_handle_contact_form');
add_action('admin_post_nopriv_thrivingstudio_contact_submit', 'thrivingstudio_handle_contact_form');

/**
 * Redirect back to the contact page with a submission status.
 *
 * @param string $status
 */
function thrivingstudio_redirect_contact_form($status) {
    $fallback = home_url('/contact/');
    $redirect = wp_get_referer() ?: $fallback;

    wp_safe_redirect(add_query_arg('contact_status', $status, $redirect));
    exit;
}
