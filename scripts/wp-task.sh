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

# Uncategorized posts that belong in a pillar ("post-slug|pillar-slug").
UNCATEGORIZED_MOVES=(
  "is-the-internet-dead-the-truth-behind-the-dead-internet-theory|curious-world"
  "why-google-bard-is-named-bard|curious-world"
)

# The spam post found on staging and live on 2026-10-27.
SPAM_POST_SLUG="kms-activator-office-2016"

# Posts that also belong in a pillar their categories don't map to
# ("post-slug|pillar-slug"). Uncategorized is removed from them too, unless
# permalinks include %category%, where that would change the post's URL.
PILLAR_ADDITIONS=(
  "detoxify-your-mind-in-7-simple-steps|inner-strength"
  "the-habit-loop-explained-why-you-stay-stuck-and-how-to-break-it|inner-strength"
  "if-you-are-ambitious-but-lazy-this-is-what-you-should-do|discipline"
)

# Posts that do not fit the three pillars, switched to draft on 2026-10-28.
OFF_BRAND_POSTS=(
  crack-the-instagram-code-organic-growth-strategies-for-2024-and-beyond-2
  from-idea-to-reality-your-step-by-step-guide-to-launching-your-first-blog
  unleash-your-pups-inner-genius-expert-tips-for-a-mentally-stimulated-and-well-behaved-dog
  how-anora-redefined-the-modern-cinderella-story
)

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

  map-pillars)
    # Pillar archives already include their mapped categories through the
    # theme (thrivingstudio_pillar_archive_tax_query), so categories are never
    # reparented and their URLs stay the same. Only posts are changed here.
    for pillar in "${PILLARS[@]}"; do
      slug="${pillar%%|*}"
      if ! wpc term get category "$slug" --by=slug --field=term_id >/dev/null 2>&1; then
        echo "Pillar missing, run create-pillar-categories first: $slug" >&2
        exit 1
      fi
    done
    echo "== Uncategorized posts moved into a pillar"
    for entry in "${UNCATEGORIZED_MOVES[@]}"; do
      post_slug="${entry%%|*}"
      pillar="${entry#*|}"
      post_id="$(wpc post list --post_type=post --post_status=any --name="$post_slug" --field=ID)"
      if [ -z "$post_id" ]; then
        echo "Not present: $post_slug"
        continue
      fi
      wpc post term add "$post_id" category "$pillar" >/dev/null
      wpc post term remove "$post_id" category uncategorized >/dev/null 2>&1 || true
      echo "Moved to $pillar: $post_id $post_slug"
    done
    echo
    echo "== Posts added to a second pillar"
    keep_uncategorized=no
    if [[ "$(wpc option get permalink_structure)" == *"%category%"* ]]; then
      keep_uncategorized=yes
    fi
    for entry in "${PILLAR_ADDITIONS[@]}"; do
      post_slug="${entry%%|*}"
      pillar="${entry#*|}"
      post_id="$(wpc post list --post_type=post --post_status=any --name="$post_slug" --field=ID)"
      if [ -z "$post_id" ]; then
        echo "Not present: $post_slug"
        continue
      fi
      wpc post term add "$post_id" category "$pillar" >/dev/null
      if [ "$keep_uncategorized" = no ]; then
        wpc post term remove "$post_id" category uncategorized >/dev/null 2>&1 || true
      fi
      echo "Added to $pillar: $post_id $post_slug"
    done
    ;;

  restore-category-parents)
    # Undoes the reparenting done by an earlier version of map-pillars, using
    # the original parent it saved in term meta.
    restored=0
    for term_id in $(wpc term list category --field=term_id --hide_empty=0); do
      original="$(wpc term meta get "$term_id" _thrivingstudio_original_parent 2>/dev/null || true)"
      if [ -z "$original" ]; then
        continue
      fi
      wpc term update category "$term_id" --parent="$original" >/dev/null
      wpc term meta delete "$term_id" _thrivingstudio_original_parent >/dev/null
      echo "Restored: $(wpc term get category "$term_id" --field=slug) (parent $original)"
      restored=$((restored + 1))
    done
    echo "Categories restored: $restored"
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

  draft-off-brand-posts)
    for slug in "${OFF_BRAND_POSTS[@]}"; do
      post_id="$(wpc post list --post_type=post --post_status=publish --name="$slug" --field=ID)"
      if [ -z "$post_id" ]; then
        echo "Not published, left unchanged: $slug"
        continue
      fi
      wpc post update "$post_id" --post_status=draft >/dev/null
      echo "Switched to draft: $post_id $slug"
    done
    ;;

  content-audit)
    # Read-only. Pillar mapping comes from the deployed theme, so this report
    # always matches what the pillar pages show.
    PILLAR_FILE="$THEME_PATH/inc/theme/site-copy.php" wpc eval-file - <<'PHP'
