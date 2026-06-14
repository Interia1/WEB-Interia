# Vývojový diagram projektu WEB-Interia

Táto stránka obsahuje detailnejší, stále editovateľný Mermaid diagram vývoja webu.

```mermaid
flowchart TD
    A[Kickoff projektu<br/>Ciele, rozpočet, tím, termíny] --> B[Business analýza<br/>Materiály / Polotovary / Atyp výroba]
    B --> C[Informačná architektúra webu<br/>Stránky, navigácia, CTA]
    C --> D[Technologický návrh<br/>Frontend, Backend, E-shop, CMS, DB]
    D --> E[UX/UI návrh<br/>Wireframy, dizajn systém, prototyp]
    E --> F[MVP implementácia]

    F --> F1[Web prezentácia]
    F --> F2[Katalóg produktov]
    F --> F3[E-shop]
    F --> F4[Formulár na atyp dopyt + upload výkresov]
    F --> F5[SEO základ + analytika]

    F1 --> G[Integrácie]
    F2 --> G
    F3 --> G
    F4 --> G
    F5 --> G

    G --> G1[CRM]
    G --> G2[Fakturácia API]
    G --> G3[ERP / sklad]
    G --> G4[Platobná brána]
    G --> G5[Dopravcovia]

    G1 --> H[Testovanie]
    G2 --> H
    G3 --> H
    G4 --> H
    G5 --> H

    H --> H1[Funkčné testy]
    H --> H2[Performance testy]
    H --> H3[Security + GDPR]
    H --> H4[UAT so zákazníkom]

    H1 --> I[Deploy produkcie]
    H2 --> I
    H3 --> I
    H4 --> I

    I --> J[Monitoring a observability<br/>Logy, alerty, SLA]
    J --> K[Rozvoj v cykloch]

    K --> K1[B2B funkcie<br/>Individuálne cenníky, role]
    K --> K2[AI funkcie<br/>Smart vyhľadávanie, chatbot]
    K --> K3[Automatizácie<br/>Dopyt -> Ponuka -> Objednávka -> Faktúra]
    K --> K4[Multijazyčnosť + nové trhy]

    K1 --> L[Kontinuálne zlepšovanie na roky dopredu]
    K2 --> L
    K3 --> L
    K4 --> L
```
