#!/usr/bin/env bash
# Runs one WordPress maintenance task with WP-CLI. Executed on the server by
# .github/workflows/wp-tasks.yml over SSH; it only knows the tasks below.
#
# Usage: wp-task.sh <task> <theme-path>
set -euo pipefail

TASK="${1:-}"
THEME_PATH="${2:-}"

if [[ "$THEME_PATH" != *"/wp-content/themes/"* ]]; then
  echo "Theme path must point inside wp-content/themes. Got: $THEME_PATH" >&2
  exit 1
fi

WP_ROOT="${THEME_PATH%%/wp-content/themes/*}"

if ! command -v wp >/dev/null 2>&1; then
  echo "WP-CLI (wp) is not installed on this server." >&2
  exit 1
fi

# Print the first N lines of stdin, then drain the rest so the writer never
# gets SIGPIPE (which pipefail would turn into a failed run).
first_lines() {
  head -n "$1"
  cat >/dev/null
}

wpc() {
  wp --path="$WP_ROOT" --skip-themes --skip-plugins "$@"
}

# Pillars must match thrivingstudio_pillar_definitions() in inc/theme/site-copy.php.
PILLARS=(
  "inner-strength|Inner Strength|Emotional strength, awareness, and how the mind and human behaviour really work."
  "discipline|Discipline|Habits, focus, fitness, and practical tools for growing steadily in life."
  "curious-world|Curious World|Science and technology explained simply, and good news from around the world."
)

# The spam post found on staging and live on 2026-10-27.
SPAM_POST_SLUG="kms-activator-office-2016"

HERO_MODS=(
  thrivingstudio_home_hero_title
  thrivingstudio_home_hero_subtitle
  thrivingstudio_home_hero_button_text
  thrivingstudio_home_hero_button_link
)

