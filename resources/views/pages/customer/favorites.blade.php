@extends('layouts.app')

@section('title', 'Obľúbené produkty | WEB-Interia')

@section('content')
<section class="shop-catalog py-3">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Obľúbené produkty</h1>
            <a href="{{ route('eshop.catalog.index', [], false) }}">Späť do katalógu</a>
        </div>
        @if ($products->isEmpty())
            <p class="text-secondary">Zatiaľ nemáte žiadne obľúbené produkty.</p>
        @else
            @include('pages.catalog-results')
        @endif
    </div>
</section>
<script src="/js/product-name-wrap.js?v={{ filemtime(public_path('js/product-name-wrap.js')) }}" defer></script>
@endsection
