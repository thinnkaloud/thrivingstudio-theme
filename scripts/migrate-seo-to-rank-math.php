<?php
/**
 * One-time migration: copy the old theme SEO fields into Rank Math.
 *
 * Before July 2026 the theme stored per-post SEO data in its own meta keys.
 * Rank Math does not read them, so this copies each value into the matching
 * Rank Math field. Run it with WP-CLI from the WordPress root:
 *
 *   wp eval-file wp-content/themes/thrivingstudio/scripts/migrate-seo-to-rank-math.php
 *       Dry run (default). Shows what would change and writes nothing.
 *
 *   wp eval-file wp-content/themes/thrivingstudio/scripts/migrate-seo-to-rank-math.php apply
 *       Writes the changes.
 *
 *   wp eval-file wp-content/themes/thrivingstudio/scripts/migrate-seo-to-rank-math.php undo
 *       Removes only the values this script wrote, if they are unchanged since.
 *
 * Safety rules:
 * - A Rank Math field that already has a value is never overwritten.
 * - The old theme fields are left untouched.
 * - Every field written is recorded on the post, so `undo` can remove exactly those.
 */

if (!defined('WP_CLI') || !WP_CLI) {
    // This file is deployed with the theme; refuse to run over the web.
    http_response_code(404);
    exit;
}

$thrivingstudio_mode = isset($args[0]) ? strtolower((string) $args[0]) : 'dry-run';

if (!in_array($thrivingstudio_mode, ['dry-run', 'apply', 'undo'], true)) {
    WP_CLI::error("Unknown mode '{$thrivingstudio_mode}'. Use no argument (dry run), 'apply', or 'undo'.");
}

const THRIVINGSTUDIO_MIGRATION_LOG_KEY = '_thrivingstudio_rank_math_migrated';

/**
 * Old theme meta key => Rank Math meta key, for fields copied as-is.
 */
function thrivingstudio_migration_text_fields() {
    return [
        '_thrivingstudio_seo_title'          => 'rank_math_title',
        '_thrivingstudio_meta_description'   => 'rank_math_description',
        '_thrivingstudio_focus_keyword'      => 'rank_math_focus_keyword',
        '_thrivingstudio_canonical_url'      => 'rank_math_canonical_url',
        '_thrivingstudio_social_title'       => 'rank_math_facebook_title',
        '_thrivingstudio_social_description' => 'rank_math_facebook_description',
        '_thrivingstudio_social_image'       => 'rank_math_facebook_image',
    ];
}

/**
 * Convert the old comma-separated robots string into Rank Math's array.
 *
 * Rank Math stores robots as a list such as ['noindex', 'nofollow'].
 * "follow" is Rank Math's default and has no entry; max-* tokens belong to
 * Rank Math's advanced robots settings and are reported, not copied.
 *
 * @param string $robots_meta
 * @return array{robots: string[], skipped: string[]}
 */
function thrivingstudio_migration_convert_robots($robots_meta) {
    $supported = ['index', 'noindex', 'nofollow', 'noarchive', 'nosnippet', 'noimageindex'];
    $robots = [];
    $skipped = [];

    foreach (array_filter(array_map('trim', explode(',', strtolower((string) $robots_meta)))) as $token) {
        if (in_array($token, $supported, true)) {
            $robots[] = $token;
        } elseif ($token !== 'follow') {
            $skipped[] = $token;
        }
    }

    return ['robots' => array_values(array_unique($robots)), 'skipped' => $skipped];
}

/**
 * Whether a Rank Math field already holds a value.
 *
 * @param int    $post_id
 * @param string $key
 * @return bool
 */
function thrivingstudio_migration_has_value($post_id, $key) {
    $value = get_post_meta($post_id, $key, true);

    return is_array($value) ? !empty($value) : trim((string) $value) !== '';
}

/**
 * Build the list of Rank Math fields to write for one post.
 *
 * @param int $post_id
 * @return array{writes: array<string, mixed>, kept: string[], notes: string[]}
 */
