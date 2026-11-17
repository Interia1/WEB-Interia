<?php

namespace Tests\Feature;

use App\Models\CatalogCategory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShoppingTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_allows_guest_cart_but_favorites_require_login(): void
    {
        $product = Product::factory()->create([
            'sku' => 'TEST-123',
            'minimum_order_quantity' => 3,
        ]);
        $this->get(route('eshop.catalog.index'))
            ->assertOk()
            ->assertSee('<span>Kód tovaru:</span>', false)
            ->assertSee('class="shop-product-code-value" title="TEST-123">TEST-123</span>', false)
            ->assertDontSee('Min. 3 ks')
            ->assertSee('/js/product-name-wrap.js', false)
            ->assertSee('Partnerská cena: nedostupná')
            ->assertSee('Prihláste sa pre obľúbené')
            ->assertSeeInOrder(['class="shop-product-favorite"', 'class="shop-product-image"', 'class="shop-product-info"'], false)
            ->assertSee('name="quantity" type="hidden" value="3"', false)
            ->assertSee('title="Pridať 3 ks do košíka Môj košík"', false)
            ->assertSee('<span class="shop-cart-primary-name">Môj košík</span>', false)
            ->assertDontSee('data-cart-choices', false);
        $this->get(route('eshop.product.show', $product))
            ->assertOk()
            ->assertSee('TEST-123')
            ->assertSee('3 ks')
            ->assertSee('Partnerská cena');
        $this->post(route('eshop.favorites.update', $product), ['favorite' => 1])->assertRedirect(route('login'));
        $this->get(route('eshop.favorites'))->assertRedirect(route('login'));
    }

    public function test_guest_cart_accumulates_displays_and_removes_products(): void
    {
        $product = Product::factory()->create(['minimum_order_quantity' => 3, 'price' => '12.50', 'currency' => 'EUR']);
        $usd = Product::factory()->create(['price' => '2.00', 'currency' => 'USD']);
        $ajaxHtml = $this->getJson(route('eshop.catalog.index'))->assertOk()->json('results');
        $this->assertStringContainsString('name="quantity"', $ajaxHtml);
        $this->post(route('eshop.cart.add', $product), ['quantity' => 2])->assertSessionHasErrors('quantity');
        $this->post(route('eshop.cart.add', $product), ['quantity' => 3.5])->assertSessionHasErrors('quantity');
        $this->post(route('eshop.cart.add', $product), ['quantity' => 3])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $product), ['quantity' => 4])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $usd), ['quantity' => 2])->assertSessionHasNoErrors();
        $this->get('/')->assertSeeInOrder(['Obsah košíka', $product->name, '7 ks × 12,50 EUR'])
            ->assertSee('<div class="cart-menu-item-meta">7 ks × 12,50 EUR</div>', false)
            ->assertSee('87,50 €')->assertSee('4,00 USD')
            ->assertSee('po prihlásení sa neprenesie do účtu');
        $this->delete(route('eshop.cart.remove', $product))->assertSessionHasNoErrors();
        $this->get('/')->assertDontSee('7 ks × 12,50 EUR')->assertSee('4,00 USD');
        $this->delete(route('eshop.cart.remove', $usd))->assertSessionHasNoErrors();
        $this->get('/')->assertSeeInOrder(['Obsah košíka', 'Košík je prázdny.']);
    }

    public function test_cart_name_adds_the_minimum_quantity_to_the_chosen_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['minimum_order_quantity' => 6, 'order_increment' => 3]);
        $other = $user->shoppingCarts()->create(['name' => 'Druhý', 'is_default' => false]);
        $html = $this->actingAs($user)->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $inputs = $xpath->query('//*[@id="catalog-product-'.$product->id.'"]//form[contains(@class, "shop-product-cart-form")]/input[@name="quantity" and @type="hidden"]');
        $this->assertCount(1, $inputs);
        $quantity = $inputs->item(0)->getAttribute('value');
        $this->assertSame('6', $quantity);

        $this->post(route('eshop.cart.add', $product), ['quantity' => $quantity])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $product), ['quantity' => $quantity, 'cart_id' => $other->id])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $product), ['quantity' => $quantity, 'cart_id' => $other->id])->assertSessionHasNoErrors();
        $html = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $this->assertCartBadge($html, $product, null, 6);
        $this->assertCartBadge($html, $product, $other->id, 12);
    }

    public function test_batch_quantity_unit_and_surcharge_are_enforced_and_displayed(): void
    {
        $category = CatalogCategory::create(['slug' => 'small-parts', 'name' => 'Drobné diely']);
        $surcharge = $category->parameters()->create([
            'name' => 'S príplatkom',
            'type' => 'select',
            'options' => ['Atypický rozmer', 'Expresná výroba', 'Neštandardný materiál', 'Špeciálna povrchová úprava', 'Malosériová výroba'],
            'filterable' => false,
        ]);
        $surchargeWithoutVat = $category->parameters()->create(['name' => 'Príplatok bez DPH', 'type' => 'number', 'unit' => 'EUR', 'filterable' => false]);
        $surchargeWithVat = $category->parameters()->create(['name' => 'Príplatok s DPH', 'type' => 'number', 'unit' => 'EUR', 'filterable' => false]);
        $product = Product::factory()->create([
            'category' => $category->slug,
            'category_label' => $category->name,
            'minimum_order_quantity' => 100,
            'sales_unit' => 'ks',
            'order_increment' => 100,
        ]);
        $product->parameterValues()->create([
            'catalog_parameter_id' => $surcharge->id,
            'value' => 'Atypický rozmer',
        ]);
        $product->parameterValues()->createMany([
            ['catalog_parameter_id' => $surchargeWithoutVat->id, 'value' => '20.00', 'numeric_value' => '20.00'],
            ['catalog_parameter_id' => $surchargeWithVat->id, 'value' => '24.00', 'numeric_value' => '24.00'],
        ]);

        $this->get(route('eshop.catalog.index'))
            ->assertOk()
            ->assertDontSee('Min. 100 ks')
            ->assertDontSee('Dávka 100 ks')
            ->assertSee('name="quantity" type="hidden" value="100"', false)
            ->assertSeeInOrder([
                '<strong class="shop-product-surcharge">',
                '<span>S príplatkom: Atypický rozmer</span>',
                '<span>20,00 EUR bez DPH / 24,00 EUR s DPH</span>',
                '</strong>',
            ], false);
        $this->post(route('eshop.cart.add', $product), ['quantity' => 150])->assertSessionHasErrors('quantity');
        $this->post(route('eshop.cart.add', $product), ['quantity' => 100])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.adjust', $product), ['change' => 'increase'])->assertSessionHasNoErrors();
        $this->get('/')->assertSee('200 ks ×');
        $this->post(route('eshop.cart.adjust', $product), ['change' => 'decrease'])->assertSessionHasNoErrors();
        $this->get('/')->assertSee('100 ks ×');
    }

    public function test_guest_cart_limits_and_cannot_access_account_carts(): void
    {
        $user = User::factory()->create();
        $cart = $user->shoppingCarts()->firstOrFail();
        $product = Product::factory()->create();
        $cart->products()->attach($product, ['quantity' => 5]);

        $this->post(route('eshop.cart.add', $product), ['quantity' => 100000])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $product), ['quantity' => 1])->assertSessionHasErrors('quantity');
        $this->post(route('eshop.cart.add', $product), ['quantity' => 1, 'cart_id' => $cart->id])->assertNotFound();
        $this->delete(route('eshop.cart.remove', $product), ['cart_id' => $cart->id])->assertNotFound();
        $this->assertSame(5, $cart->products()->firstOrFail()->pivot->quantity);
        $this->get('/')->assertSee('100000 ks');

        $this->flushSession();
        $this->get('/')->assertSee('Košík je prázdny.');
        $this->actingAs($user)->get('/')->assertSee('5 ks');
    }

    public function test_guest_cart_is_separate_from_account_after_login(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->post(route('eshop.cart.add', $product), ['quantity' => 2])->assertSessionHasNoErrors();
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertRedirect();
        $this->get('/')->assertSee('Košík je prázdny.')->assertDontSee('2 ks ×');
        $this->assertSame(0, $user->shoppingCarts()->firstOrFail()->products()->count());
        $this->post(route('logout'))->assertRedirect();
        $this->get('/')->assertSee('Košík je prázdny.');
    }

    public function test_deleted_products_are_not_shown_in_guest_cart(): void
    {
        $product = Product::factory()->create();
        $this->post(route('eshop.cart.add', $product), ['quantity' => 1])->assertSessionHasNoErrors();
        $product->delete();

        $this->get('/')->assertSee('Košík je prázdny.')->assertSee('0,00 €');
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

            $this->assertCount(1, $xpath->query('//div[@class="shop-product-table"]/div[@class="shop-product-head"]/following-sibling::ul[@class="shop-product-list"][@aria-label="Produkty"]/li[@class="shop-product-row"]'));
            $this->assertCount(1, $code);
            $this->assertStringContainsString('TEST-123', $code->item(0)->textContent);
            $this->assertCount(1, $xpath->query('//span[@class="shop-product-meta shop-product-code"]/span[@class="shop-product-code-value"]'));
            $this->assertCount(0, $xpath->query('//div[@class="shop-product-info"]/following-sibling::div[1][@class="shop-product-details"]/div[@class="shop-product-actions"]/span[@class="shop-product-meta"]'));
            $this->assertCount(0, $xpath->query('//div[@class="shop-product-actions"]//span[@class="shop-product-meta shop-product-code"]'));
            $this->assertCount(1, $xpath->query('//div[@class="shop-product-details"]/div[@class="shop-product-actions"]/following-sibling::*[1][contains(@class, "shop-product-availability")]/following-sibling::*[1][@class="shop-product-pricing"]/following-sibling::*[1][@class="shop-product-category"]'));
            $this->assertStringContainsString('<span></span><span>Dostupnosť</span><span>Cena</span><span>Kategória</span>', $html);
        }
    }

    public function test_cart_accumulates_valid_quantities_and_is_isolated_per_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create(['minimum_order_quantity' => 3, 'price' => '12.50', 'currency' => 'EUR']);
        $this->actingAs($user)->get(route('eshop.catalog.index'))
            ->assertSee('name="quantity" type="hidden" value="3"', false)
            ->assertSee('value="3"', false)
            ->assertSee('<button type="submit" class="btn btn-primary shop-icon-button" aria-label="Použiť filtre"', false)
            ->assertSee('<button class="shop-cart-primary" type="submit" aria-label="Pridať do predvoleného košíka Môj košík:', false)
            ->assertSeeInOrder(['<span>Produkt</span>', '<span></span>', '<span>Dostupnosť</span>', '<span>Cena</span>', '<span>Kategória</span>'], false)
            ->assertSeeInOrder(['class="shop-product-favorite-form"', 'class="shop-product-image"', 'class="shop-product-info"', 'class="shop-product-actions"', 'class="shop-product-availability', 'class="shop-product-pricing"', 'class="shop-product-category"'], false);
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
            $this->assertStringContainsString('<span class="shop-cart-primary-name">Môj košík</span>', $html);
            $this->assertStringContainsString('<span class="shop-cart-option-width" aria-hidden="true">Druhy kosik</span>', $html);
            $this->assertNoCartOption($html, $product, $default->id);
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
        foreach ([
            $this->get(route('eshop.catalog.index'))->assertOk()->getContent(),
            $this->get(route('eshop.favorites'))->assertOk()->getContent(),
            $this->getJson(route('eshop.catalog.index'))->assertOk()->json('results'),
        ] as $html) {
            $this->assertStringContainsString('<span class="shop-cart-primary-name">Druhy kosik</span>', $html);
            $this->assertStringContainsString('title="Pridať 1 ks do košíka Druhy kosik"', $html);
            $this->assertCartBadge($html, $product, $default->id, 100000);
            $this->assertNoCartOption($html, $product, $other->id);
        }
        $this->put(route('eshop.carts.rename', $other), ['name' => 'Nový názov'])->assertSessionHasNoErrors();
        $this->get(route('eshop.catalog.index'))->assertSee('<span class="shop-cart-primary-name">Nový názov</span>', false);
        $this->delete(route('eshop.cart.remove', $product), ['cart_id' => $default->id])->assertSessionHasNoErrors();
        $this->assertSame(0, $default->products()->count());
        $this->assertSame(6, $other->products()->firstOrFail()->pivot->quantity);
    }

    public function test_only_cart_is_shown_as_primary_and_not_repeated_in_the_picker(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $default = $user->shoppingCarts()->firstOrFail();
        $html = $this->actingAs($user)->get(route('eshop.catalog.index'))->assertOk()->getContent();

        $this->assertCartBadge($html, $product, null, 0);
        $this->assertNoCartOption($html, $product, $default->id);
        $this->assertStringContainsString('Spravovať košíky', $html);
    }

    public function test_header_cart_choices_show_the_current_default_and_allow_switching(): void
    {
        $user = User::factory()->create();
        $default = $user->shoppingCarts()->where('is_default', true)->firstOrFail();
        $other = $user->shoppingCarts()->create(['name' => 'Druhy kosik', 'is_default' => false]);
        $this->actingAs($user);

        $this->get('/')
            ->assertSee('<strong class="cart-menu-cart-name">Môj košík · predvolený</strong>', false)
            ->assertSee('<button type="submit" class="cart-menu-select">Druhy kosik</button>', false)
            ->assertSeeInOrder(['Spravovať košíky', '<h3 class="cart-menu-items-title">Obsah košíka</h3>', 'Košík je prázdny.'], false);

        $this->post(route('eshop.carts.default', $other))->assertRedirect();

        $this->get('/')
            ->assertSee('<strong class="cart-menu-cart-name">Druhy kosik · predvolený</strong>', false)
            ->assertSee('<button type="submit" class="cart-menu-select">'.$default->name.'</button>', false);
    }

    public function test_header_ajax_actions_refresh_products_and_keep_carts_private(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::factory()->create();
        $default = $user->shoppingCarts()->firstOrFail();
        $other = $user->shoppingCarts()->create(['name' => 'Druhy kosik', 'is_default' => false]);
        $other->products()->attach($product, ['quantity' => 3]);
        $this->actingAs($user);

        $response = $this->postJson(route('eshop.carts.default', $other), ['product_ids' => [$product->id]])
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('header', fn ($header) => str_contains($header, 'Druhy kosik · predvolený'))
            ->assertJsonPath('results', fn ($results) => str_contains($results, '<span class="shop-cart-primary-name">Druhy kosik</span>'));
        $this->assertCartBadge($response->json('results'), $product, null, 3);
        $this->assertCartBadge($response->json('results'), $product, $default->id, 0);
        $this->assertNoCartOption($response->json('results'), $product, $other->id);
        $this->postJson(route('eshop.carts.default', $default), ['product_ids' => [$product->id, $product->id]])
            ->assertUnprocessable()->assertJsonValidationErrors('product_ids.1');
        $this->assertTrue($other->fresh()->is_default);
        $this->postJson(route('eshop.carts.default', $otherUser->shoppingCarts()->firstOrFail()))
            ->assertNotFound();

        $this->postJson(route('eshop.cart.remove', $product), ['_method' => 'DELETE', 'cart_id' => $other->id])
            ->assertOk()->assertJsonPath('product_id', $product->id)
            ->assertJsonPath('header', fn ($header) => str_contains($header, 'Košík je prázdny.'));
        $this->assertSame(0, $other->products()->count());
    }

    public function test_guest_header_ajax_removes_a_product(): void
    {
        $product = Product::factory()->create();
        $this->post(route('eshop.cart.add', $product), ['quantity' => 1])->assertSessionHasNoErrors();
        $this->deleteJson(route('eshop.cart.remove', $product))
            ->assertOk()->assertJsonPath('product_id', $product->id)
            ->assertJsonPath('header', fn ($header) => str_contains($header, 'Košík je prázdny.'));
        $this->get('/')->assertSee('Košík je prázdny.');
    }

    public function test_product_badges_show_quantities_for_each_cart_in_all_product_lists(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::factory()->create();
        $secondProduct = Product::factory()->create();
        $user->favoriteProducts()->attach([$product->id, $secondProduct->id]);
        $default = $user->shoppingCarts()->where('is_default', true)->firstOrFail();
        $other = $user->shoppingCarts()->create(['name' => 'Druhý', 'is_default' => false]);
        $otherUser->shoppingCarts()->firstOrFail()->products()->attach($product, ['quantity' => 99]);
        $this->actingAs($user);

        $html = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $this->assertCartBadge($html, $product, null, 0);
        $this->assertCartBadge($html, $product, $other->id, 0);

        $this->post(route('eshop.cart.add', $product), ['quantity' => 3])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $product), ['quantity' => 4, 'cart_id' => $other->id])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $product), ['quantity' => 2, 'cart_id' => $other->id])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $secondProduct), ['quantity' => 8, 'cart_id' => $other->id])->assertSessionHasNoErrors();

        foreach ([
            $this->get(route('eshop.catalog.index'))->assertOk()->getContent(),
            $this->get(route('eshop.favorites'))->assertOk()->getContent(),
            $this->getJson(route('eshop.catalog.index'))->assertOk()->json('results'),
        ] as $html) {
            $this->assertCartBadge($html, $product, null, 3);
            $this->assertNoCartOption($html, $product, $default->id);
            $this->assertCartBadge($html, $product, $other->id, 6);
            $this->assertCartBadge($html, $secondProduct, null, 0);
            $this->assertCartBadge($html, $secondProduct, $other->id, 8);
        }

        $this->post(route('eshop.carts.default', $other))->assertSessionHasNoErrors();
        $html = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $this->assertCartBadge($html, $product, null, 6);
        $this->assertNoCartOption($html, $product, $other->id);
        $this->delete(route('eshop.cart.remove', $product), ['cart_id' => $other->id])->assertSessionHasNoErrors();
        $html = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $this->assertCartBadge($html, $product, null, 0);
        $this->assertCartBadge($html, $product, $default->id, 3);
        $this->assertNoCartOption($html, $product, $other->id);
        $this->post(route('eshop.cart.add', $product), ['quantity' => 100000, 'cart_id' => $other->id])->assertSessionHasNoErrors();
        $html = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $this->assertCartBadge($html, $product, null, 100000);
        $this->assertNoCartOption($html, $product, $other->id);
    }

    public function test_guest_product_badges_reflect_session_cart(): void
    {
        $product = Product::factory()->create();
        $other = Product::factory()->create();
        $html = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $this->assertCartBadge($html, $product, null, 0);

        $this->post(route('eshop.cart.add', $product), ['quantity' => 4])->assertSessionHasNoErrors();
        $this->post(route('eshop.cart.add', $product), ['quantity' => 2])->assertSessionHasNoErrors();
        $html = $this->getJson(route('eshop.catalog.index'))->assertOk()->json('results');
        $this->assertCartBadge($html, $product, null, 6);
        $this->assertCartBadge($html, $other, null, 0);
        $this->delete(route('eshop.cart.remove', $product))->assertSessionHasNoErrors();
        $html = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $this->assertCartBadge($html, $product, null, 0);
    }

    public function test_default_cart_name_is_limited_to_fifteen_characters_in_product_lists(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $user->favoriteProducts()->attach($product);
        $cart = $user->shoppingCarts()->firstOrFail();
        $this->actingAs($user);

        foreach ([
            ['WWWWWWWWWWWWWWW', 'WWWWWWWWWWWWWWW'],
            ['WWWWWWWWWWWWWWWZ', 'WWWWWWWWWWWWWWW…'],
            ['žltý názov 123456789', 'žltý názov 1234…'],
            ["12345678901234e\u{0301}extra", "12345678901234e\u{0301}…"],
            ['abc<defghijklmnop', 'abc<defghijklmn…'],
        ] as [$name, $displayName]) {
            $this->put(route('eshop.carts.rename', $cart), ['name' => $name])->assertSessionHasNoErrors();
            foreach ([
                $this->get(route('eshop.catalog.index'))->assertOk()->getContent(),
                $this->get(route('eshop.favorites'))->assertOk()->getContent(),
                $this->getJson(route('eshop.catalog.index'))->assertOk()->json('results'),
            ] as $html) {
                $this->assertStringContainsString('<span class="shop-cart-primary-name">'.e($displayName).'</span>', $html);
                $this->assertStringContainsString('title="Pridať 1 ks do košíka '.e($name).'"', $html);
            }
        }
    }

    public function test_guest_adjustment_respects_minimum_and_maximum(): void
    {
        $product = Product::factory()->create(['minimum_order_quantity' => 3]);
        $url = route('eshop.cart.adjust', $product);
        $html = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $this->assertCartControls($html, $product, true, false);
        $this->post($url, ['change' => 'decrease'])->assertSessionHasErrors('quantity');
        $this->post($url, ['change' => 'increase'])->assertSessionHasNoErrors();
        $html = $this->get(route('eshop.catalog.index'))->getContent();
        $this->assertCartBadge($html, $product, null, 3);
        $this->assertCartControls($html, $product, false, false);
        $this->post($url, ['change' => 'increase'])->assertSessionHasNoErrors();
        $this->assertCartBadge($this->getJson(route('eshop.catalog.index'))->json('results'), $product, null, 4);
        $this->post($url, ['change' => 'decrease'])->assertSessionHasNoErrors();
        $this->post($url, ['change' => 'decrease'])->assertSessionHasNoErrors();
        $this->assertCartBadge($this->get(route('eshop.catalog.index'))->getContent(), $product, null, 0);
        $this->post($url, ['change' => 'invalid'])->assertSessionHasErrors('change');
        $this->post($url, ['change' => 'increase', 'cart_id' => 1])->assertNotFound();
        $this->post(route('eshop.cart.add', $product), ['quantity' => 100000])->assertSessionHasNoErrors();
        $this->post($url, ['change' => 'increase'])->assertSessionHasErrors('quantity');
        $html = $this->get(route('eshop.catalog.index'))->getContent();
        $this->assertCartBadge($html, $product, null, 100000);
        $this->assertCartControls($html, $product, false, true);
    }

    public function test_adjustment_changes_only_the_current_default_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['minimum_order_quantity' => 3]);
        $default = $user->shoppingCarts()->firstOrFail();
        $other = $user->shoppingCarts()->create(['name' => 'Druhý', 'is_default' => false]);
        $other->products()->attach($product, ['quantity' => 7]);
        $this->actingAs($user);
        $url = route('eshop.cart.adjust', $product);

        $this->post($url, ['change' => 'increase'])->assertSessionHasNoErrors();
        $this->assertSame(3, $default->products()->firstOrFail()->pivot->quantity);
        $this->assertSame(7, $other->products()->firstOrFail()->pivot->quantity);
        $this->post(route('eshop.carts.default', $other))->assertSessionHasNoErrors();
        $this->post($url, ['change' => 'decrease'])->assertSessionHasNoErrors();
        $this->assertSame(3, $default->products()->firstOrFail()->pivot->quantity);
        $this->assertSame(6, $other->products()->firstOrFail()->pivot->quantity);
        $this->post($url, ['change' => 'increase', 'cart_id' => $default->id])->assertSessionHasNoErrors();
        $this->assertSame(4, $default->products()->firstOrFail()->pivot->quantity);
        $this->assertSame(6, $other->products()->firstOrFail()->pivot->quantity);
    }

    public function test_dropdown_adjustments_target_the_selected_cart_and_enforce_quantity_limits(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['minimum_order_quantity' => 3]);
        $user->favoriteProducts()->attach($product);
        $default = $user->shoppingCarts()->firstOrFail();
        $other = $user->shoppingCarts()->create(['name' => 'Práca', 'is_default' => false]);
        $default->products()->attach($product, ['quantity' => 100000]);
        $this->actingAs($user);

        foreach ([
            $this->get(route('eshop.catalog.index'))->assertOk()->getContent(),
            $this->get(route('eshop.favorites'))->assertOk()->getContent(),
            $this->getJson(route('eshop.catalog.index'))->assertOk()->json('results'),
        ] as $html) {
            $this->assertCartControls($html, $product, false, true);
            $this->assertNoCartOption($html, $product, $default->id);
            $this->assertCartControls($html, $product, true, false, $other->id);
        }

        $otherUrl = route('eshop.cart.adjust', ['product' => $product, 'cart_id' => $other->id]);
        $defaultUrl = route('eshop.cart.adjust', ['product' => $product, 'cart_id' => $default->id]);
        $this->post($otherUrl, ['change' => 'decrease'])->assertSessionHasErrors('quantity');
        $this->post($otherUrl, ['change' => 'increase'])->assertSessionHasNoErrors();
        $this->assertSame(3, $other->products()->firstOrFail()->pivot->quantity);
        $this->post($otherUrl, ['change' => 'increase'])->assertSessionHasNoErrors();
        $this->assertSame(4, $other->products()->firstOrFail()->pivot->quantity);
        $this->post($otherUrl, ['change' => 'decrease'])->assertSessionHasNoErrors();
        $this->post($otherUrl, ['change' => 'decrease'])->assertSessionHasNoErrors();
        $this->assertSame(0, $other->products()->count());
        $this->post($defaultUrl, ['change' => 'increase'])->assertSessionHasErrors('quantity');
        $this->post($defaultUrl, ['change' => 'decrease'])->assertSessionHasNoErrors();
        $this->assertSame(99999, $default->products()->firstOrFail()->pivot->quantity);
        $this->assertTrue($default->fresh()->is_default);
        $this->post(route('eshop.cart.adjust', $product), ['change' => 'increase', 'cart_id' => 'invalid'])->assertSessionHasErrors('cart_id');
    }

    public function test_ajax_cart_actions_return_updated_product_controls_and_header(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['minimum_order_quantity' => 3, 'price' => '2.50', 'currency' => 'EUR']);
        $default = $user->shoppingCarts()->firstOrFail();
        $other = $user->shoppingCarts()->create(['name' => 'Práca', 'is_default' => false]);
        $this->actingAs($user);

        $response = $this->postJson(route('eshop.cart.adjust', ['product' => $product, 'cart_id' => $other->id]), ['change' => 'increase'])
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('product_id', $product->id)
            ->assertJsonPath('message', 'Množstvo v košíku bolo upravené.');
        $this->assertCartBadge($response->json('results'), $product, null, 0);
        $this->assertCartBadge($response->json('results'), $product, $other->id, 3);
        $this->assertStringContainsString('Košík je prázdny.', $response->json('header'));

        $response = $this->postJson(route('eshop.cart.add', $product), ['quantity' => 3])->assertOk();
        $this->assertCartBadge($response->json('results'), $product, null, 3);
        $this->assertNoCartOption($response->json('results'), $product, $default->id);
        $this->assertStringContainsString('7,50 €', $response->json('header'));
        $this->assertStringContainsString('<h3 class="cart-menu-items-title">Obsah košíka</h3>', $response->json('header'));
        $this->assertStringContainsString('<div class="cart-menu-item-meta">3 ks × 2,50 EUR</div>', $response->json('header'));
        $this->assertStringContainsString('3 ks × 2,50 EUR', $response->json('header'));
        $this->postJson(route('eshop.cart.adjust', ['product' => $product, 'cart_id' => $other->id]), ['change' => 'decrease'])->assertOk();
        $this->postJson(route('eshop.cart.adjust', ['product' => $product, 'cart_id' => $other->id]), ['change' => 'decrease'])
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');
    }

    public function test_guest_ajax_cart_actions_update_header_and_report_errors(): void
    {
        $product = Product::factory()->create(['minimum_order_quantity' => 2, 'price' => '1.50', 'currency' => 'EUR']);
        $response = $this->postJson(route('eshop.cart.adjust', $product), ['change' => 'increase'])->assertOk();
        $this->assertCartBadge($response->json('results'), $product, null, 2);
        $this->assertStringContainsString('3,00 €', $response->json('header'));
        $this->postJson(route('eshop.cart.add', $product), ['quantity' => 1])->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->postJson(route('eshop.cart.adjust', ['product' => $product, 'cart_id' => 1]), ['change' => 'increase'])->assertNotFound();
    }

    public function test_non_ajax_cart_notice_is_shown_by_the_matching_product(): void
    {
        $product = Product::factory()->create(['name' => 'Skúšaná položka']);
        $other = Product::factory()->create();
        $this->from(route('eshop.catalog.index'))->post(route('eshop.cart.add', $product), ['quantity' => 1])
            ->assertRedirect(route('eshop.catalog.index'))->assertSessionHas('status_product_id', $product->id);
        $html = $this->get(route('eshop.catalog.index'))->assertOk()->getContent();
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $this->assertCount(1, $xpath->query('//*[@id="catalog-product-'.$product->id.'"]//*[@data-cart-notice and not(@hidden)]'));
        $this->assertCount(0, $xpath->query('//*[@id="catalog-product-'.$other->id.'"]//*[@data-cart-notice and not(@hidden)]'));
        $this->assertCount(0, $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " site-notice-container ")]'));

        $this->from(route('eshop.catalog.index'))->post(route('eshop.cart.add', $product), ['quantity' => 1])->assertRedirect();
        $this->get(route('eshop.catalog.index', ['q' => 'nevyskytujucisa']))->assertSee('site-notice-container', false);
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
        $this->post(route('eshop.cart.adjust', ['product' => $product, 'cart_id' => $foreign->id]), ['change' => 'increase'])->assertNotFound();
        $this->post(route('eshop.cart.adjust', ['product' => $product, 'cart_id' => $foreign->id]), ['change' => 'decrease'])->assertNotFound();
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

    private function assertNoCartOption(string $html, Product $product, int $cartId): void
    {
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $this->assertCount(0, $xpath->query('//*[@id="catalog-product-'.$product->id.'"]//button[@name="cart_id" and @value="'.$cartId.'"]'));
    }

    private function assertCartBadge(string $html, Product $product, ?int $cartId, int $quantity): void
    {
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $button = $cartId === null
            ? 'button[contains(concat(" ", normalize-space(@class), " "), " shop-cart-primary ")]'
            : 'button[@name="cart_id" and @value="'.$cartId.'"]';
        $buttons = $xpath->query('//*[@id="catalog-product-'.$product->id.'"]//'.$button);
        $this->assertCount(1, $buttons);
        $this->assertCount(1, $xpath->query('preceding-sibling::span[@class="shop-cart-quantity-controls"]', $buttons->item(0)));
        $badge = $xpath->query($cartId === null
            ? '../span[@class="shop-cart-quantity-controls"]/span[@class="shop-cart-quantity"]'
            : '../span[@class="shop-cart-quantity-controls"]/span[@class="shop-cart-quantity"]', $buttons->item(0));
        $this->assertCount(1, $badge);
        $this->assertSame((string) $quantity, trim($badge->item(0)->textContent));
        $this->assertStringContainsString('V ko', $buttons->item(0)->getAttribute('aria-label'));
        $this->assertStringContainsString($quantity.' ks', $buttons->item(0)->getAttribute('aria-label'));
    }

    private function assertCartControls(string $html, Product $product, bool $minusDisabled, bool $plusDisabled, ?int $cartId = null): void
    {
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $container = $cartId === null
            ? 'div[contains(concat(" ", normalize-space(@class), " "), " shop-cart-primary-actions ")]'
            : 'div[@class="shop-cart-option"][button[@name="cart_id" and @value="'.$cartId.'"]]';
        $controls = $xpath->query('//*[@id="catalog-product-'.$product->id.'"]//'.$container.'/span[@class="shop-cart-quantity-controls"]/*');
        $this->assertCount(3, $controls);
        $this->assertSame('decrease', $controls->item(0)->getAttribute('value'));
        $this->assertSame('shop-cart-quantity', $controls->item(1)->getAttribute('class'));
        $this->assertSame('increase', $controls->item(2)->getAttribute('value'));
        $this->assertSame($minusDisabled, $controls->item(0)->hasAttribute('disabled'));
        $this->assertSame($plusDisabled, $controls->item(2)->hasAttribute('disabled'));
        foreach ([$controls->item(0), $controls->item(2)] as $button) {
            $url = $cartId === null
                ? route('eshop.cart.adjust', $product, false)
                : route('eshop.cart.adjust', ['product' => $product, 'cart_id' => $cartId], false);
            $this->assertSame($url, $button->getAttribute('formaction'));
            $this->assertTrue($button->hasAttribute('formnovalidate'));
            $this->assertNotSame('', $button->getAttribute('aria-label'));
        }
    }
}
