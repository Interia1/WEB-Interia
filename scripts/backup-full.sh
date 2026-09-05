#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PROJECT_NAME="$(basename "$ROOT_DIR")"
BACKUP_DIR="${BACKUP_DIR:-/workspaces/${PROJECT_NAME}-backups}"
STAMP="$(date +%Y%m%d-%H%M%S)"
ARCHIVE="${BACKUP_DIR}/${PROJECT_NAME}-FULL-${STAMP}.tar.gz"
STATUS_FILE="${BACKUP_DIR}/${PROJECT_NAME}-FULL-${STAMP}-git-status.txt"

mkdir -p "$BACKUP_DIR"

(
  cd "$ROOT_DIR"
  git --no-pager status --short > "$STATUS_FILE" 2>/dev/null || true
)

tar \
  --exclude="${PROJECT_NAME}-backups" \
  -czf "$ARCHIVE" \
  -C "$(dirname "$ROOT_DIR")" \
  "$PROJECT_NAME" \
  -C "$BACKUP_DIR" \
  "$(basename "$STATUS_FILE")"

printf "Full backup created:\n%s\n" "$ARCHIVE"
ls -lh "$ARCHIVE"
