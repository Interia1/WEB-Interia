@php
    $selection = $activeFilters[$parameter->id] ?? [];
    $selectionLabel = $parameter->type === 'number'
        ? implode(' · ', array_filter([
            isset($selection['min']) ? 'Od '.$selection['min'] : null,
            isset($selection['max']) ? 'Do '.$selection['max'] : null,
        ]))
        : 'Vybrané: '.count($selection);
@endphp
<fieldset class="shop-facet" data-facet>
    <legend>{{ $parameter->name }}{{ $parameter->unit ? ' ('.$parameter->unit.')' : '' }}</legend>
    <span class="shop-facet-selection" data-facet-selection>{{ $selectionLabel ?: 'Ľubovoľný rozsah' }}</span>
    @if ($parameter->type === 'number')
        <div class="shop-range">
            <input class="form-control form-control-sm" type="number" step="any" name="filters[{{ $parameter->id }}][min]" value="{{ $activeFilters[$parameter->id]['min'] ?? '' }}" placeholder="Od" aria-label="{{ $parameter->name }} od">
            <span aria-hidden="true">–</span>
            <input class="form-control form-control-sm" type="number" step="any" name="filters[{{ $parameter->id }}][max]" value="{{ $activeFilters[$parameter->id]['max'] ?? '' }}" placeholder="Do" aria-label="{{ $parameter->name }} do">
        </div>
    @else
        @php
            $available = ($availableValues[$parameter->id] ?? collect())->pluck('value')->all();
            $choices = $parameter->type === 'boolean' ? ['1', '0'] : ($parameter->options ?? []);
            $selected = $activeFilters[$parameter->id] ?? [];
            $choices = array_filter($choices, fn ($choice) => in_array((string) $choice, $available, true) || in_array((string) $choice, $selected, true));
        @endphp
        @if (count($choices) > 6)
            <input type="search" class="form-control form-control-sm mb-1" data-facet-search placeholder="Hľadať možnosť" aria-label="Hľadať: {{ $parameter->name }}">
        @endif
        <div class="shop-facet-options">
            @forelse ($choices as $choice)
                <label data-facet-option>
                    <input class="form-check-input" type="checkbox" name="filters[{{ $parameter->id }}][]" value="{{ $choice }}" @checked(in_array((string) $choice, $selected, true))>
                    <span>{{ $parameter->type === 'boolean' ? ($choice === '1' ? 'Áno' : 'Nie') : $choice }}</span>
                </label>
            @empty
                <span class="small text-secondary">Bez hodnôt</span>
            @endforelse
        </div>
    @endif
</fieldset>