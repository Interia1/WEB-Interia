<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_name_without_losing_verification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('customer.account.update'), [
            'name' => 'Upravene Meno',
            'email' => $user->email,
        ])->assertSessionHasNoErrors();

        $this->assertSame('Upravene Meno', $user->fresh()->name);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_change_requires_new_verification(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('customer.account.update'), [
            'name' => $user->name,
            'email' => 'novy-email@example.com',
        ])->assertRedirect(route('verification.notice'));

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
