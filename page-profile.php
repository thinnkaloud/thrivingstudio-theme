<?php
/**
 * Front-end profile page for the page slug "profile".
 */

get_header();

$is_logged_in = is_user_logged_in();
$user = $is_logged_in ? wp_get_current_user() : null;
$display_name = $is_logged_in ? ($user->display_name ?: $user->user_login) : '';
$description = $is_logged_in ? get_user_meta($user->ID, 'description', true) : '';
?>

<main class="flex-1">
    <div class="ts-profile-page container mx-auto px-4 sm:px-6 lg:px-8">
        <section class="ts-profile-shell">
            <p class="ts-profile-kicker"><?php esc_html_e('Account', 'thrivingstudio'); ?></p>
            <h1 class="ts-profile-title"><?php esc_html_e('Profile', 'thrivingstudio'); ?></h1>

            <?php if ($is_logged_in) : ?>
                <p class="ts-profile-intro">
                    <?php esc_html_e('Manage how you appear when you comment and interact with Thriving Studio.', 'thrivingstudio'); ?>
                </p>

                <div class="ts-profile-panel">
                    <div class="ts-profile-identity">
                        <?php echo get_avatar($user->ID, 96, '', $display_name, ['class' => 'ts-profile-avatar']); ?>
                        <div>
                            <h2 class="ts-profile-name"><?php echo esc_html($display_name); ?></h2>
                            <p class="ts-profile-email"><?php echo esc_html($user->user_email); ?></p>
                        </div>
                    </div>

                    <div class="ts-profile-details">
                        <div class="ts-profile-detail">
                            <span class="ts-profile-detail-label"><?php esc_html_e('Bio', 'thrivingstudio'); ?></span>
                            <p><?php echo esc_html($description ?: __('No bio added yet.', 'thrivingstudio')); ?></p>
                        </div>
                        <div class="ts-profile-detail">
                            <span class="ts-profile-detail-label"><?php esc_html_e('Comment name', 'thrivingstudio'); ?></span>
                            <p><?php echo esc_html($display_name); ?></p>
                        </div>
                    </div>

                    <div class="ts-profile-actions">
                        <a class="ts-profile-action ts-profile-action-primary" href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>">
                            <?php esc_html_e('Edit Profile', 'thrivingstudio'); ?>
                        </a>
                        <a class="ts-profile-action" href="<?php echo esc_url(thrivingstudio_get_account_logout_url(home_url('/'))); ?>">
                            <?php esc_html_e('Log out', 'thrivingstudio'); ?>
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <p class="ts-profile-intro">
                    <?php esc_html_e('Sign in to keep your profile, avatar, and comments connected across the site.', 'thrivingstudio'); ?>
                </p>
                <div class="ts-profile-panel ts-profile-panel-guest">
                    <a class="ts-account-login ts-account-login--profile" href="<?php echo esc_url(thrivingstudio_get_account_login_url(get_permalink())); ?>">
                        <?php echo esc_html(thrivingstudio_get_account_login_label()); ?>
                    </a>
                    <p><?php esc_html_e('After login, this page will show your WordPress profile details.', 'thrivingstudio'); ?></p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php get_footer(); ?>
