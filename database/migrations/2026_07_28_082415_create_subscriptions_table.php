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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('store')->nullable();
            $table->string('product_id');
            $table->string('entitlement')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('original_transaction_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('purchase_date')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('will_renew')->default(false);
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
