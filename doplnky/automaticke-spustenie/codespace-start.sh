#!/usr/bin/env bash

set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

PORT="${WEB_PORT:-8000}"
LOG_FILE="/tmp/web-interia-artisan.log"
PID_FILE="/tmp/web-interia-artisan.pid"
LOCAL_URL="http://127.0.0.1:${PORT}"
MEILISEARCH_VERSION="1.17.1"
MEILISEARCH_PORT="${MEILISEARCH_PORT:-7700}"
MEILISEARCH_URL="http://127.0.0.1:${MEILISEARCH_PORT}"
MEILISEARCH_KEY="${MEILISEARCH_KEY:-local-development-master-key}"
MEILISEARCH_BIN="${HOME}/.local/bin/meilisearch"
MEILISEARCH_DATA="${HOME}/.local/share/web-interia/meilisearch"
MEILISEARCH_LOG="/tmp/web-interia-meilisearch.log"

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

if ! curl -fsS -o /dev/null "${MEILISEARCH_URL}/health" 2>/dev/null; then
  if [[ ! -x "$MEILISEARCH_BIN" ]] || [[ "$($MEILISEARCH_BIN --version 2>/dev/null)" != "meilisearch ${MEILISEARCH_VERSION}" ]]; then
    case "$(uname -m)" in
      x86_64) meilisearch_arch="amd64" ;;
      aarch64|arm64) meilisearch_arch="aarch64" ;;
      *)
        log "CHYBA: Nepodporovana architektura pre Meilisearch: $(uname -m)"
        exit 1
        ;;
    esac

    log "Instalujem Meilisearch ${MEILISEARCH_VERSION}..."
    mkdir -p "$(dirname "$MEILISEARCH_BIN")"
    curl -fL "https://github.com/meilisearch/meilisearch/releases/download/v${MEILISEARCH_VERSION}/meilisearch-linux-${meilisearch_arch}" -o "$MEILISEARCH_BIN" || exit 1
    chmod +x "$MEILISEARCH_BIN"
  fi

  mkdir -p "$MEILISEARCH_DATA"
  log "Spustam Meilisearch na porte ${MEILISEARCH_PORT}."
  MEILI_ENV=development MEILI_MASTER_KEY="$MEILISEARCH_KEY" nohup "$MEILISEARCH_BIN" \
    --db-path "$MEILISEARCH_DATA" \
    --http-addr "127.0.0.1:${MEILISEARCH_PORT}" >"$MEILISEARCH_LOG" 2>&1 &

  meilisearch_ready=0
  for _ in $(seq 1 30); do
    if curl -fsS -o /dev/null "${MEILISEARCH_URL}/health" 2>/dev/null; then
      meilisearch_ready=1
      break
    fi
    sleep 1
  done

  if [[ "$meilisearch_ready" != "1" ]]; then
    log "CHYBA: Meilisearch neodpoveda. Posledne logy:"
    tail -n 20 "$MEILISEARCH_LOG" 2>/dev/null || true
    exit 1
  fi
fi

export SCOUT_DRIVER="meilisearch"
export MEILISEARCH_HOST="$MEILISEARCH_URL"
export MEILISEARCH_KEY

if ! curl -fsS -H "Authorization: Bearer ${MEILISEARCH_KEY}" "${MEILISEARCH_URL}/indexes/products" >/dev/null 2>&1; then
  log "Inicializujem produktovy vyhladavaci index."
  php artisan scout:sync-index-settings || exit 1
  php artisan scout:import 'App\Models\Product' || exit 1
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
  log "gh CLI chyba, instalujem ho pre nastavenie verejneho portu."
  sudo apt-get update >/dev/null 2>&1 || true
  sudo apt-get install -y gh >/dev/null 2>&1 || true
fi

if ! command -v gh >/dev/null 2>&1; then
  log "CHYBA: gh CLI sa nepodarilo nainstalovat."
  log "Nastav port ${PORT} na Public rucne v paneli Ports."
  exit 1
fi

TOKEN="${GH_TOKEN:-${GITHUB_TOKEN:-}}"
if [[ -z "$TOKEN" ]]; then
  TOKEN="$(gh auth token 2>/dev/null || true)"
fi

if [[ -z "$TOKEN" ]]; then
  log "Token nie je dostupny - nastav port ${PORT} na Public rucne v paneli Ports."
  exit 0
fi

visibility_set=0
for _ in $(seq 1 15); do
  if GH_TOKEN="$TOKEN" gh codespace ports visibility -c "$CODESPACE_NAME" "${PORT}:public" >/dev/null 2>&1; then
    visibility_set=1
    break
  fi
  sleep 2
done

if [[ "$visibility_set" != "1" ]]; then
  log "CHYBA: Port ${PORT} sa nepodarilo nastavit na Public:"
  GH_TOKEN="$TOKEN" gh codespace ports visibility -c "$CODESPACE_NAME" "${PORT}:public" 2>&1 || true
  exit 1
fi

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