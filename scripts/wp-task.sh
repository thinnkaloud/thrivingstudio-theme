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

# Leftovers from the paused TFM publishing app (removed 2026-10-27).
TFM_CATEGORY_SLUGS=(api-posts bridge-tests ops-hub)
TFM_PLUGINS=(thriving-studio-article-import tfm-connector)

# Unknown PHP files found in the site root on live on 2026-10-27.
SUSPICIOUS_FILES=(testo.php amp.php)

# Accounts no longer needed (the site is run solo now). Their posts are
# reassigned to REASSIGN_TO, never deleted.
OLD_ACCOUNTS=(VivekSharma thriving_chatgpt)
REASSIGN_TO="Nareshk304"

HERO_MODS=(
  thrivingstudio_home_hero_title
  thrivingstudio_home_hero_subtitle
  thrivingstudio_home_hero_button_text
  thrivingstudio_home_hero_button_link
)

trash_spam_post() {
  ids="$(wpc post list --post_type=any --post_status=any --name="$SPAM_POST_SLUG" --field=ID)"
  if [ -z "$ids" ]; then
    echo "No post with slug $SPAM_POST_SLUG. Nothing to do."
  else
    for post_id in $ids; do
      echo "Moving to trash (restorable under Posts > Trash): $post_id $(wpc post get "$post_id" --field=post_title)"
      wpc post delete "$post_id"
    done
  fi
}

remove_tfm_integrations() {
  echo "== Application passwords named TFM (revoked)"
  for user_id in $(wpc user list --field=ID); do
    wpc user application-password list "$user_id" --fields=uuid,name --format=csv 2>/dev/null | tail -n +2 |
      while IFS=, read -r uuid name; do
        case "$(printf '%s' "$name" | tr '[:upper:]' '[:lower:]')" in
          *tfm*)
            wpc user application-password delete "$user_id" "$uuid" >/dev/null
            printf 'Revoked for user %s: %s\n' "$user_id" "$name"
            ;;
        esac
      done
  done
  echo
  echo "== TFM categories (deleted only when empty)"
  for slug in "${TFM_CATEGORY_SLUGS[@]}"; do
    term_id="$(wpc term get category "$slug" --by=slug --field=term_id 2>/dev/null || true)"
    if [ -z "$term_id" ]; then
      echo "Not present: $slug"
    # The term's count field only includes published posts, so count posts
    # in every status (drafts, scheduled, private, trash) directly.
    elif [ "$(wpc post list --post_type=any --post_status=publish,future,draft,pending,private,trash --cat="$term_id" --format=count)" != "0" ]; then
      echo "Kept, still has posts (any status): $slug"
    else
      wpc term delete category "$term_id" >/dev/null
      echo "Deleted: $slug"
    fi
  done
  echo
  echo "== TFM plugins (deactivated, files kept)"
  for plugin in "${TFM_PLUGINS[@]}"; do
    if ! wpc plugin is-installed "$plugin"; then
      echo "Not installed: $plugin"
    elif wpc plugin is-active "$plugin"; then
      # Load plugins here so the plugin's own deactivation hook runs.
      wp --path="$WP_ROOT" plugin deactivate "$plugin"
    else
      echo "Already inactive: $plugin"
    fi
  done
}

remove_old_accounts() {
  echo "== Old accounts (deleted, posts reassigned to $REASSIGN_TO)"
  local reassign_id user_id posts
  reassign_id="$(wpc user get "$REASSIGN_TO" --field=ID)"
  for login in "${OLD_ACCOUNTS[@]}"; do
    user_id="$(wpc user get "$login" --field=ID 2>/dev/null || true)"
    if [ -z "$user_id" ]; then
      echo "Not present: $login"
    elif [ "$user_id" = "$reassign_id" ]; then
      echo "Refusing to delete the account posts are reassigned to: $login" >&2
      exit 1
    else
      posts="$(wpc post list --post_type=any --post_status=any --author="$user_id" --format=count)"
      wpc user delete "$user_id" --reassign="$reassign_id" --yes >/dev/null
      echo "Deleted: $login ($posts posts reassigned to $REASSIGN_TO)"
    fi
  done
}

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
    echo "== Application passwords (tools that can post as a user)"
    for user_id in $(wpc user list --role=administrator --field=ID) $(wpc user list --role=editor --field=ID) $(wpc user list --role=author --field=ID); do
      printf -- '-- user %s (%s)\n' "$user_id" "$(wpc user get "$user_id" --field=user_login)"
      wpc user application-password list "$user_id" --fields=name,created,last_used,last_ip --format=table 2>/dev/null || echo "(none)"
    done
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
    trash_spam_post
    ;;

  remove-tfm-integrations)
    remove_tfm_integrations
    ;;

  remove-old-accounts)
    remove_old_accounts
    ;;

  cleanup)
    trash_spam_post
    echo
    remove_tfm_integrations
    echo
    remove_old_accounts
    ;;

  inspect-files)
    # Read-only. Never executes the files; prints metadata and a cut-down,
    # escaped preview so obfuscated code cannot flood or garble the log.
    for name in "${SUSPICIOUS_FILES[@]}"; do
      file="$WP_ROOT/$name"
      echo "== $name"
      if [ ! -f "$file" ]; then
        echo "Not present."
        echo
        continue
      fi
      ls -la "$file"
      stat -c 'Changed: %z | Modified: %y | Size: %s bytes | Owner: %U' "$file"
      sha256sum "$file" | cut -d' ' -f1
      echo "Lines: $(wc -l < "$file")  Longest line: $(awk '{ if (length > max) max = length } END { print max + 0 }' "$file") chars"
      echo "-- Patterns common in backdoors (matching lines):"
      for pattern in 'eval *\(' 'base64_decode' 'gzinflate|gzuncompress|str_rot13' 'assert *\(' 'system *\(|shell_exec|passthru|exec *\(|popen|proc_open' '\$_(POST|GET|REQUEST|COOKIE|SERVER)' 'file_put_contents|fwrite|move_uploaded_file' 'create_function'; do
        printf '   %-60s %s\n' "$pattern" "$(grep -cE "$pattern" "$file" || true)"
      done
      echo "-- First 40 lines (cut to 200 characters, control characters shown):"
      head -n 40 "$file" | cut -c1-200 | cat -v
      echo
    done
    ;;

  quarantine-files)
    # Move the files out of the public web folder so they cannot be requested,
    # but keep them as evidence. Undo by moving them back.
    quarantine_dir="$(dirname "$WP_ROOT")/quarantine-$(date -u +%Y%m%d-%H%M%S)"
    mkdir -p "$quarantine_dir"
    chmod 700 "$quarantine_dir"
    for name in "${SUSPICIOUS_FILES[@]}"; do
      file="$WP_ROOT/$name"
      if [ -f "$file" ]; then
        mv "$file" "$quarantine_dir/$name.quarantined"
        chmod 600 "$quarantine_dir/$name.quarantined"
        echo "Quarantined: $name -> $quarantine_dir/$name.quarantined"
      else
        echo "Not present: $name"
      fi
    done
    ;;

  *)
    echo "Unknown task: $TASK" >&2
    exit 1
    ;;
esac
