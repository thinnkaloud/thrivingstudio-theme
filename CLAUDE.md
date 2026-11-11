# CLAUDE.md

WordPress theme for thrivingstudio.xyz. Tailwind CSS, plain PHP, no framework. The repo root is the theme root.

## Layout

- `functions.php` loads modules; most logic lives in `inc/theme/*.php` (one file per concern: account, contact, mail, google-auth, blocks, content-types, etc.). Security headers and CSP are in `inc/security.php`.
- Page templates are `page-*.php` at the root. Shared pieces are in `template-parts/`.
- CSS source is `frontend/index.css`; JS source is `frontend/main.js`.
- `frontend/build.css` and `frontend/main.min.js` are committed build output. The server never builds, so rebuild and commit them whenever you touch CSS, Tailwind classes in PHP, or `main.js`.
- `docs/archive/` is history only. Don't read it unless asked.

## Commands

- `npm run build` rebuilds CSS and JS.
- `npm test` runs hygiene, CSS, and PHP lint checks. Run it before every push.
- CI runs `npm run test:ci`, which also fails if committed build output is stale.

## Branches and deploys

- `develop` auto-deploys to staging on push. `main` deploys to live only by manual workflow dispatch.
- Feature branches open PRs into `develop`. Never push to `main` or `develop` directly.
- Deploys use `rsync --delete`: deleting a file from the repo deletes it from the server.
- See `docs/deployment.md` and `docs/release-checklist-noncoder.md` for details.

## Rules

- Secrets live in `wp-config.php`, never in git. Config constants use the `THRIVINGSTUDIO_` prefix.
- SEO meta, schema, and sitemap belong to Rank Math, not the theme (`docs/seo.md`).
- Bump `THRIVINGSTUDIO_VERSION` in `functions.php`, `style.css`, and `package.json` together, and add a `CHANGELOG.md` entry, when releasing.
- The site owner is not a developer. Explain changes in plain language.
