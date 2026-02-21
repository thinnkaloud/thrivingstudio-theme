<?php
$is_mobile = isset($args['is_mobile']) && $args['is_mobile'];

// Check if category menu location exists
$locations = get_nav_menu_locations();

if (isset($locations['category_menu'])) {
    $menu = wp_get_nav_menu_object($locations['category_menu']);
    $menu_items = $menu ? wp_get_nav_menu_items($menu->term_id) : [];
} else {
    $menu_items = [];
}

if ($is_mobile): 
    // Render for mobile hamburger menu
    ?>
    <div class="px-2 space-y-1">
        <?php
        if (!empty($menu_items)) {
            foreach ($menu_items as $item) {
                // Check if the current page is the same as the menu item
                $is_current_page = (get_queried_object_id() == $item->object_id);
                // Check if the current page is a post and belongs to the category of the menu item
                $is_in_category = is_singular('post') && has_category($item->object_id);
                $is_current = $is_current_page || $is_in_category;
                $active_class = $is_current ? ' bg-gray-100 dark:bg-gray-700' : '';
                echo '<a href="' . esc_url($item->url) . '" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' . $active_class . '">' . esc_html($item->title) . '</a>';
            }
        }
        ?>
    </div>
    <?php
else:
    // Original desktop rendering
    ?>
<div class="container mx-auto px-4 sm:px-6 lg:px-8 my-1">
    
    <nav aria-label="Category Menu">
        <ul class="category-menu-list ts-category-menu-list flex items-center justify-start text-sm font-medium border-b border-gray-200 dark:border-gray-700">
            <?php
                if (has_nav_menu('category_menu')) {
                    // Get the menu items
                    $locations = get_nav_menu_locations();
                    $menu = wp_get_nav_menu_object($locations['category_menu']);
                    $menu_items = wp_get_nav_menu_items($menu->term_id);
                    
                    // Collect all subcategory IDs to exclude them from main list
                    $subcategory_ids = [];
                    foreach ($menu_items as $item) {
                        if ($item->object === 'category' && $item->object_id) {
                            $subcategories = get_categories([
                                'parent' => $item->object_id,
                                'hide_empty' => false
                            ]);
                            foreach ($subcategories as $sub) {
                                $subcategory_ids[] = $sub->term_id;
                            }
                        }
                    }
                    
                    foreach ($menu_items as $item) {
                        // Skip the "Blog" item
                        if (strtolower($item->title) === 'blog') {
                            continue;
                        }
                        
                        // Skip if this is a subcategory (already shown in dropdown)
                        if ($item->object === 'category' && in_array($item->object_id, $subcategory_ids)) {
                            continue;
                        }
                        
                        $is_current = (get_queried_object_id() == $item->object_id);
                        $current_class = $is_current ? ' current-menu-item' : '';
                        
                        // Check if this is a category and get child categories
                        $has_subcategories = false;
                        $subcategories = [];
                        
                        if ($item->object === 'category' && $item->object_id) {
                            $subcategories = get_categories([
                                'parent' => $item->object_id,
                                'hide_empty' => false
                            ]);
                            $has_subcategories = !empty($subcategories);
                        }
                        
                        $dropdown_class = $has_subcategories ? ' has-dropdown' : '';
                        echo '<li class="menu-item menu-item-type-' . $item->type . ' menu-item-object-' . $item->object . $current_class . ' py-0.5 relative' . $dropdown_class . '">';
                        echo '<a href="' . esc_url($item->url) . '" class="text-sm align-middle flex items-center">' . esc_html($item->title) . '</a>';
                        
                        // Add dropdown for subcategories
                        if ($has_subcategories) {
                            echo '<ul class="dropdown-menu ts-dropdown-menu absolute bg-white border border-gray-200 rounded-md shadow-lg py-1 min-w-[200px] z-50">';
                            foreach ($subcategories as $subcategory) {
                                $sub_is_current = is_category($subcategory->term_id);
                                $sub_current_class = $sub_is_current ? ' bg-gray-100' : '';
                                echo '<li class="' . $sub_current_class . '">';
                                echo '<a href="' . esc_url(get_category_link($subcategory->term_id)) . '" class="block pl-3 pr-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">';
                                echo esc_html($subcategory->name);
                                echo '</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        }
                        echo '</li>';
                    }
                } else {
                    // Fallback: Show some default categories with subcategories
                    $categories = get_categories([
                        'parent' => 0,
                        'number' => 5,
                        'hide_empty' => false
                    ]);
                    
                    // Collect all subcategory IDs to exclude them from main list
                    $subcategory_ids = [];
                    foreach ($categories as $category) {
                        $subcategories = get_categories([
                            'parent' => $category->term_id,
                            'hide_empty' => false
                        ]);
                        foreach ($subcategories as $sub) {
                            $subcategory_ids[] = $sub->term_id;
                        }
                    }
                    
                    if (!empty($categories)) {
                        foreach ($categories as $category) {
                            // Skip if this is a subcategory (already shown in dropdown)
                            if (in_array($category->term_id, $subcategory_ids)) {
                                continue;
                            }
                            
                            $subcategories = get_categories([
                                'parent' => $category->term_id,
                                'hide_empty' => false
                            ]);
                            $has_subcategories = !empty($subcategories);
                            $dropdown_class = $has_subcategories ? ' has-dropdown' : '';
                            
                            echo '<li class="menu-item menu-item-type-taxonomy menu-item-object-category py-0.5 relative' . $dropdown_class . '">';
                            echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="text-sm align-middle flex items-center">' . esc_html($category->name) . '</a>';
                            
                            if ($has_subcategories) {
                                echo '<ul class="dropdown-menu ts-dropdown-menu absolute bg-white border border-gray-200 rounded-md shadow-lg py-1 min-w-[200px] z-50">';
                                foreach ($subcategories as $subcategory) {
                                    $sub_is_current = is_category($subcategory->term_id);
                                    $sub_current_class = $sub_is_current ? ' bg-gray-100' : '';
                                    echo '<li class="' . $sub_current_class . '">';
                                    echo '<a href="' . esc_url(get_category_link($subcategory->term_id)) . '" class="block pl-3 pr-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">';
                                    echo esc_html($subcategory->name);
                                    echo '</a>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                            }
                            echo '</li>';
                        }
                    }
                }
            ?>
        </ul>
    </nav>
</div>
<?php
endif;
?> 
