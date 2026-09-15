<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'WEB-Interia')</title>
    <meta name="description" content="@yield('description', 'WEB-Interia - moderné riešenia pre váš web a digitálny rast.')">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="WEB-Interia">
    <meta property="og:title" content="@yield('og_title', 'WEB-Interia')">
    <meta property="og:description" content="@yield('og_description', 'Moderná, mobilne optimalizovaná prezentácia WEB-Interia.')">
    <meta property="og:url" content="{{ url()->current() }}">

    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="d-flex flex-column min-vh-100 bg-light text-dark">
<nav class="navbar navbar-expand-xl border-bottom shadow-sm site-nav sticky-top" aria-label="Hlavná navigácia">
    <div class="container">
        <div class="site-brand-contact">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home', [], false) }}" aria-label="Domov" title="Domov">
                <img src="/images/Logo%20png%20bez%20pozadia.png" alt="Interia" class="navbar-brand-logo">
            </a>
        </div>
        <div class="site-header-contact" aria-label="Rýchly kontakt">
            <a href="tel:+421900000000"><i class="bi bi-telephone" aria-hidden="true"></i><strong>+421 900 000 000</strong></a>
            <a href="mailto:admin@interia.test"><i class="bi bi-envelope" aria-hidden="true"></i><span>admin@interia.test</span></a>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Prepnúť navigáciu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto gap-lg-2">
                <li class="nav-item history-nav" aria-label="História prehliadania">
                    <button type="button" class="history-nav-button" data-history-back aria-label="Späť" title="Späť">
                        <i class="bi bi-arrow-90deg-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="history-nav-button" data-history-forward aria-label="Ďalej" title="Ďalej">
                        <i class="bi bi-arrow-90deg-right" aria-hidden="true"></i>
                    </button>
                </li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">O nás</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('gallery') ? 'active' : '' }}" href="{{ route('gallery') }}">Galéria</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('faq') ? 'active' : '' }}" href="{{ route('faq') }}">Podpora</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('legal.terms') ? 'active' : '' }}" href="{{ route('legal.terms') }}#reklamacie">Reklamácie</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Kontakty</a></li>
                <li class="nav-item"><a class="nav-link nav-izone {{ request()->routeIs('customer.*') ? 'active' : '' }}" href="{{ route('customer.zone') }}">I-zóna</a></li>
                @guest
                    <li class="nav-item dropdown account-menu">
                        <button class="nav-link account-menu-toggle {{ request()->routeIs('login', 'register') ? 'active' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Používateľský účet">
                            <i class="bi bi-person" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end account-menu-dropdown">
                            <li><a class="dropdown-item" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Prihlásenie</a></li>
                            <li><a class="dropdown-item" href="{{ route('register') }}"><i class="bi bi-person-plus" aria-hidden="true"></i> Registrácia</a></li>
                        </ul>
                    </li>
                @endguest
                @auth
                    <li class="nav-item"><a class="nav-link" href="{{ route('customer.account.edit') }}">Môj účet</a></li>
                    <li class="nav-item"><a class="nav-link nav-cta-outline fw-semibold" href="{{ route('customer.orders') }}">Moje objednávky</a></li>
                    @can('access-internal')
                        <li class="nav-item"><a class="nav-link nav-cta fw-semibold" href="{{ route('internal.dashboard') }}">Interná zóna</a></li>
                    @endcan
                    @can('view-project-structure')
                        <li class="nav-item"><a class="nav-link nav-cta-outline" href="{{ route('dev.structure') }}">Developer</a></li>
                    @endcan
                    @can('export-consents')
                        <li class="nav-item"><a class="nav-link nav-cta-outline" href="{{ route('admin.consents.export') }}">Export súhlasov</a></li>
                    @endcan
                    <li class="nav-item">
                        <form method="post" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">Odhlásiť ({{ Auth::user()->name }})</button>
                        </form>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="flex-grow-1">
    @if (session('status'))
        <div class="site-notice-container" aria-live="polite" aria-atomic="true">
            <div class="site-notice" role="status">
                <div class="d-flex align-items-center">
                    <span class="site-notice-icon" aria-hidden="true">✓</span>
                    <div class="site-notice-body">{{ session('status') }}</div>
                </div>
            </div>
        </div>
    @endif
    @yield('content')
</main>

<footer class="site-footer border-top py-3">
    <div class="container">
        <div class="row g-3">
            <div class="col-12 col-lg-2">
                <h2 class="h6">WEB-Interia</h2>
                <p class="small text-secondary mb-0">Moderná digitálna prezentácia pripravená na ďalšie fázy projektu.</p>
            </div>
            <div class="col-12 col-lg-7">
                <h2 class="h6">Linky</h2>
                <ul class="list-unstyled small mb-0 footer-inline-links">
                    <li><a href="{{ route('home') }}">Domov</a></li>
                    <li><a href="{{ route('about') }}">O nás</a></li>
                    <li><a href="{{ route('services') }}">Služby</a></li>
                    <li><a href="{{ route('catalogs.overview') }}">Katalógy</a></li>
                    <li><a href="{{ route('materials') }}">Materiál</a></li>
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                    <li><a href="{{ route('contact') }}">Kontakt</a></li>
                    <li><a href="{{ route('legal.privacy') }}">Ochrana osobných údajov</a></li>
                    <li><a href="{{ route('legal.terms') }}">Obchodné podmienky</a></li>
                </ul>
            </div>
            <div class="col-12 col-lg-3">
                <h2 class="h6">Kontakt & sociálne siete</h2>
                <ul class="list-unstyled small mb-0 footer-inline-links">
                    <li><a href="mailto:info@web-interia.sk">info@web-interia.sk</a></li>
                    <li><a href="https://www.linkedin.com" aria-label="LinkedIn WEB-Interia">LinkedIn</a></li>
                    <li><a href="https://www.facebook.com" aria-label="Facebook WEB-Interia">Facebook</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<div class="cookie-banner" id="cookieBanner" role="dialog" aria-live="polite" aria-label="Nastavenie cookies" hidden>
    <div class="container py-3">
        <div class="d-lg-flex align-items-center justify-content-between gap-3">
            <p class="mb-3 mb-lg-0 small">Používame nevyhnutné cookies a voliteľné analytické/marketingové cookies. Vyberte si svoje preferencie.</p>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="openCookieModal">Nastavenia</button>
                <button class="btn btn-outline-primary btn-sm" type="button" data-cookie-action="reject-optional">Len nevyhnutné</button>
                <button class="btn btn-primary btn-sm" type="button" data-cookie-action="accept-all">Súhlasím so všetkým</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cookieModal" tabindex="-1" aria-labelledby="cookieModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="cookieModalLabel">Podrobnosti o cookies</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavrieť"></button>
            </div>
            <div class="modal-body">
                <p class="small">Nevyhnutné cookies sú vždy aktívne. Ostatné si môžete zapnúť/vypnúť.</p>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="analyticsCookies">
                    <label class="form-check-label" for="analyticsCookies">Analytické cookies</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="marketingCookies">
                    <label class="form-check-label" for="marketingCookies">Marketingové cookies</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Zrušiť</button>
                <button type="button" class="btn btn-primary" id="saveCookieSettings">Uložiť nastavenia</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="/assets/cookie-consent.js?v={{ filemtime(public_path('js/cookie-consent.js')) }}"></script>
<script src="/assets/site-notice.js?v={{ filemtime(public_path('js/site-notice.js')) }}"></script>
<script src="/assets/history-navigation.js?v={{ filemtime(public_path('js/history-navigation.js')) }}"></script>
@stack('scripts')
</body>
</html>
