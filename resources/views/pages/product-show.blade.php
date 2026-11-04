@extends('layouts.app')

@section('title', $product->name . ' | WEB-Interia')
@section('description', $product->short_description)

@section('content')
@php
    $returnTo = request()->query('return_to');
    $catalogPath = route('eshop.catalog.index', [], false);
    $returnParts = is_string($returnTo) ? parse_url($returnTo) : false;
    $hasResults = $returnParts !== false
        && ($returnParts['path'] ?? '') === $catalogPath
        && !isset($returnParts['scheme']) && !isset($returnParts['host'])
        && str_starts_with($returnTo, $catalogPath)
        && !preg_match('/[\\\\\x00-\x20\x7f]/', $returnTo);
    $backUrl = $hasResults ? $returnTo : route('eshop.catalog.index', ['category' => $product->category], false);
@endphp
<section class="pt-1 pb-3">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center shop-product-navigation">
            <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center" data-product-back href="{{ $backUrl }}"><i class="bi bi-arrow-90deg-left shop-product-back-icon" aria-hidden="true"></i>{{ $hasResults ? 'spat na vysledky' : 'Späť do kategórie' }}</a>
            <nav aria-label="Navigácia produktu" class="small shop-product-breadcrumb">
                <ol class="breadcrumb mb-0 text-break">
                    <li class="breadcrumb-item"><a href="{{ route('eshop.catalog.index', [], false) }}">E-shop</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('eshop.catalog.index', ['category' => $product->category]) }}">{{ $product->category_label }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>

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
                            <dt class="col-5 text-secondary">Kód tovaru</dt>
                            <dd class="col-7">{{ $product->sku ?: 'Neuvedený' }}</dd>

                            <dt class="col-5 text-secondary">Minimálne množstvo</dt>
                            <dd class="col-7">{{ $product->minimum_order_quantity }} ks</dd>

                            <dt class="col-5 text-secondary">Kategória</dt>
                            <dd class="col-7">{{ $product->category_label }}</dd>

                            <dt class="col-5 text-secondary">Dostupnosť</dt>
                            <dd class="col-7">{{ $product->availability }}</dd>

                            <dt class="col-5 text-secondary">Cena</dt>
                            <dd class="col-7 fw-semibold">{{ number_format((float) $product->price, 2, ',', ' ') }} {{ $product->currency }}</dd>
                            <dt class="col-5 text-secondary">Partnerská cena</dt>
                            <dd class="col-7">Nedostupná</dd>
                            @foreach ($product->parameterValues()->with('parameter')->get()->sortBy('parameter.position') as $value)
                                <dt class="col-5 text-secondary">{{ $value->parameter->name }}</dt>
                                <dd class="col-7">{{ $value->parameter->type === 'boolean' ? ($value->value === '1' ? 'Áno' : 'Nie') : $value->value }}{{ $value->parameter->unit ? ' '.$value->parameter->unit : '' }}</dd>
                            @endforeach
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
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('eshop.product.show', ['product' => $related->slug, 'return_to' => $hasResults ? $returnTo : null], false) }}">Pozrieť detail</a>
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