<?php
require_once getenv('PILLAR_FILE');

$pillar_of = [];
foreach (thrivingstudio_pillar_definitions() as $pillar => $definition) {
    $pillar_of[$pillar] = $pillar;
    foreach ($definition['categories'] as $slug) {
        $pillar_of[$slug] = $pillar;
    }
}

// A category's pillar, following its parents for unmapped children.
$category_pillar = function ($term) use (&$category_pillar, $pillar_of) {
    if (isset($pillar_of[$term->slug])) {
        return $pillar_of[$term->slug];
    }
    return $term->parent ? $category_pillar(get_term($term->parent, 'category')) : '';
};

echo "== Posts by status\n";
foreach ((array) wp_count_posts('post') as $status => $count) {
    if ($count) {
        echo "  $status: $count\n";
    }
}

$groups = [];
foreach (get_posts(['post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC']) as $post) {
    $categories = get_the_category($post->ID);
    $pillars = array_unique(array_filter(array_map($category_pillar, $categories)));
    sort($pillars);
    $line = sprintf('  %d  %s  %s  [%s]', $post->ID, substr($post->post_date, 0, 10), $post->post_title, implode(', ', wp_list_pluck($categories, 'slug')));
    $groups[$pillars ? implode(' + ', $pillars) : 'no pillar'][] = $line;
}
ksort($groups);
foreach ($groups as $group => $lines) {
    printf("\n== Published posts: %s (%d)\n%s\n", $group, count($lines), implode("\n", $lines));
}

echo "\n== Categories (published posts / pillar)\n";
foreach (get_categories(['hide_empty' => false]) as $term) {
    printf("  %-45s %4d  %s\n", $term->slug, $term->count, $category_pillar($term) ?: '-');
}

echo "\n== Menus\n";
$locations = array_flip(array_filter((array) get_nav_menu_locations()));
foreach (wp_get_nav_menus() as $menu) {
    printf("  %s%s\n", $menu->name, isset($locations[$menu->term_id]) ? ' (location: ' . $locations[$menu->term_id] . ')' : '');
    foreach ((array) wp_get_nav_menu_items($menu->term_id) as $item) {
        printf("    %s%s -> %s\n", $item->menu_item_parent ? '  ' : '', $item->title, $item->url);
    }
}

echo "\n== Pages\n";
foreach (get_pages(['post_status' => 'publish,draft,pending,private']) as $page) {
    printf("  %d  %-8s %-30s %s\n", $page->ID, $page->post_status, $page->post_name, get_page_template_slug($page->ID) ?: 'default');
}

