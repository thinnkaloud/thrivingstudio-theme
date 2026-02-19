#!/usr/bin/env bash
set -euo pipefail

DEPLOY_ENV="${1:-}"
if [ -z "$DEPLOY_ENV" ]; then
  echo "Usage: $0 <staging|live>" >&2
  exit 1
fi

if [ "$DEPLOY_ENV" != "staging" ] && [ "$DEPLOY_ENV" != "live" ]; then
  echo "Invalid deploy environment: $DEPLOY_ENV" >&2
  exit 1
fi

if [ "$DEPLOY_ENV" = "staging" ] && [ "${GITHUB_REF_NAME:-}" != "develop" ]; then
  echo "Staging deploys must run from develop. Current branch: ${GITHUB_REF_NAME:-unknown}" >&2
  exit 1
fi

for bin in rsync ssh ssh-keyscan; do
  if ! command -v "$bin" >/dev/null 2>&1; then
    echo "Missing required command: $bin" >&2
    exit 1
  fi
done

THEME_SLUG="${THEME_SLUG:-thrivingstudio}"

if [ "$DEPLOY_ENV" = "staging" ]; then
  REQUIRED_VARS=(STAGING_HOST STAGING_PORT STAGING_USER STAGING_PATH)
  REMOTE_PATH="${STAGING_PATH:-}"
else
  REQUIRED_VARS=(LIVE_HOST LIVE_PORT LIVE_USER LIVE_PATH)
  REMOTE_PATH="${LIVE_PATH:-}"
fi

for name in "${REQUIRED_VARS[@]}"; do
  if [ -z "${!name:-}" ]; then
    echo "Missing required environment variable: $name" >&2
    exit 1
  fi
done

if [[ "$REMOTE_PATH" != *"/wp-content/themes/"* ]]; then
  echo "Deploy path must point inside wp-content/themes. Got: $REMOTE_PATH" >&2
  exit 1
fi

if [ "$(basename "$REMOTE_PATH")" != "$THEME_SLUG" ]; then
  echo "Deploy path must target theme '${THEME_SLUG}'. Got: $REMOTE_PATH" >&2
  exit 1
fi

echo "Deploy checks passed for '$DEPLOY_ENV'."
echo "Branch: ${GITHUB_REF_NAME:-unknown}"
echo "Target path: $REMOTE_PATH"
