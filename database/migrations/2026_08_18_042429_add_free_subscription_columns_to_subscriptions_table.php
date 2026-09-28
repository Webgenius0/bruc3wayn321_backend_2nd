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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('type')->default('revenuecat')->after('store');
            $table->timestamp('free_expires_at')->nullable()->after('expires_at');
            $table->string('trial_type')->nullable()->after('free_expires_at'); // 'free_trial', 'freemium', null
        });
        // Keep unique('user_id') — one subscription per user.
        // type distinguishes 'free' vs 'revenuecat'.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['type', 'free_expires_at', 'trial_type']);
        });
    }
};
