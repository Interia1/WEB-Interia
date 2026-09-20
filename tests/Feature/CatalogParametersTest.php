<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CatalogCategory;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductParameters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CatalogParametersTest extends TestCase
{
    use RefreshDatabase;

    public function test_assortment_uses_all_categories_images_and_only_public_filter_values(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $brand = $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum', 'Unused'], 'filterable' => true]);
        $private = $category->parameters()->create(['name' => 'Internal', 'type' => 'select', 'options' => ['Secret'], 'filterable' => false]);
        $product = Product::factory()->create(['category' => 'hinges', 'image_path' => 'images/hinge.jpg']);
        $product->parameterValues()->createMany([
            ['catalog_parameter_id' => $brand->id, 'value' => 'Blum'],
            ['catalog_parameter_id' => $private->id, 'value' => 'Secret'],
        ]);
        CatalogCategory::create(['slug' => 'empty', 'name' => 'Empty']);
        $response = $this->get(route('eshop.catalog.index', ['q' => 'no matching product']))->assertOk();
        $response->assertSee('data-assortment-trigger', false)
            ->assertSee('id="catalog-assortment"', false)
            ->assertSee('data-assortment-category="hinges"', false)
            ->assertSee('src="'.asset('images/hinge.jpg').'"', false)
            ->assertDontSee('Secret');
        $items = $response->viewData('assortment')->keyBy('slug');
        $this->assertSame(1, $items['hinges']['total']);
        $this->assertSame('images/hinge.jpg', $items['hinges']['image']);
        $this->assertSame([$brand->id], $items['hinges']['parameters']->pluck('id')->all());
        $this->assertSame(['Blum'], $items['hinges']['values'][$brand->id]->pluck('value')->all());
        $this->assertSame(0, $items['empty']['total']);
        $this->assertNull($items['empty']['image']);
    }

    public function test_catalog_html_and_json_responses_remain_distinct_when_returning_from_a_product(): void
    {
        $product = Product::factory()->create();
        $url = route('eshop.catalog.index', ['category' => $product->category]);

        $json = $this->getJson($url)->assertOk()
            ->assertHeader('Vary', 'Accept, X-Requested-With')
            ->assertJsonStructure(['results', 'filters', 'active', 'total']);
        $this->assertTrue($json->headers->hasCacheControlDirective('no-store'));

        $this->get(route('eshop.product.show', $product), ['Accept' => 'text/html'])->assertOk();
        $this->get($url, ['Accept' => 'text/html'])->assertOk()
            ->assertHeader('Vary', 'Accept, X-Requested-With')
            ->assertHeader('Content-Type', 'text/html; charset=utf-8')
            ->assertViewIs('pages.catalog-index')
            ->assertSee('data-live-catalog', false);

        $this->get($url, ['Accept' => '*/*', 'X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertHeader('Vary', 'Accept, X-Requested-With')
            ->assertJsonStructure(['results', 'filters', 'active', 'total']);
    }

    public function test_category_dropdown_contains_zero_one_or_ten_filter_fields(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        foreach ([0, 1, 10] as $count) {
            while ($category->parameters()->count() < $count) {
                $category->parameters()->create(['name' => 'Parameter '.$category->parameters()->count(), 'type' => 'number', 'filterable' => true]);
            }
            $response = $this->getJson(route('eshop.catalog.index', ['category' => 'hinges']))->assertOk();
            $html = $response->json('filters');
            $this->assertStringContainsString('<h2 class="shop-filters-title" title="Hinges">Hinges</h2>', $html);
            $this->assertStringContainsString('class="shop-filters-reset" data-catalog-link data-catalog-clear-filters', $html);
            $this->assertStringContainsString('aria-label="Vynulovať filtre"', $html);
            $this->assertStringNotContainsString('Filtre: Hinges', $html);
            $this->assertStringContainsString('class="shop-facet-grid"', $html);
            $this->assertSame($count, substr_count($html, '<fieldset class="shop-facet"'));
            $this->assertStringNotContainsString('shop-facet-trigger', $html);
            $this->assertStringNotContainsString('shop-more-filters', $html);
        }
    }

    public function test_seeded_categories_are_available_for_parameter_definitions(): void
    {
        $this->seed(\Database\Seeders\ProductSeeder::class);
        $this->assertSame(Product::query()->distinct()->count('category'), CatalogCategory::count());
        $category = CatalogCategory::firstOrFail();
        $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum']]);
        $this->seed(\Database\Seeders\ProductSeeder::class);
        $this->assertSame(1, $category->parameters()->count());
    }

    public function test_used_options_cannot_be_removed_and_hidden_filters_are_rejected(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $brand = $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum', 'Hettich'], 'filterable' => true]);
        $product = Product::factory()->create(['category' => 'hinges']);
        app(ProductParameters::class)->replace($product, app(ProductParameters::class)->validate('hinges', [$brand->id => 'Blum']));
        $this->actingAs(User::factory()->create(['role' => UserRole::Administrator]));
        $data = ['name' => 'Brand', 'type' => 'select', 'position' => 0, 'options_text' => 'Hettich'];
        $this->put(route('internal.catalog.parameters.update', $brand), $data)->assertSessionHasErrors('options_text');
        $this->put(route('internal.catalog.parameters.update', $brand), [...$data, 'options_text' => "Blum\nHettich"])->assertSessionHasNoErrors();
        $this->getJson(route('eshop.catalog.index', ['category' => 'hinges', 'filters' => [$brand->id => ['Blum']]]))->assertUnprocessable();
        $this->get(route('eshop.product.show', $product))->assertOk()->assertSee('Brand')->assertSee('Blum');
    }

    public function test_bulk_failure_rolls_back_all_products(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $brand = $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum', 'Hettich']]);
        $angle = $category->parameters()->create(['name' => 'Angle', 'type' => 'number', 'required' => true]);
        $first = Product::factory()->create(['category' => 'hinges']);
        $second = Product::factory()->create(['category' => 'hinges']);
        app(ProductParameters::class)->replace($first, app(ProductParameters::class)->validate('hinges', [$brand->id => 'Blum', $angle->id => 110]));
        $this->actingAs(User::factory()->contentManager()->create())
            ->put(route('internal.products.bulk-update'), ['category' => 'hinges', 'product_ids' => [$first->id, $second->id], 'parameters' => [$brand->id => 'Hettich']])->assertSessionHasErrors('parameters.'.$angle->id);
        $this->assertDatabaseHas('product_parameter_values', ['product_id' => $first->id, 'catalog_parameter_id' => $brand->id, 'value' => 'Blum']);
    }

    public function test_query_count_and_page_size_stay_bounded_for_large_catalog(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $brand = $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum', 'Hettich'], 'filterable' => true]);
        $template = Product::factory()->raw(['category' => 'hinges', 'category_label' => 'Hinges']);
        for ($chunk = 0; $chunk < 10; $chunk++) {
            $rows = [];
            $values = [];
            for ($offset = 1; $offset <= 500; $offset++) {
                $id = $chunk * 500 + $offset;
                $rows[] = [...$template, 'id' => $id, 'name' => 'Hinge '.$id, 'slug' => 'hinge-'.$id, 'price' => $id / 10];
                $values[] = ['product_id' => $id, 'catalog_parameter_id' => $brand->id, 'value' => $id % 2 ? 'Blum' : 'Hettich'];
            }
            DB::table('products')->insert($rows);
            DB::table('product_parameter_values')->insert($values);
        }
        DB::enableQueryLog();
        $started = microtime(true);
        $response = $this->getJson(route('eshop.catalog.index', ['category' => 'hinges', 'sort' => 'price_asc', 'filters' => [$brand->id => ['Blum']], 'page' => 2]));
        $elapsed = (microtime(true) - $started) * 1000;
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();
        $response->assertOk()->assertJsonPath('total', 2500);
        $this->assertSame(30, substr_count($response->json('results'), 'class="shop-product-row"'));
        $this->assertLessThanOrEqual(10, $queries);
        fwrite(STDOUT, sprintf("\nCatalog benchmark: 5000 products, %d queries, %.1f ms, %d bytes\n", $queries, $elapsed, strlen($response->getContent())));
    }

    public function test_live_response_contains_only_results_filters_and_active_values(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $brand = $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum', 'Unused'], 'filterable' => true]);
        $product = Product::factory()->create(['category' => 'hinges']);
        app(ProductParameters::class)->replace($product, app(ProductParameters::class)->validate('hinges', [$brand->id => 'Blum']));
        $response = $this->getJson(route('eshop.catalog.index', ['category' => 'hinges', 'filters' => [$brand->id => ['Blum']]]))
            ->assertOk()->assertJsonStructure(['results', 'filters', 'active', 'total'])->assertJsonPath('total', 1);
        $this->assertStringContainsString('shop-product-row', $response->json('results'));
        $this->assertStringContainsString('Blum', $response->json('filters'));
        $this->assertStringNotContainsString('Unused', $response->json('filters'));
        $this->assertStringContainsString('Brand: Blum', $response->json('active'));
        $this->assertStringNotContainsString('<html', $response->json('results'));
    }

    public function test_catalog_combines_choices_ranges_and_boolean_filters(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $brand = $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum', 'Hettich', 'Other'], 'filterable' => true]);
        $angle = $category->parameters()->create(['name' => 'Angle', 'type' => 'number', 'filterable' => true]);
        $damping = $category->parameters()->create(['name' => 'Damping', 'type' => 'boolean', 'filterable' => true]);
        $expected = [];
        foreach ([['Blum', 110, '0'], ['Hettich', 120, '0'], ['Other', 110, '0'], ['Blum', 95, '0'], ['Blum', 110, '1']] as $index => [$brandValue, $angleValue, $dampingValue]) {
            $product = Product::factory()->create(['category' => 'hinges', 'name' => 'Hinge '.$index]);
            app(ProductParameters::class)->replace($product, app(ProductParameters::class)->validate('hinges', [$brand->id => $brandValue, $angle->id => $angleValue, $damping->id => $dampingValue]));
            if ($index < 2) {
                $expected[] = $product->id;
            }
        }
        $filters = [$brand->id => ['Blum', 'Hettich'], $angle->id => ['min' => 100, 'max' => 120], $damping->id => ['0']];
        $this->get(route('eshop.catalog.index', ['category' => 'hinges', 'sort' => 'name', 'filters' => $filters]))
            ->assertOk()->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === $expected);
        $this->getJson(route('eshop.catalog.index', ['category' => 'hinges', 'filters' => [$angle->id => ['min' => 120, 'max' => 100]]]))->assertUnprocessable();
        $this->getJson(route('eshop.catalog.index', ['category' => 'boards', 'filters' => [$brand->id => ['Blum']]]))->assertUnprocessable();
    }

    public function test_catalog_paginates_without_loading_all_products(): void
    {
        Product::factory()->count(65)->create();
        $this->get(route('eshop.catalog.index', ['sort' => 'price_asc', 'page' => 2]))
            ->assertOk()->assertViewHas('products', fn ($products) => $products->count() === 30 && $products->total() === 65 && $products->currentPage() === 2);
    }

    public function test_bulk_edit_preserves_other_values_and_rejects_other_categories(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $brand = $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum', 'Hettich']]);
        $angle = $category->parameters()->create(['name' => 'Angle', 'type' => 'number', 'required' => true]);
        $products = Product::factory()->count(2)->create(['category' => 'hinges']);
        foreach ($products as $product) {
            app(ProductParameters::class)->replace($product, app(ProductParameters::class)->validate('hinges', [$brand->id => 'Blum', $angle->id => '110']));
        }
        $this->actingAs(User::factory()->contentManager()->create());
        $this->get(route('internal.products.bulk', ['category' => 'hinges']))->assertOk();
        $this->put(route('internal.products.bulk-update'), ['category' => 'hinges', 'product_ids' => $products->modelKeys(), 'parameters' => [$brand->id => 'Hettich', $angle->id => '']])->assertSessionHasNoErrors();
        foreach ($products as $product) {
            $this->assertDatabaseHas('product_parameter_values', ['product_id' => $product->id, 'catalog_parameter_id' => $brand->id, 'value' => 'Hettich']);
            $this->assertDatabaseHas('product_parameter_values', ['product_id' => $product->id, 'catalog_parameter_id' => $angle->id, 'value' => '110']);
        }
        $other = Product::factory()->create(['category' => 'boards']);
        $this->put(route('internal.products.bulk-update'), ['category' => 'hinges', 'product_ids' => [$other->id], 'parameters' => [$brand->id => 'Blum']])->assertSessionHasErrors('product_ids.0');
    }

    public function test_only_administrator_can_define_filters(): void
    {
        $this->actingAs(User::factory()->contentManager()->create())
            ->post(route('internal.catalog.store'), ['name' => 'Hinges', 'slug' => 'hinges'])->assertForbidden();
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $this->actingAs($admin)->post(route('internal.catalog.store'), ['name' => 'Hinges', 'slug' => 'hinges'])->assertSessionHasNoErrors();
        $category = CatalogCategory::firstOrFail();
        $this->post(route('internal.catalog.parameters.store', $category), ['name' => 'Brand', 'type' => 'select', 'options_text' => "Blum\nHettich", 'position' => 0, 'filterable' => 1])->assertSessionHasNoErrors();
        $this->assertSame(['Blum', 'Hettich'], $category->parameters()->first()->options);
        $this->get(route('internal.catalog.index'))->assertOk()->assertSee('Brand');
    }

    public function test_product_editor_saves_and_copies_category_parameters(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $parameter = $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum'], 'required' => true]);
        $data = Product::factory()->make(['category' => 'hinges'])->toArray();
        $this->actingAs(User::factory()->contentManager()->create());
        $this->post(route('internal.products.store'), $data)->assertSessionHasErrors('parameters.'.$parameter->id);
        $this->assertDatabaseCount('products', 0);
        $this->post(route('internal.products.store'), [...$data, 'parameters' => [$parameter->id => 'Blum']])->assertSessionHasNoErrors();
        $product = Product::firstOrFail();
        $this->assertSame('Blum', $product->parameterValues()->first()->value);
        $this->get(route('internal.products.create', ['copy' => $product->id]))->assertOk()->assertViewHas('parameterValues', [$parameter->id => 'Blum']);
        $this->get(route('internal.products.edit', $product))->assertOk()->assertSee('value="Blum" selected', false);
    }

    public function test_typed_values_are_validated_and_saved(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $angle = $category->parameters()->create(['name' => 'Angle', 'type' => 'number', 'required' => true]);
        $damping = $category->parameters()->create(['name' => 'Damping', 'type' => 'boolean']);
        $product = Product::factory()->create(['category' => 'hinges']);
        $service = app(ProductParameters::class);
        $values = $service->validate('hinges', [$angle->id => '110.5', $damping->id => '0']);
        $service->replace($product, $values);

        $this->assertDatabaseHas('product_parameter_values', ['product_id' => $product->id, 'numeric_value' => 110.5]);
        $this->assertDatabaseHas('product_parameter_values', ['catalog_parameter_id' => $damping->id, 'value' => '0']);
    }

    public function test_required_parameters_cannot_be_omitted(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum'], 'required' => true]);
        $this->expectException(ValidationException::class);
        app(ProductParameters::class)->validate('hinges', []);
    }

    public function test_values_from_other_categories_are_rejected(): void
    {
        $category = CatalogCategory::create(['slug' => 'hinges', 'name' => 'Hinges']);
        $parameter = $category->parameters()->create(['name' => 'Brand', 'type' => 'select', 'options' => ['Blum']]);
        $this->expectException(ValidationException::class);
        app(ProductParameters::class)->validate('boards', [$parameter->id => 'Blum']);
    }
}
