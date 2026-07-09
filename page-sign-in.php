<?php
/**
 * Front-end sign-in and sign-up page for the page slug "sign-in".
 */

$redirect_url = function_exists('thrivingstudio_account_sanitize_redirect')
    ? thrivingstudio_account_sanitize_redirect()
    : wp_validate_redirect(
        isset($_GET['redirect_to']) && is_scalar($_GET['redirect_to']) ? (string) wp_unslash($_GET['redirect_to']) : '',
        home_url('/')
    );
$auth_error = function_exists('thrivingstudio_account_get_auth_error') ? thrivingstudio_account_get_auth_error() : '';
$google_url = function_exists('thrivingstudio_get_google_login_url')
    ? thrivingstudio_get_google_login_url($redirect_url)
    : wp_login_url($redirect_url);
$email_signup_enabled = function_exists('thrivingstudio_account_email_signup_is_enabled')
    ? thrivingstudio_account_email_signup_is_enabled()
    : true;
$form_action = function_exists('thrivingstudio_get_account_sign_in_url')
    ? thrivingstudio_get_account_sign_in_url($redirect_url)
    : home_url('/sign-in/');

get_header();
?>

<main class="flex-1">
    <div class="ts-auth-page container mx-auto px-4 sm:px-6 lg:px-8">
        <section class="ts-auth-shell" aria-labelledby="ts-auth-title">
            <div class="ts-auth-heading">
                <p class="ts-auth-kicker"><?php esc_html_e('Account', 'thrivingstudio'); ?></p>
                <h1 id="ts-auth-title" class="ts-auth-title"><?php esc_html_e('Sign in or create your profile', 'thrivingstudio'); ?></h1>
                <p class="ts-auth-intro">
                    <?php esc_html_e('Choose how you want to join Thriving Studio. Google is fastest, and email works for readers who prefer a direct account.', 'thrivingstudio'); ?>
                </p>
            </div>

            <?php if ($auth_error) : ?>
                <div class="ts-auth-notice ts-auth-notice-error" role="alert">
                    <?php echo esc_html($auth_error); ?>
                </div>
            <?php endif; ?>

            <div class="ts-auth-grid">
                <section class="ts-auth-panel ts-auth-panel-provider" aria-labelledby="ts-auth-google-title">
                    <div>
                        <span class="ts-auth-provider-mark" aria-hidden="true">G</span>
                        <p class="ts-auth-eyebrow"><?php esc_html_e('Recommended', 'thrivingstudio'); ?></p>
                        <h2 id="ts-auth-google-title"><?php esc_html_e('Continue with Google', 'thrivingstudio'); ?></h2>
                        <p><?php esc_html_e('Use your Google account to sign in quickly and keep your avatar connected.', 'thrivingstudio'); ?></p>
                    </div>

                    <a class="ts-auth-button ts-auth-button-primary" href="<?php echo esc_url($google_url); ?>">
                        <?php esc_html_e('Continue with Google', 'thrivingstudio'); ?>
                    </a>

                    <ul class="ts-auth-points" aria-label="<?php esc_attr_e('Google sign-in benefits', 'thrivingstudio'); ?>">
                        <li><?php esc_html_e('No separate password to remember', 'thrivingstudio'); ?></li>
                        <li><?php esc_html_e('Your reader profile stays connected', 'thrivingstudio'); ?></li>
                        <li><?php esc_html_e('Access stays limited to reader features', 'thrivingstudio'); ?></li>
                    </ul>
                </section>

                <div class="ts-auth-email-stack">
                    <section class="ts-auth-panel" aria-labelledby="ts-auth-email-title">
                        <div class="ts-auth-panel-header">
                            <div>
                                <p class="ts-auth-eyebrow"><?php esc_html_e('Email account', 'thrivingstudio'); ?></p>
                                <h2 id="ts-auth-email-title"><?php esc_html_e('Sign in with email', 'thrivingstudio'); ?></h2>
                            </div>
                            <a href="<?php echo esc_url(wp_lostpassword_url($redirect_url)); ?>">
                                <?php esc_html_e('Forgot password?', 'thrivingstudio'); ?>
                            </a>
                        </div>

                        <form class="ts-auth-form" method="post" action="<?php echo esc_url($form_action); ?>">
                            <?php wp_nonce_field('thrivingstudio_email_login', 'thrivingstudio_auth_nonce'); ?>
                            <input type="hidden" name="thrivingstudio_auth_action" value="email_login">
                            <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_url); ?>">

                            <label class="ts-auth-field">
                                <span><?php esc_html_e('Email', 'thrivingstudio'); ?></span>
                                <input class="ts-auth-input" type="email" name="ts_auth_email" autocomplete="email" required>
                            </label>

                            <label class="ts-auth-field">
                                <span><?php esc_html_e('Password', 'thrivingstudio'); ?></span>
                                <input class="ts-auth-input" type="password" name="ts_auth_password" autocomplete="current-password" required>
                            </label>

                            <label class="ts-auth-checkbox">
                                <input type="checkbox" name="ts_auth_remember" value="1" checked>
                                <span><?php esc_html_e('Keep me signed in', 'thrivingstudio'); ?></span>
                            </label>

                            <button class="ts-auth-button ts-auth-button-secondary" type="submit">
                                <?php esc_html_e('Sign in', 'thrivingstudio'); ?>
                            </button>
                        </form>
                    </section>

                    <?php if ($email_signup_enabled) : ?>
                        <section class="ts-auth-panel" aria-labelledby="ts-auth-signup-title">
                            <div class="ts-auth-panel-header">
                                <div>
                                    <p class="ts-auth-eyebrow"><?php esc_html_e('New here?', 'thrivingstudio'); ?></p>
                                    <h2 id="ts-auth-signup-title"><?php esc_html_e('Create an email account', 'thrivingstudio'); ?></h2>
                                </div>
                            </div>

                            <form class="ts-auth-form" method="post" action="<?php echo esc_url($form_action); ?>">
                                <?php wp_nonce_field('thrivingstudio_email_signup', 'thrivingstudio_auth_nonce'); ?>
                                <input type="hidden" name="thrivingstudio_auth_action" value="email_signup">
                                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_url); ?>">

                                <label class="ts-auth-field">
                                    <span><?php esc_html_e('Name', 'thrivingstudio'); ?></span>
                                    <input class="ts-auth-input" type="text" name="ts_auth_name" autocomplete="name" maxlength="80" required>
                                </label>

                                <label class="ts-auth-field">
                                    <span><?php esc_html_e('Email', 'thrivingstudio'); ?></span>
                                    <input class="ts-auth-input" type="email" name="ts_auth_email" autocomplete="email" required>
                                </label>

                                <label class="ts-auth-field">
                                    <span><?php esc_html_e('Password', 'thrivingstudio'); ?></span>
                                    <input class="ts-auth-input" type="password" name="ts_auth_password" autocomplete="new-password" minlength="8" required>
                                </label>

                                <label class="ts-auth-field">
                                    <span><?php esc_html_e('Confirm password', 'thrivingstudio'); ?></span>
                                    <input class="ts-auth-input" type="password" name="ts_auth_password_confirm" autocomplete="new-password" minlength="8" required>
                                </label>

                                <label class="ts-auth-honeypot" aria-hidden="true">
                                    <span><?php esc_html_e('Company', 'thrivingstudio'); ?></span>
                                    <input type="text" name="ts_auth_company" tabindex="-1" autocomplete="off">
                                </label>

                                <button class="ts-auth-button ts-auth-button-secondary" type="submit">
                                    <?php esc_html_e('Create account', 'thrivingstudio'); ?>
                                </button>
                            </form>
                        </section>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
get_footer();
