# Sprava filtrov katalogu

## Administrator: definicie

1. Otvorte `/interna/kategorie` cez internu navigaciu **Kategorie a filtre**.
2. Rozbalte existujucu kategoriu alebo vytvorte novu s jedinecnym kodom.
3. Pridajte parameter: nazov, typ, poradie, povinnost a viditelnost filtra.
4. Pre vyber zo zoznamu zadajte moznosti po riadkoch (najviac 100).
   Pre cislo mozete pridat jednotku; treti typ je ano/nie.
5. Ulozte. Filtre sa zobrazia podla poradia v rozbalovacej ponuke
  prislusnej kategorie pod vyhladavanim.

Definicie moze menit iba administrator. Spravca obsahu moze vyplnat hodnoty
produktov, nie menit definicie. Typ alebo moznost, ktoru uz pouzivaju produkty,
sa neda odstranit bez predchadzajucej upravy hodnot. Vypnutie **Zobrazit filter**
skryje ovladanie pre zakaznika, ale zachova udaje v detaile produktu.

Novy povinny parameter sa nevyplni existujucim produktom automaticky.
Pri ich dalsom ulozeni bude povinny; doplnit ho mozno hromadnou upravou.

## Rychle vyplnanie

- V sprave produktov vyberte kod kategorie zo zoznamu. Jej parametre sa zobrazia
  v kompaktnych riadkoch, medzi poliami mozno prechadzat klavesom Tab.
- Pri vytvarani cez ikonu kopirovania v zozname produktov sa prevezme kategoria
  a parametre zdrojoveho produktu. Nazov, kod a cenu zadavate nanovo.
- **Hromadne parametre**: vyberte kategoriu, oznacte produkty na aktualnej stranke
  (najviac 50), vyplnte spolocne hodnoty a ulozte.
- Prazdne polia pri hromadnej uprave znamenaju **bez zmeny**, nie vymazanie.
  Hodnota **Nie** je skutocna hodnota, nie prazdne pole.
- Pri chybe sa hromadna uprava neulozi ani jednemu produktu. Prechod na inu
  stranku hromadneho vyberu nezachovava neulozeny vyber produktov.
- Zmena kategorie produktu odstrani pri uspesnom ulozeni hodnoty starej kategorie.

## Nakupny katalog

Na `/eshop/katalog` sa vyber kategorie, moznosti filtra a zoradenie prejavia
bez obnovy celej stranky. Vyhladavanie a ciselne rozsahy pockaju 250 ms po pisani.
Starsia odpoved nikdy neprepise novsi vyber. Pri chybe sa zachovaju predosle
vysledky a zobrazi sa chyba; poziadavku mozno zopakovat tlacidlom hladania.

Moznosti jedneho filtra sa spajaju cez ALEBO, rozne parametre cez A ZAROVEN.
Ciselne hranice su vratane zadanej hodnoty. Vyberove filtre ponukaju hodnoty
pouzite v celej zvolenej kategorii; zoznam sa nezuzuje po kazdom zaskrtnuti,
aby sa dali rychlo kombinovat alternativy. Dlhe zoznamy maju lokalne hladanie.

Aktivne filtre sa daju zrusit jednotlivo alebo naraz. Stav je ulozeny v URL,
funguje jeho zdielanie, strankovanie aj historia prehliadaca.
Bez JavaScriptu zostava verejny formular odosielatelny tlacidlom hladania.

Kategorie tvoria jeden vodorovne posuvatelny riadok pod vyhladavanim.
Prejdenie mysou na kategoriu okamzite vyberie jej produkty a otvori filtre.
Po odchode z tlacidla aj ponuky sa filtre zavru az po jednej sekunde; navrat
do ponuky alebo na tlacidlo toto zatvorenie zrusi. Na dotykovom zariadeni
sa ponuka ovlada kliknutim. Na desktope maju filtre vyhradeny priestor vpravo
od katalogu, na uzkej obrazovke sa zobrazuju nad kategoriami. Neprekryvaju
produkty ani tlacidla. Ponuka ma obmedzenu vysku a vlastne rolovanie;
na mobile sa polia zoradia do jedneho stlpca. Klik mimo alebo Escape ponuku zavrie.
Escape vrati klavesnicove zameranie na kategoriu. Zaskrtnutie aj odskrtnutie sa
prejavi okamzite bez potvrdenia a ponuka zostane otvorena. Vybrane hodnoty sa
zobrazuju v jednom vodorovne posuvatelnom riadku pod kategoriami; prazdny sumar
nezabera miesto. Stav je ulozeny v URL, nie ako trvale nastavenie uctu.

