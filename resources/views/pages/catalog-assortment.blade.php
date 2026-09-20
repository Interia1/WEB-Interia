<section id="catalog-assortment" class="shop-assortment" data-assortment role="dialog" aria-modal="false" aria-labelledby="assortment-title" hidden>
    <header class="shop-assortment-header">
        <h2 id="assortment-title">Sortiment</h2>
        <div class="shop-assortment-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" class="form-control" data-assortment-search placeholder="Hľadať kategóriu alebo parameter" aria-label="Hľadať v sortimente">
        </div>
        <a href="{{ route('eshop.catalog.index', [], false) }}" class="shop-assortment-all">Všetky produkty</a>
        <button type="button" class="shop-assortment-close" data-assortment-close aria-label="Zavrieť sortiment" title="Zavrieť sortiment"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="shop-assortment-body">
        <div class="shop-assortment-grid">
            @foreach ($assortment as $item)
                <article class="shop-assortment-category" data-assortment-category="{{ $item['slug'] }}" data-assortment-terms="{{ $item['label'].' '.$item['parameters']->map(fn ($parameter) => $parameter->name.' '.implode(' ', $parameter->options ?? []))->implode(' ') }}">
                    <a class="shop-assortment-category-link" data-assortment-link href="{{ route('eshop.catalog.index', ['category' => $item['slug']], false) }}">
                        <span class="shop-assortment-image">
                            @if ($item['image'])
                                <img src="{{ asset($item['image']) }}" alt="" width="64" height="64" loading="lazy">
                            @else
                                <i class="bi bi-box-seam" aria-hidden="true"></i>
                            @endif
                        </span>
                        <div><h3>{{ $item['label'] }}</h3><span class="shop-assortment-count">{{ $item['total'] }} produktov</span></div>
                        <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                    </a>
                    @if ($item['parameters']->isNotEmpty())
                        <form method="get" action="{{ route('eshop.catalog.index', [], false) }}" data-assortment-form>
                            <input type="hidden" name="category" value="{{ $item['slug'] }}">
                            <input type="hidden" name="q" value="{{ $activeSearch }}">
                            <input type="hidden" name="sort" value="{{ $activeSort }}">
                            @foreach ($item['parameters'] as $parameter)
                                <details class="shop-assortment-parameter">
                                    <summary>{{ $parameter->name }}<i class="bi bi-plus" aria-hidden="true"></i></summary>
                                    @include('pages.catalog-filter-field', ['activeFilters' => $activeCategory === $item['slug'] ? $activeFilters : [], 'availableValues' => $item['values']])
                                </details>
                            @endforeach
                            <button class="shop-assortment-apply" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Zobraziť produkty <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                        </form>
                    @endif
                </article>
            @endforeach
        </div>
        <p class="shop-assortment-empty" data-assortment-empty role="status" hidden>Žiadna kategória nezodpovedá vyhľadávaniu.</p>
    </div>
</section>