#!/bin/bash

# =============================================================================
# start.sh - Automatický štart WEB-Interia (Linux/Mac)
# Použitie: ./start.sh
# =============================================================================

set -e

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

# ─────────────────────────────────────────
# KROK 2: Kontrola, či web už beží
# ─────────────────────────────────────────
echo ""
echo -e "${YELLOW}[2/5] Kontrolujem stav webu...${NC}"

if docker ps --format '{{.Names}}' 2>/dev/null | grep -qiE "interia|laravel|app|nginx|php"; then
    echo ""
    echo -e "${YELLOW}   ⚠️  Web sa zdá byť už spustený!${NC}"
    echo ""
    echo -e "   Otvorte prehliadač na: ${CYAN}http://localhost:8000${NC}"
    echo ""
    read -rp "   Chcete web reštartovať? (y/n): " restart_choice
    if [[ "$restart_choice" =~ ^[Yy]$ ]]; then
        echo "   Zastavujem starú inštanciu..."
        docker-compose down 2>/dev/null || true
        echo -e "${GREEN}   ✅ Staré kontajnery zastavené${NC}"
    else
        echo ""
        echo -e "${GREEN}   ✅ Web beží na: ${CYAN}http://localhost:8000${NC}"
        echo ""
        # Otvorenie prehliadača
        if [[ "$OSTYPE" == "darwin"* ]]; then
            open "http://localhost:8000"
        else
            xdg-open "http://localhost:8000" 2>/dev/null || true
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
        echo "   Mergujem: \"${PR_TITLE}\""
        if gh pr merge "$LATEST_PR" --merge --auto 2>/dev/null; then
            echo -e "${GREEN}   ✅ PR #${LATEST_PR} zlúčený do main${NC}"
            sleep 2
        else
            echo -e "${YELLOW}   ⚠️  PR nebolo možné zlúčiť automaticky (pokračujem...)${NC}"
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
echo -e "   🌐 Adresa webu: ${CYAN}http://localhost:8000${NC}"
echo ""
echo "   Otváram prehliadač..."
echo ""

# Otvorenie prehliadača
if [[ "$OSTYPE" == "darwin"* ]]; then
    open "http://localhost:8000"
elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
    if command -v xdg-open &> /dev/null; then
        xdg-open "http://localhost:8000" 2>/dev/null
    elif command -v sensible-browser &> /dev/null; then
        sensible-browser "http://localhost:8000" 2>/dev/null
    elif command -v gnome-open &> /dev/null; then
        gnome-open "http://localhost:8000" 2>/dev/null
    else
        echo -e "   ${YELLOW}Otvorte manuálne v prehliadači: http://localhost:8000${NC}"
    fi
fi

echo -e "   💡 Na zastavenie webu spustite: ${YELLOW}docker-compose down${NC}"
echo ""
