# Changelog

All notable changes to the Thriving Studio theme will be documented in this file.

## [Unreleased]

### Added
- **`spam-trace` task** (read-only): looks for the activator spam page in every post type and status, post meta, options, and files, and reports whether the `# FOXY SEO` crawler redirect is still in `.htaccess`.
- **`crawler-check` task** (read-only): compares what a browser and Googlebot get for key pages, and lists crawler rules in `.htaccess` and recently changed files in the site root. Added after Google flagged the site for deceptive pages on 2026-10-28.
- **Pillar mapping covers live's category names** (`health-and-wellness`, `personal-growth`, `emotional-strength-2`, `clarity-over-noise`, `reflective-practice`, `journaling-personal-growth`, `research-briefs`, and two empty ones), so no published post on live sits outside a pillar. `map-pillars` also adds two mind-focused wellness posts to Inner Strength and takes Uncategorized off a Discipline post.
- **`content-audit` task** (read-only): published posts grouped by pillar, categories, menus, pages, and reading settings. **`draft-off-brand-posts`** switches four posts that don't fit the pillars to draft.
- **Content pillars**: Inner Strength, Discipline, and Curious World, matched to categories by slug (`inner-strength`, `discipline`, `curious-world`). The homepage, Foundations page, and pillar category pages use them.
- **Pillar pages list their related categories**: each pillar's category page also shows posts from the existing categories mapped to it in `thrivingstudio_pillar_definitions()`. Categories stay where they are, so no URLs change.
- **`map-pillars` task**: moves two Uncategorized posts into Curious World. **`restore-category-parents`** undoes the category nesting from an earlier version of `map-pillars` (used on staging only).
- **Live cleanup tasks**: `inspect-files` (read-only look at the unknown `testo.php` and `amp.php` in the site root), `quarantine-files` (moves them out of the web root), `remove-old-accounts` (deletes `VivekSharma` and `thriving_chatgpt`, reassigning their posts), and `cleanup` (spam post, TFM, and accounts in one approval). `remove-tfm-integrations` now also deactivates `tfm-connector`.
- **`remove-tfm-integrations` task**: removes leftovers from the paused TFM publishing app (application passwords, empty categories) and deactivates the article import plugin.
- **Security tasks** in the WordPress Tasks workflow: `security-scan` (read-only checks for signs of a compromise) and `trash-spam-post` (trashes the `kms-activator-office-2016` spam post).
- **WordPress Tasks workflow** (`.github/workflows/wp-tasks.yml`, `scripts/wp-task.sh`): runs a fixed set of WP-CLI tasks on staging or live over SSH: site info, creating the pillar categories, clearing saved hero text, and purging the cache.
- **Site Copy panel** in the Customizer (`inc/theme/site-copy.php`). Homepage, blog, newsletter, and Book page wording now lives there instead of in templates.

### Changed
- **Positioning**: Copy across the site now follows "Thriving Studio — Clarity Over Noise". New homepage hero with "Start here" (Foundations page) and "Get the weekly letter" buttons; the newsletter is now "The Weekly Letter".
- **Homepage order**: hero, pillars, latest articles, quote cards, social profiles, newsletter.
- **Social stats** only show counts that are filled in, with no built-in numbers, and YouTube is labelled "subscribers".
- **Category pages** fall back to the category description when no hero subtitle is set.
- **Foundations page** introduces the three pillars and links to good first reads.

### Fixed
- The homepage "Start here" button only links to the Foundations page when it is published, so drafts no longer send visitors to a page they cannot open.
- Links to the journal no longer point back at the current page when no posts page is set under Settings > Reading (`thrivingstudio_get_journal_url()`).

### Removed
- The four-slot "Homepage Featured Categories" Customizer section, replaced by the pillars.
- Hardcoded description overrides for the `artificial-intelligence-and-tools` category.

## Cleanup pass (deployed 2026-10-26, not yet versioned)

### Added
- **Search**: Search results page, search form, a search icon in the desktop header, and search in the mobile menu and on the 404 page.

### Changed
- **Article cards**: One shared card (`template-parts/post-card.php`) now renders every post listing. Card images use responsive `srcset` instead of a fixed small size, homepage card titles are `h3` under their section heading, and cards without an image show a plain block instead of "No image available".
- **"Useful" button**: Each visitor (by account, or by IP when signed out) adds at most one vote per article and can only remove their own. Signed-out votes are checked by request origin instead of a nonce, so the button keeps working on cached pages and other sites cannot cast votes through a visitor's browser.
- **Contact page**: Excluded from page caching so its form nonce never expires in a cached copy.
- **Service Worker cleanup**: Only runs for visitors who still have an old worker installed, instead of clearing browser caches on every page load.
- **Accessibility**: Added the `.screen-reader-text` style, so hidden form labels are no longer visible.

### Removed
- `inc/theme/media.php`. Its WebP filter served full-size images in place of thumbnails, dropped `srcset` and classes, and accepted any upload with ".webp" in its name. WordPress handles WebP natively.
- Empty `page-blog.php`, the unreachable blog branch and filter script in `page.php`, unused `assets/js/home-filters.js` and `frontend/build-full.css`.
- CSS rules in `style.css`, which WordPress never loaded.
- Duplicate quote archive styles and mobile menu script in the header (both already handled elsewhere).

