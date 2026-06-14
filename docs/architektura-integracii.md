# Architektúra integrácií projektu WEB-Interia

Tento dokument popisuje navrhovanú architektúru integrácií pre projekt **WEB-Interia** s dôrazom na princíp **„raz a dosť“** — údaje sa zadávajú primárne v externom systéme a web ich iba synchronizuje, zobrazuje a používa v objednávkovom procese.

## 1. Cieľ integrácií

Cieľom architektúry je navrhnúť web a e-shop tak, aby:

- neboli tovary, materiály a ceny spravované duplicitne vo viacerých systémoch,
- web fungoval ako prezentačná, katalógová a objednávková vrstva,
- obchodné a skladové dáta boli riadené primárne v externých systémoch,
- bolo možné jednoducho pridávať ďalšie externé systémy bez zásadného zásahu do jadra riešenia,
- sa minimalizovala závislosť od platených SaaS riešení tam, kde to nie je nevyhnutné,
- bol kód spätne ľahko identifikovateľný voči schválenej architektúre, vývojovým dokumentom a integračným pravidlám.

## 2. Základný princíp: „raz a dosť“

Zásada **„raz a dosť“** znamená:

- tovar, materiál alebo cenníková položka sa založí v externom skladovom/ERP systéme,
- web tieto údaje neudržiava ako primárny zdroj pravdy,
- web ich iba synchronizuje a používa na prezentáciu, filtrovanie, kalkulácie a predaj,
- objednávky vytvorené vo webe sa zapisujú späť do externého systému,
- zmeny stavov a fakturačné výstupy vznikajú primárne v externom systéme a prenášajú sa späť podľa potreby.

Týmto sa znižuje riziko:

- nekonzistentných cien,
- rozdielnych skladových stavov,
- duplicitného zadávania dát,
- chýb pri manuálnom prepise údajov.

## 3. Aktuálny integračný kontext

### Prvý externý systém

Ako prvý skladový / obchodný softvér sa použije:

- **OBERON**

Architektúra však musí byť navrhnutá tak, aby OBERON nebol jediným možným systémom, ale iba **prvým konektorom**.

### Budúce typy napojení

Do budúcna sa ráta aj s napojením na:

- ďalšie skladové systémy,
- ERP systémy,
- fakturačné systémy,
- ďalšie objednávkové aplikácie,
- logistické a dopravné služby,
- platobné brány,
- CRM alebo B2B zákaznícke systémy.

## 4. Roly systémov

### Externý systém (napr. OBERON)

Externý systém je primárnym zdrojom pravdy najmä pre:

- produkty / tovary,
- materiály,
- ceny,
- skladové stavy,
- obchodné položky,
- fakturačné dokumenty,
- interné spracovanie objednávok.

### Web / e-shop

Web je zodpovedný najmä za:

- prezentáciu firmy a služieb,
- zobrazenie katalógu produktov,
- vyhľadávanie a filtrovanie,
- objednávkový proces,
- dopytový formulár pre atyp výrobu,
- zákaznícke rozhranie,
- SEO obsah a marketingové stránky,
- prípadné B2B rozhranie.

Web **nie je primárnym miestom ručnej evidencie tovarov a cien**, pokiaľ to nebude výnimočne potrebné.

## 5. Zdroj pravdy podľa typu dát

| Typ dát | Primárny zdroj | Web môže ukladať lokálne? | Poznámka |
|---|---|---:|---|
| Tovary / produkty | Externý systém | Áno | cache / index / katalógová projekcia |
| Materiály | Externý systém | Áno | pre výpočty, filtre, väzby |
| Ceny | Externý systém | Áno | na zobrazenie a predaj |
| Skladové stavy | Externý systém | Áno | synchronizované periodicky alebo podľa udalostí |
| Zákaznícke objednávky | Web → externý systém | Áno | web vytvorí a odošle |
| Stav objednávky | Externý systém | Áno | web zobrazuje zákazníkovi |
| Faktúry | Externý systém | Obmedzene | web môže evidovať referenciu / stav |
| Marketingový obsah | Web / CMS | Áno | vlastná správa vo webe |
| Dopyty na atyp výrobu | Web | Áno | môžu sa ďalej prenášať |

## 6. Navrhovaná integračná architektúra

Odporúčaný model je rozdeliť integrácie do samostatnej vrstvy.

