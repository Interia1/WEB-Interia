<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class CartQuantity
{
    public static function adjust(Product $product, int $current, bool $increase): int
    {
        if ($increase) {
            $quantity = max((intdiv($current, $product->order_increment) + 1) * $product->order_increment, $product->minimum_order_quantity);
            if ($quantity > 100000) {
                throw ValidationException::withMessages(['quantity' => 'V košíku môže byť najviac 100 000 jednotiek jedného produktu.']);
            }

            return $quantity;
        }

        if ($current === 0) {
            throw ValidationException::withMessages(['quantity' => 'Produkt sa v košíku nenachádza.']);
        }

        if ($current <= $product->minimum_order_quantity) {
            return 0;
        }

        return max(intdiv($current - 1, $product->order_increment) * $product->order_increment, $product->minimum_order_quantity);
    }
}
