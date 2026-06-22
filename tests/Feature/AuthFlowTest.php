<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_customer_orders_to_login(): void
    {
        $response = $this->get(route('customer.orders'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_login_and_access_customer_orders(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('customer.orders'));
        $this->assertAuthenticatedAs($user);

        $ordersResponse = $this->get(route('customer.orders'));
        $ordersResponse->assertOk();
    }

    public function test_user_can_register_with_required_consents(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'name' => 'Novy Zakaznik',
            'email' => 'novy@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
            'gdpr_consent' => '1',
            'terms_accepted' => '1',
            'marketing_consent' => '1',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'email' => 'novy@example.com',
            'marketing_consent' => true,
        ]);

        $user = User::where('email', 'novy@example.com')->firstOrFail();

        $this->assertNotNull($user->gdpr_consent_at);
        $this->assertNotNull($user->terms_accepted_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_requires_gdpr_and_terms_consents(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Bez suhlasu',
            'email' => 'bez-suhlasu@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['gdpr_consent', 'terms_accepted']);
    }

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('customer.orders'));

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_user_can_verify_email_via_signed_link(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('customer.orders'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_password_reset_link_can_be_requested_and_password_reset_completed(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $requestResponse = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $requestResponse->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        $resetResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $resetResponse->assertRedirect(route('login'));

        $loginResponse = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'new-password-123',
        ]);

        $loginResponse->assertRedirect(route('customer.orders'));
    }

    public function test_consents_export_is_forbidden_for_non_admin_users(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'email' => 'regular@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('admin.consents.export'));

        $response->assertForbidden();
    }
}
