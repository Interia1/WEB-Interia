<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('gdpr_consent_at')->nullable()->after('password');
            $table->string('gdpr_consent_ip', 45)->nullable()->after('gdpr_consent_at');
            $table->timestamp('terms_accepted_at')->nullable()->after('gdpr_consent_ip');
            $table->string('terms_accepted_ip', 45)->nullable()->after('terms_accepted_at');
            $table->boolean('marketing_consent')->default(false)->after('terms_accepted_ip');
            $table->timestamp('marketing_consent_at')->nullable()->after('marketing_consent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gdpr_consent_at',
                'gdpr_consent_ip',
                'terms_accepted_at',
                'terms_accepted_ip',
                'marketing_consent',
                'marketing_consent_at',
            ]);
        });
    }
};
