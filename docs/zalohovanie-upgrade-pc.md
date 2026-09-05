# Zalohovanie a postupny upgrade na PC

Tento postup pouzivajte pred kazdym vacsim krokom upravy alebo upgradu.

## 1. Vytvorenie plnej zalohy v Codespaces

V terminali spustite:

```bash
./scripts/backup-full.sh
```

Skript vytvori subor v priecinku:

```text
/workspaces/WEB-Interia-backups/
```

Nazov bude podobny:

```text
WEB-Interia-FULL-YYYYMMDD-HHMMSS.tar.gz
```

Plna zaloha obsahuje cely projekt vratane:

- `.git`
- `vendor`
- `storage`
- aktualnych neucommitnutych zmien
- suboru s vystupom `git status`

## 2. Stiahnutie zalohy do PC

Vo VS Code otvorte priecinok:

```text
/workspaces/WEB-Interia-backups
```

Pravym klikom na najnovsi `.tar.gz` subor vyberte `Download`.

## 3. Rozbalenie na PC

Na PC rozbalte archiv do samostatneho priecinka, napriklad:

```text
C:\WEB-Interia-zaloha\
```

Neprepisujte tym aktualny pracovny priecinok, kym si nie ste isti, ze zaloha funguje.

## 4. Postupny upgrade

Odporucany cyklus:

1. Vytvorte plnu zalohu.
2. Spravte jednu mensiu zmenu alebo upgrade krok.
3. Otestujte web.
4. Ak funguje, commitnite zmenu.
5. Pred dalsim vacsim krokom znovu vytvorte plnu zalohu.

## 5. Obnova zo zalohy

Ak sa nieco pokazi:

1. Rozbalte posledny funkcny `.tar.gz` archiv.
2. Otvorte rozbaleny priecinok vo VS Code.
3. Spustite web cez:

```bash
./start.sh
```

Ak projekt bezal v Codespaces, najrychlejsie je otvorit rozbaleny priecinok ako novu kopiu projektu a az potom prenieset potrebne zmeny spat.
