<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoLongNameProductsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'slug' => 'ukazka-dlheho-nazvu-hlinikovy-profil',
                'name' => 'Ukážka: hliníkový konštrukčný profil pre moderné interiérové systémy so zvýšenou pevnosťou',
                'sku' => 'DEMO-001',
                'category' => 'hlinikove-systemy',
                'category_label' => 'Hliníkové systémy',
                'price' => '199.90',
                'minimum_order_quantity' => 1,
            ],
            [
                'slug' => 'ukazka-dlheho-nazvu-ocelovy-prvok',
                'name' => 'Ukážka: oceľový montážny prvok pre nosné konštrukcie s ochrannou povrchovou úpravou',
                'sku' => 'DEMO-002',
                'category' => 'ocelove-prvky',
                'category_label' => 'Oceľové prvky',
                'price' => '159.50',
                'minimum_order_quantity' => 2,
            ],
        ] as $product) {
            Product::query()->firstOrCreate(['slug' => $product['slug']], [
                ...$product,
                'currency' => 'EUR',
                'availability' => 'Skladom',
                'short_description' => 'Ukážkový produkt pre overenie zobrazenia dlhého názvu.',
                'description' => 'Ukážkové údaje vrátane ceny a minimálneho množstva slúžia len na testovanie katalógu.',
                'image_path' => null,
                'is_featured' => true,
            ]);
        }
    }
}
