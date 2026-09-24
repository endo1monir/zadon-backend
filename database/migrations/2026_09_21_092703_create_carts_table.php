<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('status')->default('active'); // active | checked_out | abandoned
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('store_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};