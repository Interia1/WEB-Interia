@php
    $cartCount = $headerCartProducts->sum(fn ($item) => $headerCartQuantities[$item->id]);
    $cartTotals = $headerCartProducts->groupBy('currency')->map(fn ($items, $currency) => number_format($items->sum(fn ($item) => (int) round((float) $item->price * 100) * $headerCartQuantities[$item->id]) / 100, 2, ',', ' ').' '.($currency === 'EUR' ? '€' : $currency))->implode(' + ') ?: '0,00 €';
    $cartLabel = 'Košík: '.$cartTotals.', '.$cartCount.' položiek';
@endphp
<li class="nav-item dropdown cart-menu">
    <button class="nav-link cart-menu-toggle" id="headerCartToggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" data-bs-auto-close="outside" aria-expanded="false" aria-controls="headerCart" aria-label="{{ $cartLabel }}" title="{{ $cartLabel }}">
        <i class="bi bi-cart3" aria-hidden="true"></i>
        <span class="cart-summary">
            <span>Košík</span>
            <span class="cart-summary-values">
                <span class="text-nowrap" aria-hidden="true">{{ $cartTotals }} /</span>
                <span class="cart-count" aria-hidden="true">{{ $cartCount }}</span>
            </span>
        </span>
        <i class="bi bi-chevron-down header-menu-chevron" aria-hidden="true"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end cart-menu-dropdown" id="headerCart" aria-labelledby="headerCartToggle">
        <h2 class="h6 mb-3">Váš košík</h2>
        <div class="alert alert-danger" data-header-cart-error role="alert" hidden></div>
        @auth
            <div class="cart-menu-carts">
                <span class="small text-secondary">Košíky (kliknutím zmeníte predvolený)</span>
                @foreach ($headerCarts as $cart)
                    @if ($cart->is_default)
                        <strong class="cart-menu-cart-name">{{ $cart->name }} · predvolený</strong>
                    @else
                        <form method="post" action="{{ route('eshop.carts.default', $cart, false) }}" data-header-cart-action="default">
                            @csrf
                            <button type="submit" class="cart-menu-select">{{ $cart->name }}</button>
                        </form>
                    @endif
                @endforeach
                <a href="{{ route('eshop.carts.index', [], false) }}">Spravovať košíky</a>
            </div>
        @endauth
        <h3 class="cart-menu-items-title">Obsah košíka</h3>
        @if ($headerCartProducts->isNotEmpty())
            <div class="cart-menu-items">
                @foreach ($headerCartProducts as $cartProduct)
                    <div class="cart-menu-item">
                        <div><a href="{{ route('eshop.product.show', $cartProduct, false) }}">{{ $cartProduct->name }}</a><div class="cart-menu-item-meta">{{ $headerCartQuantities[$cartProduct->id] }} {{ $cartProduct->sales_unit }} × {{ number_format((float) $cartProduct->price, 2, ',', ' ') }} {{ $cartProduct->currency }}</div></div>
                        <form method="post" action="{{ route('eshop.cart.remove', $cartProduct, false) }}" data-header-cart-action="remove">
                            @csrf
                            @method('DELETE')
                            @auth
                                <input type="hidden" name="cart_id" value="{{ $headerDefaultCart->id }}">
                            @endauth
                            <button class="btn btn-link btn-sm" type="submit" aria-label="Odstrániť z košíka: {{ $cartProduct->name }}" title="Odstrániť z košíka"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-secondary small mb-3">Košík je prázdny.</p>
        @endif
        @if ($headerCartProducts->isNotEmpty())
            @guest
                <p class="small text-secondary">Košík sa uchováva len v tejto relácii a po prihlásení sa neprenesie do účtu.</p>
            @endguest
            <p class="small text-secondary">Košík slúži na uloženie výberu; dokončenie objednávky zatiaľ nie je dostupné.</p>
        @endif
        <a href="{{ route('eshop.catalog.index', [], false) }}">Prejsť do katalógu <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
</li>