## Obrazovy prehlad Sortiment

Odkaz **Sortiment** nad **Filtre** otvara panel po kratkom podrzani mysi alebo
kliknuti. Kliknutie panel ponecha otvoreny; zatvara sa krizikom, kliknutim mimo
alebo klavesom Escape. Sipka dole na odkaze presunie fokus do hladania.
Panel ma vlastne rolovanie a prisposobuje pocet stlpcov sirke obrazovky.

Hladanie kategorii a parametrov ignoruje diakritiku. Kategorie maju pocet
produktov a ukazkovy obrazok prevzaty z produktu; bez obrazka sa pouzije ikona.
Rozbalitelne skupiny obsahuju existujuce verejne filtre, vratane ciselnych
rozsahov. Vyber sa potvrdi cez **Zobrazit produkty** a otvori katalog s filtrami
v URL. Vyhladavanie a zoradenie sa zachovaju. Pri otvoreni panela sa vybrane
hodnoty zosynchronizuju s aktualnym formularom katalogu.

Samostatny strom podkategorii zatial v datovom modeli nie je. Panel nevytvara
fiktivne podkategorie: pouziva skupiny parametrov spravovane administratorom.
Sluzba `CatalogAssortment` nacitava data hromadne iba pri HTML odpovedi;
bezne AJAX filtrovanie tento prehlad znovu negeneruje.

## Databaza a nasadenie

Spustite `php artisan migrate`. Migracia
`2026_09_16_000000_create_catalog_parameters.php` prevezme existujuce kategorie
bez zmeny produktov alebo adries. Pri novej instalacii ich vytvori aj ProductSeeder.
Realne parametre sa nevymyslaju automaticky: nastavuje ich administrator.

- `catalog_categories`: kody a nazvy kategorii.
- `catalog_parameters`: typ, jednotka, povinnost, poradie a moznosti parametra.
- `product_parameter_values`: typovo overene hodnoty s indexmi pre vyber a rozsah.

Katalog filtruje, radi a strankuje priamo v SQL, najviac 30 produktov na stranku.
Dotazy na hodnoty a kategorie su hromadne, nie jeden dotaz na kazdy produkt.
Text v tomto katalogu sa hlada cez SQL v nazve, kode, kategorii, popisoch
a dostupnosti. Hlavny nasepkavac nadalej pouziva existujuci Laravel Scout.
Pre velke objemy textov treba samostatne odmerat fulltextove vyhladavanie;
SQL obsahove hladanie nema garantovanu konstantnu odozvu.

Lokaly test s 5000 produktmi a vyberovym filtrom nameral priblizne 13-20 ms,
7 SQL dotazov a asi 28 kB odpovede. Ide o SQLite test bez siete a prehliadaca,
nie o garanciu produkcnej latencie.

Tieto obrazovky su lokalna sprava katalogu. Automaticky prenos parametrov
z OBERON-u nie je sucastou tejto zmeny; buduci konektor musi mapovat hodnoty
na rovnake definicie a validovat ich sluzbou `ProductParameters`.

## Overenie

```bash
php artisan test --compact
node --test tests/Unit/catalog-filters.test.cjs
node --test tests/Unit/catalog-assortment.test.cjs
```

Testy kontroluju opravnenia, povinne hodnoty, rozsahy, kombinovanie filtrov,
kopirovanie, atomicke hromadne upravy, strankovanie a obmedzeny pocet SQL dotazov.
JavaScript testy overuju oneskorene odpovede, debounce, chyby a historiu.
V tomto vyvojovom kontajneri nie je Node na PATH; dostupny je aj Node dodany s VS Code.
Chromium bol overeny cez Playwright pri sirkach 1600, 1000, 390 a 320 px.
Kontrola zahrnula snimky, otvorenie kategorie bez posunu produktov, hranice
rozbalovacej ponuky, automaticke zaskrtnutie a odskrtnutie, sumar, URL a Escape.
Chybajuce kniznice a testovacie pisma boli rozbalene len do docasneho priecinka,
bez systemovej instalacie alebo pridavania zavislosti do projektu.