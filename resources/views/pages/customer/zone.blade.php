@extends('layouts.app')

@section('title', 'I-zóna | WEB-Interia')
@section('description', 'Vstup do zákazníckej zóny WEB-Interia.')

@section('content')
<section class="py-5 border-bottom">
    <div class="container py-lg-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-7">
                <p class="text-uppercase small text-primary fw-semibold mb-2">Zákaznícka zóna</p>
                <h1 class="display-5 fw-semibold mb-3">I-zóna</h1>
                <p class="lead text-secondary mb-4">Na jednom mieste nájdete objednávky materiálov, polotovarov aj projekty výroby na mieru.</p>
                <div class="d-flex flex-wrap gap-2">
                    @guest
                        <a class="btn btn-primary" href="{{ route('login') }}">Prihlásiť sa</a>
                        <a class="btn btn-outline-primary" href="{{ route('register') }}">Vytvoriť účet</a>
                    @endguest
                    @auth
                        <a class="btn btn-primary" href="{{ route('customer.orders') }}">Otvoriť moje objednávky</a>
                    @endauth
                </div>
            </div>
            <div class="col-lg-5">
                <div class="dashboard-card p-4">
                    <h2 class="h4 mb-3">Čo tu vybavíte</h2>
                    <ul class="mb-0 text-secondary">
                        <li class="mb-2">prehľad a stav objednávok,</li>
                        <li class="mb-2">termíny výroby a dodania,</li>
                        <li>rýchly kontakt k rozpracovanému projektu.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection