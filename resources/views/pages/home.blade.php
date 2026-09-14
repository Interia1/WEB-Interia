@extends('layouts.app')

@section('title', 'WEB-Interia | Výroba, materiál a objednávky')
@section('description', 'Tri časti podnikania: Predaj materiálov, výroba polotovarov, výroba na mieru s montážou. Vyberte si svoju cestu.')
@section('og_title', 'WEB-Interia | Tri riešenia pre váš projekt')
@section('og_description', 'Materiál, polotovary, alebo kompletná výroba na mieru s montážou. Jedno miesto pre všetko.')

@php
    $shortcutGroups = [
        'eshop' => [
            'label' => 'E-shop',
            'items' => [
                'catalog' => ['label' => 'Katalóg', 'icon' => 'bi-grid', 'url' => route('eshop.catalog.index', [], false)],
                'catalogs' => ['label' => 'Všetky katalógy', 'icon' => 'bi-journals', 'url' => route('catalogs.overview', [], false)],
                'orders' => ['label' => 'Moje objednávky', 'icon' => 'bi-bag-check', 'url' => route('customer.orders', [], false)],
                'faq' => ['label' => 'Časté otázky', 'icon' => 'bi-question-circle', 'url' => route('faq', [], false)],
                'contact' => ['label' => 'Kontakt', 'icon' => 'bi-chat-dots', 'url' => route('contact', [], false)],
            ],
        ],
        'semifinished' => [
            'label' => 'Polotovary',
            'items' => [
                'catalog' => ['label' => 'Katalóg dielov', 'icon' => 'bi-grid', 'url' => route('semifinished.catalog.index', [], false)],
                'catalogs' => ['label' => 'Všetky katalógy', 'icon' => 'bi-journals', 'url' => route('catalogs.overview', [], false)],
                'orders' => ['label' => 'Moje objednávky', 'icon' => 'bi-bag-check', 'url' => route('customer.orders', [], false)],
                'faq' => ['label' => 'Časté otázky', 'icon' => 'bi-question-circle', 'url' => route('faq', [], false)],
                'contact' => ['label' => 'Kontakt', 'icon' => 'bi-chat-dots', 'url' => route('contact', [], false)],
            ],
        ],
        'custom' => [
            'label' => 'Výroba na mieru',
            'items' => [
                'presentations' => ['label' => 'Ukážky realizácií', 'icon' => 'bi-images', 'url' => route('custom-work.presentations', [], false)],
                'customer-zone' => ['label' => 'I-zóna', 'icon' => 'bi-person-workspace', 'url' => route('customer.zone', [], false)],
                'orders' => ['label' => 'Moje projekty', 'icon' => 'bi-folder-check', 'url' => route('customer.orders', [], false)],
                'faq' => ['label' => 'Časté otázky', 'icon' => 'bi-question-circle', 'url' => route('faq', [], false)],
                'contact' => ['label' => 'Dohodnúť konzultáciu', 'icon' => 'bi-chat-dots', 'url' => route('contact', [], false)],
            ],
        ],
    ];
    $defaultShortcuts = [
        'eshop' => ['catalog', 'orders', 'contact'],
        'semifinished' => ['catalog', 'orders', 'contact'],
        'custom' => ['presentations', 'customer-zone', 'contact'],
    ];
    $savedShortcuts = auth()->user()?->home_shortcuts;
    $selectedShortcuts = [];

    foreach ($shortcutGroups as $groupKey => $group) {
        $selection = is_array($savedShortcuts) ? ($savedShortcuts[$groupKey] ?? []) : $defaultShortcuts[$groupKey];
        $selectedShortcuts[$groupKey] = array_values(array_intersect($selection, array_keys($group['items'])));

        if (count($selectedShortcuts[$groupKey]) < 1) {
            $selectedShortcuts[$groupKey] = $defaultShortcuts[$groupKey];
        }
    }
@endphp

