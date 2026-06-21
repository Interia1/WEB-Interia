@extends('layouts.app')

@section('title', 'WEB-Interia | Moderná prezentácia')
@section('description', 'WEB-Interia prináša modernú, rýchlu a mobilne optimalizovanú web prezentáciu.')
@section('og_title', 'WEB-Interia | Moderná prezentácia')
@section('og_description', 'Objavte naše riešenia, služby a kontaktujte nás.')

@section('content')
<section class="py-5 bg-white border-bottom">
    <div class="container py-lg-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-5 fw-semibold mb-3">WEB-Interia: moderný web pripravený na rast</h1>
                <p class="lead text-secondary">Rýchla a prehľadná firemná prezentácia, ktorá je pripravená na budúce rozšírenie o katalóg, API a integrácie.</p>
                <a href="{{ route('contact') }}" class="btn btn-primary btn-lg" aria-label="Prejsť na kontaktný formulár">Kontaktujte nás</a>
            </div>
            <div class="col-lg-5">
                <img class="img-fluid rounded shadow-sm" src="{{ asset('images/hero-placeholder.svg') }}" alt="Ilustrácia modernej webovej prezentácie">
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="h3 mb-4">Prečo WEB-Interia</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Mobile-first</h3>
                        <p class="mb-0 text-secondary">Bootstrap 5 responzivita pre perfektný zážitok na mobile aj desktopoch.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">GDPR-ready</h3>
                        <p class="mb-0 text-secondary">Cookies consent banner rešpektuje voľby používateľov pre analytiku a marketing.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Pripravené na ďalšie fázy</h3>
                        <p class="mb-0 text-secondary">Architektúra pripravená pre katalóg, API integrácie a e-shop funkcionalitu.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
