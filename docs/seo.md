# SEO

## Who Handles What

Since the July 2026 "Simplify SEO" change (released in 1.3.0), the theme no longer outputs any SEO tags of its own. The old SEO module, the SEO settings page, and the editor's SEO tab were removed.

**Rank Math is responsible for:**
- Meta descriptions and meta titles
- Canonical URLs and robots meta tags
- Open Graph and Twitter Card tags
- Structured data (JSON-LD), including FAQ schema
- The XML sitemap, at `https://thrivingstudio.xyz/sitemap_index.xml`

**The theme still handles:**
- The `<title>` tag through WordPress core (`title-tag` support).
- Archive page titles, in `inc/theme/document-titles.php`. It hooks Rank Math (`rank_math/frontend/title`), and Yoast (`wpseo_title`) in case the site ever switches, so archive titles stay consistent.
- Semantic markup in templates: one H1 per page and proper heading order.
- Favicons and the web app manifest in `assets/`.

**Other notes:**
- Keep Rank Math as the only SEO plugin. A second one would output duplicate tags.
- `robots.txt` is served from the site root by WordPress and Rank Math, not from the theme. Edit it under Rank Math → General Settings → Edit robots.txt.

## Rank Math Setup Checks

The theme used to handle these itself, so confirm Rank Math covers them:

1. **Sitemap.** Rank Math → Sitemap Settings is turned on, and Google Search Console has `https://thrivingstudio.xyz/sitemap_index.xml` submitted. It should not point to the old `/sitemap.xml`.
2. **FAQ schema.** The theme's FAQ block shows questions and answers but adds no schema. For FAQ rich results, use Rank Math's FAQ block in the post, or add FAQ schema through Rank Math's Schema tab.
3. **Older posts.** Posts written before July 2026 stored their SEO fields in theme-specific meta keys that Rank Math does not read: `_thrivingstudio_meta_description`, `_thrivingstudio_seo_title`, `_thrivingstudio_social_title`, `_thrivingstudio_social_description`, `_thrivingstudio_social_image`, `_thrivingstudio_canonical_url`, `_thrivingstudio_robots_meta`, and `_thrivingstudio_focus_keyword`. Those posts only have a meta description if one was also entered in Rank Math. `scripts/migrate-seo-to-rank-math.php` copies the old values into Rank Math. See "Migrating Old SEO Fields" below.

## Migrating Old SEO Fields

`scripts/migrate-seo-to-rank-math.php` is a one-time WP-CLI script. It is deployed with the theme but refuses to run from a browser. Run it over SSH from the WordPress root folder:

```bash
# 1. Dry run: lists every post and the fields it would copy. Writes nothing.
wp eval-file wp-content/themes/thrivingstudio/scripts/migrate-seo-to-rank-math.php

# 2. Apply: writes the changes.
wp eval-file wp-content/themes/thrivingstudio/scripts/migrate-seo-to-rank-math.php apply

# If needed: removes only what step 2 wrote, unless it was edited since.
wp eval-file wp-content/themes/thrivingstudio/scripts/migrate-seo-to-rank-math.php undo
```

How it behaves:
- It covers posts, pages, and quote cards in any status except trash.
- It only fills Rank Math fields that are empty. Anything already set in Rank Math is kept.
- It never changes or deletes the old theme fields.
- Robots settings are converted to Rank Math's format. `max-snippet` and similar tokens are listed in the output instead of copied, so set those in Rank Math by hand if you still want them.
- Running it twice is safe. The second run finds nothing left to copy.

After applying, open one migrated post in the editor and check the Rank Math box shows the title, description, and social image.

## Post Subtitles

Older posts may have a subtitle saved in the `_thrivingstudio_article_subtitle` field. The theme still shows it under the title and on blog cards, but the editor no longer has a field for it. For new posts, write a manual excerpt in the editor's Excerpt panel. The theme shows it when no subtitle is saved. It never generates one from the post body, so a post with neither shows no subtitle.

## Writing Each Post

- Write the meta title and meta description in the Rank Math box in the editor, and set a focus keyword.
- Keep titles under about 60 characters and descriptions around 150 to 160 characters.
- Use one H1 (the post title), then H2 and H3 in order.
- Add alt text to every image, and set a featured image so social shares have a picture.
- Link to two or three related posts on the site.

## Routine Checks

**Weekly**
- Google Search Console: look for new errors under Pages and Core Web Vitals.

**Monthly**
- Review search performance in Search Console and refresh posts that are slipping.
- Check for broken links.
- Run a few key pages through PageSpeed Insights.

**After each theme release**
- Open a post's page source and confirm there is exactly one meta description and one set of Open Graph tags, both from Rank Math.
- Test one post with Google's Rich Results Test.
- Confirm `sitemap_index.xml` still loads.

## Tools

- Rank Math (on-page SEO, schema, sitemap, SEO Analyzer)
- Google Search Console (search performance, indexing, sitemaps)
- Google Analytics through Site Kit (traffic)
- PageSpeed Insights (speed and Core Web Vitals)
- Rich Results Test (structured data)
- Ahrefs (keywords and backlinks)
