@if ($products->isNotEmpty())
    <div class="shop-product-table">
    <div class="shop-product-head" aria-hidden="true">
        <span></span><span>Produkt</span><span></span><span>Dostupnosť</span><span>Cena</span><span>Kategória</span>
    </div>
    <ul class="shop-product-list" aria-label="Produkty">
        @foreach ($products as $product)
            @php
                $returnTo = route('eshop.catalog.index', request()->query(), false).'#catalog-product-'.$product->id;
                $productUrl = route('eshop.product.show', ['product' => $product->slug, 'return_to' => $returnTo], false);
                $productNameLines = \App\Support\ProductNameLines::split($product->name);
                $surcharge = $product->parameterValues->first(fn ($value) => $value->parameter?->name === 'S príplatkom');
                $surchargeWithoutVat = $product->parameterValues->first(fn ($value) => $value->parameter?->name === 'Príplatok bez DPH');
                $surchargeWithVat = $product->parameterValues->first(fn ($value) => $value->parameter?->name === 'Príplatok s DPH');
                $surchargeAmounts = [];
                if ($surchargeWithoutVat) {
                    $surchargeAmounts[] = number_format((float) $surchargeWithoutVat->value, 2, ',', ' ').' '.($surchargeWithoutVat->parameter->unit ?: $product->currency).' bez DPH';
                }
                if ($surchargeWithVat) {
                    $surchargeAmounts[] = number_format((float) $surchargeWithVat->value, 2, ',', ' ').' '.($surchargeWithVat->parameter->unit ?: $product->currency).' s DPH';
                }
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
                        @if ($surcharge || $surchargeAmounts)
                            <strong class="shop-product-surcharge">
                                @if ($surcharge)<span>S príplatkom: {{ $surcharge->value }}</span>@endif
                                @if ($surchargeAmounts)<span>{{ implode(' / ', $surchargeAmounts) }}</span>@endif
                            </strong>
                        @endif
                        <div class="shop-product-controls">
                                <form class="shop-product-cart-form" method="post" action="{{ route('eshop.cart.add', $product, false) }}">
                                    @csrf
                                    <input name="quantity" type="hidden" value="{{ $product->minimum_order_quantity }}">
                                    <div class="shop-cart-picker" data-cart-picker>
                                        @php $defaultQuantity = $defaultCartQuantities[$product->id] ?? 0; @endphp
                                        <div class="shop-product-notice" data-cart-notice role="status" aria-live="polite" @if (! (request()->routeIs('eshop.catalog.index', 'eshop.favorites') && session('status_product_id') == $product->id && session('status'))) hidden @endif>{{ request()->routeIs('eshop.catalog.index', 'eshop.favorites') && session('status_product_id') == $product->id ? session('status') : '' }}</div>
                                        <div class="shop-cart-primary-group">
                                            <div class="btn btn-primary shop-cart-primary-actions">
                                                @include('pages.cart-quantity-controls', ['cartName' => $defaultCartName, 'cartKey' => 'default', 'quantity' => $defaultQuantity, 'adjustUrl' => route('eshop.cart.adjust', $product, false)])
                                                <button class="shop-cart-primary" type="submit" aria-label="Pridať do predvoleného košíka {{ $defaultCartName }}: {{ $product->name }}. V košíku: {{ $defaultQuantity }} {{ $product->sales_unit }}. Pridať {{ $product->minimum_order_quantity }} {{ $product->sales_unit }}." title="Pridať {{ $product->minimum_order_quantity }} {{ $product->sales_unit }} do košíka {{ $defaultCartName }}"><span class="shop-cart-primary-name">{{ $buttonCartName }}</span></button>
                                            </div>
                                        </div>
                                        @auth
                                        <details class="shop-cart-choices" data-cart-choices>
                                            <summary class="btn btn-primary shop-cart-expand" aria-label="Vybrať košík pre produkt {{ $product->name }}"><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                            <div class="shop-cart-options">
                                                <span class="shop-cart-options-title">Pridať do košíka:</span>
                                                @foreach ($shoppingCarts->where('is_default', false) as $cart)
                                                    @php $cartQuantity = $cartProductQuantities[$cart->id][$product->id] ?? 0; @endphp
                                                    <div class="shop-cart-option">
                                                        @include('pages.cart-quantity-controls', ['cartName' => $cart->name, 'cartKey' => $cart->id, 'quantity' => $cartQuantity, 'adjustUrl' => route('eshop.cart.adjust', ['product' => $product, 'cart_id' => $cart->id], false)])
                                                        <button type="submit" name="cart_id" value="{{ $cart->id }}" class="shop-cart-option-add" aria-label="Pridať {{ $product->name }} do košíka {{ $cart->name }}. V košíku: {{ $cartQuantity }} {{ $product->sales_unit }}. Pridať {{ $product->minimum_order_quantity }} {{ $product->sales_unit }}." title="Pridať {{ $product->minimum_order_quantity }} {{ $product->sales_unit }} do košíka {{ $cart->name }}"><span class="shop-cart-option-name"><span>{{ $cart->name }}</span><span class="shop-cart-option-width" aria-hidden="true">{{ $cart->name }}</span></span></button>
                                                    </div>
                                                @endforeach
                                                <a href="{{ route('eshop.carts.index', [], false) }}">Spravovať košíky</a>
                                            </div>
                                        </details>
                                        @endauth
                                    </div>
                                </form>
                        </div>
                    </div>
                    <span class="shop-product-availability {{ $product->availability === 'Skladom' ? 'shop-in-stock' : '' }}">{{ $product->availability }}</span>
                    <div class="shop-product-pricing">
                        <strong class="shop-product-price">{{ number_format((float) $product->price, 2, ',', ' ') }} {{ $product->currency }}</strong>
                        <span class="shop-product-meta">Partnerská cena: nedostupná</span>
                    </div>
                    <span class="shop-product-category">{{ $product->category_label }}</span>
                </div>
                <a class="shop-product-detail shop-icon-button" data-product-link href="{{ $productUrl }}" aria-label="Detail: {{ $product->name }}" title="Zobraziť detail"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
            </li>
        @endforeach
    </ul>
    </div>
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