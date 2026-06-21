#!/bin/bash

# =============================================================================
# start.sh - Automatický štart WEB-Interia (Linux/Mac)
# Použitie: ./start.sh
# =============================================================================

set -e

WEB_PORT="${WEB_PORT:-8000}"
WEB_URL="http://localhost:${WEB_PORT}"

start_codespaces_public_port_guard() {
    if [ -z "${CODESPACE_NAME:-}" ]; then
        return 0
    fi

    if ! command -v gh &> /dev/null; then
        echo -e "${YELLOW}   ⚠️  gh CLI nie je dostupne, trvale verejne porty sa nedaju automaticky udrzat${NC}"
        return 0
    fi

    local token="${GH_TOKEN:-${GITHUB_TOKEN:-}}"
    if [ -z "${token}" ]; then
        echo -e "${YELLOW}   ⚠️  Chyba token (GH_TOKEN/GITHUB_TOKEN), trvale verejne porty sa nespustili${NC}"
        return 0
    fi

    local db_port="${DB_PORT:-5432}"
    local interval="${PORT_VISIBILITY_INTERVAL:-60}"
    local guard_pid_file="/tmp/web-interia-public-ports.pid"
    local guard_log_file="/tmp/web-interia-public-ports.log"

    if [ -f "${guard_pid_file}" ]; then
        local existing_pid
        existing_pid="$(cat "${guard_pid_file}" 2>/dev/null || true)"
        if [ -n "${existing_pid}" ] && kill -0 "${existing_pid}" 2>/dev/null; then
            echo -e "${GREEN}   ✅ Port guard uz bezi (PID: ${existing_pid})${NC}"
            return 0
        fi
    fi

    nohup env GH_TOKEN="${token}" CODESPACE_NAME="${CODESPACE_NAME}" WEB_PORT="${WEB_PORT}" DB_PORT="${db_port}" PORT_VISIBILITY_INTERVAL="${interval}" \
        bash -lc 'while true; do gh codespace ports visibility -c "$CODESPACE_NAME" "${WEB_PORT}:public" >/dev/null 2>&1 || true; gh codespace ports visibility -c "$CODESPACE_NAME" 8001:public >/dev/null 2>&1 || true; gh codespace ports visibility -c "$CODESPACE_NAME" "${DB_PORT}:public" >/dev/null 2>&1 || true; sleep "$PORT_VISIBILITY_INTERVAL"; done' \
        >"${guard_log_file}" 2>&1 &

    echo $! >"${guard_pid_file}"
    echo -e "${GREEN}   ✅ Port guard bezi na pozadi (kazdych ${interval}s)${NC}"
}

start_codespaces_static_server() {
    local preview_port="${PREVIEW_PORT:-8001}"

    echo -e "${BLUE}[Codespaces] Docker nie je dostupny, spustam lokalny static preview...${NC}"

    pgrep -f "python3 -m http.server ${WEB_PORT} --bind 0.0.0.0" >/dev/null || \
        (nohup python3 -m http.server "${WEB_PORT}" --bind 0.0.0.0 >/tmp/web-interia.log 2>&1 & echo $! >/tmp/web-interia.pid)

    pgrep -f "python3 -m http.server ${preview_port} --bind 0.0.0.0" >/dev/null || \
        (nohup python3 -m http.server "${preview_port}" --bind 0.0.0.0 >/tmp/web-interia-preview.log 2>&1 & echo $! >/tmp/web-interia-preview.pid)

    if command -v gh &> /dev/null && [ -n "${CODESPACE_NAME:-}" ]; then
        local token="${GH_TOKEN:-${GITHUB_TOKEN:-}}"
        if [ -n "${token}" ]; then
            GH_TOKEN="${token}" gh codespace ports visibility -c "${CODESPACE_NAME}" "${WEB_PORT}:public" >/dev/null 2>&1 || true
            GH_TOKEN="${token}" gh codespace ports visibility -c "${CODESPACE_NAME}" "${preview_port}:public" >/dev/null 2>&1 || true
            start_codespaces_public_port_guard
        fi
    fi

    echo -e "${GREEN}   ✅ Static preview bezi na: ${CYAN}${WEB_URL}${NC}"
    echo ""
    echo "   Otvaram prehliadač..."
    echo ""

    if [[ "$OSTYPE" == "darwin"* ]]; then
        open "${WEB_URL}"
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        if command -v xdg-open &> /dev/null; then
            xdg-open "${WEB_URL}" 2>/dev/null || true
        fi
    fi

    exit 0
}

