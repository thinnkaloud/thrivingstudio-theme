<?php

/**
 * Register theme customizer settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize
 */
function thrivingstudio_customize_register($wp_customize) {
    // Logo Section
    $wp_customize->add_section('thrivingstudio_logo_section', [
        'title' => __('Logo Settings', 'thrivingstudio'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('thrivingstudio_logo_text', [
        'default' => 'Thriving Studio',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('thrivingstudio_logo_text', [
        'label' => __('Logo Text', 'thrivingstudio'),
        'section' => 'thrivingstudio_logo_section',
        'type' => 'text',
    ]);

    // Colors Section
    $wp_customize->add_setting('thrivingstudio_primary_color', [
        'default' => '#3b82f6',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'thrivingstudio_primary_color', [
        'label' => __('Primary Color', 'thrivingstudio'),
        'section' => 'colors',
    ]));

    // Social Media Section
    $wp_customize->add_section('thrivingstudio_social_section', [
        'title' => __('Social Media', 'thrivingstudio'),
        'priority' => 40,
    ]);

    // Register a single setting for all social profiles (array of [platform, url])
    $wp_customize->add_setting('thrivingstudio_social_profiles', [
        'default' => json_encode([]),
        'sanitize_callback' => function($input) {
            $arr = json_decode($input, true);
            if (!is_array($arr)) {
                return json_encode([]);
            }
            // Validate each entry
            $valid = array_filter($arr, function($item) {
                return isset($item['platform'], $item['url']) && filter_var($item['url'], FILTER_VALIDATE_URL);
            });
            return json_encode(array_values($valid));
        },
        'transport' => 'refresh',
    ]);

    // Custom control class (JS will handle UI)
    if (class_exists('WP_Customize_Control')) {
        class ThrivingStudio_Sortable_Social_Profiles_Control extends WP_Customize_Control {
            public $type = 'sortable_social_profiles';

            public function render_content() {
                ?>
                <label><span class="customize-control-title"><?php echo esc_html($this->label); ?></span></label>
                <div id="sortable-social-profiles" data-setting="<?php echo esc_attr($this->id); ?>"></div>
                <?php
            }
        }
        $wp_customize->add_control(new ThrivingStudio_Sortable_Social_Profiles_Control($wp_customize, 'thrivingstudio_social_profiles', [
            'label' => __('Social Profiles', 'thrivingstudio'),
            'section' => 'thrivingstudio_social_section',
            'settings' => 'thrivingstudio_social_profiles',
        ]));
    }
    // Enqueue JS for the control
    add_action('customize_controls_enqueue_scripts', function() {
        wp_enqueue_script('thrivingstudio-customizer-social', get_template_directory_uri() . '/assets/js/customizer-social.js', ['jquery', 'jquery-ui-sortable'], THRIVINGSTUDIO_VERSION, true);
    });

    // Footer Section
    $wp_customize->add_section('thrivingstudio_footer_section', [
        'title' => __('Footer Settings', 'thrivingstudio'),
        'priority' => 50,
    ]);

    $wp_customize->add_setting('thrivingstudio_footer_text', [
        'default' => '© ' . date('Y') . ' Thriving Studio. All rights reserved.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);

    $wp_customize->add_control('thrivingstudio_footer_text', [
        'label' => __('Footer Text', 'thrivingstudio'),
        'section' => 'thrivingstudio_footer_section',
        'type' => 'textarea',
    ]);

    // Homepage Featured Categories Section
    $wp_customize->add_section('thrivingstudio_featured_categories_section', [
        'title' => __('Homepage Featured Categories', 'thrivingstudio'),
        'priority' => 35,
    ]);

    if (class_exists('WP_Customize_Control')) {
        class ThrivingStudio_Featured_Category_Dropdown_Control extends WP_Customize_Control {
            public $type = 'featured_category_dropdown';

            public function render_content() {
                $categories = get_categories(['hide_empty' => false]);
                ?>
                <label><span class="customize-control-title"><?php echo esc_html($this->label); ?></span></label>
                <select <?php $this->link(); ?> style="width:100%;max-width:300px;">
                    <option value="0">— None —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($this->value(), $cat->term_id); ?>><?php echo esc_html($cat->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php
            }
        }
        for ($i = 1; $i <= 4; $i++) {
            $wp_customize->add_setting("thrivingstudio_featured_category_$i", [
                'default' => '',
                'sanitize_callback' => 'absint',
            ]);
            $wp_customize->add_control(new ThrivingStudio_Featured_Category_Dropdown_Control($wp_customize, "thrivingstudio_featured_category_$i", [
                'label' => sprintf(__('Featured Category #%d', 'thrivingstudio'), $i),
                'section' => 'thrivingstudio_featured_categories_section',
                'settings' => "thrivingstudio_featured_category_$i",
            ]));
            // Add description field
            $wp_customize->add_setting("thrivingstudio_featured_category_{$i}_desc", [
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ]);
            $wp_customize->add_control("thrivingstudio_featured_category_{$i}_desc", [
                'label' => sprintf(__('Featured Category #%d Description', 'thrivingstudio'), $i),
                'section' => 'thrivingstudio_featured_categories_section',
                'type' => 'text',
            ]);
        }
    }

    // Homepage Social Stats Section (separate from social profiles)
    $wp_customize->add_section('thrivingstudio_homepage_social_stats_section', [
        'title' => __('Homepage Social Stats', 'thrivingstudio'),
        'priority' => 36,
    ]);

    // Social Stats Section Title
    $wp_customize->add_setting('thrivingstudio_home_social_stats_title', [
        'default'           => __('Our Social Circle', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_home_social_stats_title', [
        'label'    => __('Social Stats Section Title', 'thrivingstudio'),
        'section'  => 'thrivingstudio_homepage_social_stats_section',
        'type'     => 'text',
    ]);

    // Social Media Follower Counts
    $socials = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'pinterest' => 'Pinterest',
        'youtube' => 'YouTube',
    ];
    foreach ($socials as $key => $label) {
        $wp_customize->add_setting("thrivingstudio_home_social_{$key}_count", [
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control("thrivingstudio_home_social_{$key}_count", [
            'label' => sprintf(__('%s Followers', 'thrivingstudio'), $label),
            'section' => 'thrivingstudio_homepage_social_stats_section',
            'type' => 'text',
        ]);
    }

    // Homepage Quotes Section Title
    $wp_customize->add_section('thrivingstudio_home_quotes_section', [
        'title'    => __('Homepage Quotes Section', 'thrivingstudio'),
        'priority' => 37,
    ]);
    $wp_customize->add_setting('thrivingstudio_home_quotes_title', [
        'default'           => __('Inspirational Quotes', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_home_quotes_title', [
        'label'    => __('Quotes Section Title', 'thrivingstudio'),
        'section'  => 'thrivingstudio_home_quotes_section',
        'type'     => 'text',
    ]);

    // Header CTA Section for Get In Touch button
    $wp_customize->add_section('thrivingstudio_header_cta_section', [
        'title'    => __('Header CTA', 'thrivingstudio'),
        'priority' => 31,
    ]);
    $wp_customize->add_setting('thrivingstudio_header_cta_text', [
        'default'           => __('Get In Touch', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_header_cta_text', [
        'label'    => __('CTA Button Text', 'thrivingstudio'),
        'section'  => 'thrivingstudio_header_cta_section',
        'type'     => 'text',
    ]);
    $wp_customize->add_setting('thrivingstudio_header_cta_link', [
        'default'           => '#',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('thrivingstudio_header_cta_link', [
        'label'    => __('CTA Button Link', 'thrivingstudio'),
        'section'  => 'thrivingstudio_header_cta_section',
        'type'     => 'url',
    ]);

    // Homepage Hero Section
    $wp_customize->add_section('thrivingstudio_homepage_hero_section', [
        'title'    => __('Homepage Hero Section', 'thrivingstudio'),
        'priority' => 30,
    ]);
    $wp_customize->add_setting('thrivingstudio_home_hero_title', [
        'default'           => __('Welcome to <span class="text-black">Thriving Studio</span>', 'thrivingstudio'),
        'sanitize_callback' => 'wp_kses_post',
    ]);
    $wp_customize->add_control('thrivingstudio_home_hero_title', [
        'label'    => __('Hero Title (HTML allowed)', 'thrivingstudio'),
        'section'  => 'thrivingstudio_homepage_hero_section',
        'type'     => 'text',
    ]);
    $wp_customize->add_setting('thrivingstudio_home_hero_subtitle', [
        'default'           => __('Deep insights, visual storytelling, and timeless ideas for a thriving creative life.', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_home_hero_subtitle', [
        'label'    => __('Hero Subtitle', 'thrivingstudio'),
        'section'  => 'thrivingstudio_homepage_hero_section',
        'type'     => 'text',
    ]);
    $wp_customize->add_setting('thrivingstudio_home_hero_button_text', [
        'default'           => __('Learn More', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_home_hero_button_text', [
        'label'    => __('Hero Button Text', 'thrivingstudio'),
        'section'  => 'thrivingstudio_homepage_hero_section',
        'type'     => 'text',
    ]);
    $wp_customize->add_setting('thrivingstudio_home_hero_button_link', [
        'default'           => '#',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('thrivingstudio_home_hero_button_link', [
        'label'    => __('Hero Button Link', 'thrivingstudio'),
        'section'  => 'thrivingstudio_homepage_hero_section',
        'type'     => 'url',
    ]);

    // Top Bar Notification Section
    $wp_customize->add_section('thrivingstudio_topbar_section', [
        'title' => __('Top Bar Notification', 'thrivingstudio'),
        'priority' => 5,
    ]);
    $wp_customize->add_setting('thrivingstudio_topbar_message', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_topbar_message', [
        'label' => __('Top Bar Message', 'thrivingstudio'),
        'section' => 'thrivingstudio_topbar_section',
        'type' => 'text',
        'description' => __('This message will appear in a yellow bar above the header. Leave empty to hide.', 'thrivingstudio'),
    ]);
    $wp_customize->add_setting('thrivingstudio_topbar_show', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_topbar_show', [
        'label' => __('Show Top Bar', 'thrivingstudio'),
        'section' => 'thrivingstudio_topbar_section',
        'type' => 'checkbox',
        'description' => __('Toggle to show or hide the top bar notification section.', 'thrivingstudio'),
    ]);
}
add_action('customize_register', 'thrivingstudio_customize_register');
