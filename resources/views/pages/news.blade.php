@extends('layouts.app')

@section('title', 'Novinky | WEB-Interia')
@section('description', 'Nové produkty, služby a oznamy WEB-Interia.')

@section('content')
<section class="py-5">
    <div class="container py-lg-4">
        <h1 class="display-5 fw-semibold mb-3">Novinky</h1>
        <p class="lead text-secondary">Momentálne tu nie sú zverejnené žiadne novinky.</p>
        <p>S otázkami na sortiment alebo služby nás kontaktujte.</p>
        <div class="d-flex flex-wrap gap-3">
            <a href="{{ route('services', [], false) }}">Pozrieť naše služby <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            <a href="{{ route('contact', [], false) }}">Napísať nám <i class="bi bi-chat-dots" aria-hidden="true"></i></a>
        </div>
    </div>
</section>
@endsection