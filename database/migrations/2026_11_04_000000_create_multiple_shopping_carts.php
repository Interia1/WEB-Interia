<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->requireMaintenance();
        $this->runSafely(function () {
            Schema::create('shopping_carts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name', 80);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
                $table->unique(['user_id', 'name']);
            });

            Schema::create('shopping_cart_items', function (Blueprint $table) {
                $table->foreignId('shopping_cart_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('quantity');
                $table->timestamps();
                $table->primary(['shopping_cart_id', 'product_id']);
            });

            DB::table('users')->orderBy('id')->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $cartId = DB::table('shopping_carts')->insertGetId([
                        'user_id' => $user->id,
                        'name' => 'Môj košík',
                        'is_default' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('cart_items')->where('user_id', $user->id)->orderBy('product_id')
                        ->chunk(500, function ($items) use ($cartId) {
                            DB::table('shopping_cart_items')->insert($items->map(fn ($item) => [
                                'shopping_cart_id' => $cartId,
                                'product_id' => $item->product_id,
                                'quantity' => $item->quantity,
                                'created_at' => $item->created_at,
                                'updated_at' => $item->updated_at,
                            ])->all());
                        });
                }
            });

            $missing = DB::table('cart_items')
                ->leftJoin('shopping_carts', 'cart_items.user_id', '=', 'shopping_carts.user_id')
                ->leftJoin('shopping_cart_items', function ($join) {
                    $join->on('shopping_cart_items.shopping_cart_id', '=', 'shopping_carts.id')
                        ->on('shopping_cart_items.product_id', '=', 'cart_items.product_id')
                        ->on('shopping_cart_items.quantity', '=', 'cart_items.quantity');
                })->whereNull('shopping_cart_items.product_id')->exists();
            if ($missing || DB::table('cart_items')->count() !== DB::table('shopping_cart_items')->count()) {
                throw new RuntimeException('Existujúce položky košíkov sa nepodarilo úplne preniesť.');
            }

            Schema::drop('cart_items');
        });
    }

    public function down(): void
    {
        $this->requireMaintenance();
        $this->runSafely(function () {
            $multipleCarts = DB::table('shopping_carts')->select('user_id')
                ->groupBy('user_id')->havingRaw('COUNT(*) > 1')->exists();
            if ($multipleCarts) {
                throw new RuntimeException('Migráciu košíkov nemožno vrátiť, kým existujú viaceré košíky jedného používateľa.');
            }

            Schema::create('cart_items', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('quantity');
                $table->timestamps();
                $table->primary(['user_id', 'product_id']);
            });

            DB::table('shopping_cart_items')->join('shopping_carts', 'shopping_carts.id', '=', 'shopping_cart_items.shopping_cart_id')
                ->select('shopping_carts.user_id', 'shopping_cart_items.product_id', 'shopping_cart_items.quantity', 'shopping_cart_items.created_at', 'shopping_cart_items.updated_at')
                ->orderBy('shopping_carts.id')->orderBy('shopping_cart_items.product_id')
                ->chunk(500, function ($items) {
                    DB::table('cart_items')->insert($items->map(fn ($item) => (array) $item)->all());
                });

            Schema::drop('shopping_cart_items');
            Schema::drop('shopping_carts');
        });
    }

    private function requireMaintenance(): void
    {
        if (! app()->environment('testing') && DB::table('users')->exists() && ! app()->isDownForMaintenance()) {
            throw new RuntimeException('Migrácia košíkov vyžaduje režim údržby a zastavenie všetkých ďalších zapisujúcich procesov.');
        }
    }

    private function runSafely(Closure $callback): void
    {
        if (DB::getDriverName() === 'sqlite' || DB::connection()->getSchemaGrammar()->supportsSchemaTransactions()) {
            DB::transaction($callback);
        } else {
            $callback();
        }
    }
};
