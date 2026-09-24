<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('name');
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('role')->default('customer')->index()->after('phone_verified_at');
            $table->string('avatar')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'phone_verified_at', 'role', 'avatar', 'city', 'is_active']);
        });
    }
};