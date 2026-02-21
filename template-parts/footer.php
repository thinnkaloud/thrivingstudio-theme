    </div><!-- #site-content -->
</div><!-- #site-wrapper -->

<!-- AdSense ads managed by Site Kit -->

<footer class="bg-white border-t border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between py-6">
            <!-- Left Side: Copyright -->
            <div class="text-sm text-gray-500 mb-4 md:mb-0">
                <?php echo wp_kses_post(get_theme_mod('thrivingstudio_footer_text', '© ' . date('Y') . ' Thriving Studio. All Rights Reserved.')); ?>
            </div>

            <!-- Right Side: Menu and Socials -->
            <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-8">
                <?php
                if (has_nav_menu('footer')) {
                    // Custom Walker for footer menu links.
                    if (!class_exists('ThrivingStudio_Footer_Menu_Walker')) {
                        class ThrivingStudio_Footer_Menu_Walker extends Walker_Nav_Menu {
                            public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
                                $classes = empty($item->classes) ? [] : (array) $item->classes;
                                $classes[] = 'menu-item-' . $item->ID;
                                
                                $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
                                $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
                                
                                $item_id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
                                $item_id = $item_id ? ' id="' . esc_attr($item_id) . '"' : '';
                                
                                $output .= '<li' . $item_id . $class_names . '>';
                                
                                $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
                                $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
                                
                                $rel = !empty($item->xfn) ? $item->xfn : '';
                                if (!empty($item->target) && '_blank' === $item->target) {
                                    $rel = trim($rel . ' noopener noreferrer');
                                }
                                $attributes .= !empty($rel) ? ' rel="' . esc_attr($rel) . '"' : '';
                                $attributes .= !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
                                $attributes .= ' class="footer-menu-link"';
                                
                                $item_output = $args->before;
                                $item_output .= '<a' . $attributes . '>';
                                $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
                                $item_output .= '</a>';
                                $item_output .= $args->after;
                                
                                $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
                            }
                        }
                    }

                    wp_nav_menu([
                        'theme_location' => 'footer',
                        'menu_class' => 'footer-menu-list flex space-x-4 text-sm text-gray-500',
                        'container' => false,
                        'depth' => 1,
                        'walker' => new ThrivingStudio_Footer_Menu_Walker(),
                    ]);
                }
                ?>
                <div class="flex items-center space-x-4">
                    <!-- Social media icons only - no text labels -->
                    <?php
                    $icon_svgs = [
                        'facebook' => '<svg class="h-6 w-6 social-icon" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>',
                        'instagram' => '<svg class="h-6 w-6 social-icon" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>',
                        'youtube' => '<svg class="h-6 w-6 social-icon" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a2.994 2.994 0 0 0-2.112-2.116C19.454 3.5 12 3.5 12 3.5s-7.454 0-9.386.57A2.994 2.994 0 0 0 .502 6.186C0 8.12 0 12 0 12s0 3.88.502 5.814a2.994 2.994 0 0 0 2.112 2.116C4.546 20.5 12 20.5 12 20.5s7.454 0 9.386-.57a2.994 2.994 0 0 0 2.112-2.116C24 15.88 24 12 24 12s0-3.88-.502-5.814zM9.75 15.02V8.98l6.5 3.02-6.5 3.02z"/></svg>',
                        'pinterest' => '<svg class="h-6 w-6 social-icon" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.396 7.633 11.093-.106-.943-.202-2.39.042-3.419.221-.96 1.423-6.122 1.423-6.122s-.363-.726-.363-1.797c0-1.684.977-2.943 2.192-2.943 1.033 0 1.532.775 1.532 1.705 0 1.04-.662 2.594-1.003 4.037-.286 1.207.607 2.192 1.8 2.192 2.16 0 3.82-2.278 3.82-5.563 0-2.91-2.093-4.945-5.083-4.945-3.468 0-5.504 2.6-5.504 5.287 0 1.05.404 2.177.91 2.788.1.12.114.225.083.346-.09.36-.293 1.144-.333 1.303-.05.2-.162.242-.376.146-1.4-.573-2.273-2.37-2.273-3.818 0-3.108 2.26-6.684 6.755-6.684 3.548 0 6.308 2.527 6.308 5.899 0 3.522-2.217 6.36-5.297 6.36-1.06 0-2.057-.552-2.397-1.176l-.652 2.482c-.197.755-.583 1.7-.868 2.277.653.202 1.342.312 2.062.312 6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>',
                    ];
                    $profiles_json = get_theme_mod('thrivingstudio_social_profiles', '[]');
                    $profiles = json_decode($profiles_json, true);
                    if (is_array($profiles)) {
                        foreach ($profiles as $profile) {
                            $platform = $profile['platform'] ?? '';
                            $url = $profile['url'] ?? '';
                            if ($platform && $url && isset($icon_svgs[$platform])) {
                                echo '<a href="' . esc_url($url) . '" class="footer-social-link footer-social-' . esc_attr($platform) . '" aria-label="' . esc_attr(ucfirst($platform)) . '" target="_blank" rel="noopener noreferrer">';
                                echo $icon_svgs[$platform];
                                echo '</a>';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
