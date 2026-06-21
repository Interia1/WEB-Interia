@extends('layouts.app')

@section('title', 'Katalóg | WEB-Interia')
@section('description', 'Prehľad testovacích produktov podľa kategórií vrátane detailov, dostupnosti a ceny.')

@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="display-6 mb-3">Katalóg produktov</h1>
        <p class="lead text-secondary">Vyberte si kategóriu a pozrite si dostupné produkty vrátane základných parametrov a orientačných cien.</p>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a class="btn btn-outline-primary" href="{{ route('eshop.catalog.print') }}">Tlačová verzia katalógu</a>
            <a class="btn btn-outline-secondary" href="{{ route('catalogs.overview') }}">Všetky katalógy</a>
        </div>

        <form method="get" action="{{ route('eshop.catalog.index') }}" class="row g-2 align-items-end mb-4">
            <div class="col-md-8 col-lg-6">
                <label for="catalog-search" class="form-label">Vyhľadávanie</label>
                <input id="catalog-search" type="search" name="q" class="form-control" value="{{ $activeSearch ?? '' }}" placeholder="Názov, kategória alebo popis">
            </div>
            <div class="col-md-4 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Hľadať</button>
                <a href="{{ route('eshop.catalog.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="d-flex flex-wrap gap-2 mb-4" aria-label="Kategórie produktov">
            @foreach ($categories as $category)
                <a class="btn btn-outline-primary btn-sm" href="{{ route('eshop.catalog.category', ['category' => $category['slug']]) }}">
                    {{ $category['label'] }}
                </a>
            @endforeach
        </div>

        <div class="row g-4">
            @foreach ($products as $product)
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="badge text-bg-light border">{{ $product->category_label }}</span>
                                <span class="badge text-bg-success">{{ $product->availability }}</span>
                            </div>
                            <h2 class="h5">{{ $product->name }}</h2>
                            <p class="text-secondary flex-grow-1">{{ $product->short_description }}</p>
                            <p class="h5 mb-3">{{ number_format((float) $product->price, 2, ',', ' ') }} {{ $product->currency }}</p>
                            <a class="btn btn-primary" href="{{ route('eshop.product.show', ['product' => $product->slug]) }}">Zobraziť detail</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection