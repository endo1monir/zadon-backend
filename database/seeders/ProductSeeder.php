<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $zadon = Store::where('name_ar', 'صيدلية زادون')->firstOrFail();
        $nakheel = Store::where('name_ar', 'صيدلية النخيل')->firstOrFail();
        $souq = Store::where('name_ar', 'سوبر ماركت السوق')->firstOrFail();

        $categoryOf = fn (string $slug): Category => Category::where('slug', $slug)->firstOrFail();

        $products = [
            [$zadon, ['slug' => 'pain-relievers', 'name_ar' => 'بنادول إكسترا ٢٤ حبة', 'name_en' => 'Panadol Extra 24 Tabs', 'sku' => 'PAN-01', 'barcode' => '6281077000001', 'price' => 9.00, 'original_price' => 11.00, 'cost_price' => 6.00, 'stock' => 150, 'min_stock_alert' => 30, 'unit_ar' => 'علبة', 'storage_temp' => 'ambient', 'expiry_date' => '2027-12-31', 'description_ar' => 'مسكن لألم الصداع والحمى']],
            [$zadon, ['slug' => 'vitamins', 'name_ar' => 'فيتامين سي 1000 ملجم', 'name_en' => 'Vitamin C 1000mg', 'sku' => 'VIT-C', 'barcode' => '6281077000002', 'price' => 35.00, 'original_price' => 42.00, 'cost_price' => 22.00, 'stock' => 80, 'min_stock_alert' => 20, 'unit_ar' => 'عبوة', 'storage_temp' => 'ambient', 'storage_method' => 'مكان بارد وجاف', 'expiry_date' => '2027-06-30', 'description_ar' => 'مكمل غذائي لدعم المناعة', 'options' => [['name' => '30 كبسولة', 'price_surplus' => 0], ['name' => '60 كبسولة', 'price_surplus' => 18]]]],
            [$zadon, ['slug' => 'vitamins', 'name_ar' => 'ملتي فيتامين', 'name_en' => 'Multi Vitamin', 'sku' => 'VIT-M', 'barcode' => '6281077000003', 'price' => 45.00, 'cost_price' => 28.00, 'stock' => 60, 'min_stock_alert' => 15, 'unit_ar' => 'عبوة', 'storage_temp' => 'ambient', 'expiry_date' => '2027-05-31']],
            [$zadon, ['slug' => 'pain-relievers', 'name_ar' => 'شراب باراسيتامول 120 مل', 'name_en' => 'Paracetamol Syrup 120ml', 'sku' => 'PAR-SYR', 'barcode' => '6281077000004', 'price' => 12.00, 'cost_price' => 7.50, 'stock' => 100, 'min_stock_alert' => 25, 'unit_ar' => 'عبوة', 'storage_temp' => 'ambient', 'expiry_date' => '2028-01-31', 'description_ar' => 'للأطفال من عمر سنتين']],
            [$zadon, ['slug' => 'medicines', 'name_ar' => 'أموكسيسيلين 500 ملجم', 'name_en' => 'Amoxicillin 500mg', 'sku' => 'AMOX-500', 'barcode' => '6281077000005', 'price' => 20.00, 'cost_price' => 12.00, 'stock' => 45, 'min_stock_alert' => 10, 'unit_ar' => 'عبوة', 'is_prescription_required' => true, 'storage_temp' => 'ambient', 'expiry_date' => '2027-09-30']],
            [$zadon, ['slug' => 'first-aid', 'name_ar' => 'عدة إسعافات أولية', 'name_en' => 'First Aid Kit', 'sku' => 'FIR-01', 'barcode' => '6281077000006', 'price' => 35.00, 'cost_price' => 20.00, 'stock' => 40, 'min_stock_alert' => 8, 'unit_ar' => 'حلة', 'storage_temp' => 'ambient']],

            [$nakheel, ['slug' => 'vitamins', 'name_ar' => 'فيتامين ب12', 'name_en' => 'Vitamin B12', 'sku' => 'VIT-B12', 'barcode' => '6281077000011', 'price' => 45.00, 'original_price' => 50.00, 'cost_price' => 30.00, 'stock' => 40, 'min_stock_alert' => 10, 'unit_ar' => 'عبوة', 'storage_temp' => 'ambient', 'expiry_date' => '2027-04-30']],
            [$nakheel, ['slug' => 'skincare', 'name_ar' => 'كريم مرطب للبشرة', 'name_en' => 'Moisturizing Cream', 'sku' => 'SKIN-CRM', 'barcode' => '6281077000012', 'price' => 22.00, 'cost_price' => 13.50, 'stock' => 55, 'min_stock_alert' => 12, 'unit_ar' => 'أنبوبة', 'storage_temp' => 'ambient', 'description_ar' => 'للبشرة الجافة والحساسة']],
            [$nakheel, ['slug' => 'hair-care', 'name_ar' => 'شامبو ضد القشرة', 'name_en' => 'Anti-Dandruff Shampoo', 'sku' => 'HAIR-SHP', 'barcode' => '6281077000013', 'price' => 28.00, 'cost_price' => 16.00, 'stock' => 60, 'min_stock_alert' => 15, 'unit_ar' => 'عبوة', 'storage_temp' => 'ambient']],
            [$nakheel, ['slug' => 'home-diagnostics', 'name_ar' => 'ميزان حرارة رقمي', 'name_en' => 'Digital Thermometer', 'sku' => 'THERMO', 'barcode' => '6281077000014', 'price' => 35.00, 'cost_price' => 18.00, 'stock' => 25, 'min_stock_alert' => 30, 'unit_ar' => 'قطعة', 'storage_temp' => 'ambient']],
            [$nakheel, ['slug' => 'cold-flu', 'name_ar' => 'حقيبة البرد والإنفلونزا', 'name_en' => 'Cold & Flu Pack', 'sku' => 'COLD-PK', 'barcode' => '6281077000015', 'price' => 18.00, 'cost_price' => 10.00, 'stock' => 70, 'min_stock_alert' => 15, 'unit_ar' => 'حقيبة', 'storage_temp' => 'ambient', 'expiry_date' => '2027-11-30']],

            [$souq, ['slug' => null, 'name_ar' => 'ماء مياه معدنية 1.5 لتر', 'name_en' => 'Mineral Water 1.5L', 'sku' => 'WTR-15', 'barcode' => '6281077000021', 'price' => 2.50, 'cost_price' => 1.20, 'stock' => 500, 'min_stock_alert' => 100, 'unit_ar' => 'قارورة', 'storage_temp' => 'ambient']],
            [$souq, ['slug' => null, 'name_ar' => 'أرز بسمتي 5 كجم', 'name_en' => 'Basmati Rice 5kg', 'sku' => 'RICE-5', 'barcode' => '6281077000022', 'price' => 35.00, 'original_price' => 40.00, 'cost_price' => 26.00, 'stock' => 120, 'min_stock_alert' => 20, 'unit_ar' => 'كيس', 'storage_temp' => 'ambient']],
            [$souq, ['slug' => null, 'name_ar' => 'حليب طازج 1 لتر', 'name_en' => 'Fresh Milk 1L', 'sku' => 'MILK-1', 'barcode' => '6281077000023', 'price' => 6.00, 'cost_price' => 3.80, 'stock' => 200, 'min_stock_alert' => 40, 'unit_ar' => 'دبة', 'storage_temp' => 'chilled', 'storage_method' => 'تبريد 4°م', 'expiry_date' => '2026-10-15']],
            [$souq, ['slug' => null, 'name_ar' => 'سكر 2 كجم', 'name_en' => 'Sugar 2kg', 'sku' => 'SUG-2', 'barcode' => '6281077000024', 'price' => 8.00, 'cost_price' => 5.20, 'stock' => 150, 'min_stock_alert' => 30, 'unit_ar' => 'كيس', 'storage_temp' => 'ambient']],
            [$souq, ['slug' => null, 'name_ar' => 'رقائق بطاطس', 'name_en' => 'Potato Chips', 'sku' => 'CHP-1', 'barcode' => '6281077000025', 'price' => 3.00, 'cost_price' => 1.60, 'stock' => 300, 'min_stock_alert' => 60, 'unit_ar' => 'كيس', 'storage_temp' => 'ambient']],
        ];

        foreach ($products as [$store, $data]) {
            $categoryId = $data['slug'] !== null
                ? $categoryOf($data['slug'])->id
                : null;

            unset($data['slug']);

            Product::create(array_merge($data, [
                'store_id' => $store->id,
                'category_id' => $categoryId,
                'sales_count' => fake()->numberBetween(0, 200),
                'is_active' => true,
            ]));
        }
    }
}