@section('content')
<section class="home-hero py-5 border-bottom" id="homeShortcutManager"
    data-storage-key="web_interia_home_shortcuts_v1"
    data-preferences="{{ json_encode($selectedShortcuts) }}"
    @auth
        data-save-url="{{ route('customer.home-shortcuts.update', [], false) }}"
        data-csrf-token="{{ csrf_token() }}"
    @endauth>
    <div class="home-hero-video" data-hero-media data-hero-interval="8000" aria-hidden="true">
        <img class="home-hero-video-item is-active" src="/images/home-hero-components.jpg" alt="">
        <img class="home-hero-video-item" src="/images/home-hero-boards.jpg" alt="">
        <img class="home-hero-video-item" src="/images/home-hero-kitchen.jpg" alt="">
    </div>
    <div class="container py-lg-4">
        <div class="home-assistant" data-home-assistant data-catalog-search-url="{{ route('eshop.catalog.index', [], false) }}" data-product-search-url="{{ route('search.products', [], false) }}">
            <form class="home-assistant-search" data-assistant-form role="search">
                <i class="bi bi-search home-assistant-icon" aria-hidden="true"></i>
                <label class="visually-hidden" for="homeAssistantQuery">Čo hľadáte?</label>
                <input id="homeAssistantQuery" class="home-assistant-input" type="search" autocomplete="off" placeholder="Čo hľadáte?" aria-controls="homeAssistantResults" aria-expanded="false" data-assistant-input>
                <button class="home-assistant-submit" type="submit" aria-label="Vyhľadať" title="Vyhľadať"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
            </form>
            <div class="home-assistant-results" id="homeAssistantResults" role="region" aria-label="Výsledky vyhľadávania" hidden data-assistant-results>
                <div class="home-assistant-heading"><i class="bi bi-search" aria-hidden="true"></i><span role="status" data-assistant-heading>Výsledky vyhľadávania</span></div>
                <div data-assistant-list></div>
            </div>
            <div hidden data-assistant-options>
                <a href="{{ route('eshop.catalog.index', [], false) }}" data-assistant-option data-title="Nájsť výrobok v katalógu" data-description="Kovania, komponenty a materiál na priame objednanie." data-icon="bi-grid" data-keywords="kupit kupujem hladam produkt vyrobok material kovanie pant panty zaves zasuvka vysuv uchytka nozicka kos skrutka lepidlo hrana drez odsavac katalog cena sklad eshop"></a>
                <a href="{{ route('semifinished', [], false) }}" data-assistant-option data-title="Objednať polotovary" data-description="Porez, olepenie hrán a výroba dielov podľa rozmerov." data-icon="bi-rulers" data-keywords="narezat rezanie porez doska dosky dtd mdf rozmer rozmery olepit olepenie hrana hrany diel diely skrinka skrinky polotovar polotovary vyrobit cast nabytku"></a>
                <a href="{{ route('custom-work', [], false) }}" data-assistant-option data-title="Zákazka na mieru" data-description="Návrh, výroba a montáž kompletného riešenia." data-icon="bi-house-gear" data-keywords="na mieru navrh projekt montaz kuchyna kuchynu skrina satnik nabytok interier prevadzka kancelaria dom byt realizacia komplet kompletne poradit neviem"></a>
                <a href="{{ route('custom-work.presentations', [], false) }}" data-assistant-option data-title="Pozrieť ukážky realizácií" data-description="Inšpirujte sa dokončenými zákazkami." data-icon="bi-images" data-keywords="ukazka ukazky realizacia realizacie inspiracia inspiracie fotografia fotografie obrazok obrazky galeria referencia referencie"></a>
                <a href="{{ route('contact', [], false) }}" data-assistant-option data-title="Poradiť sa s nami" data-description="Napíšte nám, ak si nie ste istí správnym riešením." data-icon="bi-chat-dots" data-keywords="pomoc poradit konzultacia kontakt otazka neviem rozhodnut problem specialne individualne zavolat napisat"></a>
                <a href="{{ route('faq', [], false) }}" data-assistant-option data-title="Časté otázky" data-description="Rýchle odpovede k objednávkam a službám." data-icon="bi-question-circle" data-keywords="ako preco kedy doprava platba dodanie termin reklamacia objednavka otazka odpoved faq informacie"></a>
            </div>
        </div>
        <div class="row g-1 mb-4 quick-order-grid">
            <div class="col-12 col-md-auto quick-order-col">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-materials h-100">
                    <div class="card-body d-flex flex-column">
                        <a href="{{ route('materials-eshop', [], false) }}" class="btn btn-primary w-100">E-shop</a>
                        <div class="quick-shortcuts" data-shortcut-group="eshop" aria-label="Rýchle odkazy pre E-shop">
                            <div class="quick-shortcuts-list">
                                @foreach ($selectedShortcuts['eshop'] as $shortcutKey)
                                    @php($shortcut = $shortcutGroups['eshop']['items'][$shortcutKey])
                                    <a href="{{ $shortcut['url'] }}" class="quick-shortcut-link" data-shortcut-key="{{ $shortcutKey }}" title="{{ $shortcut['label'] }}"><i class="bi {{ $shortcut['icon'] }}" aria-hidden="true"></i><span>{{ $shortcut['label'] }}</span></a>
                                @endforeach
                            </div>
                            <button type="button" class="quick-shortcuts-edit" data-edit-shortcuts="eshop" aria-label="Upraviť skratky pre E-shop" title="Upraviť skratky"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                        </div>
                        <div class="quick-order-copy mt-3">
                            <details class="quick-order-more">
                                <summary class="quick-order-toggle">
                                    <span class="quick-order-desc-preview">
                                        <span class="quick-order-line"><strong>📦 E-shop:</strong> materiály, kovania,</span>
                                        <span class="quick-order-line quick-order-line-with-more">komponenty, rýchly výber...</span>
                                    </span>
                                    <span class="quick-order-inline-more" aria-hidden="true">viac...</span>
                                </summary>
                                <p class="quick-order-full mb-0">Objednajte si výsuvy, závesy, výklopy, úchytky, nožičky, rohové vybavenie skriniek, koše, zásuvné systémy, skrutky, spojovacie prvky, hrany, lepidlá, čističe, drezy, odsávače a ďalšie prvky na jednom mieste. K dispozícii máte aj prehľadný katalóg, ceny so zľavami podľa odberu a jednoduchý nákupný proces.</p>
                            </details>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-12 col-md-auto quick-order-col">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-semifinished h-100">
                    <div class="card-body d-flex flex-column">
                        <a href="{{ route('semifinished', [], false) }}" class="btn btn-primary w-100">Polotovary</a>
                        <div class="quick-shortcuts" data-shortcut-group="semifinished" aria-label="Rýchle odkazy pre Polotovary">
                            <div class="quick-shortcuts-list">
                                @foreach ($selectedShortcuts['semifinished'] as $shortcutKey)
                                    @php($shortcut = $shortcutGroups['semifinished']['items'][$shortcutKey])
                                    <a href="{{ $shortcut['url'] }}" class="quick-shortcut-link" data-shortcut-key="{{ $shortcutKey }}" title="{{ $shortcut['label'] }}"><i class="bi {{ $shortcut['icon'] }}" aria-hidden="true"></i><span>{{ $shortcut['label'] }}</span></a>
                                @endforeach
                            </div>
                            <button type="button" class="quick-shortcuts-edit" data-edit-shortcuts="semifinished" aria-label="Upraviť skratky pre Polotovary" title="Upraviť skratky"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                        </div>
                        <div class="quick-order-copy mt-3">
                            <details class="quick-order-more">
                                <summary class="quick-order-toggle">
                                    <span class="quick-order-desc-preview">
                                        <span class="quick-order-line"><strong><span class="quick-order-icon quick-order-icon-assembly" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M4 7.5 11 4l7 3.5-7 3.5-7-3.5Z"/><path d="M4 7.5v7l7 3.5v-7L4 7.5Z"/><path d="M18 7.5v7L11 18v-7l7-3.5Z"/><path d="M7.5 15.4v3.1h9v-3.1"/></svg></span> Polotovary:</strong> porez, lepenie hrán,</span>
                                        <span class="quick-order-line quick-order-line-with-more">výroba skriniek a častí...</span>
                                    </span>
                                    <span class="quick-order-inline-more" aria-hidden="true">viac...</span>
                                </summary>
                                <p class="quick-order-full mb-0">V ponuke máme konfigurátor, v ktorom si môžete zadať porez plošného materiálu, lepenie hrán alebo výrobu hotových skriniek a častí nábytku. Rozmery môžete meniť...</p>
                            </details>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-12 col-md-auto quick-order-col">
                <article class="card border-0 shadow-sm quick-order-tile quick-order-custom h-100">
                    <div class="card-body d-flex flex-column">
                        <a href="{{ route('custom-work', [], false) }}" class="btn btn-primary w-100">Zákazky na mieru</a>
                        <div class="quick-shortcuts" data-shortcut-group="custom" aria-label="Rýchle odkazy pre Výrobu na mieru">
                            <div class="quick-shortcuts-list">
                                @foreach ($selectedShortcuts['custom'] as $shortcutKey)
                                    @php($shortcut = $shortcutGroups['custom']['items'][$shortcutKey])
                                    <a href="{{ $shortcut['url'] }}" class="quick-shortcut-link" data-shortcut-key="{{ $shortcutKey }}" title="{{ $shortcut['label'] }}"><i class="bi {{ $shortcut['icon'] }}" aria-hidden="true"></i><span>{{ $shortcut['label'] }}</span></a>
                                @endforeach
                            </div>
                            <button type="button" class="quick-shortcuts-edit" data-edit-shortcuts="custom" aria-label="Upraviť skratky pre Výrobu na mieru" title="Upraviť skratky"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                        </div>
                        <div class="quick-order-copy mt-3">
                            <details class="quick-order-more">
                                <summary class="quick-order-toggle">
                                    <span class="quick-order-desc-preview">
                                        <span class="quick-order-line"><strong><span class="quick-order-icon quick-order-icon-furniture" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M4 5.5h16v13H4v-13Z"/><path d="M8.5 5.5v13"/><path d="M15.5 5.5v13"/><path d="M4 11h16"/><path d="M8.5 14.5h7"/><path d="M6.5 20h11"/></svg></span> Zákazky:</strong> projekt, výroba, montáž</span>
                                        <span class="quick-order-line quick-order-line-with-more">pre domácnosti aj firmy</span>
                                    </span>
                                    <span class="quick-order-inline-more" aria-hidden="true">viac...</span>
                                </summary>
                                <p class="quick-order-full mb-0">Poradíme vám a prejdeme si detaily zadania. Ak ide o zákazku s montážou, podľa potreby si dohodneme obhliadku priestoru. Pripravíme ponuku a návrh na mieru, zákazku vyrobíme a zabezpečíme odbornú montáž.</p>
                            </details>
                        </div>
                    </div>
                </article>
            </div>
        </div>

    </div>
