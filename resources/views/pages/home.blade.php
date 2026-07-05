@extends('layouts.app')

@section('title', 'WEB-Interia | Výroba, materiál a objednávky')
@section('description', 'Tri časti podnikania: Predaj materiálov, výroba polotovarov, výroba na mieru s montážou. Vyberte si svoju cestu.')
@section('og_title', 'WEB-Interia | Tri riešenia pre váš projekt')
@section('og_description', 'Materiál, polotovary, alebo kompletná výroba na mieru s montážou. Jedno miesto pre všetko.')

@section('content')
<section class="home-hero py-5 border-bottom">
    <div class="home-hero-video" aria-hidden="true">
        <video class="home-hero-video-item" autoplay muted loop playsinline preload="metadata" poster="/images/home-hero-components.jpg">
            <source src="/videos/home-hero-components.mp4" type="video/mp4">
        </video>
        <video class="home-hero-video-item" autoplay muted loop playsinline preload="metadata" poster="/images/home-hero-boards.jpg">
            <source src="/videos/home-hero-boards.mp4" type="video/mp4">
        </video>
        <video class="home-hero-video-item" autoplay muted loop playsinline preload="metadata" poster="/images/home-hero-kitchen.jpg">
            <source src="/videos/home-hero-kitchen.mp4" type="video/mp4">
        </video>
    </div>
    <div class="container py-lg-4">
        <div class="row g-1 mb-4 quick-order-grid">
            <div class="col-12 col-md-auto quick-order-col">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-materials h-100">
                    <div class="card-body d-flex flex-column">
                        <a href="{{ route('eshop.catalog.index') }}" class="btn btn-primary w-100">E-shop</a>
                        <div class="quick-order-copy mt-3">
                            <details class="quick-order-more">
                                <summary class="quick-order-toggle">
                                    <span class="quick-order-desc-preview">
                                        <span class="quick-order-line"><strong>📦 E-shop:</strong> materiály, kovania a komponenty</span>
                                        <span class="quick-order-line quick-order-line-with-more">rýchly výber podľa kategórie</span>
                                    </span>
                                    <span class="quick-order-inline-more" aria-hidden="true">viac...</span>
                                </summary>
                                <p class="quick-order-full mb-0">Objednajte si dosky, hrany, kovanie a ďalšie prvky na jednom mieste. K dispozícii máte prehľadný katalóg, orientačné ceny a jednoduchý nákupný proces.</p>
                            </details>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-12 col-md-auto quick-order-col">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-semifinished h-100">
                    <div class="card-body d-flex flex-column">
                        <a href="{{ route('semifinished') }}" class="btn btn-primary w-100">Polotovary</a>
                        <div class="quick-order-copy mt-3">
                            <details class="quick-order-more">
                                <summary class="quick-order-toggle">
                                    <span class="quick-order-desc-preview">
                                        <span class="quick-order-line"><strong><span class="quick-order-icon quick-order-icon-assembly" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M4 7.5 11 4l7 3.5-7 3.5-7-3.5Z"/><path d="M4 7.5v7l7 3.5v-7L4 7.5Z"/><path d="M18 7.5v7L11 18v-7l7-3.5Z"/><path d="M7.5 15.4v3.1h9v-3.1"/></svg></span> Polotovary:</strong> výroba podľa parametrov</span>
                                        <span class="quick-order-line quick-order-line-with-more">rozmery, materiál a požiadavky</span>
                                    </span>
                                    <span class="quick-order-inline-more" aria-hidden="true">viac...</span>
                                </summary>
                                <p class="quick-order-full mb-0">Po odoslaní formulára preveríme technické detaily, navrhneme optimálne riešenie a potvrdíme termín výroby. Vhodné pre stolárov aj menšie dielne.</p>
                            </details>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-12 col-md-auto quick-order-col">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-custom h-100">
                    <div class="card-body d-flex flex-column">
                        <a href="{{ route('contact') }}" class="btn btn-primary w-100">Zákazky</a>
                        <div class="quick-order-copy mt-3">
                            <details class="quick-order-more">
                                <summary class="quick-order-toggle">
                                    <span class="quick-order-desc-preview">
                                        <span class="quick-order-line"><strong><span class="quick-order-icon quick-order-icon-furniture" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M4 5.5h16v13H4v-13Z"/><path d="M8.5 5.5v13"/><path d="M15.5 5.5v13"/><path d="M4 11h16"/><path d="M8.5 14.5h7"/><path d="M6.5 20h11"/></svg></span> Zákazky:</strong> projekt, výroba a montáž</span>
                                        <span class="quick-order-line quick-order-line-with-more">pre domácnosti aj firmy</span>
                                    </span>
                                    <span class="quick-order-inline-more" aria-hidden="true">viac...</span>
                                </summary>
                                <p class="quick-order-full mb-0">Napíšte nám zadanie, rozpočet a predstavu o termíne. Pripravíme návrh na mieru, koordináciu výroby a profesionálnu montáž priamo na mieste.</p>
                            </details>
                        </div>
                    </div>
                </article>
            </div>
        </div>

    </div>
</section>

<section class="py-5 bg-light border-top border-bottom home-summary-section">
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
