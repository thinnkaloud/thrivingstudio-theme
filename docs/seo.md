# SEO

## Who Handles What

Since the July 2026 "Simplify SEO" change (released in 1.3.0), the theme no longer outputs any SEO tags of its own. The old SEO module, the SEO settings page, and the editor's SEO tab were removed.

**An SEO plugin is responsible for:**
- Meta descriptions and meta titles
- Canonical URLs and robots meta tags
- Open Graph and Twitter Card tags
- Structured data (JSON-LD), including FAQ schema
- The XML sitemap

**The theme still handles:**
- The `<title>` tag through WordPress core (`title-tag` support).
- Archive page titles, in `inc/theme/document-titles.php`. It also hooks Yoast (`wpseo_title`) and Rank Math (`rank_math/frontend/title`), so archive titles stay consistent whichever plugin is active.
- Semantic markup in templates: one H1 per page and proper heading order.
- Favicons and the web app manifest in `assets/`.

**Other notes:**
- The FAQ block no longer adds FAQ schema. If you want FAQ rich results, turn on FAQ schema in the SEO plugin.
- Run only one SEO plugin at a time. Two plugins output duplicate tags.
- `robots.txt` is served from the site root by WordPress or the SEO plugin, not from the theme.

## Post Subtitles

Older posts may have a subtitle saved in the `_thrivingstudio_article_subtitle` field. The theme still shows it under the title and on blog cards, but the editor no longer has a field for it. For new posts, write a manual excerpt in the editor's Excerpt panel. The theme shows it when no subtitle is saved. It never generates one from the post body, so a post with neither shows no subtitle.

## Writing Each Post

- Write the meta title and meta description in the SEO plugin's box in the editor.
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
- Open a post's page source and confirm there is exactly one meta description and one set of Open Graph tags.
- Test one post with Google's Rich Results Test.
- Confirm the sitemap still loads.

## Tools

- Google Search Console (search performance, indexing, sitemaps)
- Google Analytics through Site Kit (traffic)
- PageSpeed Insights (speed and Core Web Vitals)
- Rich Results Test (structured data)
- Ahrefs (keywords and backlinks)
