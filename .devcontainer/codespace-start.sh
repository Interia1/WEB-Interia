#!/usr/bin/env bash

set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

BACKUP_DIR="$(dirname "$ROOT_DIR")/$(basename "$ROOT_DIR")-backups"
BACKUP_LINK="${ROOT_DIR}/$(basename "$ROOT_DIR")-backups"
mkdir -p "$BACKUP_DIR"
if [[ ! -e "$BACKUP_LINK" && ! -L "$BACKUP_LINK" ]]; then
	ln -s "$BACKUP_DIR" "$BACKUP_LINK"
fi

bash "${ROOT_DIR}/doplnky/automaticke-spustenie/codespace-start.sh"
