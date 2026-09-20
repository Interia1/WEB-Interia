@extends('layouts.app')
@section('title', 'Hromadné parametre | WEB-Interia')
@section('content')
<section class="py-3"><div class="container">
    <h1 class="h3">Hromadné parametre</h1>
    @include('internal._navigation')
    @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <form method="get" class="d-flex gap-2 align-items-end mb-3">
        <div><label for="bulk-category" class="form-label">Kategória</label><select name="category" id="bulk-category" class="form-select" required>
            <option value="">Vyberte kategóriu</option>
            @foreach ($parameterCategories as $category)
                <option value="{{ $category->slug }}" @selected($product->category === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select></div>
        <button class="btn btn-primary" type="submit">Vybrať</button>
    </form>
    @if ($product->category)
        <form method="post" action="{{ route('internal.products.bulk-update') }}">
            @csrf @method('PUT')
            <input id="category" name="category" type="hidden" value="{{ $product->category }}">
            <div class="row g-4">
                <div class="col-lg-6">
                    <label class="d-block border-bottom py-2 fw-semibold"><input class="form-check-input" type="checkbox" data-select-products> Označiť stránku ({{ $products->count() }})</label>
                    @foreach ($products as $item)
                        <label class="d-block border-bottom py-2"><input class="form-check-input" type="checkbox" name="product_ids[]" value="{{ $item->id }}" @checked(in_array($item->id, old('product_ids', [])))> {{ $item->name }}</label>
                    @endforeach
                    <div class="mt-3">{{ $products->links() }}</div>
                </div>
                <div class="col-lg-6">
                    @include('internal.products.parameters')
                    <button class="btn btn-primary mt-3" type="submit"><i class="bi bi-check-lg" aria-hidden="true"></i> Uložiť označeným</button>
                </div>
            </div>
        </form>
    @endif
</div></section>
@endsection