<?php

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/o-nas', 'pages.about')->name('about');
Route::view('/sluzby', 'pages.services')->name('services');
Route::view('/katalogy', 'pages.catalogs')->name('catalogs.overview');

// Tri časti podnikania - nové obchodné linky
Route::view('/materialy-eshop', 'pages.shop.materials')->name('materials-eshop');
Route::view('/polotovary', 'pages.shop.semifinished')->name('semifinished');
Route::view('/vyroba-na-mieru', 'pages.shop.custom-work')->name('custom-work');
Route::view('/vyroba-na-mieru/prezentacie-dielov', 'pages.shop.custom-presentations')->name('custom-work.presentations');

// Customer order summary
Route::view('/moje-objednavky', 'pages.customer.orders')->name('customer.orders');

// Legacy routes
Route::view('/atypicka-vyroba', 'pages.custom-production')->name('custom-production');
Route::view('/materialy', 'pages.materials')->name('materials');
Route::view('/faq', 'pages.faq')->name('faq');
Route::redirect('/produkty-sluzby', '/sluzby', 301)->name('products');
Route::view('/kontakt', 'pages.contact')->name('contact');

$semifinishedCatalog = collect([
    [
        'code' => 'POL-R-001',
        'name' => 'Laserom rezaný plechový diel',
        'category' => 'rezanie',
        'category_label' => 'Rezanie',
        'material' => 'S355 oceľ / 3 mm',
        'finish' => 'Bez povrchovej úpravy',
        'lead_time' => '5-7 pracovných dní',
        'min_order' => '20 ks',
    ],
    [
        'code' => 'POL-R-002',
        'name' => 'Presný výpalok pre montážny prípravok',
        'category' => 'rezanie',
        'category_label' => 'Rezanie',
        'material' => 'Nerez 1.4301 / 4 mm',
        'finish' => 'Odstránenie otrepov',
        'lead_time' => '7 pracovných dní',
        'min_order' => '10 ks',
    ],
    [
        'code' => 'POL-O-001',
        'name' => 'Ohýbaný U-profil podľa výkresu',
        'category' => 'ohybanie',
        'category_label' => 'Ohýbanie',
        'material' => 'Hliník EN AW-5754',
        'finish' => 'Brúsenie hrán',
        'lead_time' => '6-8 pracovných dní',
        'min_order' => '30 ks',
    ],
    [
        'code' => 'POL-O-002',
        'name' => 'Ohýbaná konzola s výstuhou',
        'category' => 'ohybanie',
        'category_label' => 'Ohýbanie',
        'material' => 'DC01 oceľ / 2 mm',
        'finish' => 'Príprava na lakovanie',
        'lead_time' => '5 pracovných dní',
        'min_order' => '50 ks',
    ],
    [
        'code' => 'POL-Z-001',
        'name' => 'Zváraný rám predmontážnej jednotky',
        'category' => 'zvarence',
        'category_label' => 'Zvarence',
        'material' => 'S235 oceľové profily',
        'finish' => 'Kontrola zvarov + odhrotovanie',
        'lead_time' => '10-14 pracovných dní',
        'min_order' => '5 ks',
    ],
    [
        'code' => 'POL-P-001',
        'name' => 'Povrchovo upravený diel (komaxit)',
        'category' => 'povrch',
        'category_label' => 'Povrchová úprava',
        'material' => 'S355 oceľ / 2.5 mm',
        'finish' => 'Komaxit RAL podľa zadania',
        'lead_time' => '8-12 pracovných dní',
        'min_order' => '15 ks',
    ],
]);

