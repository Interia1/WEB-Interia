@extends('layouts.app')

@section('title', 'Katalóg polotovarov - tlač | WEB-Interia')
@section('description', 'Tlačová verzia katalógu polotovarov pre výrobcov katalógov.')

@section('content')
<section class="py-5 bg-white border-bottom print-hidden">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="display-6 mb-2">Katalóg polotovarov - tlačová verzia</h1>
                <p class="text-secondary mb-0">Čistá forma pre tlač, sadzbu a export do PDF.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('semifinished.catalog.index') }}" class="btn btn-outline-secondary">Späť na interaktívny katalóg</a>
                <button type="button" class="btn btn-primary" onclick="window.print()">Vytlačiť / Uložiť PDF</button>
            </div>
        </div>
    </div>
</section>

<section class="py-5 print-area">
    <div class="container">
        <h2 class="h4 mb-3">Prehľad výrobných položiek</h2>
        <div class="table-responsive">
            <table class="table table-bordered align-middle small">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Kód</th>
                        <th scope="col">Názov dielu</th>
                        <th scope="col">Typ</th>
                        <th scope="col">Materiál</th>
                        <th scope="col">Povrch</th>
                        <th scope="col">Min. séria</th>
                        <th scope="col">Lead time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item['code'] }}</td>
                            <td class="fw-semibold">{{ $item['name'] }}</td>
                            <td>{{ $item['category_label'] }}</td>
                            <td>{{ $item['material'] }}</td>
                            <td>{{ $item['finish'] }}</td>
                            <td>{{ $item['min_order'] }}</td>
                            <td>{{ $item['lead_time'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
