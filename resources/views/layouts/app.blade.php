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
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="d-flex flex-column min-vh-100 bg-light text-dark">
<nav class="navbar navbar-expand-lg border-bottom shadow-sm site-nav sticky-top" aria-label="Hlavná navigácia">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('home') }}">WEB-Interia</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Prepnúť navigáciu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Domov</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">O nás</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('services') }}">Služby</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('catalogs.overview') }}">Katalógy</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('custom-production') }}">Atypická výroba</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('materials') }}">Materiály</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('faq') }}">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Kontakt</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('dev.structure') }}">Vývojová štruktúra</a></li>
                @guest
                    <li class="nav-item"><a class="nav-link nav-cta-outline fw-semibold" href="{{ route('login') }}">Prihlásenie</a></li>
                    <li class="nav-item"><a class="nav-link nav-cta fw-semibold" href="{{ route('register') }}">Registrácia</a></li>
                @endguest
                @auth
                    <li class="nav-item"><a class="nav-link nav-cta-outline fw-semibold" href="{{ route('customer.orders') }}">Moje objednávky</a></li>
                    @if (Auth::user()->email === 'test@example.com')
                        <li class="nav-item"><a class="nav-link nav-cta-outline" href="{{ route('admin.consents.export') }}">Export súhlasov</a></li>
                    @endif
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
        <div class="container mt-3">
            <div class="alert mb-0 site-status" role="status">
                {{ session('status') }}
            </div>
        </div>
    @endif
    @yield('content')
</main>

<footer class="site-footer border-top mt-5 py-4">
    <div class="container">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <h2 class="h6">WEB-Interia</h2>
                <p class="small text-secondary mb-0">Moderná digitálna prezentácia pripravená na ďalšie fázy projektu.</p>
            </div>
            <div class="col-6 col-md-4">
                <h2 class="h6">Linky</h2>
                <ul class="list-unstyled small mb-0">
                    <li><a href="{{ route('home') }}">Domov</a></li>
                    <li><a href="{{ route('about') }}">O nás</a></li>
                    <li><a href="{{ route('services') }}">Služby</a></li>
                    <li><a href="{{ route('catalogs.overview') }}">Katalógy</a></li>
                    <li><a href="{{ route('materials') }}">Materiály</a></li>
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                    <li><a href="{{ route('contact') }}">Kontakt</a></li>
                    <li><a href="{{ route('legal.privacy') }}">Ochrana osobných údajov</a></li>
                    <li><a href="{{ route('legal.terms') }}">Obchodné podmienky</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4">
                <h2 class="h6">Kontakt & sociálne siete</h2>
                <ul class="list-unstyled small mb-0">
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
<script src="{{ asset('js/cookie-consent.js') }}"></script>
@stack('scripts')
</body>
</html>
