<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->string('customer_name')->nullable();
            $table->string('customer_avatar')->nullable();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->unsignedTinyInteger('courier_rating')->nullable();
            $table->text('comment')->nullable();
            $table->json('tags')->nullable();
            $table->text('store_reply')->nullable();
            $table->timestamp('store_reply_date')->nullable();
            $table->boolean('published')->default(true);
            $table->timestamps();

            $table->index(['store_id', 'published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};