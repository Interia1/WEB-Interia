<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_internal_zone(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('internal.dashboard'))->assertForbidden();
    }

    public function test_content_manager_can_access_products_but_not_users(): void
    {
        $worker = User::factory()->contentManager()->create();

        $this->actingAs($worker)->get(route('internal.dashboard'))->assertOk();
        $this->actingAs($worker)->get(route('internal.products.index'))->assertOk();
        $this->actingAs($worker)->get(route('internal.users.index'))->assertForbidden();
    }

    public function test_administrator_can_create_a_verified_worker_account(): void
    {
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)->post(route('internal.users.store'), [
            'name' => 'Novy Pracovnik',
            'email' => 'pracovnik@example.com',
            'role' => UserRole::ContentManager->value,
            'password' => 'temporary-password',
            'password_confirmation' => 'temporary-password',
        ])->assertRedirect();

        $worker = User::where('email', 'pracovnik@example.com')->firstOrFail();

        $this->assertSame(UserRole::ContentManager, $worker->role);
        $this->assertTrue($worker->hasVerifiedEmail());
        $this->assertTrue(Hash::check('temporary-password', $worker->password));
    }

    public function test_administrator_cannot_demote_their_own_account(): void
    {
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)->put(route('internal.users.update', $administrator), [
            'name' => $administrator->name,
            'email' => $administrator->email,
            'role' => UserRole::Customer->value,
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasNoErrors();

        $this->assertSame(UserRole::Administrator, $administrator->fresh()->role);
    }
}
