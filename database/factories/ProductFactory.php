<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 999),
            'category' => 'testovacia-kategoria',
            'category_label' => 'Testovacia kategória',
            'price' => fake()->randomFloat(2, 49, 1499),
            'currency' => 'EUR',
            'availability' => fake()->randomElement(['Skladom', 'Do 7 dní', 'Na objednávku']),
            'short_description' => fake()->sentence(12),
            'description' => fake()->paragraph(4),
            'image_path' => null,
            'is_featured' => fake()->boolean(25),
        ];
    }
}