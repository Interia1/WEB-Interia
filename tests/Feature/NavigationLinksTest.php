<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_links_point_to_available_pages(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();

        foreach (['about', 'gallery', 'faq', 'contact', 'customer.zone', 'login', 'register'] as $routeName) {
            $response->assertSee('href="'.route($routeName).'"', false);
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_header_has_direct_contact_links_and_support(): void
    {
        $response = $this->get(route('home'))->assertOk();
        $header = explode('</nav>', $response->getContent(), 2)[0];

        $this->assertStringContainsString('href="tel:+421900000000"', $header);
        $this->assertStringContainsString('<strong>+421 900 000 000</strong>', $header);
        $this->assertStringContainsString('href="mailto:admin@interia.test"', $header);
        $this->assertStringContainsString('>Podpora</a>', $header);
        $this->assertStringContainsString('href="'.route('legal.terms').'#reklamacie">Reklamácie</a>', $header);
        $this->assertStringNotContainsString('>Partneri</a>', $header);
        $this->assertLessThan(strpos($header, 'id="mainNav"'), strpos($header, 'href="tel:'));

        $this->get(route('faq'))->assertOk();
        $this->get(route('partners'))->assertOk();
        $this->get(route('legal.terms'))->assertOk()->assertSee('id="reklamacie"', false);
    }
}