@extends('layouts.app')

@section('title', 'WEB-Interia | Výroba, materiál a objednávky')
@section('description', 'Tri časti podnikania: Predaj materiálov, výroba polotovarov, výroba na mieru s montážou. Vyberte si svoju cestu.')
@section('og_title', 'WEB-Interia | Tri riešenia pre váš projekt')
@section('og_description', 'Materiál, polotovary, alebo kompletná výroba na mieru s montážou. Jedno miesto pre všetko.')

@section('content')
<section class="home-hero py-5 border-bottom">
    <div class="container py-lg-4">
        <h1 class="display-5 fw-semibold mb-2">Objednajte v 1 kroku</h1>
        <p class="lead text-secondary mb-4">Vyberte kategóriu a pokračujte priamo do objednávky.</p>

        <div class="auth-info d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4" role="region" aria-label="Prihlásenie a zákaznícka zóna">
            <div>
                <h2 class="h5 mb-1">Prihlásenie a zákaznícka zóna</h2>
                <p class="mb-0">Registrovaní používatelia vidia objednávky, dokumenty a stav komunikácie v jednom prehľade.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 home-hero-actions">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-primary">Prihlásiť sa</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary">Registrovať sa</a>
                @else
                    <a href="{{ route('customer.orders') }}" class="btn btn-primary">Prejsť do zóny</a>
                @endguest
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-materials h-100">
                    <div class="card-body d-flex flex-column">
                        <a href="{{ route('eshop.catalog.index') }}" class="btn btn-primary w-100">e-shop</a>
                        <div class="quick-order-copy mt-3">
                            <div class="quick-order-more" data-quick-order>
                                <button type="button" class="quick-order-toggle" aria-expanded="false">
                                    <span class="quick-order-desc-preview"><strong>📦 E-shop:</strong> ponuka materiálov, kovania a komponentov pre výrobu nábytku s rýchlym výberom podľa kategórie.</span>
                                    <span class="quick-order-inline-more" aria-hidden="true">↘ viac</span>
                                </button>
                            </div>
                            <p class="quick-order-full mb-0" hidden>Objednajte si dosky, hrany, kovanie a ďalšie prvky na jednom mieste. K dispozícii máte prehľadný katalóg, orientačné ceny a jednoduchý nákupný proces.</p>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-12 col-md-4">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-semifinished h-100">
                    <div class="card-body d-flex flex-column">
                        <a href="{{ route('semifinished') }}" class="btn btn-primary w-100">polotovary</a>
                        <div class="quick-order-copy mt-3">
                            <div class="quick-order-more" data-quick-order>
                                <button type="button" class="quick-order-toggle" aria-expanded="false">
                                    <span class="quick-order-desc-preview"><strong>⚙️ Polotovary:</strong> výroba podľa parametrov. Zadajte rozmery, materiál a požiadavky, my pripravíme polotovary presne podľa vašej špecifikácie.</span>
                                    <span class="quick-order-inline-more" aria-hidden="true">↘ viac</span>
                                </button>
                            </div>
                            <p class="quick-order-full mb-0" hidden>Po odoslaní formulára preveríme technické detaily, navrhneme optimálne riešenie a potvrdíme termín výroby. Vhodné pre stolárov aj menšie dielne.</p>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-12 col-md-4">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-custom h-100">
                    <div class="card-body d-flex flex-column">
                        <a href="{{ route('contact') }}" class="btn btn-primary w-100">zakazky</a>
                        <div class="quick-order-copy mt-3">
                            <div class="quick-order-more" data-quick-order>
                                <button type="button" class="quick-order-toggle" aria-expanded="false">
                                    <span class="quick-order-desc-preview"><strong>🔧 Zákazky:</strong> kompletný projekt + montáž. Komplexné riešenie od návrhu cez výrobu až po montáž pre domácnosti aj firemné priestory.</span>
                                    <span class="quick-order-inline-more" aria-hidden="true">↘ viac</span>
                                </button>
                            </div>
                            <p class="quick-order-full mb-0" hidden>Napíšte nám zadanie, rozpočet a predstavu o termíne. Pripravíme návrh na mieru, koordináciu výroby a profesionálnu montáž priamo na mieste.</p>
                        </div>
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

@push('scripts')
<script>
    document.querySelectorAll('[data-quick-order]').forEach((item) => {
        const toggle = item.querySelector('.quick-order-toggle');
        const fullText = item.parentElement?.querySelector('.quick-order-full');
        const inlineMore = item.querySelector('.quick-order-inline-more');

        if (!toggle || !fullText) {
            return;
        }

        toggle.addEventListener('click', () => {
            const isOpen = item.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (fullText) {
                fullText.hidden = !isOpen;
            }
            if (inlineMore) {
                inlineMore.textContent = isOpen ? '↗ menej' : '↘ viac';
            }
        });
    });
</script>
@endpush
