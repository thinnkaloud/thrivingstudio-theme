# Release Checklist (Non-Coder Friendly)

Use this checklist every time you release theme changes.

## 1) Before You Push
- Confirm you are in the theme folder:
  - `/Users/thinnkaloud/Local Sites/stagingthrivingstudioxyz/app/public/wp-content/themes/thrivingstudio`
- Confirm your branch:
  - `git branch --show-current` should be `develop`
- Confirm changed files are expected:
  - `git status`
- Run safety checks:
  - `npm run build`
  - `php -l home.php`
  - `php -l single.php`

## 2) Send Changes To Staging
- Commit your changes:
  - `git add .`
  - `git commit -m "your clear message"`
- Push to staging branch:
  - `git push origin develop`
- Wait for GitHub Actions "Deploy Theme To Staging" to finish with green check.

## 3) Staging QA (Visual Check)
- Open staging site and hard refresh.
- Check these pages:
  - Home page
  - Blog/archive page
  - One single post page
  - Mobile menu
- Confirm no broken layout, missing images, or broken links.

## 4) Promote To Live
- Merge `develop` into `main` (GitHub merge/PR flow).
- Open GitHub Actions -> "Deploy Theme To Live" -> `Run workflow`.
- Enter:
  - `confirm = DEPLOY`
  - `ref = main` (or a specific commit/tag for rollback)
- Approve environment deployment when prompted.
- Wait for green check.

## 5) Live QA (Final)
- Hard refresh live site.
- Check:
  - Home page
  - Blog page
  - One single post page
  - Main navigation and CTA buttons
- Confirm the latest visible change is live.

## 6) If Something Looks Wrong
- Do not edit live files manually.
- Roll back by running live workflow again with:
  - `confirm = DEPLOY`
  - `ref = <last good tag or commit SHA>`

## Notes
- This deploy pipeline only updates the theme folder.
- Plugins, uploads, and database are not changed by theme deploy workflows.
