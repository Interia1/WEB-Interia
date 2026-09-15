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

    public function test_header_shows_empty_cart_next_to_guest_account(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<span class="account-menu-label">Prihlásenie</span>', false)
            ->assertSeeInOrder(['<span class="cart-summary">', '<span>Košík</span>', '<span class="cart-summary-values">', '0,00 € /</span>', '<span class="cart-count" aria-hidden="true">0</span>'], false)
            ->assertSeeInOrder(['Používateľský účet', 'id="headerCartToggle"', 'aria-label="Košík: 0,00 €, 0 položiek"', 'id="headerCart"', 'Košík je prázdny.', 'href="'.route('eshop.catalog.index', [], false).'"'], false);
    }

    public function test_header_shows_cart_for_signed_in_users(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'test6']))
            ->get('/')
            ->assertOk()
            ->assertSee('aria-label="Prihlásený ako test6"', false)
            ->assertSee('<span class="account-menu-label">test6</span>', false)
            ->assertSee('aria-label="Košík: 0,00 €, 0 položiek"', false)
            ->assertSee('Košík je prázdny.')
            ->assertSeeInOrder(['id="headerAccount"', 'Môj účet', 'Moje objednávky', 'action="'.route('logout').'"', 'Odhlásiť (', 'id="headerCartToggle"'], false)
            ->assertSee('class="dropdown-item" href="'.route('customer.orders').'"', false);
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

    public function test_home_reserves_advertising_below_hero(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['<section class="home-hero', '<aside class="home-advertising"', '<section class="home-customer-info"'], false)
            ->assertSee('data-ad-count="0"', false)
            ->assertSee('Spoznať I-zónu')
            ->assertSee('Ako fungujú objednávky')
            ->assertSee('Ako fungujú ceny a zľavy')
            ->assertDontSee('Vlastný customer portal');
    }

    public function test_home_shows_updates_below_customer_information(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['<span>I-zóna</span>', '<span>Zákaznícky portál</span>', '<span>Akcie</span>', 'home-advertising-compact', 'data-ad-count="0"', 'home-updates', '<span>Ceny a zľavy</span>', '<span>Novinky</span>', '<span>Referencie</span>', 'home-contact-band'], false)
            ->assertSee('Pozrieť akcie')
            ->assertSee('Prečítať novinky')
            ->assertSee('Pozrieť referencie')
            ->assertSee('Momentálne tu nie sú zverejnené žiadne akcie.')
            ->assertSee('Momentálne tu nie sú zverejnené žiadne novinky.')
            ->assertSee('href="'.route('custom-work.presentations', [], false).'"', false);

        $this->assertSame(6, substr_count($response->getContent(), '<details class="home-info-details"'));
        $this->assertSame(6, substr_count($response->getContent(), '<div class="home-info-heading">'));
        foreach (['community', 'orders', 'promotions', 'discounts', 'news', 'references'] as $section) {
            $response->assertSee('<details class="home-info-details" id="home-info-'.$section.'-details">', false);
        }
        foreach (['customer.zone', 'customer.orders', 'promotions', 'discounts', 'news', 'custom-work.presentations'] as $route) {
            $response->assertSee('<a class="home-info-title-button" href="'.route($route, [], false).'"><i class="bi ', false);
        }
    }

    public function test_home_information_destinations_are_available(): void
    {
        foreach (['promotions' => 'Akcie', 'discounts' => 'Ceny a zľavy', 'news' => 'Novinky'] as $route => $title) {
            $this->get(route($route, [], false))
                ->assertOk()
                ->assertSee('<h1 class="display-5 fw-semibold mb-3">'.$title.'</h1>', false)
                ->assertSee('href="'.route('contact', [], false).'"', false);
        }
    }

    public function test_home_renders_one_to_thirty_advertisements(): void
    {
        foreach (range(1, 31) as $count) {
            config(['home.advertisements' => array_map(static fn (int $number): array => [
                'title' => 'Advertisement '.$number,
                'image' => '/images/home-hero-components.jpg',
                'url' => '/kontakt?advertisement='.$number,
            ], range(1, $count))]);

            $response = $this->get('/')->assertOk();
            $response->assertSee('data-ad-count="'.min($count, 30).'"', false);
            $this->assertSame(min($count, 30), substr_count($response->getContent(), 'class="home-advertisement"'));
            $response->assertSee('alt="Advertisement 1"', false)
                ->assertSee('href="/kontakt?advertisement=1"', false)
                ->assertDontSee('alt="Advertisement 31"', false);
        }
    }

    public function test_advertisement_widths_are_configurable_and_bounded(): void
    {
        config(['home.advertisements' => array_map(static fn ($weight): array => [
            'title' => 'Weighted offer',
            'image' => '/images/advertisement.webp',
            'url' => '/kontakt',
            'weight' => $weight,
        ], [1, 3, 5, -2, 99, 'invalid'])]);

        $response = $this->get('/')->assertOk();
        $response->assertSee('style="--ad-weight: 3"', false);
        $this->assertSame(3, substr_count($response->getContent(), 'style="--ad-weight: 1"'));
        $this->assertSame(2, substr_count($response->getContent(), 'style="--ad-weight: 5"'));
    }

    public function test_home_renders_video_and_image_advertisements_together(): void
    {
        foreach (['mp4', 'webm'] as $format) {
            config(['home.advertisements' => [
                [
                    'title' => 'Video offer',
                    'image' => '/images/video-poster.webp',
                    'video' => '/videos/advertisement.'.$format,
                    'url' => '/kontakt?video=1',
                ],
                [
                    'title' => 'Image offer',
                    'image' => '/images/advertisement.webp',
                    'url' => '/kontakt?image=1',
                ],
            ]]);

            $response = $this->get('/')
                ->assertOk()
                ->assertSee('data-ad-count="2"', false)
                ->assertSee('controls playsinline muted preload="none"', false)
                ->assertSee('poster="/images/video-poster.webp"', false)
                ->assertSee('src="/videos/advertisement.'.$format.'"', false)
                ->assertSee('aria-label="Video offer"', false)
                ->assertSee('href="/kontakt?video=1"', false)
                ->assertSee('alt="Image offer"', false);

            preg_match('/<video\b[^>]*>/', $response->getContent(), $videoTag);
            $this->assertStringNotContainsString('autoplay', $videoTag[0]);
        }
    }

    public function test_community_introduction_is_separate_from_orders(): void
    {
        $this->get(route('customer.zone'))
            ->assertOk()
            ->assertSee('Čo pripravujeme pre členov')
            ->assertSee('Členský obsah bude dostupný iba prihláseným členom.')
            ->assertSee('href="/moje-objednavky"', false);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('Vstúpiť do I-zóny')
            ->assertDontSee('Prihlásiť sa do portálu');
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
