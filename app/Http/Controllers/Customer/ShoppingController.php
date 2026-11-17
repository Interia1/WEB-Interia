<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Support\CartQuantity;
use App\Support\GuestCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

        return back()->with('status', $data['favorite'] ? 'Produkt je medzi obľúbenými.' : 'Produkt bol odstránený z obľúbených.')
            ->with('status_product_id', $product->id);
    }

    public function addToCart(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:'.$product->minimum_order_quantity, 'max:100000'],
            'cart_id' => ['nullable', 'integer', 'min:1'],
        ]);
        if ((int) $data['quantity'] % $product->order_increment !== 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Množstvo musí byť násobkom dávky '.$product->order_increment.' '.$product->sales_unit.'.',
            ]);
        }
        if (! $request->user()) {
            abort_if(isset($data['cart_id']), 404);
            (new GuestCart($request->session()))->add($product, (int) $data['quantity']);

            return $this->cartResponse($request, $product, 'Produkt bol pridaný do košíka.');
        }

        $cartName = DB::transaction(function () use ($request, $product, $data) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $cartQuery = $user->shoppingCarts();
            $cart = isset($data['cart_id'])
                ? $cartQuery->whereKey($data['cart_id'])->firstOrFail()
                : $cartQuery->where('is_default', true)->firstOrFail();
            $existing = $cart->products()->whereKey($product->id)->first();
            $quantity = ($existing?->pivot->quantity ?? 0) + (int) $data['quantity'];
            if ($quantity % $product->order_increment !== 0) {
                throw ValidationException::withMessages(['quantity' => 'Výsledné množstvo nie je platnou dávkou produktu.']);
            }
            if ($quantity > 100000) {
                throw ValidationException::withMessages(['quantity' => 'V košíku môže byť najviac 100 000 jednotiek jedného produktu.']);
            }
            $cart->products()->syncWithoutDetaching([$product->id => ['quantity' => $quantity]]);

            return $cart->name;
        });

        return $this->cartResponse($request, $product, 'Produkt bol pridaný do košíka „'.$cartName.'“.');
    }

    public function removeFromCart(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['cart_id' => ['nullable', 'integer', 'min:1']]);
        if (! $request->user()) {
            abort_if(isset($data['cart_id']), 404);
            (new GuestCart($request->session()))->remove($product);

            return $request->expectsJson()
                ? $this->cartResponse($request, $product, 'Produkt bol odstránený z košíka.')
                : back()->with('status', 'Produkt bol odstránený z košíka.');
        }

        DB::transaction(function () use ($request, $product, $data) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $cartQuery = $user->shoppingCarts();
            $cart = isset($data['cart_id'])
                ? $cartQuery->whereKey($data['cart_id'])->firstOrFail()
                : $cartQuery->where('is_default', true)->firstOrFail();
            $cart->products()->detach($product->id);
        });

        return $request->expectsJson()
            ? $this->cartResponse($request, $product, 'Produkt bol odstránený z košíka.')
            : back()->with('status', 'Produkt bol odstránený z košíka.');
    }

    public function adjustCartQuantity(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'change' => ['required', Rule::in(['increase', 'decrease'])],
            'cart_id' => ['nullable', 'integer', 'min:1'],
        ]);
        if (! $request->user()) {
            abort_if(isset($data['cart_id']), 404);
            (new GuestCart($request->session()))->adjust($product, $data['change'] === 'increase');
        } else {
            DB::transaction(function () use ($request, $product, $data) {
                $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $cartQuery = $user->shoppingCarts();
                $cart = isset($data['cart_id'])
                    ? $cartQuery->whereKey($data['cart_id'])->firstOrFail()
                    : $cartQuery->where('is_default', true)->firstOrFail();
                $existing = $cart->products()->whereKey($product->id)->first();
                $quantity = CartQuantity::adjust($product, $existing?->pivot->quantity ?? 0, $data['change'] === 'increase');
                if ($quantity === 0) {
                    $cart->products()->detach($product->id);
                } else {
                    $cart->products()->syncWithoutDetaching([$product->id => ['quantity' => $quantity]]);
                }
            });
        }

        return $this->cartResponse($request, $product, 'Množstvo v košíku bolo upravené.');
    }

    private function cartResponse(Request $request, Product $product, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'product_id' => $product->id,
                'message' => $message,
                'results' => view('pages.catalog-results', ['products' => collect([$product])])->render(),
                'header' => view('partials.header-cart')->render(),
            ])->header('Cache-Control', 'no-store, private');
        }

        return back()->with('status', $message)->with('status_product_id', $product->id);
    }
}
