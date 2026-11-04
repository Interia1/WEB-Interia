@extends('layouts.app')

@section('title', 'Moje košíky | WEB-Interia')

@section('content')
<section class="container py-4">
    <h1 class="h4">Moje košíky</h1>
    <p class="text-secondary">Predvolený košík sa použije pri bežnom kliknutí na „Do košíka“.</p>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('eshop.carts.store', [], false) }}" class="cart-create-form">
        @csrf
        <label for="new-cart-name">Názov nového košíka</label>
        <input class="form-control" id="new-cart-name" name="name" maxlength="80" value="{{ old('name') }}" required>
        <button class="btn btn-primary" type="submit">Vytvoriť košík</button>
    </form>
    <ul class="cart-management-list">
        @foreach ($carts as $cart)
            <li class="cart-management-item">
                <div>
                    <strong>{{ $cart->name }}</strong>
                    <span class="small text-secondary">{{ $cart->products_count }} produktov{{ $cart->is_default ? ' · predvolený' : '' }}</span>
                </div>
                <form method="post" action="{{ route('eshop.carts.rename', $cart, false) }}" class="cart-rename-form">
                    @csrf
                    @method('PUT')
                    <label class="visually-hidden" for="cart-name-{{ $cart->id }}">Premenovať košík {{ $cart->name }}</label>
                    <input class="form-control" id="cart-name-{{ $cart->id }}" name="name" maxlength="80" value="{{ $cart->name }}" required>
                    <button class="btn btn-outline-secondary" type="submit">Premenovať</button>
                </form>
                @unless ($cart->is_default)
                    <form method="post" action="{{ route('eshop.carts.default', $cart, false) }}">
                        @csrf
                        <button class="btn btn-outline-primary" type="submit">Nastaviť ako predvolený</button>
                    </form>
                @endunless
                <a class="btn btn-outline-danger" href="{{ route('eshop.carts.confirm-delete', $cart, false) }}">Odstrániť</a>
            </li>
        @endforeach
    </ul>
    <a href="{{ route('eshop.catalog.index', [], false) }}">Späť do katalógu</a>
</section>
@endsection
