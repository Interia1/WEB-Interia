<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            foreach ([
                ['ukazka-dlheho-nazvu-hlinikovy-profil', 'DEMO-DLHY-001', 'DEMO-001'],
                ['ukazka-dlheho-nazvu-ocelovy-prvok', 'DEMO-DLHY-002', 'DEMO-002'],
            ] as [$slug, $oldCode, $newCode]) {
                $product = DB::table('products')->where('slug', $slug)->where('sku', $oldCode)->lockForUpdate()->first();
                if (! $product) {
                    continue;
                }

                if (DB::table('products')->where('sku', $newCode)->exists()) {
                    throw new RuntimeException("Ukážkový kód {$newCode} už používa iný produkt.");
                }

                DB::table('products')->where('id', $product->id)->update(['sku' => $newCode]);
            }
        });
    }

    public function down(): void
    {
        // Corrected product codes must not be changed back on rollback.
    }
};
