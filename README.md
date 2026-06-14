# WEB-Interia

Webová stránka pre spoločnosť **Interia s.r.o.** – predaj materiálov, výroba polotovarov a kompletná atypická výroba na mieru.

## Technológie

- **Framework**: [Next.js 16](https://nextjs.org/) (App Router, TypeScript)
- **Štýlovanie**: [Tailwind CSS v4](https://tailwindcss.com/)
- **Stav košíka**: React Context + `useReducer`
- **Integrácie**: Vlastná integračná vrstva (POHODA, SuperFaktúra, webhooky)

## Funkcie

| Sekcia | Popis |
|---|---|
| 🏠 Domovská stránka | Landing page s prehľadom služieb a vybraných produktov |
| 🛒 E-shop | Katalóg produktov s filtráciou podľa kategórie a vyhľadávaním |
| 📦 Detail produktu | Parametre, atribúty, pridanie do košíka s výberom množstva |
| 🛍️ Košík | Prehľad položiek, zmena množstva, celková suma s DPH |
| 📝 Objednávka | Checkout s fakturačnými údajmi, adresou a platbou |
| 🔧 Služby | Laserové rezanie, CNC ohýbanie, zváračské práce, povrchové úpravy |
| ℹ️ O nás | Firemné informácie, história, hodnoty |
| 📞 Kontakt | Kontaktný formulár a kontaktné údaje |
| 🧾 Faktúry | Zoznam faktúr, detail faktúry, vytváranie novej faktúry |

## Štruktúra projektu

```
src/
├── app/                    # Next.js App Router
│   ├── api/                # REST API endpointy
│   │   ├── products/       # GET /api/products, POST, GET/PUT/DELETE /api/products/[id]
│   │   ├── orders/         # GET /api/orders, POST, GET/PUT/DELETE /api/orders/[id]
│   │   ├── invoices/       # GET /api/invoices, POST, GET/PUT/DELETE /api/invoices/[id]
│   │   └── integrations/
│   │       └── webhook/    # POST /api/integrations/webhook
│   ├── eshop/              # E-shop stránky
│   ├── invoices/           # Modul faktúr
│   ├── services/           # Stránka služieb
│   ├── about/              # O nás
│   ├── contact/            # Kontakt
│   └── layout.tsx          # Koreňový layout (Navbar + Footer)
├── components/
│   ├── layout/             # Navbar, Footer
│   ├── ui/                 # Button, Badge
│   ├── eshop/              # ProductCard, CategoryFilter
│   └── invoices/           # InvoiceCard
├── context/
│   └── CartContext.tsx     # Stav nákupného košíka
└── lib/
    ├── types.ts            # TypeScript typy
    ├── data.ts             # Vzorové dáta (mock)
    ├── utils.ts            # Pomocné funkcie (formátovanie, výpočty)
    └── integrations/
        └── index.ts        # Integračná vrstva (POHODA, SuperFaktúra)
```

## Spustenie

### 1. Inštalácia závislostí

```bash
npm install
```

### 2. Konfigurácia prostredia

```bash
cp .env.example .env.local
# Vyplňte hodnoty pre integrácie (POHODA_API_KEY, atď.)
```

### 3. Vývojový server

```bash
npm run dev
```

Aplikácia beží na [http://localhost:3000](http://localhost:3000).

### 4. Build pre produkciu

```bash
npm run build
npm run start
```

### 5. Linting

```bash
npm run lint
```

## API Endpointy

### Produkty

| Metóda | Endpoint | Popis |
|---|---|---|
| `GET` | `/api/products` | Zoznam produktov (filtre: `category`, `q`, `page`, `pageSize`) |
| `POST` | `/api/products` | Vytvorenie nového produktu |
| `GET` | `/api/products/[id]` | Detail produktu |
| `PUT` | `/api/products/[id]` | Aktualizácia produktu |
| `DELETE` | `/api/products/[id]` | Zmazanie produktu |

### Objednávky

| Metóda | Endpoint | Popis |
|---|---|---|
| `GET` | `/api/orders` | Zoznam objednávok (filter: `status`) |
| `POST` | `/api/orders` | Vytvorenie objednávky (synchronizuje sa do ERP) |
| `GET` | `/api/orders/[id]` | Detail objednávky |
| `PUT` | `/api/orders/[id]` | Aktualizácia stavu |
| `DELETE` | `/api/orders/[id]` | Zmazanie objednávky |

### Faktúry

| Metóda | Endpoint | Popis |
|---|---|---|
| `GET` | `/api/invoices` | Zoznam faktúr (filter: `status`) |
| `POST` | `/api/invoices` | Vystavenie faktúry (synchronizuje sa do SuperFaktúra / POHODA) |
| `GET` | `/api/invoices/[id]` | Detail faktúry |
| `PUT` | `/api/invoices/[id]` | Aktualizácia faktúry |
| `DELETE` | `/api/invoices/[id]` | Zmazanie faktúry |

### Webhooky

| Metóda | Endpoint | Popis |
|---|---|---|
| `POST` | `/api/integrations/webhook` | Príjem udalostí z externých systémov |

## Externé integrácie

Integračná vrstva sa nachádza v `src/lib/integrations/index.ts`.
Každý provider implementuje rozhranie `IntegrationAdapter`:

```ts
interface IntegrationAdapter {
  name: string;
  isEnabled(): boolean;
  syncInvoice(invoice: Invoice): Promise<{ externalRef: string }>;
  syncOrder(order: Order): Promise<{ externalRef: string }>;
  syncProducts(products: Product[]): Promise<void>;
  handleWebhook(payload: WebhookPayload): Promise<void>;
}
```

Aktuálne podporované systémy:
- **POHODA** – ekonomický software (SK/CZ) – konfigurácia cez `POHODA_API_URL` + `POHODA_API_KEY`
- **SuperFaktúra** – online faktúrovanie – konfigurácia cez `SUPERFAKTURA_API_KEY`

Pridanie nového systému: implementujte `IntegrationAdapter` a pridajte ho do `getActiveAdapters()`.

## Rozšíriteľnosť

Architektúra je navrhnutá pre dlhodobé rozšírovanie:

- **Databáza**: Nahraďte mock dáta v `src/lib/data.ts` volaním ORM (Prisma, Drizzle) alebo REST API
- **Autentifikácia**: Pridajte [NextAuth.js](https://next-auth.js.org/) pre admin sekciu
- **Platby**: Integrujte Stripe alebo ComGate cez nový `IntegrationAdapter`
- **E-mail**: Doplňte odosielanie potvrdení cez Resend / Nodemailer v API routoch
- **Multi-jazyčnosť**: Pripravte `next-intl` pre SK/CZ/EN lokalizáciu
- **CMS**: Napojte Sanity alebo Strapi pre správu obsahu bez deploymentu
