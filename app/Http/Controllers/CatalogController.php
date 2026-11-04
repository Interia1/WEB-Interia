<?php

namespace App\Http\Controllers;

use App\Models\CatalogCategory;
use App\Models\Product;
use App\Models\ProductParameterValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CatalogController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:recommended,name,price_asc,price_desc'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'filters' => ['sometimes', 'array', 'max:30'],
        ]);
        $search = trim($data['q'] ?? '');
        $category = $data['category'] ?? '';
        $sort = $data['sort'] ?? 'recommended';
        $definition = CatalogCategory::with(['parameters' => fn ($query) => $query->where('filterable', true)])->where('slug', $category)->first();
        $parameters = $definition?->parameters ?? collect();
        $filters = $data['filters'] ?? [];
        if (array_diff(array_keys($filters), $parameters->pluck('id')->all())) {
            throw ValidationException::withMessages(['filters' => 'Filter nepatrí do zvolenej kategórie.']);
        }
        $rules = [];
        foreach ($parameters as $parameter) {
            $key = 'filters.'.$parameter->id;
            if ($parameter->type === 'number') {
                $rules[$key] = ['sometimes', 'array:min,max'];
                $rules[$key.'.min'] = ['nullable', 'numeric', 'between:-99999999.9999,99999999.9999'];
                $rules[$key.'.max'] = ['nullable', 'numeric', 'between:-99999999.9999,99999999.9999'];
                if (isset($filters[$parameter->id]['min']) && $filters[$parameter->id]['min'] !== '') {
                    $rules[$key.'.max'][] = 'gte:'.$key.'.min';
                }
            } else {
                $rules[$key] = ['sometimes', 'array', 'max:100'];
                $rules[$key.'.*'] = ['required', 'string', Rule::in($parameter->type === 'boolean' ? ['0', '1'] : ($parameter->options ?? []))];
            }
        }
        $request->validate($rules);
        $filters = collect($filters)->map(fn ($values) => array_filter($values, fn ($value) => $value !== null && $value !== ''))->filter()->all();

        $query = Product::query()->select(['id', 'slug', 'sku', 'minimum_order_quantity', 'name', 'category', 'category_label', 'availability', 'price', 'currency', 'image_path']);
        if ($category !== '') {
            $query->where('category', $category);
        }
        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                foreach (['name', 'slug', 'category', 'category_label', 'short_description', 'description', 'availability'] as $column) {
                    $query->orWhereLike($column, '%'.$search.'%');
                }
            });
        }
        foreach ($parameters as $parameter) {
            if (! isset($filters[$parameter->id])) {
                continue;
            }
            $values = $filters[$parameter->id];
            $query->whereHas('parameterValues', function (Builder $query) use ($parameter, $values) {
                $query->where('catalog_parameter_id', $parameter->id);
                if ($parameter->type === 'number') {
                    if (isset($values['min'])) {
                        $query->where('numeric_value', '>=', $values['min']);
                    }
                    if (isset($values['max'])) {
                        $query->where('numeric_value', '<=', $values['max']);
                    }
                } else {
                    $query->whereIn('value', $values);
                }
            });
        }
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('is_featured')->orderBy('name'),
        };
        $products = $query->orderBy('id')->paginate(30)->withPath(route('eshop.catalog.index', [], false))->withQueryString();
        $categories = Product::query()->select('category as slug', 'category_label as label')->distinct()->get()
            ->concat(CatalogCategory::query()->select('slug', 'name as label')->get())
            ->unique('slug')->sortBy('label')->values();

        $availableValues = ProductParameterValue::query()
            ->join('products', 'products.id', '=', 'product_parameter_values.product_id')
            ->where('products.category', $category)
            ->whereIn('catalog_parameter_id', $parameters->where('type', '!=', 'number')->pluck('id')->all())
            ->select('catalog_parameter_id', 'value')->distinct()->get()->groupBy('catalog_parameter_id');

        $viewData = [
            'products' => $products,
            'categories' => $categories,
            'activeSearch' => $search,
            'activeCategory' => $category,
            'activeSort' => $sort,
            'parameters' => $parameters,
            'activeFilters' => $filters,
            'availableValues' => $availableValues,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'results' => view('pages.catalog-results', $viewData)->render(),
                'filters' => view('pages.catalog-filters', $viewData)->render(),
                'active' => view('pages.catalog-active-filters', $viewData)->render(),
                'total' => $products->total(),
            ])->header('Vary', 'Accept, X-Requested-With')
                ->header('Cache-Control', 'no-store, private');
        }

        $viewData['assortment'] = app(\App\Services\CatalogAssortment::class)->forCategories($categories);

        return response()->view('pages.catalog-index', $viewData)
            ->header('Vary', 'Accept, X-Requested-With');
    }
}
