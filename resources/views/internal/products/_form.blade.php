@if ($errors->any())
    <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="name">Názov</label>
        <input class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="slug">Slug</label>
        <input class="form-control" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" pattern="[A-Za-z0-9_-]+" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="category">Kód kategórie</label>
        <input class="form-control" id="category" name="category" value="{{ old('category', $product->category) }}" pattern="[A-Za-z0-9_-]+" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="category_label">Názov kategórie</label>
        <input class="form-control" id="category_label" name="category_label" value="{{ old('category_label', $product->category_label) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="price">Cena</label>
        <input class="form-control" id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('price', $product->price) }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="currency">Mena</label>
        <input class="form-control text-uppercase" id="currency" name="currency" maxlength="3" value="{{ old('currency', $product->currency ?: 'EUR') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="availability">Dostupnosť</label>
        <input class="form-control" id="availability" name="availability" value="{{ old('availability', $product->availability ?: 'Skladom') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label" for="short_description">Krátky popis</label>
        <input class="form-control" id="short_description" name="short_description" maxlength="255" value="{{ old('short_description', $product->short_description) }}" required>
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Podrobný popis</label>
        <textarea class="form-control" id="description" name="description" rows="6" required>{{ old('description', $product->description) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="image_path">Cesta k obrázku (voliteľné)</label>
        <input class="form-control" id="image_path" name="image_path" value="{{ old('image_path', $product->image_path) }}" placeholder="/images/produkt.jpg">
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" id="is_featured" name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $product->is_featured))>
            <label class="form-check-label" for="is_featured">Odporúčaný produkt</label>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Uložiť produkt</button>
    <a class="btn btn-outline-secondary" href="{{ route('internal.products.index') }}">Zrušiť</a>
</div>