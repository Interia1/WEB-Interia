#!/usr/bin/env bash

set -euo pipefail

ADDON_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${ADDON_DIR}/../.." && pwd)"

mkdir -p "${ROOT_DIR}/.devcontainer"
install -m 0644 "${ADDON_DIR}/devcontainer.json" "${ROOT_DIR}/.devcontainer/devcontainer.json"
install -m 0644 "${ADDON_DIR}/devcontainer-codespace-start.sh" "${ROOT_DIR}/.devcontainer/codespace-start.sh"

echo "Codespaces autostart bol obnoveny z doplnku."
echo "V Codespaces spustite: bash .devcontainer/codespace-start.sh"