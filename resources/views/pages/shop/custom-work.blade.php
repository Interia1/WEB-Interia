@extends('layouts.app')

@section('title', 'Výroba na mieru s montážou | WEB-Interia')
@section('description', 'Komplexné riešenia od návrhu až po montáž. Zákazný projekt s konzultáciou, výrobou a inštaláciou.')

@section('content')
<section class="py-5 bg-white border-bottom">
    <div class="container py-lg-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="display-6 mb-3">Výroba na mieru – Kompletné riešenie</h1>
                <p class="lead text-secondary mb-0">Od vášho nápadu cez návrh, výrobu, až po profesionálnu montáž na mieste. Jedno kontaktné miesto pre celý projekt.</p>
            </div>
            <a href="{{ route('custom-work.presentations') }}" class="btn btn-outline-primary">Prezentácie dielov</a>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="h3 mb-4">Fázy projektu</h2>
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-2">1. Konzultácia a analýza</h3>
                        <p class="mb-0 text-secondary">Stretnutie s vašim tímom, zbieranie požiadaviek, analýza priestoru a podmienok nasadenia.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-2">2. Návrh a kalkulácia</h3>
                        <p class="mb-0 text-secondary">Technický návrh, výkresy, špecifikácia materiálov a podrobná cena projektu.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-2">3. Výroba</h3>
                        <p class="mb-0 text-secondary">Výroba podľa schváleného návrhu, kontrola kvality v jednotlivých fázach.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-2">4. Montáž a odovzdanie</h3>
                        <p class="mb-0 text-secondary">Profesionálna inštalácia, zaučenie vášho tímu, zápisnica odovzdania.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light border-top border-bottom">
    <div class="container">
        <h2 class="h3 mb-4">Príklady projektov</h2>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Otvárací systém na mieru</h3>
                        <p class="text-secondary">Špeciálna konštrukcia s montážou včítane servisu a údržby.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Technologický prvok do prostredia</h3>
                        <p class="text-secondary">Návrh, výroba, montáž a inštalácia s ohľadom na prostredie.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Ochranný prvok alebo nosná konštrukcia</h3>
                        <p class="text-secondary">Riešenie s certifikáciou a kompletným tímom na mieste.</p>
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
                <div class="card h-100">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h4 mb-3">Kontaktujte nás pre konzultáciu</h2>
                        <p class="text-secondary mb-4">Prvá konzultácia je bez záväzku. Preberie s vami všetky detaily projektu a pripraví vám reálny odhad.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('contact') }}" class="btn btn-primary btn-lg">Kontaktovať tím →</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-3">Čo je zahrnuté</h3>
                        <ul class="mb-0 text-secondary">
                            <li>✓ Technická konzultácia</li>
                            <li>✓ Detailný návrh</li>
                            <li>✓ Výroba podľa noriem</li>
                            <li>✓ Doprava na miesto</li>
                            <li>✓ Profesionálna montáž</li>
                            <li>✓ Preškolenie</li>
                            <li>✓ Garančný servis</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
