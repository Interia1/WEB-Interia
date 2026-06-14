# Osnova tvorby webu WEB-Interia

Tento dokument slúži ako praktická osnova postupného programovania webu **WEB-Interia**. Cieľom je realizovať web po menších kontrolovateľných krokoch tak, aby bolo možné priebežne kontrolovať výsledok v prehliadači.

Osnova sa môže počas vývoja priebežne upravovať podľa výsledkov, technických zistení, možností OBERONu, obchodných priorít a spätnej väzby z testovania.

## Základný princíp realizácie

Web sa nebude realizovať naraz ako jeden veľký celok. Postup bude:

1. najprv viditeľná kostra webu,
2. potom verejné stránky,
3. potom katalóg s testovacími dátami,
4. potom formuláre a objednávkové procesy,
5. potom používateľské účty a zóny,
6. potom administrácia,
7. potom integračná vrstva,
8. potom reálne napojenie na OBERON,
9. potom pokročilé obchodné, analytické, reklamné a komunitné moduly,
10. nakoniec produkčné doladenie, bezpečnosť, výkon a monitoring.

Každá fáza musí mať:

- viditeľný výsledok v prehliadači,
- jasné URL adresy na kontrolu,
- stručné akceptačné kritériá,
- možnosť fungovať najprv s testovacími alebo mock dátami,
- až následne napojenie na reálne externé systémy.

Odporúčaný prístup:

> Najprv mock/testovacie dáta → potom import/export súbory → potom reálne napojenie na OBERON → potom automatizácia.

---

## Fáza 0 — Príprava projektu

### Cieľ

Pripraviť technický základ, aby sa web dal spustiť lokálne aj nasadiť na testovaciu alebo preview adresu.

### Čo sa spraví

- výber a potvrdenie technológií,
- základná štruktúra projektu,
- vývojové prostredie,
- základné routovanie stránok,
- testovací deploy / preview prostredie,
- základný Git workflow,
- základná kontrola funkčnosti aplikácie.

### Kontrola v prehliadači

- `/`
- `/health`
- `/styleguide` alebo podobná testovacia stránka komponentov

### Hotové, keď

- web sa dá spustiť,
- otvorí sa úvodná stránka,
- existuje testovacia verzia dostupná v prehliadači,
- je jasné, kde sa bude kontrolovať priebežný vývoj.

---

## Fáza 1 — Základná kostra webu

### Cieľ

Vytvoriť základný vizuálny rámec webu bez zložitej logiky.

### Čo sa spraví

- hlavička webu,
- hlavné menu,
- pätička,
- základný layout,
- základné farby, písmo a tlačidlá,
- responzívne správanie,
- základné informačné stránky.

### Kontrola v prehliadači

- `/`
- `/o-nas`
- `/kontakt`
- `/sluzby`
- `/vyroba`
- `/materialy`

### Hotové, keď

- každá základná stránka existuje,
- menu funguje,
- pätička je vyplnená základnými údajmi,
- stránka je použiteľná na mobile,
- ešte nie je potrebné napojenie na databázu ani OBERON.

---

## Fáza 2 — Verejný obsah a SEO základ

### Cieľ

Doplniť základný verejný obsah tak, aby web dával zmysel aj bez hotového e-shopu.

### Čo sa spraví

- texty pre hlavné sekcie,
- základné SEO titulky a popisy,
- správna štruktúra nadpisov,
- kontaktné údaje,
- CTA tlačidlá,
- základná štruktúra sitemap,
- základná stránka často kladených otázok.

### Kontrola v prehliadači

- `/`
- `/sluzby`
- `/atypicka-vyroba`
- `/materialy`
- `/kontakt`
- `/faq`

### Hotové, keď

- zákazník pochopí, čo firma ponúka,
- používateľ sa vie dostať ku kontaktu a dopytu,
- CTA tlačidlá sú jasné,
- verejný web pôsobí ako základná firemná prezentácia.

---

## Fáza 3 — Katalóg produktov s testovacími dátami

### Cieľ

Vytvoriť katalóg produktov najprv bez OBERONu, iba s testovacími dátami.

### Čo sa spraví

