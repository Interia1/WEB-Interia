<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\CatalogCategory;
use App\Models\CatalogParameter;
use App\Models\ProductParameterValue;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CatalogSettingsController extends Controller
{
    public function index()
    {
        return view('internal.catalog-settings', [
            'categories' => CatalogCategory::with('parameters')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        CatalogCategory::create($request->validate([
            'slug' => ['required', 'string', 'max:100', 'alpha_dash:ascii', 'unique:catalog_categories,slug'],
            'name' => ['required', 'string', 'max:100'],
        ]));

        return back()->with('status', 'Kategória bola pridaná.');
    }

    public function storeParameter(Request $request, CatalogCategory $category)
    {
        $category->parameters()->create($this->validated($request, $category));

        return redirect()->route('internal.catalog.index', ['category' => $category->id])->with('status', 'Parameter bol pridaný.');
    }

    public function updateParameter(Request $request, CatalogParameter $parameter)
    {
        $data = $this->validated($request, $parameter->category, $parameter);
        $used = ProductParameterValue::where('catalog_parameter_id', $parameter->id);
        if ($used->exists() && ($data['type'] !== $parameter->type ||
            ($parameter->type === 'select' && $used->whereNotIn('value', $data['options'])->exists()))) {
            throw ValidationException::withMessages(['options_text' => 'Používaný typ alebo možnosti nemožno odstrániť. Najprv upravte hodnoty produktov.']);
        }
        $parameter->update($data);

        return redirect()->route('internal.catalog.index', ['category' => $parameter->catalog_category_id])->with('status', 'Parameter bol uložený.');
    }

    private function validated(Request $request, CatalogCategory $category, ?CatalogParameter $parameter = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('catalog_parameters')->where('catalog_category_id', $category->id)->ignore($parameter)],
            'type' => ['required', Rule::in(['select', 'number', 'boolean'])],
            'unit' => ['nullable', 'string', 'max:20'],
            'options_text' => ['nullable', 'required_if:type,select', 'string', 'max:10000'],
            'required' => ['nullable', 'boolean'],
            'filterable' => ['nullable', 'boolean'],
            'position' => ['required', 'integer', 'between:0,65535'],
        ]);
        $options = collect(preg_split('/\R/u', $data['options_text'] ?? ''))->map(fn ($value) => trim($value))->filter(fn ($value) => $value !== '')->unique()->values();
        if ($data['type'] === 'select' && ($options->isEmpty() || $options->count() > 100 || $options->contains(fn ($value) => mb_strlen($value) > 100))) {
            throw ValidationException::withMessages(['options_text' => 'Zadajte 1 až 100 možností, každú do 100 znakov.']);
        }
        unset($data['options_text']);
        $data['options'] = $data['type'] === 'select' ? $options->all() : null;
        $data['unit'] = $data['type'] === 'number' ? ($data['unit'] ?? null) : null;
        $data['required'] = $request->boolean('required');
        $data['filterable'] = $request->boolean('filterable');

        return $data;
    }
}
