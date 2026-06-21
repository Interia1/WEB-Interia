@extends('layouts.app')

@section('title', 'Prezentácie dielov na mieru | WEB-Interia')
@section('description', 'Ukážky realizácií, technické riešenia a referenčné prezentácie pre zákazkovú výrobu na mieru.')

@section('content')
<section class="py-5 bg-white border-bottom">
    <div class="container py-lg-4">
        <h1 class="display-6 mb-3">Prezentácie dielov pre zákazkovú výrobu</h1>
        <p class="lead text-secondary mb-0">Namiesto klasického e-shop katalógu tu nájdete referencie, technické koncepty a priebeh realizácie kompletných riešení.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <article class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <p class="small text-uppercase text-secondary mb-2">Referenčný diel</p>
                        <h2 class="h5">Nosná konštrukcia s montážou</h2>
                        <p class="text-secondary">Kompletný návrh, statické posúdenie, výroba dielov a montáž na prevádzke zákazníka.</p>
                        <ul class="small text-secondary mb-0">
                            <li>Materiál: S355 + protikorózna ochrana</li>
                            <li>Rozsah: 34 dielov</li>
                            <li>Realizácia: 6 týždňov</li>
                        </ul>
                    </div>
                </article>
            </div>
            <div class="col-lg-4">
                <article class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <p class="small text-uppercase text-secondary mb-2">Technologický celok</p>
                        <h2 class="h5">Manipulačný modul na mieru</h2>
                        <p class="text-secondary">Výroba atypických dielov, predmontáž, skúšky funkčnosti a nasadenie do linky.</p>
                        <ul class="small text-secondary mb-0">
                            <li>Materiál: Nerez + hliník</li>
                            <li>Rozsah: 3 montážne uzly</li>
                            <li>Realizácia: 8 týždňov</li>
                        </ul>
                    </div>
                </article>
            </div>
            <div class="col-lg-4">
                <article class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <p class="small text-uppercase text-secondary mb-2">Ochranné riešenie</p>
                        <h2 class="h5">Bezpečnostné prvky prevádzky</h2>
                        <p class="text-secondary">Návrh ochranných prvkov, dokumentácia, výroba a finálne odovzdanie s revíziou.</p>
                        <ul class="small text-secondary mb-0">
                            <li>Materiál: Oceľ + komaxit</li>
                            <li>Rozsah: 120 m ochranných segmentov</li>
                            <li>Realizácia: 5 týždňov</li>
                        </ul>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light border-top">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <h2 class="h4 mb-2">Máte zadanie na kompletný projekt?</h2>
                <p class="text-secondary mb-0">Pošlite podklady a pripravíme technickú prezentáciu riešenia vrátane harmonogramu, rozpočtu a montážneho plánu.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('contact') }}" class="btn btn-primary btn-lg">Požiadať o konzultáciu</a>
            </div>
        </div>
    </div>
</section>
@endsection