- zoznam produktov,
- kategórie,
- produktová karta,
- detail produktu,
- cena,
- dostupnosť,
- základné parametre,
- obrázky,
- testovacie produkty.

### Kontrola v prehliadači

- `/katalog`
- `/katalog/[kategoria]`
- `/produkt/[slug]`

### Hotové, keď

- katalóg funguje s testovacími dátami,
- produkty majú detail,
- používateľ sa vie preklikať kategóriami,
- produktové karty sú čitateľné na mobile,
- reálne napojenie na OBERON zatiaľ nie je potrebné.

---

## Fáza 4 — Vyhľadávanie a filtrovanie katalógu

### Cieľ

Zlepšiť použiteľnosť katalógu.

### Čo sa spraví

- vyhľadávacie pole,
- filtrovanie podľa kategórie,
- filtrovanie podľa parametrov,
- filtrovanie podľa ceny,
- filtrovanie podľa dostupnosti,
- triedenie produktov,
- mobilné ovládanie filtrov.

### Kontrola v prehliadači

- `/katalog`
- `/vyhladavanie?q=...`

### Hotové, keď

- zákazník vie rýchlo nájsť produkt,
- filtre sú zrozumiteľné,
- filtre sa dajú zapnúť a vypnúť,
- katalóg je použiteľný aj na mobile.

---

## Fáza 5 — Dopytový formulár pre atypickú výrobu

### Cieľ

Umožniť zákazníkovi poslať dopyt ešte pred plným e-shopom.

### Čo sa spraví

- formulár dopytu,
- kontaktné údaje zákazníka,
- popis požiadavky,
- upload príloh,
- súhlas so spracovaním údajov,
- potvrdenie odoslania,
- základné uloženie dopytu.

### Kontrola v prehliadači

- `/dopyt`
- `/dopyt/dakujeme`

### Hotové, keď

- dopyt sa dá odoslať,
- používateľ dostane spätnú informáciu,
- prílohy sa bezpečne uložia alebo sú pripravené v testovacom režime,
- interný pracovník vie dopyt nájsť v systéme alebo v testovacom výstupe.

---

## Fáza 6 — Základný košík a objednávka ako hosť

### Cieľ

Spraviť jednoduchý nákup bez registrácie.

### Čo sa spraví

- pridanie produktu do košíka,
- zobrazenie košíka,
- úprava množstva,
- zadanie kontaktných údajov,
- zadanie dodacích údajov,
- odoslanie objednávky,
- potvrdenie objednávky.

### Kontrola v prehliadači

- `/kosik`
- `/objednavka`
- `/objednavka/dakujeme`

### Hotové, keď

- produkt sa dá pridať do košíka,
- množstvo sa dá upraviť,
- objednávka sa dá vytvoriť bez registrácie,
- objednávka sa zatiaľ môže ukladať iba vo webovej databáze alebo testovacom výstupe,
- export do OBERONu ešte nemusí byť hotový.

---

## Fáza 7 — Používateľské účty a prihlasovanie

### Cieľ

Pridať registráciu a prihlasovanie používateľov.

### Čo sa spraví

- registrácia,
- prihlásenie e-mail + heslo,
- odhlásenie,
- obnova hesla,
- základné používateľské konto,
- príprava rolí,
- rozdelenie používateľov: hosť, zákazník, firma, interný používateľ.

### Kontrola v prehliadači

- `/registracia`
- `/prihlasenie`
- `/moj-ucet`
- `/zabudnute-heslo`

### Hotové, keď

- zákazník sa vie zaregistrovať,
- zákazník sa vie prihlásiť a odhlásiť,
- systém rozlišuje neprihláseného a prihláseného používateľa,
- interné role sú technicky pripravené.

---

## Fáza 8 — Zákaznícka zóna

### Cieľ

Zákazník vidí svoje údaje, objednávky, dopyty a dokumenty.

### Čo sa spraví

- prehľad objednávok,
- detail objednávky,
- prehľad dopytov,
- detail dopytu,
- základné údaje účtu,
- firemné údaje,
- dokumenty zákazníka,
- komunikácia k objednávke alebo dopytu.

### Kontrola v prehliadači

- `/moj-ucet`
- `/moj-ucet/objednavky`
- `/moj-ucet/dopyty`
- `/moj-ucet/dokumenty`
- `/moj-ucet/komunikacia`

