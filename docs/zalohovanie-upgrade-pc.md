# Plna zaloha a stiahnutie do PC

## Vytvorenie zalohy vo WEB-Interia-backups

1. V Codespaces otvorte terminal v priecinku `WEB-Interia`.
2. Spustite tento prikaz:

```bash
./scripts/backup-full.sh
```

3. Pockajte na spravu `Full backup created`.
4. Zaloha sa ulozi sem:

```text
/workspaces/WEB-Interia-backups/
```

5. Najnovsi subor ma nazov podobny tomuto:

```text
WEB-Interia-FULL-YYYYMMDD-HHMMSS.tar.gz
```

## Stiahnutie zalohy do PC

1. Vo VS Code vlavo otvorte Explorer.
2. Otvorte priecinok `WEB-Interia-backups`.
3. Pravym tlacidlom kliknite na najnovsi subor `.tar.gz`.
4. Vyberte `Download`.
5. Ulozte subor do bezpecneho priecinka na PC, napriklad `C:\WEB-Interia-zaloha\`.

## Obnova zalohy

1. Na PC rozbalte stiahnuty `.tar.gz` archiv do noveho priecinka.
2. Otvorte rozbaleny priecinok vo VS Code.
3. Spustite web prikazom:

```bash
./start.sh
```

Nevpisujte rozbalene subory cez aktualny projekt, kym nemate overene, ze zaloha funguje.
