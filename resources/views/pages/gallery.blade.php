@extends('layouts.app')

@section('title', 'Galéria | WEB-Interia')
@section('description', 'Ukážky realizácií, výroby a detailov riešení WEB-Interia.')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="section-intro mb-4">
            <p class="text-uppercase small text-primary fw-semibold mb-2">Galéria</p>
            <h1 class="display-6 mb-3">Ukážky riešení a detailov výroby</h1>
            <p class="lead text-secondary mb-0">Priestor pre fotografie realizácií, materiálov, polotovarov a hotových nábytkových zostáv.</p>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card border-0 dashboard-card h-100">
                    <div class="card-body">
                        <h2 class="h5">Materiály</h2>
                        <p class="text-secondary mb-0">Výbery povrchov, hrán, kovaní a komponentov pre ďalšie spracovanie.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 dashboard-card h-100">
                    <div class="card-body">
                        <h2 class="h5">Polotovary</h2>
                        <p class="text-secondary mb-0">Dielce pripravené na skladanie, montáž alebo ďalšie opracovanie.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 dashboard-card h-100">
                    <div class="card-body">
                        <h2 class="h5">Zostavy</h2>
                        <p class="text-secondary mb-0">Hotové riešenia na mieru pre domácnosti aj firemné priestory.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
