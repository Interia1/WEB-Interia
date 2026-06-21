#!/usr/bin/env bash

set -euo pipefail

DEVCONTAINER_FILE=".devcontainer/devcontainer.json"

if [[ ! -f "$DEVCONTAINER_FILE" ]]; then
  echo "ERROR: Missing $DEVCONTAINER_FILE"
  exit 1
fi

python3 - <<'PY'
import json
import pathlib
import sys

path = pathlib.Path('.devcontainer/devcontainer.json')

try:
    data = json.loads(path.read_text(encoding='utf-8'))
except Exception as exc:
    print(f"ERROR: Invalid JSON in {path}: {exc}")
    sys.exit(1)

errors = []

forward_ports = data.get('forwardPorts', [])
if 8000 not in forward_ports:
    errors.append('forwardPorts must include 8000')

ports_attr = data.get('portsAttributes', {})
port8000 = ports_attr.get('8000', {})
if port8000.get('visibility') != 'public':
    errors.append('portsAttributes.8000.visibility must be public')

other_ports = data.get('otherPortsAttributes', {})
if other_ports.get('visibility') != 'public':
    errors.append('otherPortsAttributes.visibility must be public')

post_attach = data.get('postAttachCommand', '')
required_tokens = [
    'python3 -m http.server 8000 --bind 0.0.0.0',
    'gh codespace ports visibility',
    '8000:public',
]

for token in required_tokens:
    if token not in post_attach:
        errors.append(f'postAttachCommand must contain: {token}')

if errors:
    print('ERROR: Codespaces guards validation failed:')
    for msg in errors:
        print(f'- {msg}')
    sys.exit(1)

print('OK: Codespaces guards are present and correctly configured.')
PY
