<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_manager_can_create_update_and_delete_a_product(): void
    {
        $worker = User::factory()->contentManager()->create();
        $data = [
            'name' => 'Servis kovania',
            'slug' => 'servis-kovania',
            'sku' => 'SERV-001',
            'minimum_order_quantity' => 3,
            'category' => 'servis',
            'category_label' => 'Servis',
            'price' => '49.90',
            'currency' => 'eur',
            'availability' => 'Na objednávku',
            'short_description' => 'Odborná kontrola a nastavenie nábytkového kovania.',
            'description' => 'Servis zahŕňa kontrolu, nastavenie a odporúčanie ďalšieho postupu.',
            'image_path' => null,
            'is_featured' => '1',
        ];

        $this->actingAs($worker)->post(route('internal.products.store'), $data)->assertRedirect();

        $product = Product::where('slug', 'servis-kovania')->firstOrFail();
        $this->assertSame('EUR', $product->currency);
        $this->assertSame('SERV-001', $product->sku);
        $this->assertSame(3, $product->minimum_order_quantity);
        $this->assertTrue($product->is_featured);
        $this->get(route('eshop.catalog.index'))->assertSee('Servis kovania');

        $data['name'] = 'Kompletný servis kovania';
        $this->actingAs($worker)
            ->put(route('internal.products.update', $product), $data)
            ->assertSessionHasNoErrors();
        $this->assertSame('Kompletný servis kovania', $product->fresh()->name);

        $this->actingAs($worker)
            ->delete(route('internal.products.destroy', $product))
            ->assertRedirect(route('internal.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_customer_cannot_modify_products(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->post(route('internal.products.store'), [])
            ->assertForbidden();
    }

    public function test_product_codes_are_limited_to_ten_characters(): void
    {
        $worker = User::factory()->contentManager()->create();
        $data = Product::factory()->make()->toArray();
        $data['sku'] = str_repeat('A', 11);

        $this->actingAs($worker)->get(route('internal.products.create'))
            ->assertOk()
            ->assertSee('name="sku" maxlength="10"', false);
        $this->post(route('internal.products.store'), $data)->assertSessionHasErrors('sku');
        $this->assertDatabaseMissing('products', ['slug' => $data['slug']]);

        $data['sku'] = str_repeat('A', 10);
        $this->post(route('internal.products.store'), $data)->assertSessionHasNoErrors();
        $product = Product::where('slug', $data['slug'])->firstOrFail();
        $this->assertSame($data['sku'], $product->sku);

        $data['sku'] = str_repeat('B', 11);
        $this->put(route('internal.products.update', $product), $data)->assertSessionHasErrors('sku');
        $this->assertSame(str_repeat('A', 10), $product->fresh()->sku);
    }

    public function test_existing_long_product_codes_are_preserved_until_corrected(): void
    {
        $worker = User::factory()->contentManager()->create();
        $product = Product::factory()->create(['sku' => 'ABCDEFGHIJK']);
        $data = $product->toArray();

        $this->actingAs($worker)->get(route('internal.products.edit', $product))
            ->assertOk()
            ->assertSee('value="ABCDEFGHIJK"', false);
        $this->put(route('internal.products.update', $product), $data)->assertSessionHasErrors('sku');
        $this->assertSame('ABCDEFGHIJK', $product->fresh()->sku);

        $data['sku'] = 'ABCDEFGHIJ';
        $this->put(route('internal.products.update', $product), $data)->assertSessionHasNoErrors();
        $this->assertSame('ABCDEFGHIJ', $product->fresh()->sku);
    }
}
