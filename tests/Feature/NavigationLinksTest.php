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

        foreach (['about', 'gallery', 'partners', 'contact', 'customer.zone', 'login', 'register'] as $routeName) {
            $response->assertSee('href="'.route($routeName).'"', false);
            $this->get(route($routeName))->assertOk();
        }
    }
}