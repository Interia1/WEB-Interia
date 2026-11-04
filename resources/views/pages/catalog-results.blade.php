@if ($products->isNotEmpty())
    <div class="shop-product-head" aria-hidden="true">
        <span></span><span>Produkt</span><span>Nákup</span><span>Kategória</span><span>Dostupnosť</span><span>Cena</span>
    </div>
    <ul class="shop-product-list" aria-label="Produkty">
        @foreach ($products as $product)
            @php
                $returnTo = route('eshop.catalog.index', request()->query(), false).'#catalog-product-'.$product->id;
                $productUrl = route('eshop.product.show', ['product' => $product->slug, 'return_to' => $returnTo], false);
                $productNameLines = \App\Support\ProductNameLines::split($product->name);
            @endphp
            <li class="shop-product-row" id="catalog-product-{{ $product->id }}">
                @auth
                    @php $isFavorite = in_array($product->id, $favoriteProductIds, true); @endphp
                    <form class="shop-product-favorite-form" method="post" action="{{ route('eshop.favorites.update', $product, false) }}">
                        @csrf
                        <input type="hidden" name="favorite" value="{{ $isFavorite ? 0 : 1 }}">
                        <button class="shop-product-favorite{{ $isFavorite ? ' is-favorite' : '' }}" type="submit" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}" aria-label="{{ $isFavorite ? 'Odstrániť z obľúbených' : 'Pridať do obľúbených' }}: {{ $product->name }}" title="{{ $isFavorite ? 'Odstrániť z obľúbených' : 'Pridať do obľúbených' }}"><i class="bi {{ $isFavorite ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i></button>
                    </form>
                @else
                    <a class="shop-product-favorite" href="{{ route('login', [], false) }}" aria-label="Prihláste sa pre pridanie do obľúbených: {{ $product->name }}" title="Prihláste sa pre obľúbené"><i class="bi bi-star" aria-hidden="true"></i></a>
                @endauth
                <div class="shop-product-image">
                    @if ($product->image_path)
                        <img src="{{ asset($product->image_path) }}" alt="" width="48" height="48" loading="lazy">
                    @else
                        <i class="bi bi-box-seam" aria-hidden="true"></i>
                    @endif
                </div>
                <div class="shop-product-info">
                    <h2 class="shop-product-name"><a data-product-link href="{{ $productUrl }}" title="{{ $product->name }}" aria-label="{{ $product->name }}">@foreach ($productNameLines as $line)@if (! $loop->first)<br aria-hidden="true">@endif{{ $line }}@endforeach</a></h2>
                    <span class="shop-product-meta shop-product-code"><span>Kód tovaru:</span> <span class="shop-product-code-value" title="{{ $product->sku ?: 'neuvedený' }}">{{ $product->sku ?: 'neuvedený' }}</span></span>
                </div>
                <div class="shop-product-details">
                    <div class="shop-product-actions">
                        <span class="shop-product-meta">Min. {{ $product->minimum_order_quantity }} ks</span>
                        <div class="shop-product-controls">
                            @auth
                                <form class="shop-product-cart-form" method="post" action="{{ route('eshop.cart.add', $product, false) }}">
                                    @csrf
                                    <label class="visually-hidden" for="quantity-{{ $product->id }}">Množstvo: {{ $product->name }}</label>
                                    <input id="quantity-{{ $product->id }}" name="quantity" type="number" min="{{ $product->minimum_order_quantity }}" max="100000" step="1" value="{{ $product->minimum_order_quantity }}" required>
                                    <div class="shop-cart-picker" data-cart-picker>
                                        <button class="btn btn-primary shop-cart-primary" type="submit" aria-label="Pridať do predvoleného košíka: {{ $product->name }}" title="Pridať do predvoleného košíka"><i class="bi bi-cart-plus" aria-hidden="true"></i><span>Do košíka</span></button>
                                        <details class="shop-cart-choices" data-cart-choices>
                                            <summary class="btn btn-primary shop-cart-expand" aria-label="Vybrať košík pre produkt {{ $product->name }}"><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                            <div class="shop-cart-options">
                                                <span class="shop-cart-options-title">Pridať do košíka:</span>
                                                @foreach ($shoppingCarts as $cart)
                                                    <button type="submit" name="cart_id" value="{{ $cart->id }}" class="shop-cart-option"><span>{{ $cart->name }}{{ $cart->is_default ? ' · predvolený' : '' }}</span><span class="shop-cart-option-width" aria-hidden="true">{{ $cart->name }}{{ $cart->is_default ? ' · predvolený' : '' }}</span></button>
                                                @endforeach
                                                <a href="{{ route('eshop.carts.index', [], false) }}">Spravovať košíky</a>
                                            </div>
                                        </details>
                                    </div>
                                </form>
                            @else
                                <a class="shop-product-login" href="{{ route('login', [], false) }}">Prihláste sa pre nákup a obľúbené</a>
                            @endauth
                        </div>
                    </div>
                    <span class="shop-product-category">{{ $product->category_label }}</span>
                    <span class="shop-product-availability {{ $product->availability === 'Skladom' ? 'shop-in-stock' : '' }}">{{ $product->availability }}</span>
                    <div class="shop-product-pricing">
                        <strong class="shop-product-price">{{ number_format((float) $product->price, 2, ',', ' ') }} {{ $product->currency }}</strong>
                        <span class="shop-product-meta">Partnerská cena: nedostupná</span>
                    </div>
                </div>
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