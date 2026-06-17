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
    register_setting('thrivingstudio_seo_settings', 'thrivingstudio_seo_options', [
        'sanitize_callback' => 'thrivingstudio_sanitize_seo_options',
    ]);

    add_settings_section(
        'thrivingstudio_seo_search_appearance',
        'Search Appearance',
        'thrivingstudio_seo_search_appearance_callback',
        'thrivingstudio-seo-settings'
    );

    add_settings_field(
        'thrivingstudio_seo_homepage_title',
        'Homepage SEO Title',
        'thrivingstudio_seo_homepage_title_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_search_appearance'
    );

    add_settings_field(
        'thrivingstudio_seo_homepage_description',
        'Homepage Meta Description',
        'thrivingstudio_seo_homepage_description_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_search_appearance'
    );

    add_settings_field(
        'thrivingstudio_seo_default_description',
        'Default Meta Description',
        'thrivingstudio_seo_default_description_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_search_appearance'
    );

    add_settings_field(
        'thrivingstudio_seo_site_alternate_name',
        'Alternate Site Name',
        'thrivingstudio_seo_site_alternate_name_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_search_appearance'
    );

    add_settings_section(
        'thrivingstudio_seo_social_preview',
        'Social Preview',
        'thrivingstudio_seo_social_preview_callback',
        'thrivingstudio-seo-settings'
    );

    add_settings_field(
        'thrivingstudio_seo_social_title',
        'Homepage Social Title',
        'thrivingstudio_seo_social_title_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_social_preview'
    );

    add_settings_field(
        'thrivingstudio_seo_social_description',
        'Homepage Social Description',
        'thrivingstudio_seo_social_description_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_social_preview'
    );

    add_settings_field(
        'thrivingstudio_seo_social_image',
        'Default Social Image URL',
        'thrivingstudio_seo_social_image_callback',
        'thrivingstudio-seo-settings',
        'thrivingstudio_seo_social_preview'
    );

    add_settings_section(
        'thrivingstudio_seo_general',
        'Technical SEO',
        'thrivingstudio_seo_general_callback',
        'thrivingstudio-seo-settings'
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
 * Sanitize SEO settings before saving.
 *
 * @param array $input
 * @return array
 */
function thrivingstudio_sanitize_seo_options($input) {
    $input = is_array($input) ? $input : [];
    $output = [];

    $output['homepage_title'] = sanitize_text_field($input['homepage_title'] ?? '');
    $output['homepage_description'] = sanitize_textarea_field($input['homepage_description'] ?? '');
    $output['default_description'] = sanitize_textarea_field($input['default_description'] ?? '');
    $output['site_alternate_name'] = sanitize_text_field($input['site_alternate_name'] ?? '');
    $output['social_title'] = sanitize_text_field($input['social_title'] ?? '');
    $output['social_description'] = sanitize_textarea_field($input['social_description'] ?? '');
    $output['social_image'] = esc_url_raw($input['social_image'] ?? '');
    $output['google_search_console'] = sanitize_text_field($input['google_search_console'] ?? '');

    $social_media = is_array($input['social_media'] ?? null) ? $input['social_media'] : [];
    $output['social_media'] = [
        'facebook' => esc_url_raw($social_media['facebook'] ?? ''),
        'twitter' => sanitize_text_field($social_media['twitter'] ?? ''),
        'instagram' => esc_url_raw($social_media['instagram'] ?? ''),
        'linkedin' => esc_url_raw($social_media['linkedin'] ?? ''),
    ];

    $structured_data = is_array($input['structured_data'] ?? null) ? $input['structured_data'] : [];
    $organization_type = sanitize_text_field($structured_data['organization_type'] ?? 'Organization');
    $allowed_organization_types = ['Organization', 'LocalBusiness', 'Corporation', 'CreativeWork'];

    $output['structured_data'] = [
        'organization_type' => in_array($organization_type, $allowed_organization_types, true) ? $organization_type : 'Organization',
        'logo_url' => esc_url_raw($structured_data['logo_url'] ?? ''),
        'contact_email' => sanitize_email($structured_data['contact_email'] ?? ''),
        'contact_phone' => sanitize_text_field($structured_data['contact_phone'] ?? ''),
    ];

    return $output;
}

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
 * Get SEO settings options for admin fields.
 *
 * @return array
 */
function thrivingstudio_get_seo_settings_options() {
    if (function_exists('thrivingstudio_get_seo_options')) {
        return thrivingstudio_get_seo_options();
    }

    $options = get_option('thrivingstudio_seo_options', []);

    return is_array($options) ? $options : [];
}

/**
 * Get an editable admin value, falling back to the resolved site default.
 *
 * @param array  $options
 * @param string $key
 * @param string $fallback
 * @return string
 */
function thrivingstudio_get_seo_settings_field_value($options, $key, $fallback) {
    $value = $options[$key] ?? '';

    return $value !== '' ? $value : $fallback;
}

/**
 * Search appearance section callback
 */
function thrivingstudio_seo_search_appearance_callback() {
    echo '<p>Control how the homepage and fallback site details are described to search engines.</p>';
}

/**
 * Social preview section callback
 */
function thrivingstudio_seo_social_preview_callback() {
    echo '<p>Set the default title, description, and image used when the homepage is shared on social platforms.</p>';
}

/**
 * General section callback
 */
function thrivingstudio_seo_general_callback() {
    echo '<p>Configure verification, social profiles, and structured data used by search engines.</p>';
}

/**
 * Homepage title field callback
 */
function thrivingstudio_seo_homepage_title_callback() {
    $options = thrivingstudio_get_seo_settings_options();
    $default_title = function_exists('thrivingstudio_get_homepage_title') ? thrivingstudio_get_homepage_title() : 'Thriving Studio | Clarity Over Noise';
    $homepage_title = thrivingstudio_get_seo_settings_field_value($options, 'homepage_title', $default_title);
    ?>
    <input
        type="text"
        name="thrivingstudio_seo_options[homepage_title]"
        value="<?php echo esc_attr($homepage_title); ?>"
        class="regular-text"
        maxlength="70"
    />
    <p class="description">Recommended: 50-60 characters. Current default: <code><?php echo esc_html($default_title); ?></code></p>
    <?php
}

/**
 * Homepage description field callback
 */
function thrivingstudio_seo_homepage_description_callback() {
    $options = thrivingstudio_get_seo_settings_options();
    $default_description = function_exists('thrivingstudio_get_homepage_meta_description')
        ? thrivingstudio_get_homepage_meta_description()
        : 'Thriving Studio helps you cut through noise with clear, thoughtful ideas for inner growth, deeper understanding, and what truly matters.';
    $homepage_description = thrivingstudio_get_seo_settings_field_value($options, 'homepage_description', $default_description);
    ?>
    <textarea
        name="thrivingstudio_seo_options[homepage_description]"
        rows="3"
        cols="50"
        maxlength="220"
        style="width: 100%; max-width: 720px;"
    ><?php echo esc_textarea($homepage_description); ?></textarea>
    <p class="description">Recommended: clear, page-specific, and around 140-160 characters.</p>
    <?php
}

/**
 * Alternate site name field callback
 */
function thrivingstudio_seo_site_alternate_name_callback() {
    $options = thrivingstudio_get_seo_settings_options();
    $default_alternate_name = function_exists('thrivingstudio_get_site_alternate_name') ? thrivingstudio_get_site_alternate_name() : 'ThrivingStudio';
    $alternate_name = thrivingstudio_get_seo_settings_field_value($options, 'site_alternate_name', $default_alternate_name);
    ?>
    <input
        type="text"
        name="thrivingstudio_seo_options[site_alternate_name]"
        value="<?php echo esc_attr($alternate_name); ?>"
        class="regular-text"
    />
    <p class="description">Optional alternate name used in site-name structured data.</p>
    <?php
}

/**
 * Default description field callback
 */
function thrivingstudio_seo_default_description_callback() {
    $options = thrivingstudio_get_seo_settings_options();
    $fallback_description = function_exists('thrivingstudio_get_default_meta_description')
        ? thrivingstudio_get_default_meta_description()
        : 'Thriving Studio helps you cut through noise with clear, thoughtful ideas for inner growth, deeper understanding, and what truly matters.';
    $default_description = thrivingstudio_get_seo_settings_field_value($options, 'default_description', $fallback_description);
    ?>
    <textarea
        name="thrivingstudio_seo_options[default_description]"
        rows="3"
        cols="50"
        maxlength="220"
        style="width: 100%; max-width: 720px;"
    ><?php echo esc_textarea($default_description); ?></textarea>
    <p class="description">Fallback meta description for pages without their own description.</p>
    <?php
}

/**
 * Social title field callback
 */
function thrivingstudio_seo_social_title_callback() {
    $options = thrivingstudio_get_seo_settings_options();
    $social_title = $options['social_title'] ?? '';
    $placeholder = function_exists('thrivingstudio_get_homepage_title') ? thrivingstudio_get_homepage_title() : 'Thriving Studio | Clarity Over Noise';
    ?>
    <input
        type="text"
        name="thrivingstudio_seo_options[social_title]"
        value="<?php echo esc_attr($social_title); ?>"
        class="regular-text"
        maxlength="90"
        placeholder="<?php echo esc_attr($placeholder); ?>"
    />
    <p class="description">Leave empty to use the homepage SEO title.</p>
    <?php
}

/**
 * Social description field callback
 */
function thrivingstudio_seo_social_description_callback() {
    $options = thrivingstudio_get_seo_settings_options();
    $social_description = $options['social_description'] ?? '';
    ?>
    <textarea
        name="thrivingstudio_seo_options[social_description]"
        rows="3"
        cols="50"
        maxlength="220"
        style="width: 100%; max-width: 720px;"
        placeholder="Thriving Studio helps you cut through noise with clear, thoughtful ideas for inner growth, deeper understanding, and what truly matters."
    ><?php echo esc_textarea($social_description); ?></textarea>
    <p class="description">Leave empty to use the homepage meta description.</p>
    <?php
}

/**
 * Social image field callback
 */
function thrivingstudio_seo_social_image_callback() {
    $options = thrivingstudio_get_seo_settings_options();
    $social_image = $options['social_image'] ?? '';
    $default_image = get_template_directory_uri() . '/assets/images/default-og-image.jpg';
    ?>
    <input
        type="url"
        name="thrivingstudio_seo_options[social_image]"
        value="<?php echo esc_attr($social_image); ?>"
        class="regular-text"
        placeholder="<?php echo esc_url($default_image); ?>"
    />
    <p class="description">Recommended size: 1200x630. Leave empty to use the theme default social image.</p>
    <?php
}

/**
 * Google Search Console field callback
 */
function thrivingstudio_seo_google_search_console_callback() {
    $options = thrivingstudio_get_seo_settings_options();
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
    $options = thrivingstudio_get_seo_settings_options();
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
    $options = thrivingstudio_get_seo_settings_options();
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
    $options = thrivingstudio_get_seo_settings_options();
    $gsc_verification = $options['google_search_console'] ?? '';
    
    if (!empty($gsc_verification)) {
        echo '<meta name="google-site-verification" content="' . esc_attr($gsc_verification) . '" />' . "\n";
    }
}
add_action('wp_head', 'thrivingstudio_add_google_search_console', 1);
