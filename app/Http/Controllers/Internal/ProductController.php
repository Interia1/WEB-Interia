<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\CatalogCategory;
use App\Models\Product;
use App\Services\ProductParameters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function bulkEdit(Request $request): View
    {
        $category = (string) $request->query('category', '');
        $product = new Product(['category' => $category]);

        return view('internal.products.bulk', [
            ...$this->formData($product),
            'products' => Product::where('category', $category)->orderBy('name')->paginate(50)->withQueryString(),
            'bulk' => true,
        ]);
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'exists:catalog_categories,slug'],
            'product_ids' => ['required', 'array', 'min:1', 'max:50'],
            'product_ids.*' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->where('category', $request->input('category'))],
            'parameters' => ['required', 'array'],
        ]);
        $patch = array_filter($data['parameters'], fn ($value) => $value !== null && $value !== '');
        if ($patch === []) {
            throw ValidationException::withMessages(['parameters' => 'Vyplňte aspoň jeden parameter.']);
        }
        DB::transaction(function () use ($data, $patch) {
            $products = Product::whereIn('id', $data['product_ids'])->where('category', $data['category'])->lockForUpdate()->get();
            $products->load('parameterValues');
            foreach ($products as $product) {
                $values = array_replace($product->parameterValues->pluck('value', 'catalog_parameter_id')->all(), $patch);
                $service = app(ProductParameters::class);
                $service->replace($product, $service->validate($data['category'], $values));
            }
        });

        return back()->with('status', 'Parametre označených produktov boli uložené.');
    }

    public function index(): View
    {
        return view('internal.products.index', [
            'products' => Product::query()->orderBy('category_label')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        $source = $request->filled('copy') ? Product::with('parameterValues')->findOrFail($request->integer('copy')) : null;
        $product = new Product($source ? $source->only(['category', 'category_label']) : []);

        return view('internal.products.create', $this->formData($product, $source));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $values = app(ProductParameters::class)->validate($data['category'], $request->input('parameters', []));
        $product = DB::transaction(function () use ($data, $values) {
            CatalogCategory::firstOrCreate(['slug' => $data['category']], ['name' => $data['category_label']]);
            $product = Product::create($data);
            app(ProductParameters::class)->replace($product, $values);

            return $product;
        });

        return redirect()->route('internal.products.edit', $product)
            ->with('status', 'Produkt bol vytvorený.');
    }

    public function edit(Product $product): View
    {
        return view('internal.products.edit', $this->formData($product));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        $values = app(ProductParameters::class)->validate($data['category'], $request->input('parameters', []));
        DB::transaction(function () use ($product, $data, $values) {
            CatalogCategory::firstOrCreate(['slug' => $data['category']], ['name' => $data['category_label']]);
            $product->update($data);
            app(ProductParameters::class)->replace($product, $values);
        });

        return back()->with('status', 'Produkt bol uložený.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('internal.products.index')->with('status', 'Produkt bol odstránený.');
    }

    /** @return array<string, mixed> */
    private function formData(Product $product, ?Product $source = null): array
    {
        return [
            'product' => $product,
            'parameterCategories' => CatalogCategory::with('parameters')->orderBy('name')->get(),
            'parameterValues' => ($source ?? $product)->parameterValues->pluck('value', 'catalog_parameter_id')->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('products')->ignore($product)],
            'sku' => ['nullable', 'string', 'max:10', Rule::unique('products')->ignore($product)],
            'minimum_order_quantity' => ['sometimes', 'required', 'integer', 'min:1', 'max:100000'],
            'category' => ['required', 'string', 'max:100', 'alpha_dash:ascii'],
            'category_label' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'availability' => ['required', 'string', 'max:100'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['nullable', 'boolean'],
            'parameters' => ['sometimes', 'array'],
        ]);

        unset($validated['parameters']);
        $category = CatalogCategory::where('slug', $validated['category'])->first();
        $validated['category_label'] = $category?->name ?? $validated['category_label'];
        $validated['currency'] = strtoupper($validated['currency']);
        $validated['is_featured'] = $request->boolean('is_featured');

        return $validated;
    }
}