### Hotové, keď

- zákazník má použiteľnú zónu,
- zákazník vidí iba svoje objednávky a dopyty,
- cudzie údaje nie sú dostupné,
- objednávky a dopyty majú stav.

---

## Fáza 9 — Interná administrácia

### Cieľ

Pridať základné rozhranie pre pracovníkov firmy.

### Čo sa spraví

- admin dashboard,
- zoznam objednávok,
- zoznam dopytov,
- zoznam zákazníkov,
- stav synchronizácie,
- základná správa obsahu,
- základné oprávnenia.

### Kontrola v prehliadači

- `/admin`
- `/admin/objednavky`
- `/admin/dopyty`
- `/admin/zakaznici`
- `/admin/synchronizacia`
- `/admin/obsah`

### Hotové, keď

- interný pracovník má základný pracovný panel,
- objednávky a dopyty sa dajú kontrolovať,
- bežný zákazník nemá prístup do adminu,
- základné oprávnenia fungujú.

---

## Fáza 10 — Komunikačný archív

### Cieľ

Archivovať komunikáciu ku konkrétnym zákazníkom, objednávkam a dopytom.

### Čo sa spraví

- poznámky k zákazníkovi,
- komunikácia k dopytu,
- komunikácia k objednávke,
- interné komentáre,
- história stavov,
- základný 360° pohľad na zákazníka.

### Kontrola v prehliadači

- `/admin/zakaznici/[id]`
- `/admin/objednavky/[id]/komunikacia`
- `/admin/dopyty/[id]/komunikacia`

### Hotové, keď

- ku každému dopytu alebo objednávke je dohľadateľná história,
- interný pracovník vie spätne zistiť, čo sa riešilo,
- komunikácia je viazaná na zákazníka alebo obchodný prípad.

---

## Fáza 11 — Dokumenty a prílohy

### Cieľ

Doplniť centrálnu správu príloh a dokumentov.

### Čo sa spraví

- upload dokumentov,
- priradenie dokumentu k zákazníkovi,
- priradenie dokumentu k dopytu,
- priradenie dokumentu k objednávke,
- typ dokumentu,
- základná história alebo verzovanie,
- obmedzenie prístupu podľa role.

### Kontrola v prehliadači

- `/moj-ucet/dokumenty`
- `/admin/dokumenty`
- `/admin/dopyty/[id]/dokumenty`
- `/admin/objednavky/[id]/dokumenty`

### Hotové, keď

- dokumenty sú použiteľné pre dopyty a objednávky,
- zákazník vidí iba svoje dokumenty,
- interný pracovník vidí dokumenty podľa oprávnenia,
- súbory nie sú verejne dostupné bez oprávnenia.

---

## Fáza 12 — Integračná vrstva s testovacím konektorom

### Cieľ

Pripraviť integračnú architektúru ešte bez reálneho OBERONu.

### Čo sa spraví

- interné integračné rozhrania,
- testovací/mock konektor,
- import produktov z testovacieho súboru,
- import cien,
- import skladových stavov,
- export objednávky do testovacieho výstupu,
- log synchronizácie,
- základná retry logika.

### Kontrola v prehliadači

- `/admin/synchronizacia`
- `/admin/synchronizacia/logy`
- `/admin/synchronizacia/import-produktov`
- `/admin/synchronizacia/export-objednavok`

### Hotové, keď

- architektúra integrácií funguje s testovacími dátami,
- dá sa vidieť výsledok synchronizácie,
- chyby sú čitateľné v admin rozhraní,
- objednávka sa dá exportovať aspoň do testovacieho výstupu.

---

## Fáza 13 — Reálne napojenie na OBERON

### Cieľ

Vytvoriť prvý reálny konektor na externý systém OBERON.

### Čo sa spraví

Podľa možností OBERONu:

- API napojenie alebo CSV/XML/JSON import/export,
- import produktov,
- import cien,
- import skladových stavov,
- export objednávok,
- spätný import stavov objednávok,
- párovanie externého a interného ID,
- retry mechanizmus,
- logovanie chýb,
- ručné opakovanie zlyhaných operácií.

### Kontrola v prehliadači

