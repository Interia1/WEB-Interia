<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShoppingController extends Controller
{
    public function favorites(Request $request): View
    {
        return view('pages.customer.favorites', [
            'products' => $request->user()->favoriteProducts()->orderBy('name')->paginate(30),
        ]);
    }

    public function favorite(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['favorite' => ['required', 'boolean']]);
        DB::transaction(function () use ($request, $product, $data) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            if ($data['favorite']) {
                $user->favoriteProducts()->syncWithoutDetaching([$product->id]);
            } else {
                $user->favoriteProducts()->detach($product->id);
            }
        });

        return back()->with('status', $data['favorite'] ? 'Produkt je medzi obľúbenými.' : 'Produkt bol odstránený z obľúbených.');
    }

    public function addToCart(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:'.$product->minimum_order_quantity, 'max:100000'],
            'cart_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $cartName = DB::transaction(function () use ($request, $product, $data) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $cartQuery = $user->shoppingCarts();
            $cart = isset($data['cart_id'])
                ? $cartQuery->whereKey($data['cart_id'])->firstOrFail()
                : $cartQuery->where('is_default', true)->firstOrFail();
            $existing = $cart->products()->whereKey($product->id)->first();
            $quantity = ($existing?->pivot->quantity ?? 0) + (int) $data['quantity'];
            if ($quantity > 100000) {
                throw ValidationException::withMessages(['quantity' => 'V košíku môže byť najviac 100 000 kusov jedného produktu.']);
            }
            $cart->products()->syncWithoutDetaching([$product->id => ['quantity' => $quantity]]);

            return $cart->name;
        });

        return back()->with('status', 'Produkt bol pridaný do košíka „'.$cartName.'“.');
    }

    public function removeFromCart(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['cart_id' => ['nullable', 'integer', 'min:1']]);
        DB::transaction(function () use ($request, $product, $data) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $cartQuery = $user->shoppingCarts();
            $cart = isset($data['cart_id'])
                ? $cartQuery->whereKey($data['cart_id'])->firstOrFail()
                : $cartQuery->where('is_default', true)->firstOrFail();
            $cart->products()->detach($product->id);
        });

        return back()->with('status', 'Produkt bol odstránený z košíka.');
    }
}
