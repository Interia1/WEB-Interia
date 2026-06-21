@extends('layouts.app')

@section('title', 'Atypická výroba | WEB-Interia')
@section('description', 'Atypická výroba pre neštandardné rozmery, prototypy a špecifické technické požiadavky.')

@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="display-6 mb-3">Atypická výroba</h1>
        <p class="lead text-secondary">Keď štandardný produkt nestačí, navrhneme a vyrobíme riešenie presne podľa vášho zadania.</p>

        <div class="row g-4 mt-1">
            <div class="col-lg-6">
                <h2 class="h4">Čo vieme dodať</h2>
                <ul class="text-secondary mb-0">
                    <li>jednorazové prototypy na overenie návrhu,</li>
                    <li>kusová výroba špecifických dielov,</li>
                    <li>malé a stredné série pre pravidelný odber,</li>
                    <li>úpravy existujúcich riešení podľa nových požiadaviek.</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="card card-body h-100">
                    <h2 class="h5">Ako prebieha spolupráca</h2>
                    <ol class="mb-0 text-secondary">
                        <li>Krátka technická konzultácia a zber podkladov.</li>
                        <li>Návrh postupu, ceny a termínu realizácie.</li>
                        <li>Výroba, kontrola kvality a odovzdanie.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection