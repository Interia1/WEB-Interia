@extends('layouts.app')

@section('title', 'Katalógy | WEB-Interia')
@section('description', 'Interaktívne a tlačové katalógy pre e-shop materiálov a výrobu polotovarov.')

@section('content')
<section class="py-5 bg-white border-bottom">
    <div class="container py-lg-4">
        <h1 class="display-6 mb-3">Katalógy pre zákazníkov aj výrobu</h1>
        <p class="lead text-secondary mb-0">Vyberte si katalóg podľa typu objednávky. Každý katalóg má interaktívny režim pre online prácu a tlačový režim pre výrobcov katalógov.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <article class="card h-100 shadow-sm border-0">
                    <div class="card-body p-4 p-lg-5 d-flex flex-column">
                        <p class="text-uppercase small text-primary fw-semibold mb-2">E-shop</p>
                        <h2 class="h4 mb-3">Katalóg materiálov</h2>
                        <p class="text-secondary flex-grow-1">Interaktívny produktový katalóg pre nákup materiálov s filtráciou podľa kategórie a vyhľadávaním. Obsahuje aj tlačovú verziu pripravenú pre sadzbu.</p>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <a href="{{ route('eshop.catalog.index') }}" class="btn btn-primary">Interaktívny katalóg</a>
                            <a href="{{ route('eshop.catalog.print') }}" class="btn btn-outline-primary">Tlačová verzia</a>
                        </div>
                    </div>
                </article>
            </div>

            <div class="col-lg-6">
                <article class="card h-100 shadow-sm border-0">
                    <div class="card-body p-4 p-lg-5 d-flex flex-column">
                        <p class="text-uppercase small text-warning fw-semibold mb-2">Polotovary</p>
                        <h2 class="h4 mb-3">Katalóg výrobných dielov</h2>
                        <p class="text-secondary flex-grow-1">Samostatný katalóg pre polotovary s prehľadom technologických typov, materiálov a minimálnych sérií. Pre výrobných partnerov je dostupná čistá tlačová forma.</p>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <a href="{{ route('semifinished.catalog.index') }}" class="btn btn-primary">Interaktívny katalóg</a>
                            <a href="{{ route('semifinished.catalog.print') }}" class="btn btn-outline-primary">Tlačová verzia</a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
