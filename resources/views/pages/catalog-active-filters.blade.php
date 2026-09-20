@php
    $query = request()->except('page');
    $chips = [];
    if ($activeSearch ?? '') {
        $without = $query;
        unset($without['q']);
        $chips[] = ['label' => $activeSearch, 'query' => $without];
    }
    foreach ($parameters ?? [] as $parameter) {
        foreach ($activeFilters[$parameter->id] ?? [] as $key => $value) {
            $without = $query;
            unset($without['filters'][$parameter->id][$key]);
            if (empty($without['filters'][$parameter->id])) unset($without['filters'][$parameter->id]);
            $label = $parameter->name.': ';
            $label .= $parameter->type === 'boolean' ? ($value === '1' ? 'Áno' : 'Nie') : ($parameter->type === 'number' ? ($key === 'min' ? 'od ' : 'do ').$value.' '.$parameter->unit : $value);
            $chips[] = ['label' => $label, 'query' => $without];
        }
    }
@endphp
@foreach ($chips as $chip)
    <a class="shop-active-filter" data-catalog-link href="{{ route('eshop.catalog.index', $chip['query'], false) }}" aria-label="Zrušiť filter {{ $chip['label'] }}">{{ $chip['label'] }} <i class="bi bi-x-lg" aria-hidden="true"></i></a>
@endforeach
@if ($chips)
    <a class="small" data-catalog-link data-catalog-clear-filters href="{{ route('eshop.catalog.index', ['category' => $activeCategory ?? '', 'sort' => $activeSort ?? 'recommended'], false) }}">Vymazať filtre</a>
@endif