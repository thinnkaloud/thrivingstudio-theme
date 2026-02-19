# Thriving Studio WordPress Audit (Theme + SEO + Content Workflow)

## SEO Plugin(s)

- **Detected plugin folders:**
  - `/wp-content/plugins/` contains:
    - `fourth-mind-dashboard/` (custom plugin)
    - `tfm-wp-bridge/` (custom plugin)
    - `wpforms-lite/` (form plugin)
    - `wordpress-importer/` (import tool)
    - `theme-file-editor/` (theme editor)
  - **No SEO plugins detected** in the plugins directory

- **Likely SEO plugin in use:** None. The theme implements custom SEO functionality.

- **Notes:** The project repository does not contain any third-party SEO plugins (Rank Math, Yoast, All in One SEO, etc.). SEO functionality is handled entirely by custom theme code in `inc/seo.php` and `inc/seo-settings.php`.

- **SEO plugin meta key search results:**
  - Searched for: `rank_math_title`, `rank_math_description`, `_yoast_wpseo_title`, `_yoast_wpseo_metadesc`, `wpseo_`
  - **No matches found** - These meta keys are not used anywhere in the codebase
  - The theme's SEO-GUIDE.md (line 84-86) mentions Yoast SEO and Rank Math as "optional" plugins, but they are not installed or active
  - All SEO meta handling is done via custom functions in `inc/seo.php` using theme-specific meta keys (`_thrivingstudio_meta_description`, `_thrivingstudio_robots_meta`)

## Title & Meta Output

- **Files involved in outputting `<title>` or meta description:**
  - `functions.php` (line 42): Theme supports `title-tag`, meaning WordPress core handles `<title>` output automatically via `wp_head()`
  - `inc/seo.php` (lines 15-55): Custom function `thrivingstudio_seo_meta_tags()` hooked to `wp_head` at priority 1
  - `template-parts/header.php` (line 7): Contains `<?php wp_head(); ?>` which outputs title and meta tags

- **Is there any custom `<title>` or `<meta name="description">` output by the theme?**
  - **No custom `<title>` tag** - Theme uses WordPress core `title-tag` support
  - **Yes, custom meta description** - Output via `thrivingstudio_seo_meta_tags()` function in `inc/seo.php` (line 26)

- **Any signs that an SEO plugin is expected to handle titles/descriptions?**
  - No. The theme has its own SEO implementation and does not appear to expect an external SEO plugin. The custom SEO module handles:
    - Meta descriptions
    - Canonical URLs
    - Robots meta tags
    - Open Graph tags
    - Twitter Card tags
    - Structured data (JSON-LD)
    - XML sitemap generation

## Theme Post Templates

- **Theme folder:** `thrivingstudio/`

- **Single post template files used:**
  - `single.php` - Main template for single blog posts
  - No `single-post.php` found (WordPress will use `single.php` for posts)

- **How the post title is rendered:**
  - File: `single.php` (line 44)
  - Function call: `<?php the_title(); ?>`
  - Rendered inside an `<h1>` tag with classes: `text-4xl font-bold mb-0`

- **How the post content is rendered:**
  - File: `single.php` (line 64)
  - Function call: `<?php the_content(); ?>`
  - Wrapped in `<div class="prose prose-lg mx-auto">` for styling

- **Any additional fields shown on single posts:**
  - **Categories** (lines 9-42): Displays parent and child categories with custom formatting
  - **Excerpt** (lines 46-50): Custom excerpt displayed if available, styled with `text-lg text-gray-600 mb-1`
  - **Featured image** (lines 51-59): Post thumbnail displayed if available
  - **Author and date** (lines 61-63): Published date and author name
  - **Comments** (lines 67-71): Comments template included if comments are open

### Custom Fields Used in Single Post Templates

- **Meta key:** `_thrivingstudio_meta_description` — used for: Custom SEO meta description (set via SEO meta box in admin)
- **Meta key:** `_thrivingstudio_robots_meta` — used for: Custom robots meta tag (e.g., "noindex,nofollow")
- **Note:** These meta keys are used by the SEO module but are not displayed in the post template itself - they affect the `<head>` output

## Custom Taxonomies

- **Taxonomy:** `quote_card` (Custom Post Type)
  - Object type(s): `quote_card`
  - Purpose: Custom post type for quote cards with archive at `/quotecards`
  - Registered in: `functions.php` (lines 616-643)
  - Not related to blog posts/articles

