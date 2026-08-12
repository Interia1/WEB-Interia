#!/usr/bin/env bash

set -euo pipefail

cd /workspaces/WEB-Interia

if ! command -v php >/dev/null 2>&1; then
  sudo apt-get update >/dev/null 2>&1 || true
  sudo apt-get install -y php-cli php-mbstring php-xml php-curl php-zip php-pgsql php-sqlite3 >/dev/null 2>&1 || true
fi

if command -v php >/dev/null 2>&1 && [[ -f artisan ]]; then
  if ! pgrep -f "^php artisan serve --host=0.0.0.0 --port=8000$" >/dev/null 2>&1; then
    nohup php artisan serve --host=0.0.0.0 --port=8000 >/tmp/web-interia-artisan.log 2>&1 &
    echo $! >/tmp/web-interia-artisan.pid
  fi
fi

if command -v gh >/dev/null 2>&1 && [[ -n "${CODESPACE_NAME:-}" ]]; then
  token="${GH_TOKEN:-${GITHUB_TOKEN:-}}"
  if [[ -z "$token" ]]; then
    token="$(gh auth token 2>/dev/null || true)"
  fi

  if [[ -n "$token" ]]; then
    GH_TOKEN="$token" gh codespace ports visibility -c "$CODESPACE_NAME" 8000:public >/dev/null 2>&1 || true
  fi
fi
