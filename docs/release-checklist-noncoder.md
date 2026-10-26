# Release Checklist (Non-Coder Friendly)

Use this checklist every time you release theme changes.

The repo has two branches:
- `develop` is for work in progress. Every push to it deploys to staging automatically.
- `main` is what is live. It only changes by merging `develop` into it.

Never commit or push straight to `main`. Changes that skip `develop` reach live without ever being checked on staging.

## 1) Before You Push
- Confirm you are in the theme folder:
  - `/Users/thinnkaloud/Local Sites/stagingthrivingstudioxyz/app/public/wp-content/themes/thrivingstudio`
- Confirm your branch:
  - `git branch --show-current` should be `develop`
- Get the latest changes first:
  - `git pull origin develop`
- Confirm changed files are expected:
  - `git status`
- Run safety checks:
  - `npm run build`
  - `npm test` (runs the hygiene check, CSS build check, and PHP syntax check on every file)
- Every check must pass. If `npm test` lists a forbidden file (for example `something.php.backup`), delete it. Don't commit it.

## 2) Send Changes To Staging
- Commit your changes:
  - `git add .`
  - `git commit -m "your clear message"`
- Push to develop:
  - `git push origin develop`
- Wait for GitHub Actions "Deploy Theme To Staging" to finish with a green check. It runs the theme checks first and only deploys if they pass, which takes about a minute.
- If it fails at "Theme checks", open the run to see why. The usual cause is forgetting `npm run build` after editing CSS or JS. Fix it, commit, and push again.

## 3) Staging QA (Visual Check)
- Open staging site and hard refresh.
- Check these pages:
  - Home page
  - Blog/archive page
  - One single post page
  - Sign-in and profile pages
  - Contact page (send a test message)
  - Mobile menu
  - WordPress admin: posts list and the post editor
- Confirm no broken layout, missing images, or broken links.

## 4) Promote To Live
- Merge `develop` into `main` (GitHub merge/PR flow).
- Open GitHub Actions -> "Deploy Theme To Live" -> `Run workflow`.
- Leave "Use workflow from" set to `main`. The workflow refuses to run from any other branch.
- Enter:
  - `confirm = DEPLOY`
  - `ref = main`
- Approve the deployment:
  - Open the run and click "Review deployments" in the yellow banner.
  - Tick `live`, then click "Approve and deploy". Ticking the box alone does nothing.
- The theme checks run before the approval banner appears. If they fail, nothing is deployed.
- Wait for the green check. It takes about a minute after approval.

## 5) Live QA (Final)
- Purge the LiteSpeed cache: WordPress admin -> LiteSpeed Cache -> Purge All. Otherwise visitors may keep seeing old pages.
- Hard refresh live site.
- Check:
  - Home page
  - Blog page
  - One single post page
  - Main navigation and CTA buttons
- Confirm the latest visible change is live.

## 6) If Something Looks Wrong
- Do not edit live files manually.
- Roll back by running the live workflow again, still from `main`, with:
  - `confirm = DEPLOY`
  - `ref = <last good tag or commit SHA>`
- Find the last good SHA on GitHub under Code -> Commits (branch `main`), or with `git log --oneline main`. Each commit has a short SHA like `a92ed36`; use the one released before the bad change.
- Avoid rolling back past `a92ed36` (the 1.3.0 cleanup). Older commits still contain the removed backup files, so a rollback would put them back on the server.
- After a rollback, fix the problem on `develop` and release again normally.

## 7) For Bigger Releases
- Bump the version in `style.css`, `functions.php` (`THRIVINGSTUDIO_VERSION`), and `package.json`.
- Add an entry at the top of `CHANGELOG.md` describing what changed.

## Notes
- This deploy pipeline only updates the theme folder.
- Plugins, uploads, and database are not changed by theme deploy workflows.
- Deploys mirror the repo exactly: a file deleted from the repo is also deleted from the server on the next deploy.
- Files listed in `.deployignore` are never uploaded, and never deleted from the server either. To remove a file from the server, delete it from the repo. Don't add it to `.deployignore`.