echo "\n== Reading settings\n";
foreach (['show_on_front', 'page_on_front', 'page_for_posts', 'posts_per_page', 'permalink_structure'] as $option) {
    printf("  %s = %s\n", $option, get_option($option));
}
PHP
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

  crawler-check)
    # Read-only. Requests pages as a normal browser and as Googlebot and
    # compares the answers, to catch hacks that show search engines different
    # content (cloaking). Also looks for rewrite rules aimed at crawlers.
    site_url="$(wpc option get home)"
    browser_ua='Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36'
    googlebot_ua='Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
    check_paths=(/ "/$(wpc post get 2365 --field=post_name 2>/dev/null || echo remote-robotic-surgery)/" "/$SPAM_POST_SLUG/" /robots.txt)
    tmp_dir="$(mktemp -d)"
    trap 'rm -rf "$tmp_dir"' EXIT
    echo "== Responses ($site_url)"
    for path in "${check_paths[@]}"; do
      echo "-- $path"
      fetched=yes
      for agent in browser googlebot; do
        ua="$browser_ua"
        [ "$agent" = googlebot ] && ua="$googlebot_ua"
        # Clear the previous page's output so a failed request is never
        # compared or scanned as if it were this page.
        rm -f "$tmp_dir/$agent.html" "$tmp_dir/$agent.headers"
        if ! curl -sS -m 30 -A "$ua" -H 'Cache-Control: no-cache' \
          -o "$tmp_dir/$agent.html" -D "$tmp_dir/$agent.headers" \
          -w "   $agent: %{http_code}, %{size_download} bytes, final URL %{url_effective}\n" \
          -L --max-redirs 5 "${site_url%/}$path"; then
          echo "   $agent: request failed"
          fetched=no
          continue
        fi
        grep -iE '^(location|x-litespeed-cache|x-litespeed-vary|server):' "$tmp_dir/$agent.headers" 2>/dev/null \
          | tr -d '\r' | sed 's/^/      /' || true
      done
      if [ "$fetched" = no ]; then
        echo "   Skipped the comparison: at least one request failed."
        continue
      fi
      if cmp -s "$tmp_dir/browser.html" "$tmp_dir/googlebot.html"; then
        echo "   Same body for both."
      else
        echo "   Bodies differ. External script and link hosts only in the Googlebot copy:"
        grep -oiE '(src|href)="https?://[^/"]+' "$tmp_dir/googlebot.html" | sed -E 's/.*:\/\///' | sort -u > "$tmp_dir/g.hosts" || true
        grep -oiE '(src|href)="https?://[^/"]+' "$tmp_dir/browser.html" | sed -E 's/.*:\/\///' | sort -u > "$tmp_dir/b.hosts" || true
        comm -13 "$tmp_dir/b.hosts" "$tmp_dir/g.hosts" | sed 's/^/      /'
      fi
      spam_terms="$(grep -oiE 'activator|keygen|crack|casino|betting|viagra' "$tmp_dir/googlebot.html" | sort | uniq -c | tr -s ' ' | tr '\n' ';' || true)"
      echo "   Spam terms in the Googlebot copy: ${spam_terms:-none}"
    done
    echo
    echo "== Crawler and redirect rules in .htaccess files (site root and wp-content)"
    find "$WP_ROOT" -maxdepth 3 -name .htaccess -not -path '*/node_modules/*' -print0 \
      | while IFS= read -r -d '' file; do
          hits="$(grep -nEi 'googlebot|bot|crawler|HTTP_USER_AGENT|HTTP_REFERER|ErrorDocument|RewriteRule .*https?://' "$file" | cut -c1-200 | cat -v || true)"
          if [ -n "$hits" ]; then
            echo "-- $file"
            echo "$hits"
          fi
        done
    echo
    echo "== Files in the site root changed in the last 30 days"
    find "$WP_ROOT" -maxdepth 1 -type f -mtime -30 -printf '%TY-%Tm-%Td %TH:%TM  %8s  %f\n' | sort
    echo
    echo "== Script and iframe lines in post 2365"
    wpc post get 2365 --field=post_content 2>/dev/null \
      | grep -oiE '<(script|iframe)[^>]*>' | cut -c1-200 | cat -v || echo "None."
    ;;

  *)
    echo "Unknown task: $TASK" >&2
    exit 1
    ;;
esac
