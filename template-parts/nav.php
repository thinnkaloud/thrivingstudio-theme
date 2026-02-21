<?php
if (has_nav_menu('primary')) {
    // Check if we are rendering the mobile menu
    $is_mobile = isset($args['is_mobile']) && $args['is_mobile'];

    $menu_class = 'primary-menu';
    if($is_mobile) {
        // Mobile menu uses a vertical layout
        $menu_class .= ' space-y-1';
    } else {
        // Desktop menu is horizontal with consistent spacing
        $menu_class .= ' flex items-baseline w-full justify-center';
    }

    wp_nav_menu([
        'theme_location' => 'primary',
        'container'      => '',
        'menu_class'     => $menu_class,
        'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'walker'         => new class extends Walker_Nav_Menu {
            private $item_count = 0;
            
            public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
                $this->item_count++;
                $is_mobile = isset($args->is_mobile) && $args->is_mobile;
                
                // Add Blog after the first item (Home) for both desktop and mobile
                if ($this->item_count === 2) {
                    $blog_url = get_option('page_for_posts') ? get_permalink(get_option('page_for_posts')) : home_url('/blog/');
                    $is_blog = is_home() || (is_archive() && !is_category() && !is_tag() && !is_tax());
                    $blog_active_class = $is_blog ? ' current-menu-item' : '';
                    
                    if ($is_mobile) {
                        $output .= '<li class="menu-item menu-item-type-post_type menu-item-object-page' . $blog_active_class . '">';
                        $output .= '<a href="' . esc_url($blog_url) . '" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Blog</a>';
                        $output .= '</li>';
                    } else {
                        $output .= '<li class="menu-item menu-item-type-post_type menu-item-object-page primary-menu-item' . $blog_active_class . '">';
                        $output .= '<a href="' . esc_url($blog_url) . '" class="text-base font-medium text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">Blog</a>';
                        $output .= '</li>';
                    }
                }
                $is_mobile = isset($args->is_mobile) && $args->is_mobile;
                $classes = empty($item->classes) ? array() : (array) $item->classes;
                $classes[] = 'menu-item-' . $item->ID;
                
                // Add current menu item classes - only for actual current page
                if (in_array('current-menu-item', $classes) || in_array('current_page_item', $classes)) {
                    $classes[] = 'current-menu-item';
                }
                
                // Ensure proper current-menu-item classes
                if ($item->title === 'Home' && is_front_page()) {
                    $classes[] = 'current-menu-item';
                } elseif ($item->title === 'Home' && (is_home() || is_archive())) {
                    // Remove current-menu-item from Home when on blog/archive pages
                    $classes = array_filter($classes, function($class) {
                        return $class !== 'current-menu-item' && $class !== 'current_page_item';
                    });
                }
                
                $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
                $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
                
                if (!$is_mobile) {
                    if ($class_names) {
                        $class_names = rtrim(substr($class_names, 0, -1)) . ' primary-menu-item"';
                    } else {
                        $class_names = ' class="primary-menu-item"';
                    }
                }
                $output .= '<li' . $class_names . '>';
                
                                        if ($is_mobile) {
                            $output .= '<a href="' . esc_url($item->url) . '" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">';
                        } else {
                            $output .= '<a href="' . esc_url($item->url) . '" class="text-base font-medium text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">';
                        }
                $output .= esc_html($item->title);
                $output .= '</a>';
            }
            public function end_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
                $output .= '</li>';
            }
        },
    ]);
}
?> 