case "$TASK" in
  info)
    echo "== Site"
    wpc option get home
    wpc core version
    echo
    echo "== Categories"
    wpc term list category --fields=term_id,slug,name,parent,count --format=table
    echo
    echo "== Published posts"
    wpc post list --post_type=post --post_status=publish --fields=ID,post_name,post_title,post_date --orderby=date --order=DESC --format=table
    echo
    echo "== Category of each published post"
    for post_id in $(wpc post list --post_type=post --post_status=publish --field=ID); do
      printf '%s: %s\n' "$post_id" "$(wpc post term list "$post_id" category --field=slug | paste -sd, -)"
    done
    echo
    echo "== Published pages"
    wpc post list --post_type=page --post_status=publish --fields=ID,post_name,post_title --format=table
    echo
    echo "== Theme settings (Customizer)"
    wpc theme mod list --format=table
    ;;

  create-pillar-categories)
    for pillar in "${PILLARS[@]}"; do
      IFS='|' read -r slug name description <<< "$pillar"
      if wpc term get category "$slug" --by=slug --field=term_id >/dev/null 2>&1; then
        echo "Exists, left unchanged: $slug"
      else
        wpc term create category "$name" --slug="$slug" --description="$description" --porcelain >/dev/null
        echo "Created: $slug ($name)"
      fi
    done
    ;;

  clear-hero-text)
    echo "Current values, kept here so they can be restored:"
    for mod in "${HERO_MODS[@]}"; do
      printf '  %s = %s\n' "$mod" "$(wpc theme mod get "$mod" 2>/dev/null || true)"
    done
    wpc theme mod remove "${HERO_MODS[@]}"
    echo "Cleared. The homepage hero now uses the theme defaults."
    ;;

  purge-cache)
    # LiteSpeed registers its CLI commands from the plugin, so load plugins here.
    if wp --path="$WP_ROOT" litespeed-purge all 2>/dev/null; then
      echo "LiteSpeed Cache purged."
    else
      wp --path="$WP_ROOT" cache flush
      echo "LiteSpeed CLI not available; flushed the WordPress object cache instead."
    fi
    ;;

  security-scan)
    # Read-only. Changes nothing on the site.
    echo "== Spam post"
    wpc post list --post_type=any --post_status=any --name="$SPAM_POST_SLUG" \
      --fields=ID,post_status,post_author,post_date,post_modified,post_title --format=table
    for author_id in $(wpc post list --post_type=any --post_status=any --name="$SPAM_POST_SLUG" --field=post_author); do
      printf 'Author %s: %s\n' "$author_id" "$(wpc user get "$author_id" --field=user_login 2>/dev/null || echo 'user no longer exists')"
    done
    echo
    echo "== Other posts matching spam terms (any status)"
    for term in activator keygen crack casino betting viagra "<script" "<iframe"; do
      matches="$(wpc post list --post_type=any --post_status=any --s="$term" --fields=ID,post_type,post_status,post_title --format=csv | tail -n +2)"
      if [ -n "$matches" ]; then
        printf -- '-- %s\n%s\n' "$term" "$matches"
      fi
    done
    echo
    echo "== User accounts"
    wpc user list --fields=ID,user_login,roles,user_registered --orderby=registered --order=DESC --format=table
    echo
    echo "== WordPress core files (compared with wordpress.org)"
    wpc core verify-checksums || true
    echo
    echo "== Plugin files (compared with wordpress.org; non-wordpress.org plugins are skipped)"
    wpc plugin verify-checksums --all || true
    echo
    echo "== Plugins"
    wpc plugin list --fields=name,status,version,update --format=table
    echo
    echo "== Must-use plugins folder"
    ls -la "$WP_ROOT/wp-content/mu-plugins" 2>/dev/null || echo "(none)"
    echo
    echo "== PHP files inside uploads (there should be none)"
    find "$WP_ROOT/wp-content/uploads" -type f -name '*.php' 2>/dev/null | first_lines 50
    echo
    # Core files and wordpress.org plugins are already compared file by file
    # above (including files that should not exist), so leave them out here.
    echo "== PHP files changed in the last 90 days that the checksum checks do not cover"
    # In find -path, * also matches /, so root files are listed separately.
    find "$WP_ROOT" -maxdepth 1 -type f -name '*.php' -mtime -90 2>/dev/null
    find "$WP_ROOT/wp-content" -type f -name '*.php' -mtime -90 \
      -not -path '*/wp-content/plugins/*' \
      -not -path '*/wp-content/languages/*' \
      -not -path '*/wp-content/wflogs/*' \
      -not -path '*/wp-content/themes/thrivingstudio/*' \
      -not -path '*/wp-content/cache/*' \
      -not -path '*/wp-content/litespeed/*' 2>/dev/null | first_lines 100
    echo
    echo "== PHP files in plugins that are not on wordpress.org (not checksum-verified)"
    for plugin_dir in "$WP_ROOT"/wp-content/plugins/*/; do
      plugin="$(basename "$plugin_dir")"
      # WP-CLI exits 0 and prints "skipping" for plugins it cannot look up.
      if ! check_output="$(wpc plugin verify-checksums "$plugin" 2>&1)" || printf '%s' "$check_output" | grep -q 'skipping'; then
        echo "-- $plugin"
        find "$plugin_dir" -type f -name '*.php' -mtime -90 2>/dev/null | first_lines 30
      fi
    done
    echo
    echo "Scan complete."
    ;;

  trash-spam-post)
    ids="$(wpc post list --post_type=any --post_status=any --name="$SPAM_POST_SLUG" --field=ID)"
    if [ -z "$ids" ]; then
      echo "No post with slug $SPAM_POST_SLUG. Nothing to do."
    else
      for post_id in $ids; do
        echo "Moving to trash (restorable under Posts > Trash): $post_id $(wpc post get "$post_id" --field=post_title)"
        wpc post delete "$post_id"
      done
    fi
    ;;

  *)
    echo "Unknown task: $TASK" >&2
    exit 1
    ;;
esac
