<span class="shop-cart-quantity-controls" data-cart-key="{{ $cartKey }}" data-unit="{{ $product->sales_unit }}">
    <button class="shop-cart-adjust" type="submit" formaction="{{ $adjustUrl }}" formnovalidate name="change" value="decrease" aria-label="Znížiť množstvo {{ $product->name }} v košíku {{ $cartName }}" title="Znížiť množstvo" @disabled($quantity === 0)>−</button>
    <span class="shop-cart-quantity" aria-label="V košíku: {{ $quantity }} {{ $product->sales_unit }}">{{ $quantity }}</span>
    <button class="shop-cart-adjust" type="submit" formaction="{{ $adjustUrl }}" formnovalidate name="change" value="increase" aria-label="Zvýšiť množstvo {{ $product->name }} v košíku {{ $cartName }}" title="Zvýšiť množstvo" @disabled($quantity > 100000 - $product->order_increment)>+</button>
</span>
