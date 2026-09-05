# Automatické zálohy

Tento doplnok zabezpečuje, aby sa plná záloha projektu vytvorila pred každým spustením aplikácie cez `start.sh` alebo `start.bat`. Záloha sa uloží vedľa projektu do priečinka `WEB-Interia-backups`, takže nie je súčasťou webu a nepridáva sa samostatne pre mobil.

Mobil aj počítač pracujú s rovnakou bežiacou aplikáciou a rovnakými dátami. Na mobile preto stačí otvoriť URL vypísanú po spustení; žiadne samostatné nastavenie SEO ani kópia záloh nie sú potrebné.

## Použitie

Bežné spustenie automaticky vytvorí zálohu:

```bash
./start.sh
```

Vo Windowse spustite `start.bat`.

Pre jeden štart bez vytvorenia zálohy nastavte premennú `WEB_AUTO_BACKUP=0`:

```bash
WEB_AUTO_BACKUP=0 ./start.sh
```

V systéme Windows:

```bat
set WEB_AUTO_BACKUP=0
start.bat
```

Premennou `BACKUP_DIR` možno zmeniť cieľový priečinok záloh. Linuxový a macOS štart používa [scripts/backup-full.sh](../../scripts/backup-full.sh); Windows používa `backup-full.ps1` z tohto doplnku.