## [1.3.0] - 2026-10-26

Covers all work since the `v1.2-structural-cleanup` tag (Feb 2026). Versions 1.1 and 1.2 were tracked as git tags only.

### Added
- **Accounts**: Google sign-in, sign-in page, and account profile page.
- **Engagement**: "Useful" button on articles.
- **Quote cards**: Verification workflow, with an optional OpenAI helper configured through `wp-config.php` constants.
- **Newsletter**: Homepage signup connected to Brevo, using one shared form across environments.
- **SEO**: Article subtitle controls and simplified theme SEO descriptions.
- **Editor**: FAQ block styling and citation formatting.
- **Foundations page** and improved archive discovery.

### Changed
- **Single articles**: Reworked reading layout, title wrapping, sticky side rail, and outline navigation.
- **Reader navigation**: Clearer header, nav, and archive links.
- **Contact form**: Mail routed to the public inbox, with a hardened mail flow.
- **Admin**: Better posts table layout and editor typography.
- **Ads**: Lazier ad loading on single articles.

### Removed
- Unused modules: `inc/ads.php`, `inc/adsense-*.php`, `inc/image-optimizer*.php`, `inc/performance*.php`, and `inc/performance/core.php`. None of these were loaded.
- Backup copies (`inc/performance.php.backup`, `.broken`, `.disabled`). They were deployed and readable as plain text.
- One-off image scripts in the theme root, the `sharp` dependency they needed, `sftp-test.txt`, the empty `Thriving` file, `screenshot-backup.png`, and the theme-folder `robots.txt`, which had no effect.

### Technical
- `npm run check:hygiene` now fails on `.backup`, `.broken`, `.disabled`, `.bak`, and `.orig` files.

## [1.0.3] - 2024-07-19

### Removed
- **Complete AdSense File Removal**: Deleted adsense-display.php and adsense-settings.php files
- **AdSense Functions**: Removed all AdSense-related functions that could interfere with Site Kit
- **AdSense Hooks**: Eliminated all AdSense action hooks and filters

### Changed
- **Version Bump**: Updated theme version to 1.0.3
- **Clean Theme**: Theme now has zero AdSense code that could conflict with Site Kit

### Why This Change
- **Eliminates All Conflicts**: Removes any possibility of theme AdSense code interfering with Site Kit
- **Clean Slate**: Ensures Site Kit has full control over AdSense functionality
- **Better Performance**: Removes unused AdSense code from theme
- **Simplified Maintenance**: No more AdSense code to maintain in theme

## [1.0.2] - 2024-07-19

### Removed
- **AdSense Integration**: Removed built-in AdSense functionality from theme
- **AdSense Settings Page**: Removed AdSense settings from WordPress admin
- **AdSense Display Functions**: Removed ad display functions and shortcodes
- **AdSense Modules**: Removed adsense-settings.php and adsense-display.php from autoload

### Changed
- **Version Bump**: Updated theme version to 1.0.2
- **Module Loading**: Simplified autoload to focus on core SEO and performance features

### Why This Change
- **Google Site Kit Integration**: Site Kit provides better WordPress integration for Google services
- **Eliminates Conflicts**: Removes potential conflicts between theme and Site Kit AdSense features
- **Simplifies Theme**: Focuses theme on design and core SEO, letting Site Kit handle Google services
- **Better Architecture**: Follows WordPress best practices for external service integration

## [1.0.1] - 2024-07-18

### Changed
- **SEO Settings Cleanup**: Removed redundant Google Analytics functionality from theme
- **Google Site Kit Integration**: Theme now works seamlessly with Google Site Kit plugin
- **Version Bump**: Updated theme version to 1.0.1

### Removed
- Google Analytics ID field from SEO Settings page
- Manual Google Analytics tracking code generation
- Redundant Analytics functions to prevent conflicts with Site Kit

### Technical
- Removed `thrivingstudio_add_google_analytics()` function
- Removed Google Analytics settings field from admin
- Cleaned up SEO settings to focus on core SEO features

### Why This Change
- Google Site Kit provides better WordPress integration for Google services
- Eliminates potential conflicts between theme and plugin tracking codes
- Follows WordPress best practices for external service integration
- Reduces theme complexity and maintenance burden

## [1.0.0] - 2024-07-15

### Added
- Initial theme release
- Comprehensive SEO features (meta tags, Open Graph, structured data)
- XML sitemap generation
- SEO Settings admin page
- Performance optimizations
- Mobile-first responsive design
- Custom post type support
- WordPress Customizer integration

### Features
- Auto-generated and custom meta descriptions
- Open Graph and Twitter Card tags
- Structured data (JSON-LD) for articles and organization
- XML sitemap with automatic updates
- SEO meta boxes for posts and pages
- Favicon and app icon support
- robots.txt generation
- Performance optimizations (image quality, script loading)
- Security enhancements

---

## Version History

- **1.0.3** - Complete removal of AdSense files
- **1.0.2** - Removed AdSense functionality in favor of Google Site Kit
- **1.0.1** - SEO cleanup and Google Site Kit integration
- **1.0.0** - Initial release with comprehensive SEO features 