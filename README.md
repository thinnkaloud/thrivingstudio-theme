# Thriving Studio WordPress Theme

The custom WordPress theme for [thrivingstudio.xyz](https://thrivingstudio.xyz). It is built with Tailwind CSS and deployed from this repository through GitHub Actions.

## Documentation

| Doc | Use it for |
|---|---|
| [docs/release-checklist-noncoder.md](docs/release-checklist-noncoder.md) | Every release: push, staging check, live deploy, rollback |
| [docs/deployment.md](docs/deployment.md) | How the deploy pipeline is set up: branches, secrets, GitHub settings |
| [docs/seo.md](docs/seo.md) | Where SEO is handled and the routine SEO checks |
| [CHANGELOG.md](CHANGELOG.md) | What changed in each version |
| [our-commitment.md](our-commitment.md) | The editorial commitment published on the site |
| [docs/archive/](docs/archive/) | Old audits and refinement plans, kept for history only |

## Features

- Homepage with hero, social stats, quote carousel, latest articles, and a Brevo newsletter form.
- Blog, category archives, and a reading layout for single articles with an outline, a sticky side rail, related posts, and a closing call to action.
- Page templates: Blog, Book, Contact, Foundations, Profile, and Sign-in.
- Quote cards as a custom post type, with a verification workflow and an optional OpenAI helper.
- Google sign-in, account profiles, and a "useful" button on articles.
- FAQ and citation blocks for the editor.
- Security headers (including a Content Security Policy) and XML-RPC turned off.

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Node.js, only for building CSS and JS locally

## Plugins The Theme Expects

The theme leaves these jobs to plugins:

- **Brevo** for the homepage newsletter form. The form uses the `[sibwp_form id=2]` shortcode. Without the plugin (for example on a local copy), the section shows a disabled preview form.
- **Rank Math** for meta descriptions, Open Graph, schema, and the sitemap. See [docs/seo.md](docs/seo.md).
- **Google Site Kit** for Analytics and AdSense.
- **LiteSpeed Cache** for page caching. `.litespeed-cache-exclude.txt` lists what it must not minify.

## Configuration

Settings that hold secrets live in `wp-config.php` (or as server environment variables), never in git.

```php
// Contact form mail. Without SMTP_HOST, WordPress uses its default mailer.
define('THRIVINGSTUDIO_SMTP_HOST', 'smtp.example.com');
define('THRIVINGSTUDIO_SMTP_PORT', 587);
define('THRIVINGSTUDIO_SMTP_SECURE', 'tls'); // tls or ssl
define('THRIVINGSTUDIO_SMTP_USERNAME', 'smtp-user');
define('THRIVINGSTUDIO_SMTP_PASSWORD', 'smtp-password');
define('THRIVINGSTUDIO_MAIL_FROM', 'hello@thrivingstudio.xyz');
define('THRIVINGSTUDIO_MAIL_FROM_NAME', 'Thriving Studio');
define('THRIVINGSTUDIO_CONTACT_RECIPIENT', 'hello@thrivingstudio.xyz');

// Google sign-in.
define('THRIVINGSTUDIO_GOOGLE_CLIENT_ID', 'your-client-id.apps.googleusercontent.com');
define('THRIVINGSTUDIO_GOOGLE_CLIENT_SECRET', 'your-client-secret');
define('THRIVINGSTUDIO_GOOGLE_PRIVILEGED_EMAILS', 'you@example.com,editor@example.com');

// Quote card helper (optional). OPENAI_API_KEY and OPENAI_MODEL also work.
define('THRIVINGSTUDIO_OPENAI_API_KEY', 'sk-...');
define('THRIVINGSTUDIO_OPENAI_MODEL', 'model-name');
```

After changing mail settings, send a test message from the contact page and confirm it arrives.

Everything else, including the hero text, social profiles, header button, top bar, and the single article layout options, is under **Appearance > Customize**. Menus are under **Appearance > Menus**.

## Development

All build commands run from the theme folder. The root `package.json` is the only one that matters. `frontend/package.json` just forwards to it.

```bash
npm install          # first time only
npm run watch        # rebuild CSS automatically while you edit
npm run build        # build CSS and JS once
npm test             # hygiene check, CSS build check, PHP syntax check
```

### Editing CSS

- Edit `frontend/index.css`. Never edit `frontend/build.css`, because it is generated and gets overwritten.
- Rebuild with `npm run watch` (keep it running) or `npm run build:css`, then hard refresh the browser.
- Commit `frontend/build.css` together with your source changes. The server does not build anything; it uses the committed file.
- `npm run check:css` tells you if the build is out of date. A logged-in admin also sees a notice in WordPress when it is.
- If styles still look old after a rebuild, purge LiteSpeed Cache. The theme busts browser caches by file modification time.

### Editing JavaScript

- Edit `frontend/main.js`, then run `npm run build:js` to regenerate `frontend/main.min.js`. Commit both.

### Before every commit

Run `npm test`. It fails on leftover debug files and on backup copies like `something.php.backup`. Those get deployed and can expose source code, so delete them instead of committing them.

## License

GPL v2 or later.
