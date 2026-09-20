<section class="shop-facets" aria-label="Filtre kategórie">
    @if ($activeCategory ?? '')
        @php($categoryTitle = collect($categories)->firstWhere('slug', $activeCategory)['label'] ?? $activeCategory)
        <div class="shop-filters-heading">
            <h2 class="shop-filters-title" title="{{ $categoryTitle }}">{{ $categoryTitle }}</h2>
            <a class="shop-filters-reset" data-catalog-link data-catalog-clear-filters href="{{ route('eshop.catalog.index', ['category' => $activeCategory, 'sort' => $activeSort ?? 'recommended'], false) }}" aria-label="Vynulovať filtre" title="Vynulovať filtre"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></a>
        </div>
    @endif
    <div class="shop-facet-grid">
        @forelse (($parameters ?? collect()) as $parameter)
            @include('pages.catalog-filter-field')
        @empty
            <p class="shop-facets-empty">{{ ($activeCategory ?? '') ? 'Táto kategória nemá ďalšie filtre.' : 'Vyberte kategóriu' }}</p>
        @endforelse
    </div>
</section>