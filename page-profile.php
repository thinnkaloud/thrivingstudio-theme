<?php
/**
 * Front-end profile page for the page slug "profile".
 */

$is_logged_in = is_user_logged_in();
$user = $is_logged_in ? wp_get_current_user() : null;
$display_name = $is_logged_in ? ($user->display_name ?: $user->user_login) : '';
$description = $is_logged_in ? trim((string) get_user_meta($user->ID, 'description', true)) : '';
$google_picture = $is_logged_in ? (string) get_user_meta($user->ID, 'thrivingstudio_google_picture', true) : '';
$google_sub = $is_logged_in ? (string) get_user_meta($user->ID, 'thrivingstudio_google_sub', true) : '';
$is_google_profile = $google_sub !== '';
$is_staff_account = $is_logged_in && current_user_can('edit_posts');
$account_type = $is_staff_account ? __('Studio staff', 'thrivingstudio') : __('Reader profile', 'thrivingstudio');
$sign_in_source = $is_google_profile ? __('Google connected', 'thrivingstudio') : __('WordPress account', 'thrivingstudio');
$member_since = '';
$member_duration = '';
$comment_count = 0;
$recent_comments = [];
$profile_score = 0;
$profile_status = __('Getting started', 'thrivingstudio');
$topic_counts = [];
$top_topics = [];
$profile_error = '';
$profile_updated = isset($_GET['profile-updated']) && '1' === (string) $_GET['profile-updated'];
$is_editing_profile = $is_logged_in && isset($_GET['edit']) && '1' === (string) $_GET['edit'];

if (
    $is_logged_in
    && isset($_POST['thrivingstudio_profile_action'])
    && 'update_public_profile' === (string) wp_unslash($_POST['thrivingstudio_profile_action'])
) {
    $nonce = isset($_POST['thrivingstudio_profile_nonce'])
        ? sanitize_text_field((string) wp_unslash($_POST['thrivingstudio_profile_nonce']))
        : '';
    $submitted_display_name = isset($_POST['ts_profile_display_name'])
        ? sanitize_text_field((string) wp_unslash($_POST['ts_profile_display_name']))
        : '';
    $submitted_description = isset($_POST['ts_profile_description'])
        ? sanitize_textarea_field((string) wp_unslash($_POST['ts_profile_description']))
        : '';
    $submitted_description = substr($submitted_description, 0, 500);

    if (!wp_verify_nonce($nonce, 'thrivingstudio_update_public_profile')) {
        $profile_error = __('We could not verify this profile update. Please try again.', 'thrivingstudio');
        $is_editing_profile = true;
    } elseif ($submitted_display_name === '') {
        $profile_error = __('Please add a public display name.', 'thrivingstudio');
        $display_name = '';
        $description = $submitted_description;
        $is_editing_profile = true;
    } else {
        $updated_user = wp_update_user([
            'ID'           => $user->ID,
            'display_name' => $submitted_display_name,
            'nickname'     => $submitted_display_name,
        ]);

        if (is_wp_error($updated_user)) {
            $profile_error = $updated_user->get_error_message();
            $display_name = $submitted_display_name;
            $description = $submitted_description;
            $is_editing_profile = true;
        } else {
            update_user_meta($user->ID, 'description', $submitted_description);
            update_user_meta($user->ID, 'thrivingstudio_profile_customized', '1');

            wp_safe_redirect(add_query_arg('profile-updated', '1', thrivingstudio_get_account_profile_url($user->ID)));
            exit;
        }
    }
}

if ($is_logged_in) {
    $user = wp_get_current_user();
    $display_name = $display_name ?: ($user->display_name ?: $user->user_login);
    $description = trim((string) $description);
    $registered_timestamp = $user->user_registered ? strtotime($user->user_registered) : 0;

    if ($registered_timestamp) {
        $member_since = date_i18n('M Y', $registered_timestamp);
        $days_since = max(0, (int) floor((current_time('timestamp') - $registered_timestamp) / DAY_IN_SECONDS));
        $member_duration = sprintf(
            /* translators: %d: number of days. */
            _n('%d day here', '%d days here', $days_since, 'thrivingstudio'),
            $days_since
        );
    }

    $comment_count = (int) get_comments([
        'user_id' => $user->ID,
        'status'  => 'approve',
        'count'   => true,
    ]);

    $recent_comments = get_comments([
        'user_id' => $user->ID,
        'status'  => 'approve',
        'number'  => 3,
        'orderby' => 'comment_date_gmt',
        'order'   => 'DESC',
    ]);

    foreach ($recent_comments as $comment) {
        $comment_post = get_post((int) $comment->comment_post_ID);

        if (!$comment_post) {
            continue;
        }

        foreach (get_the_category($comment_post->ID) as $category) {
            $topic_counts[$category->name] = ($topic_counts[$category->name] ?? 0) + 1;
        }
    }

    arsort($topic_counts);
    $top_topics = array_slice(array_keys($topic_counts), 0, 3);

    $profile_checks = [
        $display_name !== '',
        $user->user_email !== '',
        $description !== '',
        $google_picture !== '' || $is_staff_account,
    ];
    $completed_checks = count(array_filter($profile_checks));
    $profile_score = (int) round(($completed_checks / count($profile_checks)) * 100);

    if ($profile_score >= 90) {
        $profile_status = __('Well rounded', 'thrivingstudio');
    } elseif ($profile_score >= 60) {
        $profile_status = __('Taking shape', 'thrivingstudio');
    }
}

