#!/usr/bin/env bash
# Zachovane pre spatnu kompatibilitu (starsi registrovany hook).
# Vsetka logika je v codespace-start.sh.

set -uo pipefail

bash "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/codespace-start.sh"
