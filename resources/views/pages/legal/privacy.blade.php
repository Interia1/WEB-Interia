@extends('layouts.app')

@section('title', 'Ochrana osobných údajov | WEB-Interia')
@section('description', 'Informácie o spracovaní osobných údajov používateľov WEB-Interia.')

@section('content')
<section class="py-5 border-bottom legal-header">
    <div class="container">
        <h1 class="display-6 mb-3">Zásady ochrany osobných údajov</h1>
        <p class="lead text-secondary mb-0">Pracovný právny základ pre zákaznícku zónu, registráciu a evidenciu súhlasov.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="card legal-card border-0">
            <div class="card-body p-4 p-lg-5">
                <p>Tento dokument stručne popisuje spracovanie osobných údajov pri registrácii a používaní zákazníckej zóny.</p>

                <h2 class="h5 mt-4">1. Prevádzkovateľ</h2>
                <p>WEB-Interia, kontakt: <a href="mailto:info@web-interia.sk">info@web-interia.sk</a>.</p>

                <h2 class="h5 mt-4">2. Rozsah údajov</h2>
                <p>Spracúvame najmä meno, e-mail, heslo (hash), technické údaje o súhlase (čas/IP) a údaje potrebné pre objednávkový proces.</p>

                <h2 class="h5 mt-4">3. Právny základ</h2>
                <p>Spracovanie je vykonávané najmä na základe plnenia zmluvy, zákonnej povinnosti a v rozsahu marketingu na základe dobrovoľného súhlasu.</p>

                <h2 class="h5 mt-4">4. Práva dotknutej osoby</h2>
                <p>Máte právo na prístup, opravu, obmedzenie spracovania, výmaz, prenosnosť a podanie námietky podľa GDPR.</p>

                <h2 class="h5 mt-4">5. Kontakt a žiadosti</h2>
                <p>Žiadosti o uplatnenie práv môžete zaslať na e-mail <a href="mailto:info@web-interia.sk">info@web-interia.sk</a>.</p>

                <p class="small text-secondary mt-4 mb-0">Poznámka: Obsah je pracovný základ. Pred produkciou odporúčame finálnu právnu revíziu podľa konkrétneho obchodného modelu.</p>
            </div>
        </div>
    </div>
</section>
@endsection
