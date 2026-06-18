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

    // Single Post Settings
    $wp_customize->add_section('thrivingstudio_single_post_section', [
        'title' => __('Single Post Settings', 'thrivingstudio'),
        'priority' => 38,
    ]);

    $sanitize_choice = static function($input, $setting) {
        $input = sanitize_key($input);
        $control = $setting->manager->get_control($setting->id);
        $choices = $control && isset($control->choices) ? $control->choices : [];

        return array_key_exists($input, $choices) ? $input : $setting->default;
    };

    $sanitize_int_range = static function($input, $setting) {
        $input = (int) $input;
        $control = $setting->manager->get_control($setting->id);
        $attrs = $control && isset($control->input_attrs) ? $control->input_attrs : [];
        $min = isset($attrs['min']) ? (int) $attrs['min'] : $input;
        $max = isset($attrs['max']) ? (int) $attrs['max'] : $input;

        return max($min, min($max, $input));
    };

    $wp_customize->add_setting('thrivingstudio_single_layout', [
        'default' => 'content_rail',
        'sanitize_callback' => $sanitize_choice,
    ]);
    $wp_customize->add_control('thrivingstudio_single_layout', [
        'label' => __('Post layout', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'select',
        'choices' => [
            'content_rail' => __('Content + right rail', 'thrivingstudio'),
            'centered' => __('Centered article', 'thrivingstudio'),
            'wide' => __('Wide article', 'thrivingstudio'),
        ],
        'description' => __('The right rail layout can show widgets and the article outline beside the post on desktop.', 'thrivingstudio'),
    ]);

    $wp_customize->add_setting('thrivingstudio_single_content_width', [
        'default' => 48,
        'sanitize_callback' => $sanitize_int_range,
    ]);
    $wp_customize->add_control('thrivingstudio_single_content_width', [
        'label' => __('Content max width', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'range',
        'input_attrs' => [
            'min' => 38,
            'max' => 72,
            'step' => 1,
        ],
        'description' => __('Measured in rem units. Default: 48.', 'thrivingstudio'),
    ]);

    $wp_customize->add_setting('thrivingstudio_single_featured_image_position', [
        'default' => 'below_header',
        'sanitize_callback' => $sanitize_choice,
    ]);
    $wp_customize->add_control('thrivingstudio_single_featured_image_position', [
        'label' => __('Featured image', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'select',
        'choices' => [
            'below_header' => __('Below post header', 'thrivingstudio'),
            'above_title' => __('Above title', 'thrivingstudio'),
            'hidden' => __('Hidden', 'thrivingstudio'),
        ],
    ]);

    $wp_customize->add_setting('thrivingstudio_single_show_category', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_show_category', [
        'label' => __('Show category label', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_show_excerpt', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_show_excerpt', [
        'label' => __('Show manual excerpt', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_show_author_avatar', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_show_author_avatar', [
        'label' => __('Show author avatar', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_show_author_name', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_show_author_name', [
        'label' => __('Show author name', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_show_published_date', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_show_published_date', [
        'label' => __('Show published date', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_show_reading_time', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_show_reading_time', [
        'label' => __('Show reading time', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_show_updated_date', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_show_updated_date', [
        'label' => __('Show updated date when changed', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_title_size', [
        'default' => 44,
        'sanitize_callback' => $sanitize_int_range,
    ]);
    $wp_customize->add_control('thrivingstudio_single_title_size', [
        'label' => __('Title size', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'range',
        'input_attrs' => [
            'min' => 30,
            'max' => 64,
            'step' => 1,
        ],
        'description' => __('Measured in pixels. Default: 44.', 'thrivingstudio'),
    ]);

    $wp_customize->add_setting('thrivingstudio_single_excerpt_size', [
        'default' => 16,
        'sanitize_callback' => $sanitize_int_range,
    ]);
    $wp_customize->add_control('thrivingstudio_single_excerpt_size', [
        'label' => __('Excerpt size', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'range',
        'input_attrs' => [
            'min' => 14,
            'max' => 24,
            'step' => 1,
        ],
        'description' => __('Measured in pixels. Default: 16.', 'thrivingstudio'),
    ]);

    $wp_customize->add_setting('thrivingstudio_single_body_size', [
        'default' => 16,
        'sanitize_callback' => $sanitize_int_range,
    ]);
    $wp_customize->add_control('thrivingstudio_single_body_size', [
        'label' => __('Body text size', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'range',
        'input_attrs' => [
            'min' => 15,
            'max' => 22,
            'step' => 1,
        ],
        'description' => __('Measured in pixels. Default: 16.', 'thrivingstudio'),
    ]);

    $wp_customize->add_setting('thrivingstudio_single_body_line_height', [
        'default' => 166,
        'sanitize_callback' => $sanitize_int_range,
    ]);
    $wp_customize->add_control('thrivingstudio_single_body_line_height', [
        'label' => __('Body line height', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'range',
        'input_attrs' => [
            'min' => 145,
            'max' => 190,
            'step' => 1,
        ],
        'description' => __('Stored as a percentage. Default: 166.', 'thrivingstudio'),
    ]);

    $wp_customize->add_setting('thrivingstudio_single_show_rail_widgets', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_show_rail_widgets', [
        'label' => __('Show right rail widgets', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
        'description' => __('Widget content is managed in Appearance > Widgets > Single Post Right Rail.', 'thrivingstudio'),
    ]);

    $wp_customize->add_setting('thrivingstudio_single_rail_show_mobile', [
        'default' => false,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_rail_show_mobile', [
        'label' => __('Show right rail widgets on mobile', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
        'description' => __('Widget content is managed in Appearance > Widgets > Single Post Right Rail. Leave this off to keep those modules desktop-only.', 'thrivingstudio'),
    ]);

    $wp_customize->add_setting('thrivingstudio_single_show_related_posts', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_show_related_posts', [
        'label' => __('Show related posts', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_related_posts_count', [
        'default' => 3,
        'sanitize_callback' => $sanitize_int_range,
    ]);
    $wp_customize->add_control('thrivingstudio_single_related_posts_count', [
        'label' => __('Related posts count', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'range',
        'input_attrs' => [
            'min' => 2,
            'max' => 6,
            'step' => 1,
        ],
        'description' => __('Default: 3.', 'thrivingstudio'),
    ]);

    $wp_customize->add_setting('thrivingstudio_single_show_cta', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('thrivingstudio_single_show_cta', [
        'label' => __('Show post CTA block', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_cta_title', [
        'default' => __('Want More Practical Insights?', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_single_cta_title', [
        'label' => __('CTA title', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_cta_text', [
        'default' => __('Get focused ideas on psychology, discipline, and creative growth delivered to your inbox.', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    $wp_customize->add_control('thrivingstudio_single_cta_text', [
        'label' => __('CTA text', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'textarea',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_cta_primary_label', [
        'default' => __('Subscribe', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_single_cta_primary_label', [
        'label' => __('Primary CTA label', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_cta_primary_link', [
        'default' => home_url('/#subscribe'),
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('thrivingstudio_single_cta_primary_link', [
        'label' => __('Primary CTA link', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'url',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_cta_secondary_label', [
        'default' => __('Get in touch', 'thrivingstudio'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('thrivingstudio_single_cta_secondary_label', [
        'label' => __('Secondary CTA label', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('thrivingstudio_single_cta_secondary_link', [
        'default' => home_url('/contact'),
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('thrivingstudio_single_cta_secondary_link', [
        'label' => __('Secondary CTA link', 'thrivingstudio'),
        'section' => 'thrivingstudio_single_post_section',
        'type' => 'url',
    ]);
}
add_action('customize_register', 'thrivingstudio_customize_register');
