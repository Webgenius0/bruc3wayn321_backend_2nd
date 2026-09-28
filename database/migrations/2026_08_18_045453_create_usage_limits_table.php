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
        Schema::create('usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'ai_chat', 'ai_voice', 'posts'
            $table->date('period_start'); // Start of the billing period (monthly for paid, rolling for free)
            $table->integer('current_count')->default(0);
            $table->integer('limit')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'type', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_limits');
    }
};
