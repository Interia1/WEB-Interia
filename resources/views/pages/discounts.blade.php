@extends('layouts.app')

@section('title', 'Ceny a zľavy | WEB-Interia')
@section('description', 'Informácie o cenách a pripravovaných zľavách WEB-Interia.')

@section('content')
<section class="py-5">
    <div class="container py-lg-4">
        <h1 class="display-5 fw-semibold mb-3">Ceny a zľavy</h1>
        <p class="lead text-secondary">Čo ovplyvňuje vašu cenu</p>
        <p>Pripravujeme systém, ktorý môže zohľadniť množstvo, pravidelnosť odberu alebo členské podmienky. Konkrétne pravidlá a výšky zliav ešte nie sú zverejnené.</p>
        <p>Podmienky, platnosť aj spôsob potvrdenia ceny si zatiaľ dohodnite s nami v konkrétnej ponuke.</p>
        <a href="{{ route('contact', [], false) }}">Informovať sa o cene <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
</section>
@endsection