@if ($products->isNotEmpty())
    <div class="shop-product-head" aria-hidden="true">
        <span>Produkt</span><span>Kategória</span><span>Dostupnosť</span><span>Cena</span>
    </div>
    <ul class="shop-product-list" aria-label="Produkty">
        @foreach ($products as $product)
            @php
                $returnTo = route('eshop.catalog.index', request()->query(), false).'#catalog-product-'.$product->id;
                $productUrl = route('eshop.product.show', ['product' => $product->slug, 'return_to' => $returnTo], false);
            @endphp
            <li class="shop-product-row" id="catalog-product-{{ $product->id }}">
                <div class="shop-product-image">
                    @if ($product->image_path)
                        <img src="{{ asset($product->image_path) }}" alt="" width="48" height="48" loading="lazy">
                    @else
                        <i class="bi bi-box-seam" aria-hidden="true"></i>
                    @endif
                </div>
                <h2 class="shop-product-name"><a data-product-link href="{{ $productUrl }}">{{ $product->name }}</a></h2>
                <span class="shop-product-category">{{ $product->category_label }}</span>
                <span class="shop-product-availability {{ $product->availability === 'Skladom' ? 'shop-in-stock' : '' }}">{{ $product->availability }}</span>
                <strong class="shop-product-price">{{ number_format((float) $product->price, 2, ',', ' ') }} {{ $product->currency }}</strong>
                <a class="shop-product-detail shop-icon-button" data-product-link href="{{ $productUrl }}" aria-label="Detail: {{ $product->name }}" title="Zobraziť detail"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
            </li>
        @endforeach
    </ul>
@else
    <div class="shop-catalog-empty">
        <i class="bi bi-search" aria-hidden="true"></i>
        <div>
            <h2 class="h6 mb-1">Nenašli sa žiadne produkty</h2>
            <a data-catalog-link href="{{ route('eshop.catalog.index', ['category' => $activeCategory ?? ''], false) }}">Zrušiť filtre</a>
        </div>
    </div>
@endif
@if ($products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    <nav class="shop-pagination" aria-label="Stránky produktov">
        <span class="small text-secondary">{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} z {{ $products->total() }}</span>
        @if ($products->previousPageUrl())
            <a class="btn btn-outline-secondary shop-icon-button" data-catalog-link href="{{ $products->previousPageUrl() }}" aria-label="Predchádzajúca stránka" title="Predchádzajúca stránka"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
        @endif
        <span class="small">{{ $products->currentPage() }} / {{ $products->lastPage() }}</span>
        @if ($products->nextPageUrl())
            <a class="btn btn-outline-secondary shop-icon-button" data-catalog-link href="{{ $products->nextPageUrl() }}" aria-label="Nasledujúca stránka" title="Nasledujúca stránka"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
        @endif
    </nav>
@endif