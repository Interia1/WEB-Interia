<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Session\Store;
use Illuminate\Validation\ValidationException;

class GuestCart
{
    private const SESSION_KEY = 'guest_cart';

    public function __construct(private Store $session) {}

    public function quantities(): array
    {
        return $this->session->get(self::SESSION_KEY, []);
    }

    public function products(): Collection
    {
        return Product::whereKey(array_keys($this->quantities()))->orderBy('name')->get();
    }

    public function add(Product $product, int $quantity): void
    {
        $quantities = $this->quantities();
        $quantity += $quantities[$product->id] ?? 0;
        if ($quantity % $product->order_increment !== 0) {
            throw ValidationException::withMessages(['quantity' => 'Výsledné množstvo nie je platnou dávkou produktu.']);
        }
        if ($quantity > 100000) {
            throw ValidationException::withMessages(['quantity' => 'V košíku môže byť najviac 100 000 jednotiek jedného produktu.']);
        }

        $quantities[$product->id] = $quantity;
        $this->session->put(self::SESSION_KEY, $quantities);
    }

    public function remove(Product $product): void
    {
        $quantities = $this->quantities();
        unset($quantities[$product->id]);
        $this->session->put(self::SESSION_KEY, $quantities);
    }

    public function adjust(Product $product, bool $increase): void
    {
        $quantities = $this->quantities();
        $quantity = CartQuantity::adjust($product, $quantities[$product->id] ?? 0, $increase);
        if ($quantity === 0) {
            unset($quantities[$product->id]);
        } else {
            $quantities[$product->id] = $quantity;
        }
        $this->session->put(self::SESSION_KEY, $quantities);
    }
}
