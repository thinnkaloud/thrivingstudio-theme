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

wpc() {
  wp --path="$WP_ROOT" --skip-themes --skip-plugins "$@"
}

# Pillars must match thrivingstudio_pillar_definitions() in inc/theme/site-copy.php.
PILLARS=(
  "inner-strength|Inner Strength|Emotional strength, awareness, and how the mind and human behaviour really work."
  "discipline|Discipline|Habits, focus, fitness, and practical tools for growing steadily in life."
  "curious-world|Curious World|Science and technology explained simply, and good news from around the world."
)

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

  *)
    echo "Unknown task: $TASK" >&2
    exit 1
    ;;
esac