get_header();
?>

<main class="flex-1">
    <div class="ts-profile-page container mx-auto px-4 sm:px-6 lg:px-8">
        <section class="ts-profile-shell" aria-labelledby="ts-profile-title">
            <?php if ($is_logged_in) : ?>
                <h1 id="ts-profile-title" class="screen-reader-text"><?php esc_html_e('Profile', 'thrivingstudio'); ?></h1>
                <div class="ts-profile-hero">
                    <div class="ts-profile-identity">
                        <div class="ts-profile-avatar-wrap">
                            <?php echo get_avatar($user->ID, 112, '', $display_name, ['class' => 'ts-profile-avatar']); ?>
                        </div>
                        <div class="ts-profile-person">
                            <h2 class="ts-profile-name"><?php echo esc_html($display_name); ?></h2>
                            <p class="ts-profile-email"><?php echo esc_html($user->user_email); ?></p>
                            <div class="ts-profile-meta-row" aria-label="<?php esc_attr_e('Account metadata', 'thrivingstudio'); ?>">
                                <span><?php echo esc_html($account_type); ?></span>
                                <span><?php echo esc_html($sign_in_source); ?></span>
                                <?php if ($member_since) : ?>
                                    <span><?php echo esc_html(sprintf(__('Since %s', 'thrivingstudio'), $member_since)); ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="ts-profile-intro">
                                <?php echo esc_html($description ?: __('Your public identity, conversation history, and reader signals across Thriving Studio.', 'thrivingstudio')); ?>
                            </p>
                        </div>
                    </div>

                    <div class="ts-profile-hero-actions">
                        <?php if ($is_staff_account) : ?>
                            <a class="ts-profile-action ts-profile-action-primary" href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>">
                                <?php esc_html_e('Admin profile', 'thrivingstudio'); ?>
                            </a>
                        <?php else : ?>
                            <a class="ts-profile-action ts-profile-action-primary" href="<?php echo esc_url(add_query_arg('edit', '1', thrivingstudio_get_account_profile_url($user->ID))); ?>">
                                <?php esc_html_e('Edit profile', 'thrivingstudio'); ?>
                            </a>
                        <?php endif; ?>
                        <a class="ts-profile-action" href="<?php echo esc_url(thrivingstudio_get_account_logout_url(home_url('/'))); ?>">
                            <?php esc_html_e('Log out', 'thrivingstudio'); ?>
                        </a>
                    </div>

                    <div class="ts-profile-stats" aria-label="<?php esc_attr_e('Profile summary', 'thrivingstudio'); ?>">
                        <div class="ts-profile-stat">
                            <span class="ts-profile-stat-value"><?php echo esc_html(number_format_i18n($comment_count)); ?></span>
                            <span class="ts-profile-stat-label"><?php esc_html_e('Comments', 'thrivingstudio'); ?></span>
                        </div>
                        <div class="ts-profile-stat">
                            <span class="ts-profile-stat-value"><?php echo esc_html($member_since ?: __('New', 'thrivingstudio')); ?></span>
                            <span class="ts-profile-stat-label"><?php esc_html_e('Member since', 'thrivingstudio'); ?></span>
                        </div>
                        <div class="ts-profile-stat">
                            <span class="ts-profile-stat-value"><?php echo esc_html($profile_score); ?>%</span>
                            <span class="ts-profile-stat-label"><?php esc_html_e('Profile strength', 'thrivingstudio'); ?></span>
                        </div>
                        <div class="ts-profile-stat">
                            <span class="ts-profile-stat-value"><?php echo esc_html(count($top_topics)); ?></span>
                            <span class="ts-profile-stat-label"><?php esc_html_e('Topic signals', 'thrivingstudio'); ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($profile_updated) : ?>
                    <div class="ts-profile-notice ts-profile-notice-success" role="status">
                        <?php esc_html_e('Profile updated.', 'thrivingstudio'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($profile_error) : ?>
                    <div class="ts-profile-notice ts-profile-notice-error" role="alert">
                        <?php echo esc_html($profile_error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($is_editing_profile && !$is_staff_account) : ?>
                    <section class="ts-profile-edit-panel" aria-labelledby="ts-profile-edit-title">
                        <div class="ts-profile-panel-header">
                            <div>
                                <h2 id="ts-profile-edit-title"><?php esc_html_e('Edit Public Profile', 'thrivingstudio'); ?></h2>
                                <p><?php esc_html_e('These details shape how you appear when you comment and interact on Thriving Studio.', 'thrivingstudio'); ?></p>
                            </div>
                            <span><?php esc_html_e('Public fields', 'thrivingstudio'); ?></span>
                        </div>

                        <form class="ts-profile-form" method="post" action="<?php echo esc_url(thrivingstudio_get_account_profile_url($user->ID)); ?>">
                            <?php wp_nonce_field('thrivingstudio_update_public_profile', 'thrivingstudio_profile_nonce'); ?>
                            <input type="hidden" name="thrivingstudio_profile_action" value="update_public_profile">

                            <div class="ts-profile-form-grid">
                                <label class="ts-profile-field">
                                    <span><?php esc_html_e('Display name', 'thrivingstudio'); ?></span>
                                    <input
                                        class="ts-profile-input"
                                        type="text"
                                        name="ts_profile_display_name"
                                        value="<?php echo esc_attr($display_name); ?>"
                                        maxlength="80"
                                        required
                                    >
                                </label>

                                <label class="ts-profile-field ts-profile-field-wide">
                                    <span><?php esc_html_e('Bio', 'thrivingstudio'); ?></span>
                                    <textarea
                                        class="ts-profile-textarea"
                                        name="ts_profile_description"
                                        rows="4"
                                        maxlength="500"
                                    ><?php echo esc_textarea($description); ?></textarea>
                                </label>
                            </div>

                            <p class="ts-profile-form-note">
                                <?php esc_html_e('Your email, login, and Google avatar stay private and managed by Google.', 'thrivingstudio'); ?>
                            </p>

                            <div class="ts-profile-form-actions">
                                <button class="ts-profile-action ts-profile-action-primary" type="submit">
                                    <?php esc_html_e('Save profile', 'thrivingstudio'); ?>
                                </button>
                                <a class="ts-profile-action" href="<?php echo esc_url(thrivingstudio_get_account_profile_url($user->ID)); ?>">
                                    <?php esc_html_e('Cancel', 'thrivingstudio'); ?>
                                </a>
                            </div>
                        </form>
                    </section>
                <?php endif; ?>

                <div class="ts-profile-dashboard">
                    <section class="ts-profile-panel ts-profile-panel-main" aria-labelledby="ts-profile-public-title">
                        <div class="ts-profile-panel-header">
                            <h2 id="ts-profile-public-title"><?php esc_html_e('Public Identity', 'thrivingstudio'); ?></h2>
                            <span><?php echo esc_html($profile_status); ?></span>
                        </div>

                        <div class="ts-profile-strength" aria-label="<?php esc_attr_e('Profile strength', 'thrivingstudio'); ?>">
                            <span style="width: <?php echo esc_attr((string) $profile_score); ?>%"></span>
                        </div>

                        <dl class="ts-profile-detail-list">
                            <div>
                                <dt><?php esc_html_e('Comment name', 'thrivingstudio'); ?></dt>
                                <dd><?php echo esc_html($display_name); ?></dd>
                            </div>
                            <div>
                                <dt><?php esc_html_e('Bio', 'thrivingstudio'); ?></dt>
                                <dd><?php echo esc_html($description ?: __('No bio added yet.', 'thrivingstudio')); ?></dd>
                            </div>
                            <div>
                                <dt><?php esc_html_e('Avatar source', 'thrivingstudio'); ?></dt>
                                <dd><?php echo esc_html($google_picture ? __('Google profile photo', 'thrivingstudio') : __('WordPress avatar', 'thrivingstudio')); ?></dd>
                            </div>
                            <div>
                                <dt><?php esc_html_e('Email visibility', 'thrivingstudio'); ?></dt>
                                <dd><?php esc_html_e('Private to your account', 'thrivingstudio'); ?></dd>
                            </div>
                        </dl>
                    </section>

                    <section class="ts-profile-panel" aria-labelledby="ts-profile-activity-title">
                        <div class="ts-profile-panel-header">
                            <h2 id="ts-profile-activity-title"><?php esc_html_e('Recent Activity', 'thrivingstudio'); ?></h2>
                            <span><?php echo esc_html($member_duration ?: __('New member', 'thrivingstudio')); ?></span>
                        </div>

                        <?php if ($recent_comments) : ?>
                            <ol class="ts-profile-activity-list">
                                <?php foreach ($recent_comments as $comment) : ?>
                                    <?php
                                    $comment_post = get_post((int) $comment->comment_post_ID);
                                    if (!$comment_post) {
                                        continue;
                                    }
                                    ?>
                                    <li>
                                        <a href="<?php echo esc_url(get_comment_link($comment)); ?>">
                                            <?php echo esc_html(get_the_title($comment_post)); ?>
                                        </a>
                                        <span><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($comment->comment_date))); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php else : ?>
                            <div class="ts-profile-empty">
                                <strong><?php esc_html_e('No public replies yet', 'thrivingstudio'); ?></strong>
                                <p><?php esc_html_e('When you join article conversations, your latest approved comments will appear here.', 'thrivingstudio'); ?></p>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="ts-profile-panel" aria-labelledby="ts-profile-signals-title">
                        <div class="ts-profile-panel-header">
                            <h2 id="ts-profile-signals-title"><?php esc_html_e('Reader Signals', 'thrivingstudio'); ?></h2>
                            <span><?php echo esc_html($top_topics ? __('Active', 'thrivingstudio') : __('Learning', 'thrivingstudio')); ?></span>
                        </div>

                        <?php if ($top_topics) : ?>
                            <div class="ts-profile-topic-list">
                                <?php foreach ($top_topics as $topic) : ?>
                                    <span><?php echo esc_html($topic); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="ts-profile-empty">
                                <strong><?php esc_html_e('Topic pattern not set', 'thrivingstudio'); ?></strong>
                                <p><?php esc_html_e('Your profile can grow around the themes you read, save, and discuss most.', 'thrivingstudio'); ?></p>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="ts-profile-panel" aria-labelledby="ts-profile-account-title">
                        <div class="ts-profile-panel-header">
                            <h2 id="ts-profile-account-title"><?php esc_html_e('Account', 'thrivingstudio'); ?></h2>
                            <span><?php echo esc_html($sign_in_source); ?></span>
                        </div>

                        <ul class="ts-profile-account-list">
                            <li>
                                <span><?php esc_html_e('Access level', 'thrivingstudio'); ?></span>
                                <strong><?php echo esc_html($account_type); ?></strong>
                            </li>
                            <li>
                                <span><?php esc_html_e('Comment identity', 'thrivingstudio'); ?></span>
                                <strong><?php echo esc_html($display_name); ?></strong>
                            </li>
                            <li>
                                <span><?php esc_html_e('Login method', 'thrivingstudio'); ?></span>
                                <strong><?php echo esc_html($sign_in_source); ?></strong>
                            </li>
                        </ul>
                    </section>
                </div>
            <?php else : ?>
                <div class="ts-profile-heading">
                    <div>
                        <h1 id="ts-profile-title" class="ts-profile-title"><?php esc_html_e('Profile', 'thrivingstudio'); ?></h1>
                        <p class="ts-profile-intro">
                            <?php esc_html_e('Your public identity, conversation history, and reader signals across Thriving Studio.', 'thrivingstudio'); ?>
                        </p>
                    </div>
                </div>

                <div class="ts-profile-hero ts-profile-hero-guest">
                    <div>
                        <h2 class="ts-profile-name"><?php esc_html_e('Build your Thriving Studio profile', 'thrivingstudio'); ?></h2>
                        <p class="ts-profile-guest-copy">
                            <?php esc_html_e('Sign in to keep your avatar, comments, and reader identity connected across the site.', 'thrivingstudio'); ?>
                        </p>
                    </div>
                    <a class="ts-account-login ts-account-login--profile" href="<?php echo esc_url(thrivingstudio_get_account_login_url(thrivingstudio_get_account_profile_url())); ?>">
                        <?php echo esc_html(thrivingstudio_get_account_login_label()); ?>
                    </a>
                </div>

                <div class="ts-profile-dashboard ts-profile-dashboard-guest">
                    <section class="ts-profile-panel">
                        <div class="ts-profile-panel-header">
                            <h2><?php esc_html_e('Identity', 'thrivingstudio'); ?></h2>
                            <span><?php esc_html_e('Ready', 'thrivingstudio'); ?></span>
                        </div>
                        <p><?php esc_html_e('Name, avatar, and comment identity stay consistent after sign in.', 'thrivingstudio'); ?></p>
                    </section>
                    <section class="ts-profile-panel">
                        <div class="ts-profile-panel-header">
                            <h2><?php esc_html_e('Activity', 'thrivingstudio'); ?></h2>
                            <span><?php esc_html_e('Growing', 'thrivingstudio'); ?></span>
                        </div>
                        <p><?php esc_html_e('Approved replies and topic signals can gather into one member view.', 'thrivingstudio'); ?></p>
                    </section>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php get_footer(); ?>