Route::get('/eshop/katalog', function (Request $request) {
    $search = trim((string) $request->query('q', ''));

    $products = Product::query()
        ->when($search !== '', fn ($query) => $query
            ->where('name', 'like', "%{$search}%")
            ->orWhere('short_description', 'like', "%{$search}%")
            ->orWhere('category_label', 'like', "%{$search}%"))
        ->orderByDesc('is_featured')
        ->orderBy('name')
        ->get();

    $categories = $products
        ->map(fn (Product $product) => [
            'slug' => $product->category,
            'label' => $product->category_label,
        ])
        ->unique('slug')
        ->sortBy('label')
        ->values();

    return view('pages.catalog-index', [
        'products' => $products,
        'categories' => $categories,
        'activeSearch' => $search,
    ]);
})->name('eshop.catalog.index');

Route::get('/eshop/katalog/tlac', function () {
    $products = Product::query()
        ->orderBy('category_label')
        ->orderBy('name')
        ->get();

    return view('pages.catalog-print', [
        'products' => $products,
    ]);
})->name('eshop.catalog.print');

Route::get('/eshop/katalog/{category}', function (string $category) {
    $products = Product::query()
        ->where('category', $category)
        ->orderBy('name')
        ->get();

    abort_if($products->isEmpty(), 404);

    $categories = Product::query()
        ->select('category', 'category_label')
        ->orderBy('category_label')
        ->get()
        ->unique('category')
        ->values()
        ->map(fn (Product $product) => [
            'slug' => $product->category,
            'label' => $product->category_label,
        ]);

    return view('pages.catalog-category', [
        'products' => $products,
        'categories' => $categories,
        'activeCategory' => $category,
        'activeCategoryLabel' => $products->first()->category_label,
    ]);
})->name('eshop.catalog.category');

Route::get('/eshop/produkt/{product:slug}', function (Product $product) {
    $relatedProducts = Product::query()
        ->where('category', $product->category)
        ->whereKeyNot($product->id)
        ->orderByDesc('is_featured')
        ->orderBy('name')
        ->limit(3)
        ->get();

    return view('pages.product-show', [
        'product' => $product,
        'relatedProducts' => $relatedProducts,
    ]);
})->name('eshop.product.show');

Route::get('/polotovary/katalog', function (Request $request) use ($semifinishedCatalog) {
    $activeCategory = trim((string) $request->query('typ', ''));
    $search = trim((string) $request->query('q', ''));

    $items = $semifinishedCatalog
        ->when($activeCategory !== '', fn ($collection) => $collection->where('category', $activeCategory))
        ->when($search !== '', fn ($collection) => $collection->filter(function (array $item) use ($search) {
            return str_contains(mb_strtolower($item['name']), mb_strtolower($search))
                || str_contains(mb_strtolower($item['material']), mb_strtolower($search))
                || str_contains(mb_strtolower($item['category_label']), mb_strtolower($search));
        }))
        ->values();

    $categories = $semifinishedCatalog
        ->map(fn (array $item) => ['slug' => $item['category'], 'label' => $item['category_label']])
        ->unique('slug')
        ->sortBy('label')
        ->values();

    return view('pages.shop.semifinished-catalog', [
        'items' => $items,
        'categories' => $categories,
        'activeCategory' => $activeCategory,
        'activeSearch' => $search,
    ]);
})->name('semifinished.catalog.index');

Route::get('/polotovary/katalog/tlac', function () use ($semifinishedCatalog) {
    return view('pages.shop.semifinished-catalog-print', [
        'items' => $semifinishedCatalog,
    ]);
})->name('semifinished.catalog.print');

// Legacy URL compatibility
Route::redirect('/katalog', '/eshop/katalog', 301)->name('catalog.index');
Route::redirect('/katalog/{category}', '/eshop/katalog/{category}', 301)->name('catalog.category');
Route::redirect('/produkt/{product}', '/eshop/produkt/{product}', 301)->name('product.show');

Route::post('/kontakt', function (Request $request) {
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'message' => ['required', 'string', 'max:2000'],
    ]);

    return redirect()->route('contact')->with('status', 'Ďakujeme za správu. Ozveme sa vám čo najskôr.');
})->name('contact.submit');
