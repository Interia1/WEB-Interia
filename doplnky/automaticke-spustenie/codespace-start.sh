#!/usr/bin/env bash

set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

PORT="${WEB_PORT:-8000}"
LOG_FILE="/tmp/web-interia-artisan.log"
PID_FILE="/tmp/web-interia-artisan.pid"
LOCAL_URL="http://127.0.0.1:${PORT}"

log() { printf '[web-interia] %s\n' "$*"; }

if ! command -v php >/dev/null 2>&1; then
  log "PHP chyba, instalujem runtime..."
  sudo apt-get update >/dev/null 2>&1 || true
  sudo apt-get install -y php-cli php-mbstring php-xml php-curl php-zip php-pgsql php-sqlite3 >/dev/null 2>&1 || true
fi

if ! command -v php >/dev/null 2>&1; then
  log "CHYBA: PHP sa nepodarilo nainstalovat."
  exit 1
fi

pkill -f "python3 -m http.server ${PORT}" >/dev/null 2>&1 || true

if curl -fsS -o /dev/null "${LOCAL_URL}" 2>/dev/null; then
  log "Laravel server uz bezi."
else
  if pgrep -f "[p]hp artisan serve --host=0.0.0.0 --port=${PORT}" >/dev/null 2>&1; then
    log "Nefunkcny Laravel server sa restartuje."
    pkill -f "[p]hp artisan serve --host=0.0.0.0 --port=${PORT}" >/dev/null 2>&1 || true
  fi
  log "Spustam: php artisan serve --host=0.0.0.0 --port=${PORT}"
  nohup php artisan serve --host=0.0.0.0 --port="${PORT}" >"${LOG_FILE}" 2>&1 &
  echo $! >"${PID_FILE}"
fi

ready=0
for _ in $(seq 1 60); do
  if curl -fsS -o /dev/null "${LOCAL_URL}" 2>/dev/null; then
    ready=1
    break
  fi
  sleep 1
done

if [[ "$ready" != "1" ]]; then
  log "CHYBA: Server neodpoveda na ${LOCAL_URL}. Posledne logy:"
  tail -n 20 "${LOG_FILE}" 2>/dev/null || true
  exit 1
fi
log "Server bezi: ${LOCAL_URL}"

if [[ -z "${CODESPACE_NAME:-}" ]]; then
  log "Mimo Codespaces - port ${PORT} ostava lokalny."
  exit 0
fi

if [[ -n "${BROWSER:-}" ]]; then
  log "Aktivujem Codespaces port forwarding pre port ${PORT}."
  "$BROWSER" "$LOCAL_URL" >/dev/null 2>&1 || true
fi

if ! command -v gh >/dev/null 2>&1; then
  log "gh CLI nie je dostupne - port bol otvoreny cez VS Code."
  log "Ak URL nie je verejna, nastav port ${PORT} na Public v paneli Ports."
  exit 0
fi

TOKEN="${GH_TOKEN:-${GITHUB_TOKEN:-}}"
if [[ -z "$TOKEN" ]]; then
  TOKEN="$(gh auth token 2>/dev/null || true)"
fi

if [[ -z "$TOKEN" ]]; then
  log "Token nie je dostupny - nastav port ${PORT} na Public rucne v paneli Ports."
  exit 0
fi

for _ in $(seq 1 15); do
  if GH_TOKEN="$TOKEN" gh codespace ports visibility -c "$CODESPACE_NAME" "${PORT}:public" >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

PUBLIC_URL="$(GH_TOKEN="$TOKEN" gh codespace ports -c "$CODESPACE_NAME" --json sourcePort,browseUrl --jq ".[] | select(.sourcePort==${PORT}) | .browseUrl" 2>/dev/null || true)"

if [[ -n "$PUBLIC_URL" ]]; then
  log "Port ${PORT} je PUBLIC."
  log "OTVOR STRANKU TU: ${PUBLIC_URL}"
  if [[ -n "${BROWSER:-}" ]]; then
    "$BROWSER" "$PUBLIC_URL" >/dev/null 2>&1 || true
  fi
else
  log "Port ${PORT} nastaveny na public - URL najdes v paneli Ports."
fi

exit 0