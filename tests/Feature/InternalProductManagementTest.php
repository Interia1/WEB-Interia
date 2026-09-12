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
}
