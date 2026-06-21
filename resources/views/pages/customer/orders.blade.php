@extends('layouts.app')

@section('title', 'Moje objednávky | WEB-Interia')
@section('description', 'Prehľad všetkých vašich objednávok. Materiály, polotovary, výroba na mieru.')

@section('content')
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <h1 class="display-5 fw-semibold mb-3">Prehľad mojich objednávok</h1>
        <p class="lead text-secondary mb-0">Tu vidíte všetky vaše objednávky zo všetkých troch kategórií naraz. Sledujte stav a detaily každej objednávky.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <!-- Section 1: Materials -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h3 mb-0">📦 Objednávky materiálov (E-shop)</h2>
                <a href="{{ route('materials-eshop') }}" class="btn btn-outline-primary">Objednať viac →</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Číslo objednávky</th>
                            <th scope="col">Materiál</th>
                            <th scope="col">Množstvo</th>
                            <th scope="col">Suma</th>
                            <th scope="col">Stav</th>
                            <th scope="col">Dátum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#MAT-2026-001</td>
                            <td>Hliník 40×40 mm</td>
                            <td>10 m</td>
                            <td>€ 120,00</td>
                            <td><span class="badge bg-success">Dodané</span></td>
                            <td>21.6.2026</td>
                        </tr>
                        <tr>
                            <td>#MAT-2026-002</td>
                            <td>Nerez oceľ lúčovej</td>
                            <td>5 m</td>
                            <td>€ 285,00</td>
                            <td><span class="badge bg-info">V doprave</span></td>
                            <td>20.6.2026</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-center text-secondary"><em>Bez ďalších objednávok. <a href="{{ route('materials-eshop') }}">Prejsť do e-shopu →</a></em></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="fs-6 text-secondary">Celk. suma za túto sekciu: <strong>€ 405,00</strong></p>
        </div>

        <!-- Section 2: Semi-finished products -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h3 mb-0">⚙️ Objednávky polotovarov (Výroba na objednávku)</h2>
                <a href="{{ route('semifinished') }}" class="btn btn-outline-primary">Objednať polotovary →</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Číslo objednávky</th>
                            <th scope="col">Popis</th>
                            <th scope="col">Počet kusov</th>
                            <th scope="col">Suma</th>
                            <th scope="col">Stav</th>
                            <th scope="col">Termín</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#POL-2026-015</td>
                            <td>Hnuté časti 200×100 mm</td>
                            <td>50 ks</td>
                            <td>€ 890,00</td>
                            <td><span class="badge bg-warning">Vo výrobe</span></td>
                            <td>28.6.2026</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-center text-secondary"><em>Bez ďalších objednávok. <a href="{{ route('semifinished') }}">Objednať polotovary →</a></em></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="fs-6 text-secondary">Celk. suma za túto sekciu: <strong>€ 890,00</strong></p>
        </div>

        <!-- Section 3: Custom work with assembly -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h3 mb-0">🔧 Projekty výroby na mieru (Kompletné riešenie)</h2>
                <a href="{{ route('custom-work') }}" class="btn btn-outline-primary">Požiadať o projekt →</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Číslo projektu</th>
                            <th scope="col">Popis</th>
                            <th scope="col">Fáza</th>
                            <th scope="col">Suma</th>
                            <th scope="col">Pokrok</th>
                            <th scope="col">Dokončenie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#PRJ-2026-08</td>
                            <td>Otvárací systém pre sklad</td>
                            <td>Výroba</td>
                            <td>€ 5 400,00</td>
                            <td><div class="progress" style="height: 1.2rem;"><div class="progress-bar" style="width: 65%;">65%</div></div></td>
                            <td>15.7.2026</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-center text-secondary"><em>Bez ďalších projektov. <a href="{{ route('custom-work') }}">Kontaktovať pre nový projekt →</a></em></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="fs-6 text-secondary">Celk. suma za túto sekciu: <strong>€ 5 400,00</strong></p>
        </div>

        <!-- SUMMARY CARD -->
        <div class="card border-primary shadow-sm bg-light">
            <div class="card-body p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <h3 class="h4 mb-3">📊 Súhrn všetkých objednávok</h3>
                        <div class="row g-2">
                            <div class="col-6">
                                <p class="text-secondary mb-1">Materiály (E-shop):</p>
                                <p class="h5 mb-0">€ 405,00</p>
                            </div>
                            <div class="col-6">
                                <p class="text-secondary mb-1">Polotovary:</p>
                                <p class="h5 mb-0">€ 890,00</p>
                            </div>
                            <div class="col-6">
                                <p class="text-secondary mb-1">Výroba na mieru:</p>
                                <p class="h5 mb-0">€ 5 400,00</p>
                            </div>
                            <div class="col-6">
                                <p class="text-secondary fw-semibold mb-1">Celková suma:</p>
                                <p class="h5 mb-0 text-primary fw-semibold">€ 6 695,00</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h3 class="h5 mb-2">Ďalšie akcie</h3>
                        <p class="text-secondary mb-3">Chcete niečo zmeniť alebo máte otázku na konkrétnu objednávku?</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('contact') }}" class="btn btn-primary">Kontaktovať tím</a>
                            <a href="{{ route('home') }}" class="btn btn-outline-primary">Späť na domov</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
