<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sales_unit', 10)->default('ks')->after('minimum_order_quantity');
            $table->unsignedInteger('order_increment')->default(1)->after('sales_unit');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sales_unit', 'order_increment']);
        });
    }
};