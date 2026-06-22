@extends('layouts.app')

@section('title', 'WEB-Interia | Výroba, materiály a objednávky')
@section('description', 'Tri časti podnikania: Predaj materiálov, výroba polotovarov, výroba na mieru s montážou. Vyberte si svoju cestu.')
@section('og_title', 'WEB-Interia | Tri riešenia pre váš projekt')
@section('og_description', 'Materiály, polotovary, alebo kompletná výroba na mieru s montážou. Jedno miesto pre všetko.')

@section('content')
<section class="home-hero py-5 border-bottom">
    <div class="container py-lg-4">
        <h1 class="display-5 fw-semibold mb-2">Objednajte v 1 kroku</h1>
        <p class="lead text-secondary mb-4">Vyberte kategóriu a pokračujte priamo do objednávky.</p>

        <div class="auth-info d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4" role="region" aria-label="Prihlásenie a vývojová štruktúra">
            <div>
                <h2 class="h5 mb-1">Prihlásenie a zákaznícka zóna</h2>
                <p class="mb-0">Registrovaní používatelia vidia objednávky, dokumenty a stav komunikácie v jednom prehľade.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 home-hero-actions">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-primary">Prihlásiť sa</a>
                @else
                    <a href="{{ route('customer.orders') }}" class="btn btn-primary">Prejsť do zóny</a>
                @endguest
                <a href="{{ route('dev.structure') }}" class="btn btn-outline-primary">Vývojová štruktúra</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-4">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-materials h-100">
                    <div class="card-body d-flex flex-column">
                        <p class="small text-uppercase fw-semibold mb-2">📦 E-shop materiálov</p>
                        <h2 class="h5 mb-2">Hliník, oceľ, nerez</h2>
                        <p class="text-secondary flex-grow-1 mb-3">Interaktívny katalóg s okamžitým výberom položiek.</p>
                        <a href="{{ route('eshop.catalog.index') }}" class="btn btn-primary w-100">Materiály - komponenty</a>
                    </div>
                </article>
            </div>
            <div class="col-12 col-lg-4">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-semifinished h-100">
                    <div class="card-body d-flex flex-column">
                        <p class="small text-uppercase fw-semibold mb-2">⚙️ Polotovary</p>
                        <h2 class="h5 mb-2">Výroba podľa parametrov</h2>
                        <p class="text-secondary flex-grow-1 mb-3">Otvorte formulár a pošlite špecifikáciu výroby.</p>
                        <a href="{{ route('semifinished') }}" class="btn btn-primary w-100">Porezy a polotovary</a>
                    </div>
                </article>
            </div>
            <div class="col-12 col-lg-4">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-custom h-100">
                    <div class="card-body d-flex flex-column">
                        <p class="small text-uppercase fw-semibold mb-2">🔧 Výroba na mieru</p>
                        <h2 class="h5 mb-2">Kompletný projekt + montáž</h2>
                        <p class="text-secondary flex-grow-1 mb-3">Zadajte dopyt na riešenie na mieru pre váš projekt.</p>
                        <a href="{{ route('contact') }}" class="btn btn-primary w-100">Výroba na mieru</a>
                    </div>
                </article>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('catalogs.overview') }}" class="btn btn-outline-dark">Porovnať všetky katalógy</a>
            <a href="{{ route('customer.orders') }}" class="btn btn-outline-dark">Moje objednávky</a>
        </div>
    </div>
</section>

<section class="py-5 border-bottom">
    <div class="container">
        <div class="section-intro mb-4">
            <h2 class="h3 mb-2">Štruktúra podľa osnovy, architektúry a vývoja</h2>
            <p class="text-secondary mb-0">Na hlavnej stránke je dostupný základný vstup pre zákazníka aj vývojára. Odkaz na detailnú štruktúru nájdete nižšie.</p>
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="card h-100 border-0 dev-card">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-primary">Osnova tvorby webu</h3>
                        <p class="text-secondary mb-0">Postupné fázy od kostry webu až po zákaznícke účty, administráciu a produkčný monitoring.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100 border-0 dev-card">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-primary">Architektúra integrácií</h3>
                        <p class="text-secondary mb-0">Princíp raz a dosť, integračná vrstva konektorov, synchronizácia dát a bezpečný tok objednávok.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100 border-0 dev-card">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-primary">Vývojový diagram</h3>
                        <p class="text-secondary mb-0">Roadmapa rolí, zón, notifikácií, analytiky, výkonu a DevOps pre ďalšie fázy vývoja.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('dev.structure') }}" class="btn btn-outline-dark">Otvoriť detail vývojovej štruktúry</a>
        </div>
    </div>
</section>

<section class="py-5 bg-light border-top border-bottom">
    <div class="container">
        <h2 class="h3 mb-4">Ako si to zjednotiť?</h2>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100 summary-kpi border-0">
                    <div class="card-body">
                        <h3 class="h5 mb-3">Prehľad všetkých objednávok</h3>
                        <p class="text-secondary mb-3">Všetky vaše objednávky zo všetkých troch sekcií na jednom mieste. Vidíte stav, sumy, termíny a jednoducho sa orientujete, čo ste si objednali.</p>
                        <a href="{{ route('customer.orders') }}" class="btn btn-primary">Zobraziť moje objednávky</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 summary-kpi border-0">
                    <div class="card-body">
                        <h3 class="h5 mb-3">Vlastný customer portal</h3>
                        <p class="text-secondary mb-3">Po prvej objednávke si vytvoríte účet a máte náhľad na:</p>
                        <ul class="text-secondary mb-0">
                            <li>Všetky objednávky a projekty</li>
                            <li>Stav a termíny jednotlivých položiek</li>
                            <li>Faktúry a doklady</li>
                            <li>Prímä komunikácia s našim tímom</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card h-100 border-0 dashboard-card">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h4 mb-3">Máte otázky?</h2>
                        <p class="text-secondary mb-4">Nie ste si istí, ktorá možnosť je pre vás vhodná? Kontaktujte nás – poraďujeme vám zadarmo.</p>
                        <a href="{{ route('contact') }}" class="btn btn-primary btn-lg">Kontaktovať tím →</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 border-0 dashboard-card">
                    <div class="card-body">
                        <h3 class="h5 mb-3">Kontakt</h3>
                        <p class="mb-2"><strong>E-mail:</strong> <a href="mailto:info@web-interia.sk">info@web-interia.sk</a></p>
                        <p class="mb-2"><strong>Telefón:</strong> +421 900 000 000</p>
                        <p class="mb-0 text-secondary">Po - Pia: 8:00 - 16:30</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
