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

## Kosik bez registracie

Neprihlaseny navstevnik moze pridavat a odoberat produkty v jednom kosiku,
ktory sa uchovava v jeho relacii prehliadaca. Po vyprsani relacie sa obsah
strati; kosik hosta sa neprenasa do uctu pri prihlaseni ani registracii.
Oblubene produkty a viacere kosiky vyzaduju prihlasenie. Dokoncenie objednavky
zatial nie je dostupne.
Pri kazdom produkte tlacidlo zobrazuje nazov predvoleneho kosika a cislo
oznacuje mnozstvo produktu v nom; v rozbalenej ponuke su ostatne kosiky
s ich mnozstvami. Predvoleny kosik sa zobrazuje iba v hlavnom tlacidle.
Tlacidlo zobrazuje najviac 15 znakov nazvu; dlhsie nazvy sa mozu v pevnej
sirke skratit aj skor. Cely nazov je v jeho popise.
Cast panelu s nazvom ma rovnaku sirku nezavisle od nazvu; cast s mnozstvom
ma rovnaku sirku ako v rozbalenom zozname. Na uzkej obrazovke sa panel
prisposobi dostupnemu priestoru.
Tlacidlami minus a plus pri cisle sa meni mnozstvo v danom kosiku:
plus pri nule prida minimalne mnozstvo a minus pri minime produkt odstrani.
Pri zmene mnozstva zostava ponuka kosikov otvorena az do odchodu mysou;
potvrdenie alebo chyba sa zobrazi pri danom produkte. Bez JavaScriptu
sa zmena vykona standardnym odoslanim formulara.
V hornom kosiku zostava ponuka pri zmene predvoleneho kosika alebo odstraneni
produktu otvorena, kym z nej neodide mys; bez JavaScriptu sa formular odosle
standardne.

## Kategorie a rychle filtre e-shopu

Administrator nastavuje parametre v **Interna zona > Kategorie a filtre**
na `/interna/kategorie`. Hodnoty sa vyplnaju pri produkte; dostupne je aj
kopirovanie parametrov a hromadne upravy oznacenych produktov.

Postup a technicke podrobnosti: [Sprava filtrov katalogu](docs/filtre-katalogu.md).

## Reklamny pas na uvodnej stranke

Navod na vlozenie obrazkov a videi je priamo v administracii na `/interna`,
v casti **Reklamy na uvodnej stranke**. Obsahuje postup aj priklady konfiguracie
`config/home.php` pre 1 az 30 reklam s volitelnou sirkovou vahou `weight` (1 az 5).
Pri nedostatku miesta sa pas posuva vodorovne. Podporovane su obrazky a videa MP4/WebM
s nahladovym obrazkom a rucnym spustenim. Priame nahravanie cez administraciu
zatial nie je dostupne; navod je urceny spravcovi s pristupom k suborom webu.
