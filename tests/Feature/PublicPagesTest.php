<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_public_pages_return_ok(): void
    {
        $this->get('/')->assertOk();
        $this->get('/o-nas')->assertOk();
        $this->get('/produkty-sluzby')->assertOk();
        $this->get('/kontakt')->assertOk();
    }

    public function test_home_contains_cookie_banner_and_meta(): void
    {
        $response = $this->get('/');

        $response->assertSee('cookieBanner', false)
            ->assertSee('Podrobnosti o cookies', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('name="description"', false);
    }

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
