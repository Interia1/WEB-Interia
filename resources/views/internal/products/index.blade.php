@extends('layouts.app')

@section('title', 'Správa produktov | WEB-Interia')

@section('content')
<section class="internal-shell py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="h2 mb-0">Produkty</h1>
            <a class="btn btn-primary" href="{{ route('internal.products.create') }}">Pridať produkt</a>
        </div>
        @include('internal._navigation')
        <a class="btn btn-outline-primary mb-3" href="{{ route('internal.products.bulk') }}"><i class="bi bi-ui-checks" aria-hidden="true"></i> Hromadné parametre</a>

        <div class="internal-panel table-responsive">
            <table class="table align-middle mb-0 internal-table">
                <thead><tr><th>Názov</th><th>Kategória</th><th>Cena</th><th>Dostupnosť</th><th></th></tr></thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td><strong>{{ $product->name }}</strong><br><span class="small text-secondary">{{ $product->slug }}</span></td>
                            <td>{{ $product->category_label }}</td>
                            <td>{{ number_format((float) $product->price, 2, ',', ' ') }} {{ $product->currency }}</td>
                            <td>{{ $product->availability }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('internal.products.edit', $product) }}">Upraviť</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('internal.products.create', ['copy' => $product->id]) }}" title="Nový produkt s rovnakými parametrami" aria-label="Kopírovať parametre: {{ $product->name }}"><i class="bi bi-copy" aria-hidden="true"></i></a>
                                <form class="d-inline" method="post" action="{{ route('internal.products.destroy', $product) }}" onsubmit="return confirm('Naozaj odstrániť tento produkt?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Odstrániť</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Zatiaľ nie sú vytvorené žiadne produkty.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $products->links() }}</div>
    </div>
</section>
@endsection