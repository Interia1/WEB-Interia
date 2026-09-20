@extends('layouts.app')

@section('title', 'Kategórie a filtre | WEB-Interia')

@section('content')
<section class="py-3"><div class="container">
    <h1 class="h3">Kategórie a filtre</h1>
    @include('internal._navigation')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('internal.catalog.store') }}" class="row g-2 align-items-end mb-4">
        @csrf
        <div class="col-sm-5"><label for="category-name" class="form-label">Nová kategória</label><input id="category-name" name="name" class="form-control" required maxlength="100"></div>
        <div class="col-sm-5"><label for="category-slug" class="form-label">Kód kategórie</label><input id="category-slug" name="slug" class="form-control" required pattern="[A-Za-z0-9_-]+" maxlength="100"></div>
        <div class="col-sm-2"><button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg" aria-hidden="true"></i> Pridať</button></div>
    </form>
    @foreach ($categories as $category)
        <details class="border-top py-3" id="category-{{ $category->id }}" @if(old('_category', request('category')) == $category->id) open @endif>
            <summary class="fw-semibold">{{ $category->name }} <span class="small text-secondary">({{ $category->parameters->count() }})</span></summary>
            @foreach ($category->parameters as $parameter)
                <form method="post" action="{{ route('internal.catalog.parameters.update', $parameter) }}" class="border-bottom py-3">
                    @csrf @method('PUT')
                    <input type="hidden" name="_category" value="{{ $category->id }}">
                    @include('internal.parameter-definition', ['fieldPrefix' => 'parameter-'.$parameter->id])
                </form>
            @endforeach
            <details class="mt-3" @if(old('_editor') === 'new-'.$category->id) open @endif>
                <summary>Pridať parameter</summary>
                <form method="post" action="{{ route('internal.catalog.parameters.store', $category) }}" class="py-3">
                    @csrf
                    <input type="hidden" name="_category" value="{{ $category->id }}">
                    @include('internal.parameter-definition', ['parameter' => new \App\Models\CatalogParameter(['type' => 'select', 'filterable' => true, 'position' => $category->parameters->count()]), 'fieldPrefix' => 'new-'.$category->id])
                </form>
            </details>
        </details>
    @endforeach
</div></section>
@endsection