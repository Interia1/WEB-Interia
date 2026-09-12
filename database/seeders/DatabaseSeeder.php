<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (app()->environment(['local', 'testing'])) {
            User::query()->updateOrCreate(['email' => 'admin@interia.test'], [
                'name' => 'Test Administrator',
                'role' => UserRole::Administrator,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'gdpr_consent_at' => now(),
                'gdpr_consent_ip' => '127.0.0.1',
                'terms_accepted_at' => now(),
                'terms_accepted_ip' => '127.0.0.1',
            ]);

            User::query()->updateOrCreate(['email' => 'pracovnik@interia.test'], [
                'name' => 'Test Pracovník',
                'role' => UserRole::ContentManager,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'gdpr_consent_at' => now(),
                'gdpr_consent_ip' => '127.0.0.1',
                'terms_accepted_at' => now(),
                'terms_accepted_ip' => '127.0.0.1',
            ]);

            User::query()->updateOrCreate(['email' => 'zakaznik@interia.test'], [
                'name' => 'Test Zákazník',
                'role' => UserRole::Customer,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'gdpr_consent_at' => now(),
                'gdpr_consent_ip' => '127.0.0.1',
                'terms_accepted_at' => now(),
                'terms_accepted_ip' => '127.0.0.1',
            ]);
        }

        $this->call(ProductSeeder::class);
    }
}
