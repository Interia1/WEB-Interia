@extends('layouts.app')

@section('title', 'O nás | WEB-Interia')
@section('description', 'Spoznajte históriu, tím a hodnoty WEB-Interia.')

@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="display-6 mb-4">O nás</h1>
        <div class="row g-4">
            <div class="col-lg-4">
                <h2 class="h4">História</h2>
                <p class="text-secondary">WEB-Interia vznikla s cieľom prepojiť moderný dizajn, výkon a škálovateľný vývoj.</p>
            </div>
            <div class="col-lg-4">
                <h2 class="h4">Tím</h2>
                <p class="text-secondary">Náš tím kombinuje skúsenosti z web developmentu, UX a digitálnej stratégie.</p>
            </div>
            <div class="col-lg-4">
                <h2 class="h4">Hodnoty</h2>
                <p class="text-secondary">Transparentnosť, kvalita a dlhodobá udržateľnosť riešení pre klientov.</p>
            </div>
        </div>
    </div>
</section>
@endsection
