@extends('layouts.app')

@section('title', 'Predaj materiálov | WEB-Interia')
@section('description', 'Nakupujte kvalitné materiály v našom e-shope. Rýchla dodávka, jasné ceny.')

@section('content')
<section class="py-5 bg-white border-bottom">
    <div class="container py-lg-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="display-6 mb-3">Predaj materiálov – E-shop</h1>
                <p class="lead text-secondary">Vyberte si materiály z nášho katalógu a objednajte ich online. Garantujeme kvalitu a rýchlu dodávku.</p>
            </div>
        </div>

        <div class="row g-3 align-items-center">
            <div class="col-lg-8">
                <div class="alert alert-info">
                    <strong>⚡ Ako to funguje:</strong> Vyberte materiál, pridajte do košíka, zadajte adresu dodávky a zaplaťte online.
                </div>
            </div>
            <div class="col-lg-4 text-end">
                <a href="{{ route('eshop.catalog.index') }}" class="btn btn-primary btn-lg">
                    Otvoriť e-shop katalóg →
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="h3 mb-4">Dostupné kategórie materiálov</h2>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Hliník</h3>
                        <p class="text-secondary">Nízka hmotnosť, odolnosť voči korózii, vhodný na exteriér.</p>
                        <a href="{{ route('eshop.catalog.index') }}" class="btn btn-sm btn-outline-primary mt-3">Pozrieť →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Oceľ</h3>
                        <p class="text-secondary">Vysoká pevnosť, robustnosť, vhodná na nosné prvky.</p>
                        <a href="{{ route('eshop.catalog.index') }}" class="btn btn-sm btn-outline-primary mt-3">Pozrieť →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Nerez</h3>
                        <p class="text-secondary">Elegancia a funkčnosť, ideálny na viditeľné prvky.</p>
                        <a href="{{ route('eshop.catalog.index') }}" class="btn btn-sm btn-outline-primary mt-3">Pozrieť →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light border-top">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h4 mb-3">Objednajte teraz z katalógu</h2>
                        <p class="text-secondary mb-4">Všetky materiály sú skladom. Objednávky spracovávame v pracovné dni do 24 hodín.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('eshop.catalog.index') }}" class="btn btn-primary btn-lg">Prejsť do e-shopu</a>
                            <a href="{{ route('eshop.catalog.print') }}" class="btn btn-outline-primary btn-lg">Tlačový katalóg</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-3">Benefity nákupu</h3>
                        <ul class="mb-0 text-secondary">
                            <li>✓ Transparentné ceny bez skrytých poplatkov</li>
                            <li>✓ Bezpečné online platby</li>
                            <li>✓ Rýchla dodávka na adresu</li>
                            <li>✓ Návrat do 14 dní bez otázok</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
