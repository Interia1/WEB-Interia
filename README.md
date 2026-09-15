# WEB-Interia

Spustenie je prerobene nanovo na jeden stabilny rezim:

- Laravel app bezi na porte `8000`
- funguje na PC (`127.0.0.1`) aj mobile (`LAN_IP`)
- v Codespaces sa pouziva public URL portu `8000`

## Rychly start

### Windows

- Spustite `start.bat` (alebo `OTVOR_WEB.bat`).

### Mac / Linux

```bash
chmod +x start.sh
./start.sh
```

Podrobny navod je v `README-start.md`.

## Reklamny pas na uvodnej stranke

Navod na vlozenie obrazkov a videi je priamo v administracii na `/interna`,
v casti **Reklamy na uvodnej stranke**. Obsahuje postup aj priklady konfiguracie
`config/home.php` pre 1 az 30 reklam s volitelnou sirkovou vahou `weight` (1 az 5).
Pri nedostatku miesta sa pas posuva vodorovne. Podporovane su obrazky a videa MP4/WebM
s nahladovym obrazkom a rucnym spustenim. Priame nahravanie cez administraciu
zatial nie je dostupne; navod je urceny spravcovi s pristupom k suborom webu.