- `/admin/synchronizacia/oberon`
- `/admin/synchronizacia/oberon/importy`
- `/admin/synchronizacia/oberon/exporty`
- `/admin/synchronizacia/oberon/chyby`

### Hotové, keď

- minimálny tok OBERON → web funguje,
- minimálny tok web → OBERON funguje,
- produkty, ceny a skladové stavy sa načítajú do webu,
- objednávka sa vie odoslať alebo pripraviť na odoslanie do OBERONu,
- chyby sú viditeľné v admin rozhraní.

---

## Fáza 14 — Notifikácie

### Cieľ

Doplniť odosielanie správ zákazníkom a interným pracovníkom.

### Čo sa spraví

- e-mail po odoslaní dopytu,
- e-mail po vytvorení objednávky,
- interné upozornenie na nový dopyt,
- interné upozornenie na novú objednávku,
- upozornenie na chybu synchronizácie,
- šablóny e-mailov,
- log odoslaných notifikácií.

### Kontrola v prehliadači

- `/admin/notifikacie`
- `/admin/notifikacie/sablony`
- `/admin/notifikacie/logy`

### Hotové, keď

- dôležité udalosti generujú notifikácie,
- notifikácie sa dajú spätne dohľadať,
- chyba odoslania je viditeľná pre administrátora.

---

## Fáza 15 — Platby a doprava

### Cieľ

Doplniť obchodné dokončenie objednávky.

### Čo sa spraví

- výber dopravy,
- osobný odber,
- cena dopravy,
- platba prevodom,
- dobierka,
- príprava na platobnú bránu,
- stav platby,
- základná evidencia refundácií a dobropisov ako budúci proces.

### Kontrola v prehliadači

- `/objednavka/doprava`
- `/objednavka/platba`
- `/admin/objednavky/[id]/platby`
- `/admin/objednavky/[id]/doprava`

### Hotové, keď

- objednávka má dopravu,
- objednávka má platobnú metódu,
- admin vidí stav platby a dopravy,
- systém je pripravený na budúce rozšírenie o platobnú bránu.

---

## Fáza 16 — I-zóna

### Cieľ

Vytvoriť komunitno-vzdelávaciu časť webu.

### Čo sa spraví

- odborné články,
- návody,
- videá,
- FAQ,
- kategórie tém,
- diskusné vlákna,
- moderovanie,
- práva prístupu.

### Kontrola v prehliadači

- `/i-zona`
- `/i-zona/clanky`
- `/i-zona/navody`
- `/i-zona/videa`
- `/i-zona/diskusia`
- `/admin/i-zona`

### Hotové, keď

- I-zóna je použiteľná,
- obsah sa dá spravovať,
- diskusné vlákna fungujú aspoň v základnom režime,
- moderátor vie skryť alebo spravovať príspevok,
- verejný používateľ nevidí neverejný obsah.

---

## Fáza 17 — Reklama, promo bloky a analytika

### Cieľ

Doplniť merateľné promo pozície a analytiku.

### Čo sa spraví

- promo bloky na stránkach,
- bannery,
- odporúčané produkty,
- UTM kampane,
- základné analytické eventy,
- analytický dashboard,
- napojenie na cookies consent,
- anonymizácia alebo pseudonymizácia podľa potreby.

### Kontrola v prehliadači

- `/admin/reklama`
- `/admin/kampane`
- `/admin/analytika`
- `/admin/analytika/vyhladavanie`
- `/admin/analytika/kosik`

### Hotové, keď

- promo bloky sú spravovateľné,
- reklamy sa dajú zapnúť a vypnúť,
- merajú sa základné kliky alebo udalosti,
- analytika rešpektuje cookies consent,
- výsledky sú viditeľné v admin rozhraní.

---

## Fáza 18 — Bezpečnosť, GDPR a právne texty

### Cieľ

Pripraviť web na bezpečnú a právne použiteľnú prevádzku.

### Čo sa spraví

- cookies lišta,
- správa cookies súhlasov,
- GDPR texty,
- obchodné podmienky,
- reklamačný poriadok,
- marketingové súhlasy,
- audit log,
- 2FA pre interné role,
- ochrana administrácie,
- rate limiting,
- bezpečné uploady,
- zálohovanie,
- retenčné pravidlá.

