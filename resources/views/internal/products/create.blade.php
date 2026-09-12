@extends('layouts.app')

@section('title', 'Nový produkt | WEB-Interia')

@section('content')
<section class="internal-shell py-5"><div class="container">
    <h1 class="h2 mb-4">Nový produkt</h1>
    @include('internal._navigation')
    <form class="internal-panel" method="post" action="{{ route('internal.products.store') }}">
        @csrf
        @include('internal.products._form')
    </form>
</div></section>
@endsection