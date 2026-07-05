# WEB-Interia - Spustenie na PC aj mobile

Toto je novy, jednoduchy postup bez obchadzok.

## Co sa spusta

- Laravel server na porte `8000`
- Host `0.0.0.0` (dostupne na PC aj na mobile v rovnakej sieti)
- V Codespaces sa pouziva verejna URL portu `8000`

## Windows

1. Otvorte priecinok `WEB-Interia`.
2. Spustite `start.bat` (alebo `OTVOR_WEB.bat`).
3. Skript vypise:
   - PC URL: `http://127.0.0.1:8000`
   - Mobile URL: `http://<LAN_IP>:8000`

## Mac / Linux

1. V termine prejdite do projektu:

```bash
cd WEB-Interia
```

2. Spustite:

```bash
chmod +x start.sh
./start.sh
```

3. Skript vypise:
   - PC URL: `http://127.0.0.1:8000`
   - Mobile URL: `http://<LAN_IP>:8000`

## Codespaces (PC aj mobil)

1. Spustite `./start.sh` v Codespace terminali.
2. Otvorte URL portu `8000` z panelu Ports.
3. Port `8000` musi byt `Public`.

Poznamka:
- Nepouzivajte `localhost` v mobile.
- V mobile otvarajte bud `http://<LAN_IP>:8000` (lokalna siet), alebo Codespaces public URL.

## Ak to nefunguje

1. Overte, ze bezi server na 8000:

```bash
curl -I http://127.0.0.1:8000
```

2. Ak je port obsadeny inou appkou, zastavte ju.
3. V Codespaces nastavte port 8000 na Public a otvorte URL z panelu Ports.
