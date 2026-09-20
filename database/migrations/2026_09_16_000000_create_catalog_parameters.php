<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('name', 100);
            $table->timestamps();
        });

        DB::table('products')->select('category', 'category_label')->distinct()->orderBy('category')->get()
            ->each(fn ($product) => DB::table('catalog_categories')->insertOrIgnore([
                'slug' => $product->category,
                'name' => $product->category_label,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

        Schema::create('catalog_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('type', 20);
            $table->string('unit', 20)->nullable();
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->boolean('filterable')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['catalog_category_id', 'name']);
        });

        Schema::create('product_parameter_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalog_parameter_id')->constrained()->cascadeOnDelete();
            $table->string('value', 100);
            $table->decimal('numeric_value', 12, 4)->nullable();
            $table->unique(['product_id', 'catalog_parameter_id'], 'product_parameter_unique');
            $table->index(['catalog_parameter_id', 'value', 'product_id'], 'parameter_choice_lookup');
            $table->index(['catalog_parameter_id', 'numeric_value', 'product_id'], 'parameter_number_lookup');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['category', 'price', 'id'], 'catalog_category_price');
            $table->index(['category', 'name'], 'catalog_category_name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('catalog_category_price');
            $table->dropIndex('catalog_category_name');
        });
        Schema::dropIfExists('product_parameter_values');
        Schema::dropIfExists('catalog_parameters');
        Schema::dropIfExists('catalog_categories');
    }
};