### Architektonické vrstvy

1. **Prezentačná vrstva**
   - frontend webu
   - produktové stránky
   - e-shop
   - zákaznícke účty
   - formuláre

2. **Aplikačná vrstva webu**
   - objednávková logika
   - katalógová logika
   - validácie
   - autorizácia
   - B2B pravidlá

3. **Integračná vrstva**
   - konektory na externé systémy
   - mapovanie dátových polí
   - synchronizačné joby
   - fronty / retry mechanizmy
   - logovanie integrácií
   - monitoring chýb

4. **Externé systémy**
   - OBERON
   - ďalšie ERP/skladové systémy
   - fakturačné nástroje
   - objednávkové aplikácie
   - dopravcovia
   - platobné systémy

## 7. Adapter / connector model

Aby nebol web natvrdo previazaný len s jedným systémom, odporúča sa použiť model:

- **spoločné interné rozhranie integrácie**,
- nad ním samostatné konektory pre konkrétne systémy.

Príklad:

- `InventoryConnectorInterface`
- `OrderConnectorInterface`
- `InvoiceConnectorInterface`
- `OberonConnector`
- `AnotherErpConnector`
- `MarketplaceConnector`

Výhody:

- jednoduchšie rozšírenie na ďalšie systémy,
- menšia závislosť business logiky od konkrétneho dodávateľa,
- jednoduchšie testovanie,
- jednoduchšie mapovanie rôznych formátov dát.

## 8. Hlavné dátové toky

### 8.1 Externý systém → web

Do webu sa budú prenášať najmä tieto dáta:

- produkty / tovary,
- materiály,
- kategórie,
- ceny,
- skladové stavy,
- technické parametre,
- identifikátory položiek,
- prílohy alebo odkazy na dokumentáciu, ak budú dostupné,
- stavy objednávok,
- informácie o vystavených faktúrach.

### 8.2 Web → externý systém

Z webu sa budú zapisovať najmä:

- nové objednávky,
- položky objednávok,
- zákaznícke údaje,
- dodacie údaje,
- poznámky k objednávke,
- zmeny stavov, ak budú riadené aj z webu,
- požiadavky z formulárov,
- podklady potrebné pre fakturáciu alebo interné spracovanie.

## 9. Synchronizačné režimy

Nie všetky dáta musia byť synchronizované rovnakým spôsobom.

### Režimy synchronizácie

1. **Periodická synchronizácia**
   - vhodná pre produkty, ceny, skladové stavy
   - napr. každých X minút podľa možností systému

2. **Synchronizácia pri udalosti**
   - vhodná tam, kde externý systém vie poskytovať webhooky alebo trigger mechanizmy
   - ak to OBERON alebo budúce systémy umožnia

3. **Manuálne spustenie synchronizácie**
   - administrátor môže spustiť opätovné načítanie dát
   - užitočné pri chybách alebo po väčších zmenách

4. **Asynchrónne spracovanie cez frontu**
   - vhodné pre objednávky a náročnejšie integračné operácie
   - znižuje riziko pádu používateľského procesu pri dočasnej chybe externého systému

## 10. Odporúčaný model objednávky

Objednávka by mala mať životný cyklus rozdelený minimálne takto:

1. zákazník vytvorí objednávku vo webe,
2. web objednávku validuje a uloží,
3. web odošle objednávku do externého systému,
4. externý systém vytvorí vlastný interný záznam objednávky,
5. externý systém vracia identifikátor alebo potvrdenie,
6. ďalšie stavy objednávky sa synchronizujú späť do webu,
7. fakturácia sa vykoná v externom systéme,
8. zákazník dostane faktúru alebo odkaz/informáciu podľa implementácie procesu.

## 11. Fakturácia

Navrhovaný princíp:

- faktúry nevznikajú primárne vo webe,
- faktúry vytvára externý ekonomický/skladový systém,
- web môže uchovávať:
  - číslo faktúry,
  - stav vystavenia,
  - dátum vystavenia,
  - odkaz na dokument,
  - informáciu o odoslaní zákazníkovi.

Tým sa zachová konzistentnosť účtovných dokladov a zníži sa duplicita logiky.

## 12. Chybové scenáre a odolnosť

Architektúra musí rátať s tým, že externý systém nemusí byť vždy dostupný.