- **No custom taxonomies found for blog posts/articles**
  - The theme uses standard WordPress categories and tags only
  - Categories are extensively used throughout templates (archive.php, home.php, single.php)
  - Categories support custom `hero_subtitle` term meta (functions.php lines 762-800)

## Content Workflow Details

- **Excerpt handling:**
  - The theme displays `the_excerpt()` in multiple locations:
    - `single.php` (line 48): Displayed on single post pages if excerpt exists
    - `home.php` (line 73): Displayed in blog index/archive cards
    - `archive.php` (line 63): Displayed in category archive cards
    - `index.php` (line 39): Displayed in fallback template
    - `front-page.php` (line 183): Displayed on homepage featured posts
    - `page.php` (line 83): Displayed on page templates
  - The SEO module (`inc/seo.php` line 70) uses `get_the_excerpt()` as fallback for meta descriptions if custom meta description is not set

- **Reading time:**
  - **No custom reading time calculation found** in the codebase
  - No "X min read" labels or reading time functions detected

- **Series / ribbons / labels:**
  - **Categories are used as labels/badges:**
    - Displayed prominently on single posts (single.php lines 9-42)
    - Shown on archive cards (archive.php line 53, home.php line 63)
    - Category filtering implemented on blog index (home.php lines 90-369) with JavaScript-based filtering
    - Categories have custom color mapping in JavaScript (home.php lines 141-179)
  - **No custom "Series" taxonomy** - categories serve this purpose
  - **No badge/ribbon system** beyond category display

- **Notable conventions:**
  - Categories are heavily emphasized in the UI
  - Parent/child category relationships are displayed with separators (bullet points) on single posts
  - Category hero sections with custom subtitles on archive pages (archive.php lines 6-22)
  - Featured categories can be set via theme customizer (functions.php lines 380-423)
  - The theme expects posts to have at least one category assigned

## Summary for TS Article Import Plugin Design

- **Recommended SEO meta keys to target (based on actual code/plugins in this repo):**
  - `_thrivingstudio_meta_description` - Custom meta description (string, max ~160 chars recommended)
  - `_thrivingstudio_robots_meta` - Robots meta tag (string, e.g., "noindex,nofollow" or "index,follow")
  - **Note:** The theme does NOT use Rank Math, Yoast, or other SEO plugin meta keys. Use the custom theme meta keys listed above.

- **Fields that the plugin should always set:**
  - `post_title` - Post title (required)
  - `post_name` - Post slug/permalink (auto-generated from title if not provided)
  - `post_content` - Post content/body
  - `post_excerpt` - Post excerpt (used in archives and as SEO fallback)
  - `post_status` - Typically 'publish' for published posts
  - `post_type` - 'post' for blog articles
  - `post_author` - Author ID
  - `post_date` - Publication date
  - `categories` - Array of category IDs or slugs (categories are heavily used in the theme)
  - `tags` - Array of tag IDs or slugs (optional)
  - `_thumbnail_id` - Featured image attachment ID (if available)
  - `_thrivingstudio_meta_description` - SEO meta description (optional but recommended)
  - `_thrivingstudio_robots_meta` - Robots meta (optional, defaults to "index,follow" if not set)

- **Optional fields the plugin might set in future:**
  - None identified for regular blog posts. The theme does not appear to use custom fields for posts beyond SEO meta.
  - **Note:** The `quote_card` custom post type uses `_quote_card_author` and `_quote_card_caption` meta keys, but these are not relevant for blog posts.

- **Any potential conflicts or things to avoid:**
  - **Do NOT manually output `<title>` tags** - The theme uses WordPress core `title-tag` support, so titles are handled automatically
  - **Do NOT output duplicate meta descriptions** - The theme's SEO module (`inc/seo.php`) already outputs meta descriptions via `wp_head()`. If setting `_thrivingstudio_meta_description`, it will be used automatically
  - **Categories are important** - Always assign at least one category to posts, as the theme displays categories prominently and uses them for filtering/navigation
  - **Excerpts are used** - Set `post_excerpt` when available, as it's displayed in archive views and used as SEO fallback
  - **Featured images are expected** - The theme templates check for `has_post_thumbnail()` and display images in multiple locations
  - **Do NOT override theme's SEO output** - The theme has its own SEO implementation. Avoid conflicts by using the theme's meta keys (`_thrivingstudio_meta_description`, `_thrivingstudio_robots_meta`) rather than trying to output meta tags directly
  - **Sitemap generation** - The theme auto-generates `sitemap.xml` on post publish/update (inc/seo.php lines 442-506). No manual sitemap handling needed

