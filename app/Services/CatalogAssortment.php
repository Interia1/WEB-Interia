<?php

namespace App\Services;

use App\Models\CatalogCategory;
use App\Models\Product;
use App\Models\ProductParameterValue;
use Illuminate\Support\Collection;

class CatalogAssortment
{
    public function forCategories(Collection $categories): Collection
    {
        $definitions = CatalogCategory::with(['parameters' => fn ($query) => $query->where('filterable', true)])->get()->keyBy('slug');
        $products = Product::query()->select('category')
            ->selectRaw("COUNT(*) as total, MIN(NULLIF(image_path, '')) as image_path")
            ->groupBy('category')->get()->keyBy('category');
        $values = ProductParameterValue::query()
            ->join('products', 'products.id', '=', 'product_parameter_values.product_id')
            ->join('catalog_parameters', 'catalog_parameters.id', '=', 'product_parameter_values.catalog_parameter_id')
            ->where('catalog_parameters.filterable', true)
            ->where('catalog_parameters.type', '!=', 'number')
            ->select('products.category', 'catalog_parameter_id', 'value')->distinct()->get()->groupBy('category');

        return $categories->map(fn ($item) => [
            'slug' => $item['slug'],
            'label' => $item['label'],
            'total' => $products->get($item['slug'])?->total ?? 0,
            'image' => $products->get($item['slug'])?->image_path,
            'parameters' => $definitions->get($item['slug'])?->parameters ?? collect(),
            'values' => ($values->get($item['slug']) ?? collect())->groupBy('catalog_parameter_id'),
        ]);
    }
}