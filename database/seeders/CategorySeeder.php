<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['type' => 'store', 'name_ar' => 'صيدليات', 'name_en' => 'Pharmacies', 'slug' => 'pharmacies', 'icon' => 'https://placehold.co/200x200/2563eb/ffffff?text=Pharmacies'],
            ['type' => 'store', 'name_ar' => 'سوبر ماركت', 'name_en' => 'Supermarkets', 'slug' => 'supermarkets', 'icon' => 'https://placehold.co/200x200/059669/ffffff?text=Supermarkets'],

            ['type' => 'product', 'name_ar' => 'أدوية وعقاقير', 'name_en' => 'Medicines', 'slug' => 'medicines', 'icon' => 'https://placehold.co/200x200/dc2626/ffffff?text=Medicines'],
            ['type' => 'product', 'name_ar' => 'أدوية البرد والإنفلونزا', 'name_en' => 'Cold & Flu', 'slug' => 'cold-flu', 'icon' => 'https://placehold.co/200x200/dc2626/ffffff?text=Cold+Flu', 'parent' => 'medicines'],
            ['type' => 'product', 'name_ar' => 'مسكنات الألم', 'name_en' => 'Pain Relievers', 'slug' => 'pain-relievers', 'icon' => 'https://placehold.co/200x200/dc2626/ffffff?text=Pain+Relievers', 'parent' => 'medicines'],

            ['type' => 'product', 'name_ar' => 'فيتامينات ومكملات', 'name_en' => 'Vitamins & Supplements', 'slug' => 'vitamins', 'icon' => 'https://placehold.co/200x200/d97706/ffffff?text=Vitamins'],
            ['type' => 'product', 'name_ar' => 'العناية بالبشرة', 'name_en' => 'Skincare', 'slug' => 'skincare', 'icon' => 'https://placehold.co/200x200/d946ef/ffffff?text=Skincare'],
            ['type' => 'product', 'name_ar' => 'العناية بالشعر', 'name_en' => 'Hair Care', 'slug' => 'hair-care', 'icon' => 'https://placehold.co/200x200/d946ef/ffffff?text=Hair+Care'],
            ['type' => 'product', 'name_ar' => 'الإسعافات الأولية', 'name_en' => 'First Aid', 'slug' => 'first-aid', 'icon' => 'https://placehold.co/200x200/0891b2/ffffff?text=First+Aid'],
            ['type' => 'product', 'name_ar' => 'مستلزمات الأطفال', 'name_en' => 'Baby Care', 'slug' => 'baby-care', 'icon' => 'https://placehold.co/200x200/4f46e5/ffffff?text=Baby+Care'],
            ['type' => 'product', 'name_ar' => 'التشخيص المنزلي', 'name_en' => 'Home Diagnostics', 'slug' => 'home-diagnostics', 'icon' => 'https://placehold.co/200x200/16a34a/ffffff?text=Home+Diagnostics'],
        ];

        $ids = [];

        foreach ($categories as $category) {
            $parentSlug = $category['parent'] ?? null;
            unset($category['parent']);

            $id = Category::create(array_merge($category, [
                'parent_id' => $parentSlug !== null ? ($ids[$parentSlug] ?? null) : null,
                'is_active' => true,
                'sort_order' => 0,
            ]))->id;

            $ids[$category['slug']] = $id;
        }
    }
}
