<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);

            $table->text('address_ar');
            $table->text('address_en')->nullable();
            $table->string('city');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('cr_number')->nullable();
            $table->string('vat_number')->nullable();

            $table->string('status')->default('open'); // open | busy | closed
            $table->unsignedInteger('prep_time_min')->default(15);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('min_order', 10, 2)->default(0);
            $table->string('manager_name')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_open_24_7')->default(false);
            $table->string('opening_time')->nullable();
            $table->string('closing_time')->nullable();
            $table->decimal('delivery_radius_km', 8, 2)->default(10);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['city', 'status']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};