</section>

<div class="modal fade" id="shortcutModal" tabindex="-1" aria-labelledby="shortcutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shortcut-modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title h5 mb-1" id="shortcutModalLabel">Upraviť rýchle odkazy</h2>
                    <p class="small text-secondary mb-0">Vyberte 1 až 5 skratiek pre každú časť.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavrieť"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs shortcut-tabs" role="tablist">
                    @foreach ($shortcutGroups as $groupKey => $group)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="shortcut-tab-{{ $groupKey }}" data-bs-toggle="tab" data-bs-target="#shortcut-panel-{{ $groupKey }}" type="button" role="tab" aria-controls="shortcut-panel-{{ $groupKey }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $group['label'] }}</button>
                        </li>
                    @endforeach
                </ul>
                <div class="tab-content pt-3">
                    @foreach ($shortcutGroups as $groupKey => $group)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="shortcut-panel-{{ $groupKey }}" role="tabpanel" aria-labelledby="shortcut-tab-{{ $groupKey }}" tabindex="0">
                            <div class="shortcut-options" data-shortcut-options="{{ $groupKey }}">
                                @foreach ($group['items'] as $shortcutKey => $shortcut)
                                    <label class="shortcut-option" for="shortcut-{{ $groupKey }}-{{ $shortcutKey }}">
                                        <input class="form-check-input" type="checkbox" id="shortcut-{{ $groupKey }}-{{ $shortcutKey }}" value="{{ $shortcutKey }}" data-label="{{ $shortcut['label'] }}" data-icon="{{ $shortcut['icon'] }}" data-url="{{ $shortcut['url'] }}" @checked(in_array($shortcutKey, $selectedShortcuts[$groupKey], true))>
                                        <i class="bi {{ $shortcut['icon'] }}" aria-hidden="true"></i>
                                        <span>{{ $shortcut['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="alert alert-danger small mt-3 mb-0 d-none" role="alert" data-shortcut-error></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Zrušiť</button>
                <button type="button" class="btn btn-primary" data-save-shortcuts>Uložiť skratky</button>
            </div>
        </div>
    </div>
</div>

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

@push('scripts')
<script src="/assets/hero-media.js?v={{ filemtime(public_path('js/hero-media.js')) }}"></script>
<script src="/assets/quick-order.js?v={{ filemtime(public_path('js/quick-order.js')) }}"></script>
@endpush
