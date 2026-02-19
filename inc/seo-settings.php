<?php
/**
 * SEO Settings Page
 * WordPress admin settings for SEO configuration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add SEO settings menu
 */
function thrivingstudio_seo_settings_menu() {
    add_options_page(
        'SEO Settings',
        'SEO Settings',
        'manage_options',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_settings_page'
    );
}
add_action('admin_menu', 'thrivingstudio_seo_settings_menu');

/**
 * Register SEO settings
 */
function thrivingstudio_register_seo_settings() {
    register_setting('thrivingstudio_seo_settings', 'thrivingstudio_seo_options');

    add_settings_section(
        'thrivingstudio_seo_general',
        'General SEO Settings',
        'thrivingstudio_seo_general_callback',
        'thrivingstudio-seo-settings'
    );

    add_settings_field(
        'thrivingstudio_seo_default_description',
        'Default Meta Description',
        'thrivingstudio_seo_default_description_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_general'
    );

    add_settings_field(
        'thrivingstudio_seo_google_search_console',
        'Google Search Console Verification',
        'thrivingstudio_seo_google_search_console_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_general'
    );

    add_settings_field(
        'thrivingstudio_seo_social_media',
        'Social Media Handles',
        'thrivingstudio_seo_social_media_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_general'
    );

    add_settings_field(
        'thrivingstudio_seo_structured_data',
        'Structured Data Settings',
        'thrivingstudio_seo_structured_data_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_general'
    );
}
add_action('admin_init', 'thrivingstudio_register_seo_settings');

/**
 * SEO settings page callback
 */
