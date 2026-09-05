#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

PORT="${WEB_PORT:-8000}"
LOCAL_URL="http://127.0.0.1:${PORT}"
LOG_FILE="/tmp/web-interia-artisan.log"
PID_FILE="/tmp/web-interia-artisan.pid"

msg() { printf "%s\n" "$*"; }
err() { printf "ERROR: %s\n" "$*" >&2; }

require_project_root() {
  if [[ ! -f artisan ]]; then
    err "Subor artisan nebol najdeny. Spustite skript v priecinku WEB-Interia."
    exit 1
  fi
}

install_php_if_needed_codespaces() {
  if command -v php >/dev/null 2>&1; then
    return 0
  fi

  if [[ -n "${CODESPACE_NAME:-}" ]] && command -v sudo >/dev/null 2>&1; then
    msg "PHP nie je dostupne, instalujem runtime..."
    sudo apt-get update >/dev/null 2>&1 || true
    sudo apt-get install -y php-cli php-mbstring php-xml php-curl php-zip php-sqlite3 >/dev/null 2>&1 || true
  fi
}

require_php() {
  if command -v php >/dev/null 2>&1; then
    return 0
  fi

  err "PHP nie je dostupne. Nainstalujte PHP 8.2+ a skuste znova."
  exit 1
}

create_startup_backup() {
  if [[ "${WEB_AUTO_BACKUP:-1}" == "0" ]]; then
    msg "Automaticka zaloha je pre tento start vypnuta."
    return 0
  fi

  msg "Vytvaram automaticku zalohu pred spustenim..."
  "${ROOT_DIR}/scripts/backup-full.sh"
}

stop_wrong_server() {
  pkill -f "python3 -m http.server ${PORT} --bind 0.0.0.0" >/dev/null 2>&1 || true
}

start_laravel() {
  if pgrep -f "php artisan serve --host=0.0.0.0 --port=${PORT}" >/dev/null 2>&1; then
    msg "Laravel server uz bezi na porte ${PORT}."
    return 0
  fi

  msg "Spustam Laravel server na porte ${PORT}..."
  nohup php artisan serve --host=0.0.0.0 --port="${PORT}" >"${LOG_FILE}" 2>&1 &
  echo $! >"${PID_FILE}"
}

wait_for_server() {
  local i
  for i in $(seq 1 25); do
    if curl -fsS "${LOCAL_URL}" >/dev/null 2>&1; then
      return 0
    fi
    sleep 1
  done

  err "Server neodpoveda na ${LOCAL_URL}."
  if [[ -f "${LOG_FILE}" ]]; then
    msg "Posledne logy:"
    tail -n 40 "${LOG_FILE}" || true
  fi
  exit 1
}

open_browser() {
  local url="$1"

  if [[ -n "${BROWSER:-}" ]]; then
    "$BROWSER" "$url" >/dev/null 2>&1 || true
    return 0
  fi

  if command -v xdg-open >/dev/null 2>&1; then
    xdg-open "$url" >/dev/null 2>&1 || true
  elif command -v open >/dev/null 2>&1; then
    open "$url" >/dev/null 2>&1 || true
  fi
}

print_local_and_mobile_urls() {
  local lan_ip
  lan_ip="$(hostname -I 2>/dev/null | awk '{print $1}')"

  msg ""
  msg "WEB-Interia bezi."
  msg "PC:     ${LOCAL_URL}"
  if [[ -n "$lan_ip" ]]; then
    msg "Mobile: http://${lan_ip}:${PORT}"
    msg "Poznamka: mobil musi byt na rovnakej Wi-Fi sieti."
  fi
}

set_codespaces_public() {
  local token public_url
  token="${GH_TOKEN:-${GITHUB_TOKEN:-}}"

  if [[ -z "${CODESPACE_NAME:-}" ]]; then
    return 1
  fi

  if [[ -z "$token" ]] && command -v gh >/dev/null 2>&1; then
    token="$(gh auth token 2>/dev/null || true)"
  fi

  if [[ -z "$token" ]] || ! command -v gh >/dev/null 2>&1; then
    msg "Codespaces: nastavte port ${PORT} na Public v paneli Ports."
    return 1
  fi

  GH_TOKEN="$token" gh codespace ports visibility -c "$CODESPACE_NAME" "${PORT}:public" >/dev/null 2>&1 || true

  public_url="$(GH_TOKEN="$token" gh codespace ports -c "$CODESPACE_NAME" --json sourcePort,browseUrl --jq '.[] | select(.sourcePort==8000) | .browseUrl' 2>/dev/null || true)"

  msg ""
  msg "WEB-Interia bezi v Codespaces."
  if [[ -n "$public_url" ]]; then
    msg "Public URL: ${public_url}"
    open_browser "$public_url"
    return 0
  fi

  msg "Otvorte URL portu 8000 z panelu Ports."
  return 1
}

main() {
  require_project_root
  install_php_if_needed_codespaces
  require_php
  create_startup_backup
  stop_wrong_server
  start_laravel
  wait_for_server

  if [[ -n "${CODESPACE_NAME:-}" ]]; then
    set_codespaces_public || true
    return 0
  fi

  print_local_and_mobile_urls
  open_browser "$LOCAL_URL"
}

main "$@"
