# Automatické spustenie

Tento doplnok uchováva overený postup štartu aplikácie. Je to referenčný návod pre obnovu, ak sa štartovacie skripty alebo ich nastavenie neskôr poškodia.

## Nastavenie štartu

| Položka | Hodnota | Poznámka |
| --- | --- | --- |
| Aplikácia | Laravel (`php artisan serve`) | Server aplikácie |
| Host | `0.0.0.0` | Dostupné z počítača aj z lokálnej siete |
| Port | `8000` | Linux/macOS môže použiť `WEB_PORT` |
| PC URL | `http://127.0.0.1:8000` | Lokálne otvorenie v prehliadači |
| Mobil URL | `http://<LAN_IP>:8000` | Mobil musí byť na rovnakej Wi-Fi sieti |
| Log Linux/macOS | `/tmp/web-interia-artisan.log` | Výpis Laravel servera |
| Log Windows | `%TEMP%\web-interia-artisan.log` | Výpis Laravel servera |
| Záloha pred štartom | zapnutá | Vypne sa cez `WEB_AUTO_BACKUP=0` |

## Bežné spustenie

### Windows

V koreňovom priečinku projektu spustite `start.bat`. Súbor `OTVOR_WEB.bat` iba otvorí tento skript. Pred štartom sa vykoná `doplnky\automaticke-zalohy\backup-full.ps1`.

### Linux a macOS

V koreňovom priečinku projektu spustite:

```bash
chmod +x start.sh
./start.sh
```

Skript volá `scripts/backup-full.sh`, zastaví prípadný nesprávny Python server na zvolenom porte, spustí Laravel a čaká na odpoveď URL. Pre iný port použite napríklad:

```bash
WEB_PORT=8080 ./start.sh
```

Pri použití iného portu otvorte aj zodpovedajúcu PC a mobilnú URL s týmto portom.

## Codespaces

Pri otvorení alebo reštarte Codespace sa spustí `.devcontainer/codespace-start.sh`, ktorý zavolá skript `doplnky/automaticke-spustenie/codespace-start.sh`. Doplnok zabezpečí Laravel server na `0.0.0.0:8000`; pred opätovným použitím servera overí HTTP odpoveď stránky a nefunkčný proces automaticky reštartuje. Konfigurácia `.devcontainer/devcontainer.json` port `8000` forwarduje, nastaví ho na `Public` a automaticky otvorí jeho URL v prehliadači.

Toto funguje aj po úplnom zastavení a opätovnom otvorení Codespace. Aby nastavenie zostalo dostupné aj v novom Codespace alebo po odstránení aktuálneho Codespace, súbory doplnku a `.devcontainer` musia byť commitnuté a odoslané do GitHub repozitára.

Skript `start.sh` v Codespaces tiež nastaví port na `Public`, ak má k dispozícii `gh` a prihlasovací token. Inak nastavte port ručne v paneli Ports. Pre počítač aj mobil použite public URL portu, nie `localhost`.

Kontrolu konfigurácie spustíte z koreňa projektu:

```bash
bash scripts/enforce-codespaces-guards.sh
```

## Obnova pri probléme

1. Vytvorte plnú zálohu podľa doplnku [Automatické zálohy](../automaticke-zalohy/README.md).
2. Porovnajte aktuálne súbory `start.sh`, `start.bat`, `OTVOR_WEB.bat`, `scripts/backup-full.sh` a `doplnky/automaticke-zalohy/backup-full.ps1` s posledným funkčným stavom v Gite:

```bash
git diff HEAD -- start.sh start.bat OTVOR_WEB.bat scripts/backup-full.sh doplnky/automaticke-zalohy/backup-full.ps1
```

3. Obnovte iba poškodený súbor z posledného commitu, napríklad:

```bash
git restore --source=HEAD -- start.sh
chmod +x start.sh
```

4. Overte štart cez `./start.sh` alebo `start.bat`. Ak sa server neozve, pozrite uvedený log a overte, že PHP 8.2+ je dostupné.

Tento doplnok nemení bežné spúšťanie ani automaticky neprepisuje žiadne súbory.