<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_index_renders_seeded_products(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get('/katalog')
            ->assertOk()
            ->assertSee('Katalóg produktov')
            ->assertSee('ALU Frame 100')
            ->assertSee('Steel Strong 50');
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