# Ak skript bezi v GitHub Codespaces, pokus sa automaticky prepnut porty na verejne.
set_codespaces_public_ports() {
    if [ -z "${CODESPACE_NAME:-}" ]; then
        return 0
    fi

    if [ -z "${GITHUB_TOKEN:-}" ] && [ -z "${GH_TOKEN:-}" ]; then
        echo -e "${YELLOW}   ⚠️  Codespaces token nebol najdeny, porty prepnite rucne na Public v paneli Ports${NC}"
        return 0
    fi

    if ! command -v gh &> /dev/null; then
        if command -v sudo &> /dev/null; then
            echo -e "${YELLOW}   ℹ️  Instalujem GitHub CLI pre automaticke prepnutie portov...${NC}"
            sudo apt-get update -y >/dev/null 2>&1 || true
            sudo apt-get install -y gh >/dev/null 2>&1 || true
        fi
    fi

    if command -v gh &> /dev/null; then
        local token="${GH_TOKEN:-${GITHUB_TOKEN:-}}"
        local db_port="${DB_PORT:-5432}"
        GH_TOKEN="${token}" gh codespace ports visibility -c "${CODESPACE_NAME}" "${WEB_PORT}:public" >/dev/null 2>&1 || true
        GH_TOKEN="${token}" gh codespace ports visibility -c "${CODESPACE_NAME}" 8001:public >/dev/null 2>&1 || true
        GH_TOKEN="${token}" gh codespace ports visibility -c "${CODESPACE_NAME}" "${db_port}:public" >/dev/null 2>&1 || true
        echo -e "${GREEN}   ✅ Codespaces porty su nastavene na Public (alebo uz boli)${NC}"
    else
        echo -e "${YELLOW}   ⚠️  Nepodarilo sa pripravit gh CLI, porty prepnite rucne na Public v paneli Ports${NC}"
    fi
}

# Farby pre terminál
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

echo ""
echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   🚀  WEB-Interia - Automatický štart  ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

# ─────────────────────────────────────────
# KROK 1: Kontrola Docker
# ─────────────────────────────────────────
echo -e "${YELLOW}[1/5] Kontrolujem Docker...${NC}"

if ! command -v docker &> /dev/null; then
    if [ -n "${CODESPACE_NAME:-}" ]; then
        start_codespaces_static_server
    fi

    echo ""
    echo -e "${RED}╔══════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║  ❌  Docker nie je nainštalovaný!                    ║${NC}"
    echo -e "${RED}╚══════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "   Docker je potrebný na spustenie webu."
    echo ""
    echo "   ➡️  Stiahnite Docker Desktop zadarmo:"
    echo -e "   ${CYAN}https://www.docker.com/products/docker-desktop/${NC}"
    echo ""
    echo "   Po inštalácii spustite Docker Desktop a"
    echo "   spustite tento skript znova."
    echo ""
    exit 1
fi

if ! docker info &> /dev/null 2>&1; then
    echo ""
    echo -e "${RED}╔══════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║  ❌  Docker nie je spustený!                         ║${NC}"
    echo -e "${RED}╚══════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "   Otvorte aplikáciu Docker Desktop a počkajte kým sa spustí."
    echo "   Potom spustite tento skript znova."
    echo ""
    exit 1
fi

echo -e "${GREEN}   ✅ Docker je nainštalovaný a spustený${NC}"

# Volitelny krok pre Codespaces: spristupni forwardovane porty verejne.
if [ -n "${CODESPACE_NAME:-}" ]; then
    echo ""
    echo -e "${YELLOW}[Codespaces] Nastavujem porty na Public...${NC}"
    set_codespaces_public_ports
    start_codespaces_public_port_guard
fi

# ─────────────────────────────────────────
# KROK 2: Kontrola, či web už beží
# ─────────────────────────────────────────
echo ""
echo -e "${YELLOW}[2/5] Kontrolujem stav webu...${NC}"

if command -v lsof &> /dev/null && lsof -i ":${WEB_PORT}" -sTCP:LISTEN -t &> /dev/null; then
    echo ""
    echo -e "${RED}╔══════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║  ❌  Port ${WEB_PORT} je už obsadený!                    ║${NC}"
    echo -e "${RED}╚══════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "   Uvoľnite port alebo spustite skript s iným portom:"
    echo -e "   ${CYAN}WEB_PORT=8090 ./start.sh${NC}"
    echo ""
    exit 1
fi

if docker ps --format '{{.Names}}' 2>/dev/null | grep -qiE "interia|laravel|app|nginx|php"; then
    echo ""
    echo -e "${YELLOW}   ⚠️  Web sa zdá byť už spustený!${NC}"
    echo ""
    echo -e "   Otvorte prehliadač na: ${CYAN}${WEB_URL}${NC}"
    echo ""
    read -rp "   Chcete web reštartovať? (y/n): " restart_choice
    if [[ "$restart_choice" =~ ^[Yy]$ ]]; then
        echo "   Zastavujem starú inštanciu..."
        docker-compose down 2>/dev/null || true
        echo -e "${GREEN}   ✅ Staré kontajnery zastavené${NC}"
    else
        echo ""
        echo -e "${GREEN}   ✅ Web beží na: ${CYAN}${WEB_URL}${NC}"
        echo ""
        # Otvorenie prehliadača
        if [[ "$OSTYPE" == "darwin"* ]]; then
            open "${WEB_URL}"
        else
            xdg-open "${WEB_URL}" 2>/dev/null || true
        fi
        exit 0
    fi