function thrivingstudio_migration_plan($post_id) {
    $writes = [];
    $kept = [];
    $notes = [];

    foreach (thrivingstudio_migration_text_fields() as $old_key => $new_key) {
        $old_value = trim((string) get_post_meta($post_id, $old_key, true));

        if ($old_value === '') {
            continue;
        }

        if (thrivingstudio_migration_has_value($post_id, $new_key)) {
            $kept[] = $new_key;
            continue;
        }

        $writes[$new_key] = $old_value;
    }

    // Rank Math also wants the attachment ID for the social image when one exists.
    if (isset($writes['rank_math_facebook_image']) && !thrivingstudio_migration_has_value($post_id, 'rank_math_facebook_image_id')) {
        $attachment_id = attachment_url_to_postid($writes['rank_math_facebook_image']);

        if ($attachment_id) {
            $writes['rank_math_facebook_image_id'] = (string) $attachment_id;
        }
    }

    $old_robots = trim((string) get_post_meta($post_id, '_thrivingstudio_robots_meta', true));

    if ($old_robots !== '') {
        $converted = thrivingstudio_migration_convert_robots($old_robots);

        if ($converted['skipped']) {
            $notes[] = 'robots tokens not copied (set them in Rank Math if needed): ' . implode(', ', $converted['skipped']);
        }

        if ($converted['robots']) {
            if (thrivingstudio_migration_has_value($post_id, 'rank_math_robots')) {
                $kept[] = 'rank_math_robots';
            } else {
                $writes['rank_math_robots'] = $converted['robots'];
            }
        }
    }

    return ['writes' => $writes, 'kept' => $kept, 'notes' => $notes];
}

/**
 * Format a value for the report.
 *
 * @param mixed $value
 * @return string
 */
function thrivingstudio_migration_preview($value) {
    $text = is_array($value) ? implode(',', $value) : (string) $value;

    return mb_strlen($text) > 70 ? mb_substr($text, 0, 67) . '...' : $text;
}

$thrivingstudio_post_ids = get_posts([
    'post_type'        => ['post', 'page', 'quote_card'],
    'post_status'      => ['publish', 'future', 'draft', 'pending', 'private'],
    'posts_per_page'   => -1,
    'fields'           => 'ids',
    'orderby'          => 'ID',
    'order'            => 'ASC',
    'suppress_filters' => true,
]);

if ($thrivingstudio_mode === 'undo') {
    $thrivingstudio_undone = 0;

    foreach ($thrivingstudio_post_ids as $post_id) {
        $log = get_post_meta($post_id, THRIVINGSTUDIO_MIGRATION_LOG_KEY, true);

        if (!is_array($log) || !$log) {
            continue;
        }

        foreach ($log as $key => $written_value) {
            $current = get_post_meta($post_id, $key, true);

            if ($current == $written_value) { // phpcs:ignore Universal.Operators.StrictComparisons -- meta values may round-trip as strings.
                delete_post_meta($post_id, $key);
                $thrivingstudio_undone++;
            } else {
                WP_CLI::warning("Post {$post_id}: {$key} was edited after the migration, so it was left as is.");
            }
        }

        delete_post_meta($post_id, THRIVINGSTUDIO_MIGRATION_LOG_KEY);
        WP_CLI::log("Post {$post_id}: undone.");
    }

    WP_CLI::success("Undo finished. Removed {$thrivingstudio_undone} field(s).");
    return;
}

$thrivingstudio_posts_changed = 0;
$thrivingstudio_fields_written = 0;
$thrivingstudio_fields_kept = 0;

foreach ($thrivingstudio_post_ids as $post_id) {
    $plan = thrivingstudio_migration_plan($post_id);

    if (!$plan['writes'] && !$plan['kept'] && !$plan['notes']) {
        continue;
    }

    WP_CLI::log(sprintf('Post %d (%s): %s', $post_id, get_post_type($post_id), get_the_title($post_id)));

    foreach ($plan['writes'] as $key => $value) {
        WP_CLI::log("    + {$key} = " . thrivingstudio_migration_preview($value));
    }

    foreach ($plan['kept'] as $key) {
        WP_CLI::log("    = {$key} already set in Rank Math, kept");
    }

    foreach ($plan['notes'] as $note) {
        WP_CLI::log("    ! {$note}");
    }

    $thrivingstudio_fields_kept += count($plan['kept']);

    if (!$plan['writes']) {
        continue;
    }

    $thrivingstudio_posts_changed++;
    $thrivingstudio_fields_written += count($plan['writes']);

    if ($thrivingstudio_mode === 'apply') {
        $log = get_post_meta($post_id, THRIVINGSTUDIO_MIGRATION_LOG_KEY, true);
        $log = is_array($log) ? $log : [];

        foreach ($plan['writes'] as $key => $value) {
            update_post_meta($post_id, $key, $value);
            $log[$key] = $value;
        }

        update_post_meta($post_id, THRIVINGSTUDIO_MIGRATION_LOG_KEY, $log);
    }
}

$thrivingstudio_summary = sprintf(
    '%d post(s), %d field(s) %s. %d field(s) already set in Rank Math were kept.',
    $thrivingstudio_posts_changed,
    $thrivingstudio_fields_written,
    $thrivingstudio_mode === 'apply' ? 'written' : 'would be written',
    $thrivingstudio_fields_kept
);

if ($thrivingstudio_mode === 'apply') {
    WP_CLI::success($thrivingstudio_summary);
} else {
    WP_CLI::success('Dry run, nothing written. ' . $thrivingstudio_summary . " Run again with 'apply' to write.");
}
