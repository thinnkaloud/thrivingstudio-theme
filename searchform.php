<?php
$search_field_id = wp_unique_id('ts-search-field-');
?>
<form role="search" method="get" class="ts-search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label class="screen-reader-text" for="<?php echo esc_attr($search_field_id); ?>"><?php esc_html_e('Search articles', 'thrivingstudio'); ?></label>
    <input type="search" id="<?php echo esc_attr($search_field_id); ?>" class="ts-search-input" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search articles and quotes', 'thrivingstudio'); ?>">
    <button type="submit" class="ts-search-submit"><?php esc_html_e('Search', 'thrivingstudio'); ?></button>
</form>