function thrivingstudio_seo_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    if (isset($_GET['settings-updated'])) {
        add_settings_error(
            'thrivingstudio_seo_messages',
            'thrivingstudio_seo_message',
            'Settings Saved',
            'updated'
        );
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <?php settings_errors('thrivingstudio_seo_messages'); ?>

        <form action="options.php" method="post">
            <?php
            settings_fields('thrivingstudio_seo_settings');
            do_settings_sections('thrivingstudio-seo-settings');
            submit_button('Save Settings');
            ?>
        </form>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>SEO Status</h2>
            <table class="form-table">
                <tr>
                    <th>XML Sitemap</th>
                    <td>
                        <?php
                        $sitemap_url = home_url('/sitemap.xml');
                        if (file_exists(ABSPATH . 'sitemap.xml')) {
                            echo '<span style="color: green;">✓ Available at: <a href="' . esc_url($sitemap_url) . '" target="_blank">' . esc_url($sitemap_url) . '</a></span>';
                        } else {
                            echo '<span style="color: red;">✗ Not found. Create a post or page to generate sitemap.</span>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Robots.txt</th>
                    <td>
                        <?php
                        $robots_url = home_url('/robots.txt');
                        if (file_exists(ABSPATH . 'robots.txt')) {
                            echo '<span style="color: green;">✓ Available at: <a href="' . esc_url($robots_url) . '" target="_blank">' . esc_url($robots_url) . '</a></span>';
                        } else {
                            echo '<span style="color: red;">✗ Not found</span>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>SSL Certificate</th>
                    <td>
                        <?php
                        if (is_ssl()) {
                            echo '<span style="color: green;">✓ HTTPS enabled</span>';
                        } else {
                            echo '<span style="color: orange;">⚠ HTTP (recommend enabling HTTPS)</span>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>Quick Actions</h2>
            <p>
                <a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank" class="button">View Sitemap</a>
                <a href="<?php echo esc_url(home_url('/robots.txt')); ?>" target="_blank" class="button">View Robots.txt</a>
                <a href="https://search.google.com/search-console" target="_blank" class="button">Google Search Console</a>
                <a href="https://analytics.google.com/" target="_blank" class="button">Google Analytics</a>
            </p>
        </div>
    </div>
    <?php
}

/**
 * General section callback
 */
function thrivingstudio_seo_general_callback() {
    echo '<p>Configure basic SEO settings for your website.</p>';
}

/**
 * Default description field callback
 */
function thrivingstudio_seo_default_description_callback() {
    $options = get_option('thrivingstudio_seo_options', []);
    $default_description = isset($options['default_description']) ? $options['default_description'] : '';
    ?>
    <textarea name="thrivingstudio_seo_options[default_description]" rows="3" cols="50" style="width: 100%;"><?php echo esc_textarea($default_description); ?></textarea>
    <p class="description">Default meta description for pages without custom descriptions. Leave empty to use site description.</p>
    <?php
}

/**
 * Google Search Console field callback
 */
function thrivingstudio_seo_google_search_console_callback() {
    $options = get_option('thrivingstudio_seo_options', []);
    $gsc_verification = isset($options['google_search_console']) ? $options['google_search_console'] : '';
    ?>
    <input type="text" name="thrivingstudio_seo_options[google_search_console]" value="<?php echo esc_attr($gsc_verification); ?>" class="regular-text" />
    <p class="description">Enter your Google Search Console verification code</p>
    <?php
}

/**
 * Social media field callback
 */
function thrivingstudio_seo_social_media_callback() {
    $options = get_option('thrivingstudio_seo_options', []);
    $social_media = isset($options['social_media']) ? $options['social_media'] : [];
    ?>
    <table class="form-table">
        <tr>
            <th>Facebook</th>
            <td><input type="url" name="thrivingstudio_seo_options[social_media][facebook]" value="<?php echo esc_attr($social_media['facebook'] ?? ''); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th>Twitter</th>
            <td><input type="text" name="thrivingstudio_seo_options[social_media][twitter]" value="<?php echo esc_attr($social_media['twitter'] ?? ''); ?>" class="regular-text" placeholder="@username" /></td>
        </tr>
        <tr>
            <th>Instagram</th>
            <td><input type="url" name="thrivingstudio_seo_options[social_media][instagram]" value="<?php echo esc_attr($social_media['instagram'] ?? ''); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th>LinkedIn</th>
            <td><input type="url" name="thrivingstudio_seo_options[social_media][linkedin]" value="<?php echo esc_attr($social_media['linkedin'] ?? ''); ?>" class="regular-text" /></td>
        </tr>
    </table>
    <?php
}

/**
 * Structured data field callback
 */
function thrivingstudio_seo_structured_data_callback() {
    $options = get_option('thrivingstudio_seo_options', []);
    $structured_data = isset($options['structured_data']) ? $options['structured_data'] : [];
    ?>
    <table class="form-table">
        <tr>
            <th>Organization Type</th>
            <td>
                <select name="thrivingstudio_seo_options[structured_data][organization_type]">
                    <option value="Organization" <?php selected($structured_data['organization_type'] ?? '', 'Organization'); ?>>Organization</option>
                    <option value="LocalBusiness" <?php selected($structured_data['organization_type'] ?? '', 'LocalBusiness'); ?>>Local Business</option>
                    <option value="Corporation" <?php selected($structured_data['organization_type'] ?? '', 'Corporation'); ?>>Corporation</option>
                    <option value="CreativeWork" <?php selected($structured_data['organization_type'] ?? '', 'CreativeWork'); ?>>Creative Work</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>Logo URL</th>
            <td><input type="url" name="thrivingstudio_seo_options[structured_data][logo_url]" value="<?php echo esc_attr($structured_data['logo_url'] ?? ''); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th>Contact Email</th>
            <td><input type="email" name="thrivingstudio_seo_options[structured_data][contact_email]" value="<?php echo esc_attr($structured_data['contact_email'] ?? ''); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th>Contact Phone</th>
            <td><input type="tel" name="thrivingstudio_seo_options[structured_data][contact_phone]" value="<?php echo esc_attr($structured_data['contact_phone'] ?? ''); ?>" class="regular-text" /></td>
        </tr>
    </table>
    <?php
}

/**
 * Add Google Search Console verification
 */
function thrivingstudio_add_google_search_console() {
    $options = get_option('thrivingstudio_seo_options', []);
    $gsc_verification = $options['google_search_console'] ?? '';
    
    if (!empty($gsc_verification)) {
        echo '<meta name="google-site-verification" content="' . esc_attr($gsc_verification) . '" />' . "\n";
    }
}
add_action('wp_head', 'thrivingstudio_add_google_search_console', 1); 