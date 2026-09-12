<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('internal.products.index', [
            'products' => Product::query()->orderBy('category_label')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('internal.products.create', ['product' => new Product]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = Product::create($this->validated($request));

        return redirect()->route('internal.products.edit', $product)
            ->with('status', 'Produkt bol vytvorený.');
    }

    public function edit(Product $product): View
    {
        return view('internal.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product));

        return back()->with('status', 'Produkt bol uložený.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('internal.products.index')->with('status', 'Produkt bol odstránený.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('products')->ignore($product)],
            'category' => ['required', 'string', 'max:100', 'alpha_dash:ascii'],
            'category_label' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'availability' => ['required', 'string', 'max:100'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $validated['currency'] = strtoupper($validated['currency']);
        $validated['is_featured'] = $request->boolean('is_featured');

        return $validated;
    }
}
