# Záloha WEB-Interia

Tento návod použite vždy na konci práce, pred vypnutím Codespace alebo pred väčšou úpravou webu.

## 1. Vytvorenie zálohy

1. V Codespaces otvorte spodný panel **Terminal**.
2. Skontrolujte, že pred kurzorom vidíte cestu `/workspaces/WEB-Interia`.
3. Vložte tento príkaz a stlačte Enter:

```bash
./scripts/backup-full.sh
```

4. Počkajte, kým sa zobrazí text `Full backup created`.
5. Záloha je hotová. Je to nový súbor, ktorý sa začína názvom `WEB-Interia-FULL-` a končí `.tar.gz`.

## 2. Stiahnutie zálohy do PC

1. Vo VS Code kliknite vľavo na ikonu súborov, teda **Explorer**.
2. V zozname priečinkov nájdite a otvorte `WEB-Interia-backups`.
3. Nájdite najnovší súbor, ktorý sa začína `WEB-Interia-FULL-` a končí `.tar.gz`.
4. Kliknite na tento súbor pravým tlačidlom myši.
5. Kliknite na **Download**.
6. Počkajte na stiahnutie a súbor si nechajte uložený v PC, napríklad v priečinku `Dokumenty/WEB-Interia-zalohy`.

## 3. Obnova zálohy v PC

1. Nájdite v PC stiahnutý súbor `.tar.gz`.
2. Rozbaľte ho do nového prázdneho priečinka, napríklad `WEB-Interia-obnova`.
3. Vo VS Code zvoľte **File** -> **Open Folder**.
4. Vyberte priečinok `WEB-Interia`, ktorý vznikol po rozbalení zálohy.
5. V otvorenom priečinku spustite web ako obvykle.

Nerozbaľujte zálohu priamo cez aktuálny projekt. Najskôr ju vždy rozbaľte do nového priečinka.

## Automatická záloha pri štarte

Pri spustení webu cez `./start.sh` alebo `start.bat` sa plná záloha vytvorí automaticky aj bez týchto krokov.