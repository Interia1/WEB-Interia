<div class="mt-3" data-product-parameters data-initial-category="{{ old('category', $product->category) }}">
    @foreach ($parameterCategories as $parameterCategory)
        <fieldset data-parameter-category="{{ $parameterCategory->slug }}" data-category-label="{{ $parameterCategory->name }}" @if(old('category', $product->category) !== $parameterCategory->slug) hidden disabled @endif>
            @if ($parameterCategory->parameters->isNotEmpty())
                <legend class="h6 border-bottom pb-2">Parametre: {{ $parameterCategory->name }}</legend>
            @endif
            @foreach ($parameterCategory->parameters as $parameter)
                @php($value = old('parameters.'.$parameter->id, $parameterValues[$parameter->id] ?? ''))
                <div class="row g-2 align-items-center mb-2">
                    <label class="col-sm-4 col-form-label py-0" for="value-{{ $parameter->id }}">{{ $parameter->name }}{{ $parameter->unit ? ' ('.$parameter->unit.')' : '' }}{{ $parameter->required ? ' *' : '' }}</label>
                    <div class="col-sm-8">
                        @if ($parameter->type === 'number')
                            <input class="form-control form-control-sm" id="value-{{ $parameter->id }}" name="parameters[{{ $parameter->id }}]" type="number" step="0.0001" min="-99999999.9999" max="99999999.9999" value="{{ $value }}" @required($parameter->required && !($bulk ?? false))>
                        @else
                            <select class="form-select form-select-sm" id="value-{{ $parameter->id }}" name="parameters[{{ $parameter->id }}]" @required($parameter->required && !($bulk ?? false))>
                                <option value="">{{ ($bulk ?? false) ? 'Bez zmeny' : 'Nevyplnené' }}</option>
                                @foreach ($parameter->type === 'boolean' ? ['1' => 'Áno', '0' => 'Nie'] : array_combine($parameter->options ?? [], $parameter->options ?? []) as $option => $label)
                                    <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @endif
                        @error('parameters.'.$parameter->id)<span class="small text-danger" role="alert">{{ $message }}</span>@enderror
                    </div>
                </div>
            @endforeach
        </fieldset>
    @endforeach
</div>
<script src="{{ asset('js/product-parameters.js') }}?v={{ filemtime(public_path('js/product-parameters.js')) }}" defer></script>