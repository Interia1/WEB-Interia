<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_return_ok(): void
    {
        $this->get('/')->assertOk();
        $this->get('/o-nas')->assertOk();
        $this->get('/sluzby')->assertOk();
        $this->get('/atypicka-vyroba')->assertOk();
        $this->get('/materialy')->assertOk();
        $this->get('/faq')->assertOk();
        $this->get('/kontakt')->assertOk();
        $this->get('/ochrana-osobnych-udajov')->assertOk();
        $this->get('/obchodne-podmienky')->assertOk();
        $this->get('/registracia')->assertOk();
        $this->get('/zabudnute-heslo')->assertOk();
    }

    public function test_legacy_products_url_redirects_to_services(): void
    {
        $this->get('/produkty-sluzby')
            ->assertStatus(301)
            ->assertRedirect('/sluzby');
    }

    public function test_home_contains_cookie_banner_and_meta(): void
    {
        $response = $this->get('/');

        $response->assertSee('cookieBanner', false)
            ->assertSee('Podrobnosti o cookies', false)
            ->assertSee('Registrácia', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('name="description"', false);
    }

    public function test_logo_links_to_relative_home_url(): void
    {
        $this->get(route('eshop.catalog.index'))
            ->assertOk()
            ->assertSee('href="/" aria-label="Domov"', false);
    }

    public function test_catalog_search_finds_products_by_indexed_content(): void
    {
        Product::factory()->create([
            'name' => 'Skrytý záves',
            'category_label' => 'Nábytkové kovanie',
            'short_description' => 'Tiché zatváranie kuchynských dvierok.',
        ]);
        Product::factory()->create([
            'name' => 'Dubová pracovná doska',
            'category_label' => 'Dosky',
            'short_description' => 'Masívna doska do interiéru.',
        ]);

        $this->get(route('eshop.catalog.index', ['q' => 'kuchynských']))
            ->assertOk()
            ->assertSee('Skrytý záves')
            ->assertDontSee('Dubová pracovná doska');
    }

    public function test_product_search_endpoint_returns_direct_product_results(): void
    {
        $product = Product::factory()->create([
            'name' => 'ALU Frame 100',
            'category_label' => 'Hliníkové systémy',
            'availability' => 'Skladom',
        ]);
        Product::factory()->create(['name' => 'Inox Guard 160']);

        $this->getJson(route('search.products', ['q' => 'alu']))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', 'ALU Frame 100')
            ->assertJsonPath('products.0.description', 'Hliníkové systémy · Skladom')
            ->assertJsonPath('products.0.url', route('eshop.product.show', ['product' => $product], false));
    }

    public function test_home_business_buttons_link_to_their_subpages(): void
    {
        $response = $this->get('/');

        $response->assertSee('href="/materialy-eshop"', false)
            ->assertSee('href="/polotovary"', false)
            ->assertSee('href="/vyroba-na-mieru"', false);

        $this->get(route('materials-eshop'))->assertOk();
        $this->get(route('semifinished'))->assertOk();
        $this->get(route('custom-work'))->assertOk();
    }

    public function test_home_displays_guided_search_above_business_buttons(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-home-assistant', false)
            ->assertSee('data-catalog-search-url="/eshop/katalog"', false)
            ->assertSee('data-product-search-url="/vyhladavanie/produkty"', false)
            ->assertSee('id="homeAssistantQuery"', false)
            ->assertSee('placeholder="Čo hľadáte?"', false)
            ->assertSee('bi bi-search home-assistant-icon', false)
            ->assertDontSee('bi-stars', false)
            ->assertSee('data-title="Objednať polotovary"', false)
            ->assertSee('data-title="Zákazka na mieru"', false);
    }

    public function test_home_displays_editable_shortcuts_for_each_business_area(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('&amp;quot;', false)
            ->assertSee('data-shortcut-group="eshop"', false)
            ->assertSee('data-shortcut-group="semifinished"', false)
            ->assertSee('data-shortcut-group="custom"', false)
            ->assertSee('id="shortcutModal"', false)
            ->assertSee('title="Katalóg"', false)
            ->assertSee('Vyberte 1 až 5 skratiek pre každú časť.', false);
    }

    public function test_authenticated_user_can_save_home_shortcuts(): void
    {
        $user = User::factory()->create();
        $shortcuts = [
            'eshop' => ['catalog'],
            'semifinished' => ['orders'],
            'custom' => ['contact'],
        ];

        $this->actingAs($user)
            ->putJson(route('customer.home-shortcuts.update'), ['shortcuts' => $shortcuts])
            ->assertOk()
            ->assertJson(['shortcuts' => $shortcuts]);

        $this->assertSame($shortcuts, $user->fresh()->home_shortcuts);
    }

    public function test_home_shortcuts_reject_invalid_or_empty_selection(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson(route('customer.home-shortcuts.update'), [
                'shortcuts' => [
                    'eshop' => ['catalog', 'unknown', 'contact'],
                    'semifinished' => [],
                    'custom' => ['presentations', 'customer-zone', 'contact'],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['shortcuts.eshop.1', 'shortcuts.semifinished']);
    }

    public function test_guest_cannot_save_home_shortcuts_to_an_account(): void
    {
        $this->putJson(route('customer.home-shortcuts.update'), ['shortcuts' => []])
            ->assertUnauthorized();
    }

    public function test_quick_order_script_is_served(): void
    {
        $this->get('/assets/quick-order.js')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8')
            ->assertSee("searchUrl.searchParams.set('q', query)", false)
            ->assertDontSee('Najčastejšie možnosti', false)
            ->assertDontSee('Ďalšie možnosti', false)
            ->assertSee('mouseenter', false);
    }

    public function test_site_notice_script_is_served(): void
    {
        $this->get('/assets/site-notice.js')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8')
            ->assertSee('2000', false);
    }

    public function test_history_navigation_script_is_served(): void
    {
        $this->get('/assets/history-navigation.js')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8')
            ->assertSee('window.history.back()', false)
            ->assertSee('window.history.forward()', false);
    }

    public function test_history_navigation_is_visible_on_subpages(): void
    {
        foreach (['/o-nas', '/kontakt', '/prihlasenie', '/registracia', '/materialy-eshop'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('data-history-back', false)
                ->assertSee('bi-arrow-90deg-left', false)
                ->assertSee('data-history-forward', false)
                ->assertSee('bi-arrow-90deg-right', false);
        }
    }

    public function test_registration_can_reveal_password_fields(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('data-password-field', false)
            ->assertSee('data-password-toggle', false)
            ->assertSee('Zobraziť heslá')
            ->assertDontSee('IP adres');

        $this->get(route('assets.password-visibility-js'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8')
            ->assertSee('data-password-field', false);
    }

    public function test_contact_form_submission_returns_success_message(): void
    {
        $response = $this->post('/kontakt', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Testovacia správa',
        ]);

        $response->assertRedirect('/kontakt');

        $this->followRedirects($response)
            ->assertSee('Ďakujeme za správu. Ozveme sa vám čo najskôr.');
    }

    public function test_contact_form_requires_all_fields(): void
    {
        $response = $this->post('/kontakt', []);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
    }

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
