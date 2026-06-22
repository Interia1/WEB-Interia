<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'gdpr_consent_at' => now(),
            'gdpr_consent_ip' => '127.0.0.1',
            'terms_accepted_at' => now(),
            'terms_accepted_ip' => '127.0.0.1',
            'marketing_consent' => false,
        ]);

        $this->call(ProductSeeder::class);
    }
}
