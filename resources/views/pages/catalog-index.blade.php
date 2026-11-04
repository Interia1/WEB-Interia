@extends('layouts.app')

@section('title', 'Katalóg | WEB-Interia')
@section('description', 'Prehľad testovacích produktov podľa kategórií vrátane detailov, dostupnosti a ceny.')

@section('content')
<section class="shop-catalog py-2" data-live-catalog>
    <div class="container">
        <form method="get" action="{{ route('eshop.catalog.index', [], false) }}" class="shop-catalog-filters" data-catalog-form>
            <div class="shop-catalog-heading">
                <h1><a class="shop-catalog-home" href="{{ route('eshop.catalog.index', [], false) }}" aria-label="E-shop: späť na začiatok" title="Späť na začiatok e-shopu">E-shop</a></h1>
                <button class="shop-assortment-trigger" type="button" data-assortment-trigger aria-expanded="false" aria-controls="catalog-assortment" aria-haspopup="dialog"><i class="bi bi-grid" aria-hidden="true"></i><span>Sortiment</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
                <a class="shop-favorites-label" href="{{ auth()->check() ? route('eshop.favorites', [], false) : route('login', [], false) }}" title="{{ auth()->check() ? 'Obľúbené produkty' : 'Pre obľúbené produkty sa prihláste' }}"><i class="bi bi-star" aria-hidden="true"></i><span>Obľúbené</span><i class="bi bi-chevron-down" aria-hidden="true"></i></a>
                <details class="shop-filter-path" data-catalog-path>
                    <summary><i class="bi bi-funnel" aria-hidden="true"></i> Filtre</summary>
                    <div id="catalog-active-filters" class="shop-active-filters" data-catalog-active>@include('pages.catalog-active-filters')</div>
                </details>
                <span class="shop-catalog-total" data-catalog-total>{{ $products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $products->total() : $products->count() }} položiek</span>
            </div>
            <div class="shop-search">
                <label for="catalog-search" class="visually-hidden">Vyhľadávanie</label>
                <input id="catalog-search" type="search" name="q" class="form-control" value="{{ $activeSearch ?? '' }}" placeholder="Názov, kategória alebo popis">
            </div>
            <input id="catalog-category" type="hidden" name="category" value="{{ $activeCategory ?? '' }}">
            <div>
                <label for="catalog-sort" class="visually-hidden">Zoradiť</label>
                <select id="catalog-sort" name="sort" class="form-select">
                    @foreach (['recommended' => 'Odporúčané', 'name' => 'Názov A–Z', 'price_asc' => 'Cena od najnižšej', 'price_desc' => 'Cena od najvyššej'] as $sort => $label)
                        <option value="{{ $sort }}" @selected(($activeSort ?? 'recommended') === $sort)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="shop-filter-actions">
                <button type="submit" class="btn btn-primary shop-icon-button" aria-label="Použiť filtre" title="Použiť filtre"><i class="bi bi-search" aria-hidden="true"></i></button>
                <a href="{{ route('eshop.catalog.index', [], false) }}" class="btn btn-outline-secondary shop-icon-button" aria-label="Obnoviť e-shop" title="Späť na začiatok e-shopu"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></a>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary shop-icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Ďalšie možnosti katalógu" title="Ďalšie možnosti katalógu">
                        <i class="bi bi-three-dots" aria-hidden="true"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><label class="dropdown-item d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input m-0" data-catalog-path-toggle aria-controls="catalog-active-filters" checked> Zobraziť cestu filtrov</label></li>
                        <li><a class="dropdown-item" href="{{ route('eshop.catalog.print') }}"><i class="bi bi-printer me-2" aria-hidden="true"></i>Tlačová verzia</a></li>
                        <li><a class="dropdown-item" href="{{ route('catalogs.overview') }}"><i class="bi bi-journals me-2" aria-hidden="true"></i>Všetky katalógy</a></li>
                    </ul>
                </div>
            </div>
            <div class="shop-category-bar" data-category-bar>
                <h2 id="catalog-categories-title" class="shop-category-title">Kategórie</h2>
                <div class="shop-category-scroller" data-category-scroller>
                    <div class="shop-category-scroll shop-category-scroll-start">
                        <button class="shop-category-scroll-main" type="button" data-category-scroll-start aria-label="Predchádzajúce kategórie" title="Predchádzajúce kategórie"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                        <button class="shop-category-scroll-page" type="button" data-category-page-start aria-label="Predchádzajúca strana kategórií" title="Predchádzajúca strana kategórií"><i class="bi bi-caret-left-fill" aria-hidden="true"></i><i class="bi bi-caret-left-fill" aria-hidden="true"></i><i class="bi bi-caret-left-fill" aria-hidden="true"></i></button>
                    </div>
                    <nav class="shop-category-tabs" aria-labelledby="catalog-categories-title">
                        <a class="shop-category-button" data-catalog-link data-catalog-category="" draggable="false" href="{{ route('eshop.catalog.index', ['q' => $activeSearch ?? '', 'sort' => $activeSort ?? 'recommended'], false) }}" @if(!($activeCategory ?? '')) aria-current="true" @endif>Všetky</a>
                        @foreach ($categories as $category)
                            <a class="shop-category-button" data-catalog-link data-catalog-category="{{ $category['slug'] }}" draggable="false" href="{{ route('eshop.catalog.index', ['category' => $category['slug'], 'q' => $activeSearch ?? '', 'sort' => $activeSort ?? 'recommended'], false) }}" aria-controls="catalog-category-filters" aria-expanded="{{ ($activeCategory ?? '') === $category['slug'] ? 'true' : 'false' }}" @if(($activeCategory ?? '') === $category['slug']) aria-current="true" @endif>
                                <span>{{ $category['label'] }}</span><i class="bi bi-chevron-down" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </nav>
                    <div class="shop-category-scroll shop-category-scroll-end">
                        <button class="shop-category-scroll-main" type="button" data-category-scroll-end aria-label="Nasledujúce kategórie" title="Nasledujúce kategórie"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                        <button class="shop-category-scroll-page" type="button" data-category-page-end aria-label="Nasledujúca strana kategórií" title="Nasledujúca strana kategórií"><i class="bi bi-caret-right-fill" aria-hidden="true"></i><i class="bi bi-caret-right-fill" aria-hidden="true"></i><i class="bi bi-caret-right-fill" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div class="shop-category-connector" data-category-connector aria-hidden="true" hidden><span class="shop-connector-start"></span><span class="shop-connector-bridge"></span><span class="shop-connector-end"></span></div>
                <div id="catalog-category-filters" class="shop-category-dropdown" data-catalog-facets @if(!($activeCategory ?? '')) hidden @endif>@include('pages.catalog-filters')</div>
            </div>
        </form>

        @include('pages.catalog-assortment')

        <div class="visually-hidden" data-catalog-status role="status" aria-live="polite"></div>
        <div class="alert alert-danger" data-catalog-error role="alert" hidden></div>
        <div data-catalog-results aria-busy="false">@include('pages.catalog-results')</div>
    </div>
</section>
<script src="/js/catalog-filters.js?v={{ filemtime(public_path('js/catalog-filters.js')) }}" defer></script>
<script src="/js/product-name-wrap.js?v={{ filemtime(public_path('js/product-name-wrap.js')) }}" defer></script>
<script src="/js/catalog-return.js?v={{ filemtime(public_path('js/catalog-return.js')) }}" defer></script>
<script src="/js/catalog-assortment.js?v={{ filemtime(public_path('js/catalog-assortment.js')) }}" defer></script>
@endsection