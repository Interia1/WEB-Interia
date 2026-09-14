<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeShortcutController extends Controller
{
    private const ALLOWED_SHORTCUTS = [
        'eshop' => ['catalog', 'catalogs', 'orders', 'faq', 'contact'],
        'semifinished' => ['catalog', 'catalogs', 'orders', 'faq', 'contact'],
        'custom' => ['presentations', 'customer-zone', 'orders', 'faq', 'contact'],
    ];

    public function update(Request $request): JsonResponse
    {
        $rules = ['shortcuts' => ['required', 'array:eshop,semifinished,custom']];

        foreach (self::ALLOWED_SHORTCUTS as $group => $allowed) {
            $rules["shortcuts.{$group}"] = ['required', 'array', 'min:1', 'max:5'];
            $rules["shortcuts.{$group}.*"] = ['string', 'distinct', Rule::in($allowed)];
        }

        $validated = $request->validate($rules);
        $request->user()->update(['home_shortcuts' => $validated['shortcuts']]);

        return response()->json(['shortcuts' => $validated['shortcuts']]);
    }
}
