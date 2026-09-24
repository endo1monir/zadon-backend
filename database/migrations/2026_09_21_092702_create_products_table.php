<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('min_stock_alert')->default(0);
            $table->string('unit_ar')->default('piece');
            $table->string('unit_en')->nullable();
            $table->string('image')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->string('storage_method')->nullable();
            $table->boolean('is_prescription_required')->default(false);
            $table->string('storage_temp')->nullable(); // ambient | chilled | frozen
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sales_count')->default(0);
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->json('options')->nullable(); // e.g. [{name: "1 kg", price_surplus: 0}]
            $table->timestamps();

            $table->index(['store_id', 'is_active']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
