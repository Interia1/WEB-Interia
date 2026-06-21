@extends('layouts.app')

@section('title', 'Katalóg materiálov - tlač | WEB-Interia')
@section('description', 'Tlačová verzia katalógu materiálov pre výrobcov katalógov.')

@section('content')
<section class="py-5 bg-white border-bottom print-hidden">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="display-6 mb-2">Katalóg materiálov - tlačová verzia</h1>
                <p class="text-secondary mb-0">Určené pre export do PDF a profesionálnu tlač katalógov.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('eshop.catalog.index') }}" class="btn btn-outline-secondary">Späť na interaktívny katalóg</a>
                <button type="button" class="btn btn-primary" onclick="window.print()">Vytlačiť / Uložiť PDF</button>
            </div>
        </div>
    </div>
</section>

<section class="py-5 print-area">
    <div class="container">
        <h2 class="h4 mb-3">Prehľad položiek</h2>

        @if ($products->isEmpty())
            <div class="alert alert-info">Katalóg materiálov zatiaľ neobsahuje položky.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Názov</th>
                            <th scope="col">Kategória</th>
                            <th scope="col">Krátky popis</th>
                            <th scope="col">Dostupnosť</th>
                            <th scope="col">Cena</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td class="fw-semibold">{{ $product->name }}</td>
                                <td>{{ $product->category_label }}</td>
                                <td>{{ $product->short_description }}</td>
                                <td>{{ $product->availability }}</td>
                                <td>{{ number_format((float) $product->price, 2, ',', ' ') }} {{ $product->currency }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
@endsection