fi

# ─────────────────────────────────────────
# KROK 3: Stiahnutie najnovšieho kódu
# ─────────────────────────────────────────
echo ""
echo -e "${YELLOW}[3/5] Sťahujem najnovší kód...${NC}"

# Pokus o zlúčenie najnovšieho otvoreného PR (ak je GitHub CLI dostupný)
if command -v gh &> /dev/null; then
    echo "   Hľadám otvorené pull requesty..."
    LATEST_PR=$(gh pr list --state open --limit 1 --json number --jq '.[0].number' 2>/dev/null || echo "")
    if [ -n "$LATEST_PR" ] && [ "$LATEST_PR" != "null" ]; then
        PR_TITLE=$(gh pr view "$LATEST_PR" --json title --jq '.title' 2>/dev/null || echo "PR #$LATEST_PR")
        PR_AUTHOR=$(gh pr view "$LATEST_PR" --json author --jq '.author.login' 2>/dev/null || echo "neznámy")
        echo ""
        echo -e "   Nájdený otvorený PR:"
        echo -e "   📌 PR #${LATEST_PR}: \"${PR_TITLE}\" (od: ${PR_AUTHOR})"
        echo ""
        read -rp "   Chcete zlúčiť tento PR do main? (y/n): " merge_choice
        if [[ "$merge_choice" =~ ^[Yy]$ ]]; then
            if gh pr merge "$LATEST_PR" --merge 2>/dev/null; then
                echo -e "${GREEN}   ✅ PR #${LATEST_PR} zlúčený do main${NC}"
                sleep 2
            else
                echo -e "${YELLOW}   ⚠️  PR nebolo možné zlúčiť (pokračujem...)${NC}"
            fi
        else
            echo "   Preskakujem zlúčenie PR."
        fi
    else
        echo "   Žiadne otvorené PR nenájdené."
    fi
fi

# Git pull
if git pull origin main 2>/dev/null || git pull 2>/dev/null; then
    echo -e "${GREEN}   ✅ Kód je aktuálny${NC}"
else
    echo -e "${YELLOW}   ⚠️  Git pull zlyhal (pokračujem s aktuálnym kódom)${NC}"
fi

# Kontrola docker-compose.yml
if [ ! -f "docker-compose.yml" ] && [ ! -f "docker-compose.yaml" ]; then
    echo ""
    echo -e "${RED}╔══════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║  ❌  Súbor docker-compose.yml nebol nájdený!         ║${NC}"
    echo -e "${RED}╚══════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "   Uistite sa, že:"
    echo "   1. Ste v správnom priečinku projektu (WEB-Interia)"
    echo "   2. Bol zlúčený PR s Docker nastavením"
    echo ""
    exit 1
fi

# ─────────────────────────────────────────
# KROK 4: Spustenie Docker
# ─────────────────────────────────────────
echo ""
echo -e "${YELLOW}[4/5] Spúšťam web (Docker)...${NC}"

if ! docker-compose up -d; then
    echo ""
    echo -e "${RED}╔══════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║  ❌  Spustenie zlyhalo!                              ║${NC}"
    echo -e "${RED}╚══════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "   Skúste:"
    echo "   1. docker-compose down"
    echo "   2. docker-compose up -d"
    echo "   alebo skontrolujte logy: docker-compose logs"
    echo ""
    exit 1
fi

echo -e "${GREEN}   ✅ Docker kontajnery spustené${NC}"

# ─────────────────────────────────────────
# KROK 5: Čakanie na nabootovanie
# ─────────────────────────────────────────
echo ""
echo -e "${YELLOW}[5/5] Čakám kým sa aplikácia nabootuje...${NC}"
echo ""

WAIT_SECONDS=30
for ((i=WAIT_SECONDS; i>=1; i--)); do
    printf "\r   ⏳ Zostáva: %2ds  " "$i"
    sleep 1
done
echo -e "\r   ${GREEN}✅ Hotovo!                    ${NC}"

# ─────────────────────────────────────────
# VÝSLEDOK
# ─────────────────────────────────────────
echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅  WEB-Interia je spustený!         ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "   🌐 Adresa webu: ${CYAN}${WEB_URL}${NC}"
echo ""
echo "   Otváram prehliadač..."
echo ""

# Otvorenie prehliadača
if [[ "$OSTYPE" == "darwin"* ]]; then
    open "${WEB_URL}"
elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
    if command -v xdg-open &> /dev/null; then
        xdg-open "${WEB_URL}" 2>/dev/null
    elif command -v sensible-browser &> /dev/null; then
        sensible-browser "${WEB_URL}" 2>/dev/null
    elif command -v gnome-open &> /dev/null; then
        gnome-open "${WEB_URL}" 2>/dev/null
    else
        echo -e "   ${YELLOW}Otvorte manuálne v prehliadači: ${WEB_URL}${NC}"
    fi
fi

echo -e "   💡 Na zastavenie webu spustite: ${YELLOW}docker-compose down${NC}"
echo ""
