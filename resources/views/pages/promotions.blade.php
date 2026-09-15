@extends('layouts.app')

@section('title', 'Akcie | WEB-Interia')
@section('description', 'Aktuálne akcie a časovo obmedzené ponuky WEB-Interia.')

@section('content')
<section class="py-5">
    <div class="container py-lg-4">
        <h1 class="display-5 fw-semibold mb-3">Akcie</h1>
        <p class="lead text-secondary">Momentálne tu nie sú zverejnené žiadne akcie.</p>
        <p>Aktuálnu cenu a dostupnosť materiálov, kovania a služieb si môžete overiť u nás.</p>
        <div class="d-flex flex-wrap gap-3">
            <a href="{{ route('eshop.catalog.index', [], false) }}">Prejsť do katalógu <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            <a href="{{ route('contact', [], false) }}">Informovať sa o ponuke <i class="bi bi-chat-dots" aria-hidden="true"></i></a>
        </div>
    </div>
</section>
@endsection