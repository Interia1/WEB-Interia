@extends('layouts.app')

@section('title', $product->name . ' | WEB-Interia')
@section('description', $product->short_description)

@section('content')
<section class="py-5">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('eshop.catalog.index') }}">Katalóg</a></li>
                <li class="breadcrumb-item"><a href="{{ route('eshop.catalog.category', ['category' => $product->category]) }}">{{ $product->category_label }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <h1 class="display-6 mb-3">{{ $product->name }}</h1>
                <p class="lead text-secondary">{{ $product->short_description }}</p>
                <p class="mb-4">{{ $product->description }}</p>
                <a class="btn btn-primary" href="{{ route('contact') }}">Mám záujem o tento produkt</a>
            </div>
            <div class="col-lg-5">
                <aside class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Základné parametre</h2>
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-secondary">Kategória</dt>
                            <dd class="col-7">{{ $product->category_label }}</dd>

                            <dt class="col-5 text-secondary">Dostupnosť</dt>
                            <dd class="col-7">{{ $product->availability }}</dd>

                            <dt class="col-5 text-secondary">Cena</dt>
                            <dd class="col-7 fw-semibold">{{ number_format((float) $product->price, 2, ',', ' ') }} {{ $product->currency }}</dd>
                        </dl>
                    </div>
                </aside>
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <div class="mt-5">
                <h2 class="h4 mb-3">Súvisiace produkty</h2>
                <div class="row g-3">
                    @foreach ($relatedProducts as $related)
                        <div class="col-12 col-md-6 col-xl-4">
                            <article class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <h3 class="h6">{{ $related->name }}</h3>
                                    <p class="small text-secondary flex-grow-1">{{ $related->short_description }}</p>
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('eshop.product.show', ['product' => $related->slug]) }}">Pozrieť detail</a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection