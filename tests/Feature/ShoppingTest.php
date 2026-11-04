<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShoppingTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_shows_product_order_details_and_requires_login(): void
    {
        $product = Product::factory()->create([
            'sku' => 'TEST-123',
            'minimum_order_quantity' => 3,
        ]);
        $this->get(route('eshop.catalog.index'))
            ->assertOk()
            ->assertSee('<span>Kód tovaru:</span>', false)
            ->assertSee('class="shop-product-code-value" title="TEST-123">TEST-123</span>', false)
            ->assertSee('Min. 3 ks')
            ->assertSee('/js/product-name-wrap.js', false)
            ->assertSee('Partnerská cena: nedostupná')
            ->assertSee('Prihláste sa pre nákup a obľúbené')
            ->assertSeeInOrder(['class="shop-product-favorite"', 'class="shop-product-image"', 'class="shop-product-info"'], false)
            ->assertDontSee('name="quantity"', false);
        $this->get(route('eshop.product.show', $product))
            ->assertOk()
            ->assertSee('TEST-123')
            ->assertSee('3 ks')
            ->assertSee('Partnerská cena');
        $this->post(route('eshop.cart.add', $product), ['quantity' => 3])->assertRedirect(route('login'));
        $this->post(route('eshop.favorites.update', $product), ['favorite' => 1])->assertRedirect(route('login'));
        $this->get(route('eshop.favorites'))->assertRedirect(route('login'));
    }

    public function test_favorites_are_private_persistent_and_visible_in_ajax_results(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create();
        $this->actingAs($user)->post(route('eshop.favorites.update', $product), ['favorite' => 1])->assertRedirect();
        $this->post(route('eshop.favorites.update', $product), ['favorite' => 1])->assertRedirect();
        $this->assertSame(1, $user->favoriteProducts()->count());
        $this->get(route('eshop.favorites'))->assertSee($product->name)->assertSee('aria-pressed="true"', false)
            ->assertSee('/js/product-name-wrap.js', false);
        $this->getJson(route('eshop.catalog.index'))->assertOk()->assertJsonPath('total', 1)
            ->assertSee('bi-star-fill');

        $this->actingAs($other)->get(route('eshop.favorites'))->assertDontSee($product->name);
        $this->actingAs($user)->post(route('eshop.favorites.update', $product), ['favorite' => 0])->assertRedirect();
        $this->assertSame(0, $user->favoriteProducts()->count());
    }

    public function test_product_code_appears_beneath_the_name_before_quantity_information(): void
    {
        $product = Product::factory()->create(['sku' => 'TEST-123']);
        $guestHtml = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $user = User::factory()->create();
        $user->favoriteProducts()->attach($product);
        $this->actingAs($user);
        $catalogHtml = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $favoritesHtml = $this->get(route('eshop.favorites'))->assertOk()->getContent();
        $ajaxHtml = $this->getJson(route('eshop.catalog.index'))->assertOk()->json('results');

        foreach ([$guestHtml, $catalogHtml, $favoritesHtml, $ajaxHtml] as $html) {
            $document = new \DOMDocument;
            @$document->loadHTML($html);
            $xpath = new \DOMXPath($document);
            $code = $xpath->query('//div[@class="shop-product-info"]/h2/following-sibling::span[@class="shop-product-meta shop-product-code"]');

            $this->assertCount(1, $code);
            $this->assertStringContainsString('TEST-123', $code->item(0)->textContent);
            $this->assertCount(1, $xpath->query('//span[@class="shop-product-meta shop-product-code"]/span[@class="shop-product-code-value"]'));
            $this->assertCount(1, $xpath->query('//div[@class="shop-product-info"]/following-sibling::div[1][@class="shop-product-details"]/div[@class="shop-product-actions"]/span[@class="shop-product-meta"]'));
            $this->assertCount(0, $xpath->query('//div[@class="shop-product-actions"]//span[@class="shop-product-meta shop-product-code"]'));
        }
    }

    public function test_cart_accumulates_valid_quantities_and_is_isolated_per_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create(['minimum_order_quantity' => 3, 'price' => '12.50', 'currency' => 'EUR']);
        $this->actingAs($user)->get(route('eshop.catalog.index'))
            ->assertSee('name="quantity" type="number" min="3"', false)
            ->assertSee('value="3"', false)
            ->assertSee('<button type="submit" class="btn btn-primary shop-icon-button" aria-label="Použiť filtre"', false)
            ->assertSee('<button class="btn btn-primary shop-cart-primary" type="submit" aria-label="Pridať do predvoleného košíka:', false)
            ->assertSeeInOrder(['<span>Produkt</span>', '<span>Nákup</span>', '<span>Kategória</span>', '<span>Dostupnosť</span>', '<span>Cena</span>'], false)
            ->assertSeeInOrder(['class="shop-product-favorite-form"', 'class="shop-product-image"', 'class="shop-product-info"', 'class="shop-product-actions"', 'class="shop-product-category"', 'class="shop-product-availability', 'class="shop-product-pricing"'], false);
        $this->post(route('eshop.cart.add', $product), ['quantity' => 2])->assertSessionHasErrors('quantity');
        $this->post(route('eshop.cart.add', $product), ['quantity' => 3.5])->assertSessionHasErrors('quantity');
        $this->assertSame(0, $user->shoppingCarts()->where('is_default', true)->firstOrFail()->products()->count());
        $this->post(route('eshop.cart.add', $product), ['quantity' => 3])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $product), ['quantity' => 4])->assertSessionHasNoErrors();
        $this->assertSame(7, $user->shoppingCarts()->where('is_default', true)->firstOrFail()->products()->first()->pivot->quantity);
        $this->get('/')->assertSee('87,50 €')->assertSee('7 ks × 12,50 EUR')->assertSee($product->name);
        $this->actingAs($other)->get('/')->assertSee('Košík je prázdny.')->assertDontSee('7 ks × 12,50 EUR');
        $this->actingAs($user)->delete(route('eshop.cart.remove', $product))->assertRedirect();
        $this->assertSame(0, $user->shoppingCarts()->where('is_default', true)->firstOrFail()->products()->count());
    }

    public function test_cart_limits_and_currency_totals(): void
    {
        $user = User::factory()->create();
        $eur = Product::factory()->create(['price' => '0.10', 'currency' => 'EUR']);
        $usd = Product::factory()->create(['price' => '2.00', 'currency' => 'USD']);
        $this->actingAs($user)->post(route('eshop.cart.add', $eur), ['quantity' => 100000])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $eur), ['quantity' => 1])->assertSessionHasErrors('quantity');
        $this->post(route('eshop.cart.add', $usd), ['quantity' => 2])->assertSessionHasNoErrors();
        $this->get('/')->assertSee('10 000,00 €')->assertSee('4,00 USD');
        $this->assertSame(100000, $user->shoppingCarts()->where('is_default', true)->firstOrFail()->products()->whereKey($eur->id)->first()->pivot->quantity);
    }

    public function test_cart_and_favorites_remain_attached_to_the_account_after_logout(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->actingAs($user)->post(route('eshop.cart.add', $product), ['quantity' => 1])->assertSessionHasNoErrors();
        $this->post(route('eshop.favorites.update', $product), ['favorite' => 1])->assertSessionHasNoErrors();
        $this->post(route('logout'))->assertRedirect();
        $this->get('/')->assertDontSee($product->name);

        $this->actingAs($user)->get('/')->assertSee($product->name);
        $this->get(route('eshop.favorites'))->assertSee($product->name);
    }

    public function test_selected_cart_is_independent_and_available_in_all_product_lists(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $user->favoriteProducts()->attach($product);
        $default = $user->shoppingCarts()->where('is_default', true)->firstOrFail();
        $other = $user->shoppingCarts()->create(['name' => 'Druhy kosik', 'is_default' => false]);
        $this->actingAs($user);

        foreach ([
            $this->get(route('eshop.catalog.index'))->assertOk()->getContent(),
            $this->get(route('eshop.favorites'))->assertOk()->getContent(),
            $this->getJson(route('eshop.catalog.index'))->assertOk()->json('results'),
        ] as $html) {
            $this->assertStringContainsString('name="cart_id" value="'.$other->id.'"', $html);
            $this->assertStringContainsString('class="shop-cart-options-title">Pridať do košíka:</span>', $html);
            $this->assertStringContainsString('class="shop-cart-option"><span>Druhy kosik</span><span class="shop-cart-option-width" aria-hidden="true">Druhy kosik</span></button>', $html);
            $this->assertStringContainsString('class="shop-cart-option"><span>Môj košík · predvolený</span><span class="shop-cart-option-width" aria-hidden="true">Môj košík · predvolený</span></button>', $html);
        }

        $this->post(route('eshop.cart.add', $product), ['quantity' => 100000])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $product), ['quantity' => 4, 'cart_id' => $other->id])
            ->assertSessionHasNoErrors()->assertSessionHas('status', 'Produkt bol pridaný do košíka „Druhy kosik“.');
        $this->post(route('eshop.cart.add', $product), ['quantity' => 2, 'cart_id' => $other->id])->assertSessionHasNoErrors();
        $this->assertSame(100000, $default->products()->firstOrFail()->pivot->quantity);
        $this->assertSame(6, $other->products()->firstOrFail()->pivot->quantity);
        $this->assertTrue($default->fresh()->is_default);
        $this->get('/')->assertSee('Druhy kosik')->assertSee('100000 ks', false);
        $this->post(route('eshop.carts.default', $other))->assertSessionHasNoErrors();
        $this->get('/')->assertSee('6 ks')->assertDontSee('100000 ks');
        $this->delete(route('eshop.cart.remove', $product), ['cart_id' => $default->id])->assertSessionHasNoErrors();
        $this->assertSame(0, $default->products()->count());
        $this->assertSame(6, $other->products()->firstOrFail()->pivot->quantity);
    }

    public function test_cart_management_validation_switching_and_confirmed_deletion(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $default = $user->shoppingCarts()->firstOrFail();
        $this->actingAs($user);
        $this->get(route('eshop.carts.index'))->assertOk()->assertSee('Môj košík');
        $this->post(route('eshop.carts.store'), ['name' => ''])->assertSessionHasErrors('name');
        $this->post(route('eshop.carts.store'), ['name' => str_repeat('a', 81)])->assertSessionHasErrors('name');
        $this->post(route('eshop.carts.store'), ['name' => 'Môj košík'])->assertSessionHasErrors('name');
        $this->post(route('eshop.carts.store'), ['name' => 'Práca'])->assertSessionHasNoErrors();
        $other = $user->shoppingCarts()->where('name', 'Práca')->firstOrFail();
        $this->put(route('eshop.carts.rename', $other), ['name' => 'Môj košík'])->assertSessionHasErrors('name');
        $this->put(route('eshop.carts.rename', $other), ['name' => 'Nový'])->assertSessionHasNoErrors();
        $this->post(route('eshop.carts.default', $default))->assertSessionHasNoErrors();
        $this->assertSame(1, $user->shoppingCarts()->where('is_default', true)->count());
        $this->post(route('eshop.carts.default', $other))->assertSessionHasNoErrors();
        $this->assertTrue($other->fresh()->is_default);
        $this->post(route('eshop.cart.add', $product), ['quantity' => 2])->assertSessionHasNoErrors();
        $this->get(route('eshop.carts.confirm-delete', $other))
            ->assertOk()->assertSee('Nový')->assertSee('Potvrdiť odstránenie');
        $this->assertSame(1, $other->products()->count());
        $this->delete(route('eshop.carts.destroy', $other))->assertRedirect(route('eshop.carts.index'));
        $this->assertTrue($default->fresh()->is_default);
        $this->assertDatabaseMissing('shopping_cart_items', ['shopping_cart_id' => $other->id]);
        $this->delete(route('eshop.carts.destroy', $default))->assertRedirect(route('eshop.carts.index'));
        $this->assertSame(1, $user->shoppingCarts()->count());
        $this->assertTrue($user->shoppingCarts()->firstOrFail()->is_default);
        $this->assertSame(0, $user->shoppingCarts()->firstOrFail()->products()->count());
    }

    public function test_foreign_carts_cannot_be_accessed_or_changed(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $foreign = $other->shoppingCarts()->firstOrFail();
        $product = Product::factory()->create();
        $foreign->products()->attach($product, ['quantity' => 5]);
        $this->actingAs($user);
        $this->post(route('eshop.cart.add', $product), ['quantity' => 1, 'cart_id' => $foreign->id])->assertNotFound();
        $this->delete(route('eshop.cart.remove', $product), ['cart_id' => $foreign->id])->assertNotFound();
        $this->put(route('eshop.carts.rename', $foreign), ['name' => 'Iný'])->assertNotFound();
        $this->post(route('eshop.carts.default', $foreign))->assertNotFound();
        $this->get(route('eshop.carts.confirm-delete', $foreign))->assertNotFound();
        $this->delete(route('eshop.carts.destroy', $foreign))->assertNotFound();
        $this->assertSame(5, $foreign->products()->firstOrFail()->pivot->quantity);
        $this->assertTrue($foreign->fresh()->is_default);
        $this->post(route('eshop.carts.store'), ['name' => 'Môj košík'])->assertSessionHasErrors('name');
        $this->post(route('logout'));
        $this->get(route('eshop.carts.index'))->assertRedirect(route('login'));
        $this->post(route('eshop.carts.store'), ['name' => 'Bez účtu'])->assertRedirect(route('login'));
    }

    public function test_cart_migration_preserves_data_and_refuses_lossy_rollback(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $products = Product::factory()->count(2)->create();
        $migration = require database_path('migrations/2026_11_04_000000_create_multiple_shopping_carts.php');
        $migration->down();
        $rows = [
            ['user_id' => $user->id, 'product_id' => $products[0]->id, 'quantity' => 3, 'created_at' => '2024-01-01 10:00:00', 'updated_at' => '2024-01-02 10:00:00'],
            ['user_id' => $user->id, 'product_id' => $products[1]->id, 'quantity' => 7, 'created_at' => '2024-02-01 10:00:00', 'updated_at' => '2024-02-02 10:00:00'],
            ['user_id' => $other->id, 'product_id' => $products[0]->id, 'quantity' => 11, 'created_at' => '2024-03-01 10:00:00', 'updated_at' => '2024-03-02 10:00:00'],
        ];
        DB::table('cart_items')->insert($rows);
        $migration->up();
        $this->assertFalse(Schema::hasTable('cart_items'));
        $this->assertSame(2, DB::table('shopping_carts')->where('is_default', true)->count());
        foreach ($rows as $row) {
            $cart = DB::table('shopping_carts')->where('user_id', $row['user_id'])->first();
            $this->assertDatabaseHas('shopping_cart_items', [
                'shopping_cart_id' => $cart->id,
                'product_id' => $row['product_id'],
                'quantity' => $row['quantity'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ]);
        }
        $extra = $user->shoppingCarts()->create(['name' => 'Ďalší', 'is_default' => false]);
        try {
            $migration->down();
            $this->fail('Rollback by stratil údaje ďalšieho košíka.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('viaceré košíky', $exception->getMessage());
        }
        $this->assertFalse(Schema::hasTable('cart_items'));
        $this->assertSame(3, DB::table('shopping_cart_items')->count());
        $extra->delete();
        $migration->down();
        foreach ($rows as $row) {
            $this->assertDatabaseHas('cart_items', $row);
        }
        $migration->up();
    }
}