Treba navrhnúť:

- retry logiku,
- frontu neodoslaných operácií,
- audit log integračných udalostí,
- technické logy chýb,
- upozornenia pre administrátora,
- manuálne opakovanie neúspešných synchronizácií,
- ochranu proti duplicitnému vytvoreniu objednávky,
- mapovanie chybových stavov medzi systémami.

## 13. Administrácia a monitoring

Web by mal obsahovať minimálne interné administračné rozhranie alebo technický prehľad pre:

- posledný úspešný sync,
- posledný neúspešný sync,
- počet chýb,
- front neodoslaných operácií,
- stav importu produktov,
- stav exportu objednávok,
- párovanie identifikátorov medzi webom a externým systémom.

## 14. Bezpečnostné a prevádzkové zásady

Pri integráciách treba rátať aj s týmito pravidlami:

- autentifikácia voči externým API alebo exportným rozhraniam,
- bezpečné uchovanie prístupových údajov,
- auditovateľnosť kritických operácií,
- validácia vstupných aj výstupných dát,
- ochrana osobných údajov a GDPR,
- minimálne potrebné oprávnenia,
- oddelenie testovacieho a produkčného prostredia.

## 15. Súlad architektúry, vývoja a kódu

Pri vývoji platí zásada, že implementácia má byť spätne ľahko identifikovateľná voči tejto architektúre, vývojovým dokumentom a schváleným integračným pravidlám.

Ak sa počas návrhu alebo implementácie ukáže, že je potrebné odkloniť sa od schválenej architektúry, vývojového zadania alebo existujúceho modelu riešenia, musí byť na to explicitne upozornené ešte pred zapracovaním zmeny.

V takom prípade je potrebné:

- pomenovať dôvod odklonu,
- určiť, ktorú časť architektúry alebo vývojovej dokumentácie zmena ovplyvní,
- navrhnúť aktualizáciu architektúry alebo vývojového dokumentu,
- až následne upraviť kód tak, aby ostal konzistentný s dokumentáciou,
- zachovať takú štruktúru kódu, názvoslovie a väzby, aby bolo možné spätne dohľadať, prečo daná časť vznikla a ku ktorému architektonickému rozhodnutiu patrí.

Cieľom je zabrániť tomu, aby sa kód postupne odchyľoval od návrhu bez toho, aby bola aktualizovaná architektúra alebo vývojové pravidlá. Ak sa zmení realita v kóde, musí sa primerane zmeniť aj dokumentácia; ak sa zmení architektúra, musí sa podľa nej upraviť vývoj.

## 16. MVP odporúčanie

Pre prvú fázu odporúčané minimum:

- 1 integračný konektor: **OBERON**,
- import produktov,
- import cien,
- import skladových stavov,
- export objednávok,
- spätný import základného stavu objednávky,
- evidencia väzby medzi objednávkou vo webe a objednávkou v externom systéme,
- základný monitoring synchronizácie.

Do ďalších fáz je vhodné nechať:

- viac externých systémov,
- komplexné B2B pravidlá,
- pokročilé workflow fakturácie,
- viacúrovňové schvaľovanie,
- pokročilé notifikácie,
- automatizované rozhodovanie medzi viacerými externými systémami.

## 17. Odporúčanie pre ďalší návrh

Ďalším krokom by malo byť spracovanie samostatného dokumentu pre:

- konkrétne **dátové toky**, alebo
- **mapovanie entít a polí** medzi webom a OBERONom.

Odporúčané ďalšie dokumenty:

- `docs/datove-toky.md`
- `docs/mapovanie-entit-oberon.md`
- `docs/mvp-integracie.md`

## 18. Zhrnutie

WEB-Interia nemá byť izolovaný e-shop, ale **integračne orientovaný webový a objednávkový systém**, ktorý:

- čerpá produkty, materiály, ceny a stavy z externých systémov,
- zapisuje objednávky späť do externého systému,
- využíva externý systém ako hlavný zdroj prevádzkových a obchodných dát,
- zachováva princíp **„raz a dosť“**,
- vyžaduje upozornenie pri odklone od architektúry alebo vývojového zadania,
- udržiava kód spätne identifikovateľný voči schválenej dokumentácii,
- umožňuje budúce rozšírenie na ďalšie softvéry bez zásadného prerobenia jadra riešenia.
