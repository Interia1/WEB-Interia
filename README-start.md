# 🚀 Ako spustiť WEB-Interia

Tento návod je pre každého – **bez technických znalostí**. Stačí jeden klik a web beží vo vašom prehliadači.

---

## 📋 Obsah

- [Prvá inštalácia](#prvá-inštalácia)
- [Ako spustiť](#ako-spustiť)
- [Codespaces: checklist pre verejné porty](#codespaces-checklist-pre-verejné-porty)
- [Trvalé ochrany po zmenách](#trvalé-ochrany-po-zmenách)
- [Čo sa stane](#čo-sa-stane-krok-za-krokom)
- [Troubleshooting](#troubleshooting--čo-robiť-ak-niečo-nefunguje)
- [Ako zastaviť](#ako-zastaviť-web)

---

## 🔧 Prvá inštalácia

Pred prvým spustením musíte nainštalovať tieto 2 veci (zadarmo):

### 1. Git

Git slúži na stiahnutie kódu z internetu.

| Systém | Odkaz |
|--------|-------|
| **Windows** | [git-scm.com/downloads](https://git-scm.com/downloads) |
| **Mac** | Vstavaný – stačí otvoriť Terminal a napísať `git --version` |
| **Linux** | `sudo apt install git` |

### 2. Docker Desktop

Docker spúšťa web v izolovanom prostredí – nemusíte nič ďalej inštalovať.

| Systém | Odkaz |
|--------|-------|
| **Windows** | [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop/) |
| **Mac** | [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop/) |
| **Linux** | [docs.docker.com/engine/install](https://docs.docker.com/engine/install/) |

> ⚠️ **Dôležité:** Po inštalácii spustite **Docker Desktop** a počkajte kým sa plne naštartuje (ikona v lište by mala byť zelená / bežiaca).

### 3. Stiahnite projekt (iba raz)

Otvorte **Terminál** (Mac/Linux) alebo **Command Prompt** (Windows) a zadajte:

```bash
git clone https://github.com/Interia1/WEB-Interia.git
cd WEB-Interia
```

To je všetko! Teraz môžete spúšťať web cez skript.

---

## ▶️ Ako spustiť

### 🪟 Windows

1. Otvorte priečinok **WEB-Interia**
2. **Dvakrát kliknite** na súbor `start.bat`
3. Ak sa opýta bezpečnosť Windows – kliknite **„Spustiť aj tak"**
4. Počkajte ~30 sekúnd
5. **Prehliadač sa otvorí sám** ✅

> **Alternatíva:** Otvorte Command Prompt v priečinku a napíšte `start.bat`

---

### 🍎 Mac

1. Otvorte **Terminál** (hľadajte v Spotlight: Terminál)
2. Napíšte:
   ```bash
   cd WEB-Interia
   ./start.sh
   ```
3. Počkajte ~30 sekúnd
4. **Prehliadač sa otvorí sám** ✅

> **Prvý raz:** Ak systém hovorí "nie je povolené", spustite najprv:
> ```bash
> chmod +x start.sh
> ```

---

### 🐧 Linux

1. Otvorte **Terminál**
2. Napíšte:
   ```bash
   cd WEB-Interia
   ./start.sh
   ```
3. Počkajte ~30 sekúnd
4. **Prehliadač sa otvorí sám** (alebo otvorte manuálne) ✅

---

## 🌐 Codespaces: checklist pre verejné porty

Ak bežíte projekt v GitHub Codespaces a link nefunguje mimo vášho účtu:

1. Otvorte panel **Ports** vo VS Code.
2. Pri porte `8000` nastavte **Port Visibility -> Public**.
3. To isté urobte pre `8001` (preview) a `5432` (PostgreSQL), ak ich používate.
4. Otvorte URL z panelu Ports, nie `localhost` z vášho počítača.
5. Ak port ostáva private, použite **Rebuild Container** alebo sa odpojte/pripojte znova.

> Konfigurácia v projekte už nastavuje porty ako `public` default, ale existujúce porty v aktuálnej relácii môžu zostať pôvodne private.

---

## 🛡️ Trvalé ochrany po zmenách

Aby sa problém po ďalších úpravách neopakoval, projekt má teraz zabudované tieto kontroly:

1. `postAttachCommand` v `.devcontainer/devcontainer.json` automaticky štartuje server na porte `8000`.
2. Ten istý príkaz nastavuje porty na `public` (po jednom porte, aby chyba jedného portu nezhodila celý krok).
3. CI workflow `.github/workflows/codespaces-guards.yml` beží na `push` aj `pull_request`.
4. CI spúšťa skript `scripts/enforce-codespaces-guards.sh`, ktorý overuje, že ochrany stále existujú.

Lokálne overenie pred push:

```bash
bash scripts/enforce-codespaces-guards.sh
```

Ak kontrola zlyhá, zmena sa má opraviť ešte pred mergom.

---

## 📖 Čo sa stane (krok za krokom)

Keď spustíte skript, automaticky prebehne toto:

| Krok | Čo robí | Čas |
|------|---------|-----|
| **1** | Skontroluje, či je Docker nainštalovaný a spustený | ~2 sek |
| **2** | Skontroluje, či web už nebeží (ak áno, spýta sa čo chcete robiť) | ~2 sek |
| **3** | Stiahne najnovší kód z GitHubu (vrátane zlúčenia PR ak existuje) | ~10 sek |
| **4** | Spustí Docker kontajnery s webom | ~10 sek |
| **5** | Počká kým sa aplikácia nabootuje | ~30 sek |
| **6** | Automaticky otvorí prehliadač na `http://localhost:8000` | hneď |

**Celkový čas: ~1 minúta**

---

## 🔧 Troubleshooting – čo robiť ak niečo nefunguje

### ❌ „Docker nie je nainštalovaný"

**Riešenie:** Stiahnite a nainštalujte Docker Desktop:
👉 [https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop/)

---

### ❌ „Docker nie je spustený"

**Riešenie:**
1. Nájdite aplikáciu **Docker Desktop** na počítači
2. Spustite ju
3. Počkajte kým sa naštartuje (môže trvať 1-2 minúty)
4. Spustite skript znova

---

### ❌ „docker-compose.yml nebol nájdený"

**Riešenie:** Ste možno v nesprávnom priečinku. Uistite sa, že ste v priečinku `WEB-Interia`:
```bash
cd WEB-Interia
ls   # (Mac/Linux) – mali by ste vidieť docker-compose.yml
dir  # (Windows)   – mali by ste vidieť docker-compose.yml
```

---

### ❌ Web sa neotvára v prehliadači (Mac/Linux)

**Riešenie:** Otvorte prehliadač manuálne a zadajte adresu:
```
http://localhost:8000
```

---

### ❌ Stránka hovorí „Nemôžem sa pripojiť" alebo „Stránka nie je dostupná"

**Riešenie:** Aplikácia sa ešte štartuje. Počkajte ďalších 30 sekúnd a obnovte stránku (F5).

---

### ❌ Port 8000 je obsadený

Ak iná aplikácia používa port 8000:
```bash
# Zastavte web
docker-compose down

# Alebo skontrolujte čo beží na porte 8000
# Mac/Linux:
lsof -i :8000

# Windows:
netstat -ano | findstr :8000
```

Alternatíva: spustite na inom porte:
```bash
WEB_PORT=8090 ./start.sh
```

---

### ❌ Docker-compose zlyhalo s chybou

```bash
# Skúste manuálne:
docker-compose down
docker-compose up -d

# Ak stále nefunguje, skontrolujte logy:
docker-compose logs
```

---

### 📞 Ďalšia pomoc

Ak nič nefunguje, kontaktujte správcu projektu s týmto výpisom:
```bash
docker-compose logs
```

---

## ⏹️ Ako zastaviť web

Keď skončíte prácu, zastavte Docker kontajnery, aby nezaberali zdroje počítača:

### Mac / Linux:
```bash
cd WEB-Interia
docker-compose down
```

### Windows (Command Prompt):
```bash
cd WEB-Interia
docker-compose down
```

> Po spustení `docker-compose down` web prestane byť dostupný na `http://localhost:8000`.
> Kedykoľvek ho môžete znova spustiť cez `start.sh` / `start.bat`.

---

## 📍 Rýchly prehľad príkazov

| Akcia | Mac/Linux | Windows |
|-------|-----------|---------|
| Spustiť web | `./start.sh` | `start.bat` |
| Zastaviť web | `docker-compose down` | `docker-compose down` |
| Pozrieť logy | `docker-compose logs` | `docker-compose logs` |
| Reštartovať | `docker-compose restart` | `docker-compose restart` |

---

*Ak máte otázky alebo problémy, otvorte [Issue na GitHub](https://github.com/Interia1/WEB-Interia/issues).*
