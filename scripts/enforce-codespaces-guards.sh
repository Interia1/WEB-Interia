#!/usr/bin/env bash

set -euo pipefail

DEVCONTAINER_FILE=".devcontainer/devcontainer.json"

if [[ ! -f "$DEVCONTAINER_FILE" ]]; then
  echo "ERROR: Missing $DEVCONTAINER_FILE"
  exit 1
fi

content="$(cat "$DEVCONTAINER_FILE")"
errors=()

if ! grep -Eq '"forwardPorts"[[:space:]]*:[[:space:]]*\[[^]]*8000' <<<"$content"; then
    errors+=("forwardPorts must include 8000")
fi

if ! grep -Fq '"8000": {' <<<"$content"; then
    errors+=("portsAttributes.8000.visibility must be public")
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

required_tokens=(
    "php artisan serve --host=0.0.0.0 --port=8000"
    "gh codespace ports visibility"
    "8000:public"
)

for token in "${required_tokens[@]}"; do
    if ! grep -Fq "$token" <<<"$content"; then
        errors+=("postAttachCommand must contain: $token")
    fi
done

if (( ${#errors[@]} > 0 )); then
    echo "ERROR: Codespaces guards validation failed:"
    for msg in "${errors[@]}"; do
        echo "- $msg"
    done
    exit 1
fi

echo "OK: Codespaces guards are present and correctly configured."
