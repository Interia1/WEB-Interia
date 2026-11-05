<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class CartQuantity
{
    public static function adjust(Product $product, int $current, bool $increase): int
    {
        if ($increase) {
            if ($current >= 100000) {
                throw ValidationException::withMessages(['quantity' => 'V košíku môže byť najviac 100 000 kusov jedného produktu.']);
            }

            return max($current + 1, $product->minimum_order_quantity);
        }

        if ($current === 0) {
            throw ValidationException::withMessages(['quantity' => 'Produkt sa v košíku nenachádza.']);
        }

        return $current <= $product->minimum_order_quantity ? 0 : $current - 1;
    }
}
