<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCart;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShoppingCartController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.customer.carts', [
            'carts' => $request->user()->shoppingCarts()->withCount('products')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $data = $this->validateName($request);
            $user->shoppingCarts()->create(['name' => $data['name'], 'is_default' => false]);
        });

        return back()->with('status', 'Košík bol vytvorený.');
    }

    public function rename(Request $request, int $cart): RedirectResponse
    {
        DB::transaction(function () use ($request, $cart) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $owned = $user->shoppingCarts()->whereKey($cart)->firstOrFail();
            $data = $this->validateName($request, $owned);
            $owned->update(['name' => $data['name']]);
        });

        return back()->with('status', 'Košík bol premenovaný.');
    }

    public function setDefault(Request $request, int $cart): RedirectResponse
    {
        DB::transaction(function () use ($request, $cart) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $selected = $user->shoppingCarts()->whereKey($cart)->firstOrFail();
            $user->shoppingCarts()->where('is_default', true)->update(['is_default' => false]);
            $user->shoppingCarts()->whereKey($selected->id)->update(['is_default' => true]);
        });

        return back()->with('status', 'Predvolený košík bol zmenený.');
    }

    public function confirmDelete(Request $request, int $cart): View
    {
        return view('pages.customer.cart-delete', [
            'cart' => $request->user()->shoppingCarts()->withCount('products')->whereKey($cart)->firstOrFail(),
        ]);
    }

    public function destroy(Request $request, int $cart): RedirectResponse
    {
        $createdDefault = DB::transaction(function () use ($request, $cart) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $selected = $user->shoppingCarts()->whereKey($cart)->firstOrFail();
            $wasDefault = $selected->is_default;
            $selected->delete();
            $remaining = $user->shoppingCarts()->first();
            if (! $remaining) {
                $user->shoppingCarts()->create(['name' => 'Môj košík', 'is_default' => true]);

                return true;
            }
            if ($wasDefault) {
                $remaining->update(['is_default' => true]);
            }

            return false;
        });

        return redirect()->route('eshop.carts.index')->with('status', $createdDefault
            ? 'Košík bol odstránený. Bol vytvorený nový prázdny predvolený košík.'
            : 'Košík bol odstránený.');
    }

    /** @return array{name: string} */
    private function validateName(Request $request, ?ShoppingCart $cart = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique('shopping_carts', 'name')->where('user_id', $request->user()->id)->ignore($cart),
            ],
        ]);
    }
}
