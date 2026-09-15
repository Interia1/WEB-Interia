@extends('layouts.app')

@section('title', 'I-zóna | WEB-Interia')
@section('description', 'Spoznajte I-zónu, pripravovaný členský priestor komunity WEB-Interia.')

@section('content')
<section class="py-5 border-bottom">
    <div class="container py-lg-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-7">
                <p class="text-uppercase small text-primary fw-semibold mb-2">Registrovaná komunita</p>
                <h1 class="display-5 fw-semibold mb-3">I-zóna</h1>
                <p class="lead text-secondary mb-4">Pripravujeme členský priestor pre interné informácie, praktické podklady a novinky našej komunity. Členský obsah bude dostupný iba prihláseným členom.</p>
                <div class="d-flex flex-wrap gap-2">
                    @guest
                        <a class="btn btn-primary" href="{{ route('login') }}">Prihlásiť sa</a>
                        <a class="btn btn-outline-primary" href="{{ route('register') }}">Vytvoriť účet</a>
                    @endguest
                    @auth
                        <p class="text-secondary mb-0">Ste prihlásený. Členské materiály zatiaľ nie sú zverejnené.</p>
                    @endauth
                </div>
            </div>
            <div class="col-lg-5">
                <div>
                    <h2 class="h4 mb-3">Čo pripravujeme pre členov</h2>
                    <ul class="mb-0 text-secondary">
                        <li class="mb-2">novinky a informácie pre komunitu,</li>
                        <li class="mb-2">praktické podklady a odporúčania,</li>
                        <li>informácie o členských podmienkach.</li>
                    </ul>
                    <p class="text-secondary mt-4">Hľadáte objednávky? Tie patria do samostatného zákazníckeho portálu.</p>
                    <a href="{{ route('customer.orders', [], false) }}">Moje objednávky <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection