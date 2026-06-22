<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
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