### Kontrola v prehliadači

- `/ochrana-osobnych-udajov`
- `/cookies`
- `/obchodne-podmienky`
- `/reklamacny-poriadok`
- `/admin/audit-log`
- `/admin/nastavenia/bezpecnost`

### Hotové, keď

- web má základné právne texty,
- používateľ vie zmeniť cookies súhlas,
- analytika a reklama rešpektujú súhlas,
- interné operácie sú auditované,
- administrácia je chránená.

---

## Fáza 19 — Výkon, prístupnosť a mobilné doladenie

### Cieľ

Doladiť použiteľnosť pred produkciou.

### Čo sa spraví

- optimalizácia obrázkov,
- lazy loading,
- cache,
- Core Web Vitals,
- kontrola mobilov a tabletov,
- kontrasty,
- klávesnicové ovládanie,
- alt texty,
- rýchlosť katalógu,
- rýchlosť vyhľadávania,
- použiteľnosť formulárov.

### Kontrola v prehliadači

- verejný web na mobile,
- katalóg na mobile,
- produktový detail,
- dopytový formulár,
- košík,
- zákaznícka zóna,
- administrácia.

### Hotové, keď

- web je pohodlne použiteľný na mobile,
- katalóg je rýchly,
- vyhľadávanie je použiteľné,
- formuláre sú zrozumiteľné,
- základná prístupnosť je splnená.

---

## Fáza 20 — Produkčné nasadenie a monitoring

### Cieľ

Bezpečne spustiť web do produkcie.

### Čo sa spraví

- produkčné prostredie,
- staging,
- CI/CD,
- zálohy,
- monitoring,
- alerty,
- rollback plán,
- kontrola integrácií,
- kontrola právnych textov,
- finálne UAT testovanie,
- dokumentácia prevádzky.

### Finálna kontrola v prehliadači

- verejný web,
- katalóg,
- detail produktu,
- dopyt,
- košík,
- objednávka,
- zákaznícka zóna,
- administrácia,
- synchronizácie,
- cookies,
- mobilné zobrazenie.

### Hotové, keď

- web je nasadený,
- existuje plán obnovy,
- chyby sú monitorované,
- zálohy sú nastavené,
- základné obchodné procesy fungujú,
- integrácie sú kontrolovateľné v administrácii.

---

## Odporúčané praktické poradie realizácie

1. Technický základ projektu
2. Vizuálna kostra webu
3. Verejné stránky
4. Katalóg s testovacími dátami
5. Vyhľadávanie a filtre
6. Dopytový formulár
7. Košík a objednávka ako hosť
8. Používateľské účty
9. Zákaznícka zóna
10. Admin rozhranie
11. Komunikačný archív
12. Dokumenty a prílohy
13. Testovacia integračná vrstva
14. OBERON konektor
15. Notifikácie
16. Platby a doprava
17. I-zóna
18. Reklama a analytika
19. Bezpečnosť, GDPR a právne texty
20. Výkon, staging, produkcia

---

## Prvý odporúčaný realizačný balík

Ako prvý praktický balík je vhodné spraviť:

### Balík A — Viditeľný základ webu

Obsah:

- domovská stránka,
- hlavné menu,
- pätička,
- kontakt,
- základné služby,
- katalógová stránka s testovacími produktmi,
- detail produktu,
- dopytový formulár.

Kontrola v prehliadači:

- `/`
- `/kontakt`
- `/sluzby`
- `/katalog`
- `/produkt/test-produkt`
- `/dopyt`

Tento balík je vhodný ako prvý, pretože poskytne okamžite viditeľný výsledok a umožní priebežnú spätnú väzbu ešte pred zložitými integráciami.

---

## Poznámka k priebežným úpravám

Táto osnova nie je uzavretý nemenný dokument. Po každej fáze sa môže aktualizovať podľa toho:

- čo sa pri vývoji zistí,
- čo bude podporovať OBERON,
- ktoré časti budú obchodne dôležitejšie,
- čo sa ukáže pri kontrole v prehliadači,
- aké budú výsledky testovania,
- aké budú legislatívne alebo bezpečnostné požiadavky.
