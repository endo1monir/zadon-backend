<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Database\Seeder;

class StockAdjustmentSeeder extends Seeder
{
    public function run(): void
    {
        $adjustments = [
            ['sku' => 'PAN-01', 'type' => 'restock', 'quantity' => 50, 'previous_stock' => 100, 'new_stock' => 150, 'reason' => 'استلام شحنة جديدة'],
            ['sku' => 'VIT-C', 'type' => 'sale', 'quantity' => -20, 'previous_stock' => 100, 'new_stock' => 80, 'reason' => 'خصم المبيعات اليومية'],
            ['sku' => 'SKIN-CRM', 'type' => 'sale', 'quantity' => -5, 'previous_stock' => 60, 'new_stock' => 55, 'reason' => 'خصم المبيعات اليومية'],
            ['sku' => 'THERMO', 'type' => 'restock', 'quantity' => 5, 'previous_stock' => 20, 'new_stock' => 25, 'reason' => 'استكمال المخزون'],
            ['sku' => 'MILK-1', 'type' => 'waste', 'quantity' => -10, 'previous_stock' => 210, 'new_stock' => 200, 'reason' => 'انتهاء صلاحية'],
            ['sku' => 'CHP-1', 'type' => 'correction', 'quantity' => -50, 'previous_stock' => 350, 'new_stock' => 300, 'reason' => 'تسوية جرد دورية'],
        ];

        foreach ($adjustments as $adjustment) {
            $product = Product::where('sku', $adjustment['sku'])->firstOrFail();
            unset($adjustment['sku']);

            StockAdjustment::create(array_merge($adjustment, [
                'product_id' => $product->id,
                'store_id' => $product->store_id,
            ]));
        }
    }
}
