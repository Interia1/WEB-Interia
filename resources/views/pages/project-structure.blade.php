@extends('layouts.app')

@section('title', 'Vývojová štruktúra | WEB-Interia')
@section('description', 'Prehľad osnovy, architektúry a vývojového diagramu projektu WEB-Interia.')

@section('content')
<section class="py-5 border-bottom bg-light">
    <div class="container">
        <h1 class="display-6 fw-semibold mb-3">Vývojová štruktúra projektu</h1>
        <p class="lead text-secondary mb-0">Stránka sumarizuje podklady pre vývojára: osnova tvorby webu, architektúra integrácií a vývojový diagram.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <article class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Osnova tvorby webu</h2>
                        <ul class="text-secondary mb-0">
                            <li>Fázy 0-10: od kostry webu až po produkciu.</li>
                            <li>Každá fáza má URL kontrolu a akceptačné kritériá.</li>
                            <li>Najprv mock dáta, potom reálna integrácia.</li>
                            <li>Prihlasovanie a používateľské zóny sú plánované jadro.</li>
                        </ul>
                    </div>
                </article>
            </div>
            <div class="col-12 col-lg-4">
                <article class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Architektúra integrácií</h2>
                        <ul class="text-secondary mb-0">
                            <li>Princíp raz a dosť: master dáta mimo webu.</li>
                            <li>Web je prezentačná a objednávková vrstva.</li>
                            <li>Adapter model pre OBERON a ďalšie ERP systémy.</li>
                            <li>Integrácie: sync, retry, logy, monitoring a bezpečnosť.</li>
                        </ul>
                    </div>
                </article>
            </div>
            <div class="col-12 col-lg-4">
                <article class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Vývojový diagram</h2>
                        <ul class="text-secondary mb-0">
                            <li>Obsahuje role, zóny, I-zónu a zákaznícky workflow.</li>
                            <li>Počíta s reklamou, analytikou a mobile-first UX.</li>
                            <li>Zahŕňa DevOps, CI/CD, zálohy a disaster recovery.</li>
                            <li>Požaduje GDPR, cookies consent a riadenie retencie dát.</li>
                        </ul>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
