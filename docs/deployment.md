# Theme Deployment Workflow (Git-First)

This repository is the WordPress theme root for `thrivingstudio` only.

This doc explains how the pipeline is set up. For the step-by-step release routine, use [release-checklist-noncoder.md](release-checklist-noncoder.md).

## Branch model

- `develop` -> auto deploy to staging on push.
- `main` -> manual deploy to live via GitHub Actions `workflow_dispatch` only.
- Live rollback deploys are supported by passing a `ref` (tag, branch, or commit SHA) to the live workflow.

## What is deployed

- Only files from this theme repository are synced.
- Deployment transport is SSH + `rsync --delete`.
- Remote destination must be inside `wp-content/themes/` and must end in `/thrivingstudio`.
- This workflow does not deploy plugins, uploads, or database content.
- The server never builds anything. `frontend/build.css` and `frontend/main.min.js` are deployed exactly as committed.

## `.deployignore` and deletions

- Files matching `.deployignore` (markdown, `.github/`, `node_modules/`, env files, and similar) are never uploaded.
- Because of `rsync --delete`, a file removed from the repo is also removed from the server on the next deploy.
- Excluded files are protected on the server: rsync never deletes them. If a file needs to disappear from the server, delete it from the repo. Adding it to `.deployignore` would leave the old copy there for good.

## Required repository secrets

- `STAGING_HOST`
- `STAGING_PORT`
- `STAGING_USER`
- `STAGING_SSH_KEY`
- `STAGING_PATH`
- `LIVE_HOST`
- `LIVE_PORT`
- `LIVE_USER`
- `LIVE_SSH_KEY`
- `LIVE_PATH`

Set `STAGING_PATH` and `LIVE_PATH` to each environment's theme path, for example:

- `/path/to/site/wp-content/themes/thrivingstudio`

## Workflow files

- `.github/workflows/test.yml`
- `.github/workflows/deploy-staging.yml`
- `.github/workflows/deploy-live.yml`
- `scripts/deploy-check.sh`
- `.deployignore`

## Automatic checks

`.github/workflows/test.yml` runs `npm run test:ci` on every pull request, and before every staging and live deploy. A deploy only starts if the checks pass. They are:

- `check:hygiene`: no debug or backup files (`.backup`, `.broken`, and similar).
- `check:php`: PHP syntax check on every tracked PHP file.
- `check:build`: rebuilds `frontend/build.css` and `frontend/main.min.js` and fails if they differ from the committed files, meaning someone edited the source without rebuilding.

Live deploys test the `ref` being deployed. Rollbacks to commits from before these checks existed skip them, so an emergency rollback is never blocked.

Locally, `npm test` runs the same hygiene and PHP checks, plus a quicker timestamp-based CSS check.

## Live deploy gate

The live workflow enforces both:

- Trigger branch must be `main`.
- Dispatch input `confirm` must exactly equal `DEPLOY`.

If either check fails, deployment stops. After both pass, the `live` environment waits for a reviewer to approve the deployment.

## Rollback deploy

Use **Actions -> Deploy Theme To Live -> Run workflow** (from `main`) and set:

- `confirm=DEPLOY`
- `ref=<tag|commit|branch>`

Example:

- `ref=v1.2-structural-cleanup`
- `ref=a92ed36`

Rolling back to a commit older than `a92ed36` (the 1.3.0 cleanup) puts deleted backup files back on the server, because rsync mirrors whatever that commit contains.

## GitHub settings

In place now:

- Environment `live` requires a reviewer to approve each deployment.
- Staging and live secrets are configured.
- Ruleset `Protect main`: nobody can push to `main` directly, delete it, or force-push it. Changes reach `main` only through a pull request from `develop`. No approvals are required, so you can merge your own pull requests.

Merging `develop` into `main` now means: Pull requests -> New pull request -> base `main`, compare `develop` -> wait for "Theme checks" to pass -> Merge pull request.

Optional:

1. Ruleset `Protect main`: add "Theme checks" under required status checks, if it was not available when the ruleset was created. It shows up after the first pull request runs it.
2. Environment `live`: a wait timer before deployment.

## Validation commands

Run locally from repo root:

```bash
npm test
bash -n scripts/deploy-check.sh
git status --short
```
