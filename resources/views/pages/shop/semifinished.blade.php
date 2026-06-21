@extends('layouts.app')

@section('title', 'Výroba polotovarov | WEB-Interia')
@section('description', 'Objednajte si polotovary podľa vašej špecifikácie. Špeciálny objednávkový formulár, výrobný tím sa vám ozve v 24h.')

@section('content')
<section class="py-5 bg-white border-bottom">
    <div class="container py-lg-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="display-6 mb-3">Výroba polotovarov</h1>
                <p class="lead text-secondary mb-0">Potrebujete polotovary podľa vašej špecifikácie? Vyplňte formulár a náš tým vám pripraví presný návrh.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('semifinished.catalog.index') }}" class="btn btn-outline-primary">Interaktívny katalóg</a>
                <a href="{{ route('semifinished.catalog.print') }}" class="btn btn-primary">Tlačový katalóg</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h2 class="h4 mb-4">Ako to funguje</h2>
                <div class="row g-2">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-primary fw-semibold mb-2">1. Vyplníte formulár</p>
                                <p class="mb-0 text-secondary">Zadáte rozmery, materiál, počet kusov a ďalšie špecifiká.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-primary fw-semibold mb-2">2. Dostanete návrh</p>
                                <p class="mb-0 text-secondary">Technická špecifikácia, cena a termín v emaile do 24 hodín.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-primary fw-semibold mb-2">3. Výroba a dodávka</p>
                                <p class="mb-0 text-secondary">Po schválení návrhu zaraďujeme do výroby. Polotovary doručíme v dohodnutom termíne.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h4 mb-4">Objednávkový formulár</h2>
                        <form method="post" action="{{ route('contact.submit') }}" class="needs-validation">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Vaše meno / firma</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Špecifikácia polotovarov</label>
                                <textarea id="message" name="message" class="form-control" rows="5" placeholder="Rozmery (D×Š×V), materiál, počet kusov, povrch, tolerancie, ďalšie detaily..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefón (voliteľne)</label>
                                <input type="tel" id="phone" name="phone" class="form-control" placeholder="+421 900 000 000">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Odoslať špecifikáciu</button>

                            <p class="small text-secondary mt-3 mb-0">Po odoslaní sa vám ozve výrobný tím s kompletným návrh om a cenou.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <h2 class="h4 mb-3">Čo je polotovar?</h2>
        <p class="text-secondary">Polotovar je výrobok, ktorý nie je finálny – typicky ide o kupované kusy alebo časti, ktoré si klient ďalej doopracuje, zmontuje alebo používa ako komponenty. Medzi klasické polotovary patria:</p>
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <ul class="text-secondary">
                    <li>Rezané a hnuté časti z hliníka či ocele</li>
                    <li>Frézované komponenty podľa výkresu</li>
                    <li>Zvarované a ohnuté prvky</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="text-secondary">
                    <li>Poniklované alebo eloxované časti</li>
                    <li>Lakované jednotky bez montáže</li>
                    <li>Prefabrikované súpravy na domontovanie</li>
                </ul>
            </div>
        </div>
    </div>
</section>
@endsection
