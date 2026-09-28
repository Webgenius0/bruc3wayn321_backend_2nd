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
            $table->dropColumn('name');
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('bio')->nullable()->after('last_name');
            $table->string('user_name')->nullable()->after('bio');
            $table->string('phone')->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name');
            $table->dropColumn([
                'first_name',
                'last_name',
                'bio',
                'user_name',
                'phone',
                'date_of_birth',
            ]);
        });
    }
};
