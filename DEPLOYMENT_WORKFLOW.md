# Theme Deployment Workflow (Git-First)

This repository is the WordPress theme root for `thrivingstudio` only.

## Branch model

- `develop` -> auto deploy to staging on push.
- `main` -> manual deploy to live via GitHub Actions `workflow_dispatch` only.
- Live rollback deploys are supported by passing a `ref` (tag, branch, or commit SHA) to the live workflow.

## What is deployed

- Only files from this theme repository are synced.
- Deployment transport is SSH + `rsync --delete`.
- Remote destination must be inside `wp-content/themes/` and must end in `/thrivingstudio`.
- This workflow does not deploy plugins, uploads, or database content.

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

- `.github/workflows/deploy-staging.yml`
- `.github/workflows/deploy-live.yml`
- `scripts/deploy-check.sh`
- `.deployignore`

## Live deploy gate

The live workflow enforces both:

- Trigger branch must be `main`.
- Dispatch input `confirm` must exactly equal `DEPLOY`.

If either check fails, deployment stops.

## Rollback deploy

Use **Actions -> Deploy Theme To Live -> Run workflow** and set:

- `confirm=DEPLOY`
- `ref=<tag|commit|branch>`

Example:

- `ref=v1.4.2`
- `ref=72b26d1`

## GitHub setup checklist

1. Branch protection: `develop`
2. Require pull request before merging to `develop`
3. Optional: require status checks before merge to `develop`
4. Branch protection: `main`
5. Require pull request before merging to `main`
6. Require status checks before merge to `main`
7. Restrict who can push to `main`
8. Environment `staging`: add environment-scoped staging secrets
9. Environment `live`: add environment-scoped live secrets
10. Environment `live`: require reviewers for deployment approval
11. Environment `live`: optional wait timer before deployment

## Validation commands

Run locally from repo root:

```bash
bash -n scripts/deploy-check.sh
git status --short
```
