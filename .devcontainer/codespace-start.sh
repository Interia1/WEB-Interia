#!/usr/bin/env bash

set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

php "${ROOT_DIR}/doplnky/vypnutie-predikcie-chatu/disable-predictions.php"
bash "${ROOT_DIR}/doplnky/automaticke-spustenie/codespace-start.sh"
