<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_categories_button_has_no_dropdown_menu(): void
    {
        Product::factory()->create(['category' => 'kovanie', 'category_label' => 'Kovanie']);
        $url = route('eshop.catalog.index', ['q' => 'Panel', 'sort' => 'price_asc']);
        $this->get($url)->assertOk()
            ->assertDontSee('aria-label="Výber kategórie"', false)
            ->assertSee('<h2 id="catalog-categories-title" class="shop-category-title">Kategórie</h2>', false)
            ->assertSee('class="shop-category-tabs" aria-labelledby="catalog-categories-title"', false)
            ->assertSee('>Všetky</a>', false)
            ->assertSee('<a class="shop-favorites-label" role="link" aria-disabled="true" tabindex="0" title="Obľúbené produkty pripravujeme"><i class="bi bi-star" aria-hidden="true"></i><span>Obľúbené</span><i class="bi bi-chevron-down" aria-hidden="true"></i></a>', false)
            ->assertSee('type="button" data-category-page-start', false)
            ->assertSee('type="button" data-category-page-end', false)
            ->assertDontSee('<span>Všetky</span><i class="bi bi-chevron-down"', false);
        $response = $this->getJson($url)->assertOk();
        $this->assertStringNotContainsString('data-catalog-category=', $response->json('filters'));
    }

    public function test_eshop_heading_and_reset_link_to_catalog_without_filters(): void
    {
        $url = route('eshop.catalog.index', [], false);
        $this->get(route('eshop.catalog.index', ['q' => 'Panel', 'category' => 'kovanie', 'sort' => 'price_desc']))
            ->assertOk()
            ->assertSee('<h1><a class="shop-catalog-home" href="'.$url.'"', false)
            ->assertSee('aria-label="E-shop: späť na začiatok"', false)
            ->assertSee('<a href="'.$url.'" class="btn btn-outline-secondary shop-icon-button" aria-label="Obnoviť e-shop" title="Späť na začiatok e-shopu">', false);

        $this->get($url)->assertOk()
            ->assertViewHas('activeSearch', '')
            ->assertViewHas('activeCategory', '')
            ->assertViewHas('activeSort', 'recommended')
            ->assertViewHas('activeFilters', []);
    }

    public function test_eshop_filters_search_and_category_and_sorts_prices(): void
    {
        $expensive = Product::factory()->create(['name' => 'Panel premium', 'category' => 'kovanie', 'price' => 100]);
        $cheap = Product::factory()->create(['name' => 'Panel basic', 'category' => 'kovanie', 'price' => 9]);
        Product::factory()->create(['name' => 'Panel other', 'category' => 'ostatne', 'price' => 1]);
        Product::factory()->create(['name' => 'Doska', 'category' => 'kovanie', 'price' => 2]);

        foreach (['price_asc' => [$cheap->id, $expensive->id], 'price_desc' => [$expensive->id, $cheap->id], 'name' => [$cheap->id, $expensive->id]] as $sort => $expected) {
            $this->get(route('eshop.catalog.index', ['q' => 'Panel', 'category' => 'kovanie', 'sort' => $sort]))
                ->assertOk()
                ->assertViewHas('products', fn ($products) => $products->pluck('id')->values()->all() === $expected)
                ->assertViewHas('activeSearch', 'Panel')
                ->assertViewHas('activeCategory', 'kovanie')
                ->assertViewHas('activeSort', $sort)
                ->assertViewHas('categories', fn ($categories) => $categories->pluck('slug')->contains('ostatne'));
        }
    }

    public function test_filter_path_and_product_count_are_below_the_catalog_heading(): void
    {
        Product::factory()->create(['name' => 'Panel']);
        $response = $this->get(route('eshop.catalog.index', ['q' => 'Panel']))->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);

        $this->assertSame(1, $xpath->query('//div[@class="shop-catalog-heading"]/span[@data-catalog-total]')->length);
        $this->assertSame(1, $xpath->query('//details[@data-catalog-path]/following-sibling::*[1][@data-catalog-total]')->length);
        $this->assertSame(1, $xpath->query('//div[@class="shop-catalog-heading"]/details[@data-catalog-path]/div[@data-catalog-active]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="catalog-active-filters"]')->length);
        $this->assertSame(1, $xpath->query('//details[@data-catalog-path]//a[@class="shop-active-filter"]')->length);
    }

    public function test_eshop_keeps_categories_when_no_products_match(): void
    {
        Product::factory()->create(['category' => 'panely']);

        $this->get(route('eshop.catalog.index', ['q' => 'missing-product']))
            ->assertOk()
            ->assertSee('Nenašli sa žiadne produkty')
            ->assertViewHas('products', fn ($products) => $products->isEmpty())
            ->assertViewHas('categories', fn ($categories) => $categories->pluck('slug')->contains('panely'));
    }

    public function test_eshop_displays_compact_product_rows(): void
    {
        $product = Product::factory()->create(['price' => 12.50, 'availability' => 'Skladom', 'image_path' => 'images/product.jpg']);

        $this->get(route('eshop.catalog.index'))
            ->assertOk()
            ->assertSee('class="shop-product-row"', false)
            ->assertSee('id="catalog-category"', false)
            ->assertSee('id="catalog-sort"', false)
            ->assertSee($product->name)
            ->assertSee('12,50 EUR')
            ->assertSee('Skladom')
            ->assertSee('src="'.asset($product->image_path).'"', false)
            ->assertSee('href="'.e(route('eshop.product.show', ['product' => $product->slug, 'return_to' => route('eshop.catalog.index', [], false).'#catalog-product-'.$product->id], false)).'"', false)
            ->assertDontSee('class="card h-100 shadow-sm"', false)
            ->assertDontSee($product->short_description);
    }

    public function test_catalog_index_renders_seeded_products(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get('/katalog')
            ->assertOk()
            ->assertSee('E-shop')
            ->assertSee('ALU Frame 100')
            ->assertSee('Steel Strong 50');
    }

    public function test_product_return_preserves_filters_and_related_product_context(): void
    {
        $product = Product::factory()->create(['category' => 'kovanie']);
        $related = Product::factory()->create(['category' => 'kovanie']);
        $returnTo = route('eshop.catalog.index', ['category' => 'kovanie', 'q' => 'Panel', 'sort' => 'price_asc', 'page' => 2, 'filters' => [1 => ['Blum']]], false).'#catalog-product-'.$product->id;

        $this->get(route('eshop.product.show', ['product' => $product->slug, 'return_to' => $returnTo]))
            ->assertOk()
            ->assertSee('spat na vysledky')
            ->assertSee('bi-arrow-90deg-left shop-product-back-icon', false)
            ->assertSee('data-product-back href="'.e($returnTo).'"', false)
            ->assertSee('href="'.e(route('eshop.product.show', ['product' => $related->slug, 'return_to' => $returnTo], false)).'"', false)
            ->assertSee('aria-label="Navigácia produktu"', false)
            ->assertDontSee('data-catalog-form', false);
    }

    public function test_direct_product_visits_and_invalid_return_urls_fall_back_to_category(): void
    {
        $product = Product::factory()->create(['category' => 'kovanie']);
        $catalog = route('eshop.catalog.index', [], false);
        foreach ([null, 'https://example.com'.$catalog, '//example.com'.$catalog, '/login', $catalog.'/other', ['invalid'], $catalog."?q=bad\nvalue"] as $returnTo) {
            $this->get(route('eshop.product.show', ['product' => $product->slug, 'return_to' => $returnTo]))
                ->assertOk()
                ->assertSee('Späť do kategórie')
                ->assertDontSee('spat na vysledky')
                ->assertSee('data-product-back href="'.e(route('eshop.catalog.index', ['category' => 'kovanie'], false)).'"', false);
        }
    }

    public function test_filtered_catalog_product_links_include_current_page_and_sort(): void
    {
        Product::factory()->count(31)->create(['category' => 'kovanie', 'name' => 'Panel']);
        $query = ['q' => 'Panel', 'category' => 'kovanie', 'sort' => 'price_asc', 'page' => 2];
        $response = $this->get(route('eshop.catalog.index', $query))->assertOk();
        $product = $response->viewData('products')->first();
        $returnTo = route('eshop.catalog.index', $query, false).'#catalog-product-'.$product->id;
        $link = 'href="'.e(route('eshop.product.show', ['product' => $product->slug, 'return_to' => $returnTo], false)).'"';

        $response->assertSee($link, false);
        $json = $this->getJson(route('eshop.catalog.index', $query))->assertOk();
        $this->assertStringContainsString($link, $json->json('results'));
    }

    public function test_catalog_category_renders_only_category_products(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get('/katalog/hlinikove-systemy')
            ->assertOk()
            ->assertSee('Hliníkové systémy')
            ->assertSee('ALU Frame 100')
            ->assertDontSee('Steel Strong 50');
    }

    public function test_product_detail_renders_by_slug(): void
    {
        $this->seed(ProductSeeder::class);

        $product = Product::query()->where('slug', 'alu-frame-100')->firstOrFail();

        $this->get(route('product.show', ['product' => $product->slug]))
            ->assertOk()
            ->assertSee('ALU Frame 100')
            ->assertSee('Základné parametre');
    }

    public function test_unknown_category_returns_not_found(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get('/katalog/neexistujuca-kategoria')->assertNotFound();
    }

    public function test_unknown_product_slug_returns_not_found(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get('/produkt/neexistujuci-produkt')->assertNotFound();
    }
}