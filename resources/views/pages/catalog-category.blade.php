@extends('layouts.app')

@section('title', $activeCategoryLabel . ' | Katalóg WEB-Interia')
@section('description', 'Produkty v kategórii ' . $activeCategoryLabel . ' v testovacom katalógu WEB-Interia.')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <p class="text-secondary mb-1">Katalóg / kategória</p>
                <h1 class="display-6 mb-0">{{ $activeCategoryLabel }}</h1>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('eshop.catalog.index') }}">Späť na celý katalóg</a>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4" aria-label="Kategórie produktov">
            @foreach ($categories as $category)
                <a
                    class="btn btn-sm {{ $category['slug'] === $activeCategory ? 'btn-primary' : 'btn-outline-primary' }}"
                    href="{{ route('eshop.catalog.category', ['category' => $category['slug']]) }}"
                >
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