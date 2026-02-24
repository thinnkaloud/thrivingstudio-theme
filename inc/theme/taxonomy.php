<?php

/**
 * Add custom hero fields to category add form.
 */
add_action('category_add_form_fields', function($taxonomy) {
    ?>
    <div class="form-field">
        <label for="hero_subtitle">Hero Subtitle</label>
        <input name="hero_subtitle" id="hero_subtitle" type="text" value="" />
        <p class="description">Optional. Subtitle for the hero section.</p>
    </div>
    <?php
});

/**
 * Add custom hero fields to category edit form.
 */
add_action('category_edit_form_fields', function($term) {
    $hero_subtitle = get_term_meta($term->term_id, 'hero_subtitle', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="hero_subtitle">Hero Subtitle</label></th>
        <td>
            <input name="hero_subtitle" id="hero_subtitle" type="text" value="<?php echo esc_attr($hero_subtitle); ?>" />
            <p class="description">Optional. Subtitle for the hero section.</p>
        </td>
    </tr>
    <?php
}, 10, 1);

/**
 * Save custom hero fields for categories.
 */
add_action('created_category', function($term_id) {
    if (isset($_POST['hero_subtitle'])) {
        update_term_meta($term_id, 'hero_subtitle', sanitize_text_field($_POST['hero_subtitle']));
    }
});
add_action('edited_category', function($term_id) {
    if (isset($_POST['hero_subtitle'])) {
        update_term_meta($term_id, 'hero_subtitle', sanitize_text_field($_POST['hero_subtitle']));
    }
});
