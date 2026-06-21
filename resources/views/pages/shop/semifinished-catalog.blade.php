@extends('layouts.app')

@section('title', 'Katalóg polotovarov | WEB-Interia')
@section('description', 'Interaktívny katalóg polotovarov s filtráciou podľa technologického typu a parametrov.')

@section('content')
<section class="py-5 bg-white border-bottom">
    <div class="container py-lg-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="display-6 mb-3">Katalóg polotovarov</h1>
                <p class="lead text-secondary mb-0">Vyberte si typ výrobného dielu, skontrolujte minimálnu sériu a odošlite dopyt.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('semifinished.catalog.print') }}" class="btn btn-outline-primary">Tlačová verzia</a>
                <a href="{{ route('semifinished') }}" class="btn btn-primary">Objednávkový formulár</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <form method="get" action="{{ route('semifinished.catalog.index') }}" class="row g-3 align-items-end mb-4">
            <div class="col-lg-5">
                <label for="q" class="form-label">Vyhľadávanie</label>
                <input id="q" name="q" type="search" class="form-control" value="{{ $activeSearch }}" placeholder="Názov dielu, materiál, kategória...">
            </div>
            <div class="col-lg-4">
                <label for="typ" class="form-label">Typ výroby</label>
                <select id="typ" name="typ" class="form-select">
                    <option value="">Všetky typy</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['slug'] }}" @selected($activeCategory === $category['slug'])>{{ $category['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit">Filtrovať</button>
                <a href="{{ route('semifinished.catalog.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        @if ($items->isEmpty())
            <div class="alert alert-warning">Pre zadaný filter sa nenašli položky. Skúste širší výber alebo reset filtra.</div>
        @else
            <div class="row g-3">
                @foreach ($items as $item)
                    <div class="col-12 col-lg-6">
                        <article class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <span class="badge text-bg-light border">{{ $item['category_label'] }}</span>
                                    <span class="small text-secondary">{{ $item['code'] }}</span>
                                </div>
                                <h2 class="h5">{{ $item['name'] }}</h2>
                                <dl class="row mb-0 small">
                                    <dt class="col-4 text-secondary">Materiál</dt>
                                    <dd class="col-8">{{ $item['material'] }}</dd>
                                    <dt class="col-4 text-secondary">Úprava</dt>
                                    <dd class="col-8">{{ $item['finish'] }}</dd>
                                    <dt class="col-4 text-secondary">Min. séria</dt>
                                    <dd class="col-8">{{ $item['min_order'] }}</dd>
                                    <dt class="col-4 text-secondary">Lead time</dt>
                                    <dd class="col-8">{{ $item['lead_time'] }}</dd>
                                </dl>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
