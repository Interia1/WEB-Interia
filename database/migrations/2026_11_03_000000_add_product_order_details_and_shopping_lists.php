<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 100)->nullable()->unique();
            $table->unsignedInteger('minimum_order_quantity')->default(1);
        });

        foreach ([
            'alu-frame-100' => ['INT-AF100', 1],
            'alu-frame-pro-220' => ['INT-AFP220', 2],
            'steel-strong-50' => ['INT-SS50', 1],
            'steel-build-120' => ['INT-SB120', 2],
            'inox-line-80' => ['INT-IL80', 1],
            'inox-guard-160' => ['INT-IG160', 2],
            'composite-flex-30' => ['INT-CF30', 1],
            'composite-tech-95' => ['INT-CT95', 2],
        ] as $slug => [$sku, $minimum]) {
            DB::table('products')->where('slug', $slug)->whereNull('sku')->update([
                'sku' => $sku,
                'minimum_order_quantity' => $minimum,
            ]);
        }

        Schema::create('favorite_products', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'product_id']);
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->primary(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('favorite_products');
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn(['sku', 'minimum_order_quantity']);
        });
    }
};
