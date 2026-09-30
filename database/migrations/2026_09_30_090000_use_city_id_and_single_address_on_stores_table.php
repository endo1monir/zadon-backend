<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex(['city', 'status']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('category_id')->constrained('cities')->nullOnDelete();
        });

        $this->backfillCityIds();

        Schema::table('stores', function (Blueprint $table) {
            $table->renameColumn('address_ar', 'address');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['address_en', 'city']);
            $table->index(['city_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex(['city_id', 'status']);
            $table->string('city')->nullable()->after('address');
            $table->text('address_en')->nullable()->after('address');
        });

        $this->restoreCityNames();

        Schema::table('stores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
            $table->renameColumn('address', 'address_ar');
            $table->index(['city', 'status']);
        });
    }

    private function backfillCityIds(): void
    {
        if (! Schema::hasColumn('stores', 'city')) {
            return;
        }

        $cities = DB::table('cities')->get(['id', 'name_ar', 'name_en']);

        $lookup = [];

        foreach ($cities as $city) {
            foreach ([$city->name_ar, $city->name_en] as $name) {
                if ($name !== null) {
                    $lookup[mb_strtolower(trim($name))] = $city->id;
                }
            }
        }

        DB::table('stores')
            ->select('id', 'city')
            ->whereNotNull('city')
            ->orderBy('id')
            ->each(function ($store) use ($lookup): void {
                $key = mb_strtolower(trim((string) $store->city));

                if (isset($lookup[$key])) {
                    DB::table('stores')->where('id', $store->id)->update(['city_id' => $lookup[$key]]);
                }
            });
    }

    private function restoreCityNames(): void
    {
        DB::table('stores')
            ->select('id', 'city_id')
            ->whereNotNull('city_id')
            ->orderBy('id')
            ->each(function ($store): void {
                $name = DB::table('cities')->where('id', $store->city_id)->value('name_ar');

                if ($name !== null) {
                    DB::table('stores')->where('id', $store->id)->update(['city' => $name]);
                }
            });
    }
};
