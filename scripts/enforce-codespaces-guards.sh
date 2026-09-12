#!/usr/bin/env bash

set -euo pipefail

DEVCONTAINER_FILE=".devcontainer/devcontainer.json"
STARTUP_SCRIPT=".devcontainer/codespace-start.sh"
ADDON_SCRIPT="doplnky/automaticke-spustenie/codespace-start.sh"
ADDON_DEVCONTAINER="doplnky/automaticke-spustenie/devcontainer.json"
ADDON_STARTUP_SCRIPT="doplnky/automaticke-spustenie/devcontainer-codespace-start.sh"
RESTORE_SCRIPT="doplnky/automaticke-spustenie/obnovit-codespaces.sh"

for required_file in "$DEVCONTAINER_FILE" "$STARTUP_SCRIPT" "$ADDON_SCRIPT" "$ADDON_DEVCONTAINER" "$ADDON_STARTUP_SCRIPT" "$RESTORE_SCRIPT"; do
    if [[ ! -f "$required_file" ]]; then
        echo "ERROR: Missing $required_file"
        exit 1
    fi
done

content="$(cat "$DEVCONTAINER_FILE")"
startup_content="$(cat "$STARTUP_SCRIPT")"
addon_content="$(cat "$ADDON_SCRIPT")"
errors=()

if ! grep -Eq '"forwardPorts"[[:space:]]*:[[:space:]]*\[[^]]*8000' <<<"$content"; then
    errors+=("forwardPorts must include 8000")
fi

if ! grep -Fq '"8000": {' <<<"$content"; then
    errors+=("portsAttributes.8000.visibility must be public")
fi

if ! grep -Fq '"onAutoForward": "openBrowserOnce"' <<<"$content"; then
    errors+=("portsAttributes.8000.onAutoForward must open the site")
fi

if ! grep -Fq '"otherPortsAttributes": {' <<<"$content"; then
    errors+=("otherPortsAttributes block is required")
fi

if ! grep -Fq '"visibility": "private"' <<<"$content"; then
    errors+=("otherPortsAttributes.visibility must be private")
fi

if ! grep -Fq '"onAutoForward": "ignore"' <<<"$content"; then
    errors+=("otherPortsAttributes.onAutoForward must be ignore")
fi

if ! grep -Fq '"ghcr.io/devcontainers/features/github-cli:1"' <<<"$content"; then
    errors+=("GitHub CLI Dev Container feature is required")
fi

required_config_tokens=(
    '"postStartCommand": "bash .devcontainer/codespace-start.sh"'
    '"postAttachCommand": "bash .devcontainer/codespace-start.sh"'
)

for token in "${required_config_tokens[@]}"; do
    if ! grep -Fq "$token" <<<"$content"; then
        errors+=("devcontainer configuration must contain: $token")
    fi
done

required_startup_tokens=(
    "doplnky/automaticke-spustenie/codespace-start.sh"
)

for token in "${required_startup_tokens[@]}"; do
    if ! grep -Fq "$token" <<<"$startup_content"; then
        errors+=("Codespaces startup wrapper must contain: $token")
    fi
done

required_addon_tokens=(
    "php artisan serve --host=0.0.0.0 --port=\"\${PORT}\""
    "gh codespace ports visibility"
    "\${PORT}:public"
)

for token in "${required_addon_tokens[@]}"; do
    if ! grep -Fq "$token" <<<"$addon_content"; then
        errors+=("Automatic startup add-on must contain: $token")
    fi
done


if ! cmp -s "$DEVCONTAINER_FILE" "$ADDON_DEVCONTAINER"; then
    errors+=("$DEVCONTAINER_FILE differs from its add-on recovery copy")
fi

if ! cmp -s "$STARTUP_SCRIPT" "$ADDON_STARTUP_SCRIPT"; then
    errors+=("$STARTUP_SCRIPT differs from its add-on recovery copy")
fi

if (( ${#errors[@]} > 0 )); then
    echo "ERROR: Codespaces guards validation failed:"
    for msg in "${errors[@]}"; do
        echo "- $msg"
    done
    exit 1
fi

echo "OK: Codespaces guards are present and correctly configured."
