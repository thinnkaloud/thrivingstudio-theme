<?php get_header(); ?>

<main class="flex-1">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 pt-0 flex-1 relative">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('max-w-3xl mx-auto ts-single-article'); ?>>
                <div class="prose prose-lg mx-auto ts-single-content">
                    <!-- Category first -->
                    <div class="mb-1 ts-single-category-row">
                        <?php
                        $categories = get_the_category();
                        if ( ! empty( $categories ) ) {
                            // Separate parent and child categories
                            $parent_categories = [];
                            $child_categories = [];
                            
                            foreach( $categories as $category ) {
                                if ( $category->parent == 0 ) {
                                    $parent_categories[] = $category;
                                } else {
                                    $child_categories[] = $category;
                                }
                            }
                            
                            // Display parent category first, then child category with separator
                            $category_parts = [];
                            
                            if ( ! empty( $parent_categories ) ) {
                                $parent = $parent_categories[0]; // Use first parent category
                                $category_parts[] = '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '" class="text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors duration-200 no-underline">' . esc_html( $parent->name ) . '</a>';
                            }
                            
                            if ( ! empty( $child_categories ) ) {
                                $child = $child_categories[0]; // Use first child category
                                $category_parts[] = '<a href="' . esc_url( get_category_link( $child->term_id ) ) . '" class="text-xs font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200 no-underline">' . esc_html( $child->name ) . '</a>';
                            }
                            
                            // Join with a separator (bullet or dash)
                            echo implode( ' <span class="text-gray-400 mx-1">•</span> ', $category_parts );
                        }
                        ?>
                    </div>
                    <!-- Title second -->
                    <h1 class="text-4xl font-bold mb-0 ts-single-title"><?php the_title(); ?></h1>
                    <!-- Custom excerpt -->
                    <?php if (has_excerpt()) : ?>
                        <div class="text-lg text-gray-600 mb-1 leading-relaxed ts-single-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="mb-4 overflow-auto ts-single-featured-wrap">
                            <?php the_post_thumbnail('full', [
                                'class' => 'w-full rounded-lg ts-single-featured-image',
                                'loading' => 'lazy'
                            ]); ?>
                        </div>
                    <?php endif; ?>
                    <!-- Author and date together -->
                    <div class="text-base text-gray-500 mb-6 ts-single-meta">
                        Published on <?php the_time(get_option('date_format')); ?> by <?php the_author(); ?>
                    </div>
                    <?php the_content(); ?>
                </div>

                <?php
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
                ?>
            </article>
        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?> 
