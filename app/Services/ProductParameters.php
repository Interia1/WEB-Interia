<?php

namespace App\Services;

use App\Models\CatalogCategory;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductParameters
{
    public function validate(string $category, array $input): array
    {
        $parameters = CatalogCategory::where('slug', $category)->first()?->parameters ?? collect();
        $rules = ['parameters' => ['array']];
        $labels = [];

        foreach ($input as $parameterId => $value) {
            if (! $parameters->contains('id', $parameterId)) {
                $rules['parameters.'.$parameterId] = ['prohibited'];
            }
        }

        foreach ($parameters as $parameter) {
            $key = 'parameters.'.$parameter->id;
            $rules[$key] = [
                $parameter->required ? 'required' : 'nullable',
                ...match ($parameter->type) {
                    'number' => ['numeric', 'between:-99999999.9999,99999999.9999', 'decimal:0,4'],
                    'boolean' => [Rule::in(['0', '1'])],
                    default => ['string', Rule::in($parameter->options ?? [])],
                },
            ];
            $labels[$key] = $parameter->name;
        }

        $validated = Validator::make(['parameters' => $input], $rules, [], $labels)->validate();
        $values = [];
        foreach ($parameters as $parameter) {
            $value = $validated['parameters'][$parameter->id] ?? null;
            if ($value !== null && $value !== '') {
                $values[] = [
                    'catalog_parameter_id' => $parameter->id,
                    'value' => (string) $value,
                    'numeric_value' => $parameter->type === 'number' ? $value : null,
                ];
            }
        }

        return $values;
    }

    public function replace(Product $product, array $values): void
    {
        $product->parameterValues()->delete();
        $product->parameterValues()->createMany($values);
    }
}
