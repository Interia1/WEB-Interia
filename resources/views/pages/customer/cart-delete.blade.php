@extends('layouts.app')

@section('title', 'Odstránenie košíka | WEB-Interia')

@section('content')
<section class="container py-4">
    <h1 class="h4">Odstrániť košík?</h1>
    <p>Košík „{{ $cart->name }}“ a jeho {{ $cart->products_count }} produktov budú odstránené. Túto akciu nemožno vrátiť späť.</p>
    <form method="post" action="{{ route('eshop.carts.destroy', $cart, false) }}">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">Potvrdiť odstránenie</button>
        <a class="btn btn-outline-secondary" href="{{ route('eshop.carts.index', [], false) }}">Zrušiť</a>
    </form>
</section>
@endsection
