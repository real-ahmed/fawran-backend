<?php

namespace Database\Seeders;

use App\Models\Catalog\Category;
use App\Models\Catalog\CategoryHierarchy;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => ['en' => 'Food', 'ar' => 'مأكولات'],
                'subcategories' => [
                    ['en' => 'Fast Food', 'ar' => 'وجبات سريعة'],
                    ['en' => 'Pizza & Pasta', 'ar' => 'بيتزا ومكرونة'],
                    ['en' => 'Burgers', 'ar' => 'برجر'],
                    ['en' => 'Seafood', 'ar' => 'مأكولات بحرية'],
                    ['en' => 'Desserts', 'ar' => 'حلويات'],
                    ['en' => 'Beverages', 'ar' => 'مشروبات'],
                    ['en' => 'Healthy', 'ar' => 'صحي'],
                    ['en' => 'Grill', 'ar' => 'مشويات'],
                    ['en' => 'Shawarma', 'ar' => 'شاورما'],
                    ['en' => 'Breakfast', 'ar' => 'إفطار'],
                    ['en' => 'Asian', 'ar' => 'آسيوي'],
                    ['en' => 'Traditional', 'ar' => 'أكل شعبي'],
                ],
            ],
            [
                'name' => ['en' => 'Grocery', 'ar' => 'بقالة'],
                'subcategories' => [
                    ['en' => 'Fruits & Vegetables', 'ar' => 'فواكه وخضروات'],
                    ['en' => 'Dairy & Eggs', 'ar' => 'ألبان وبيض'],
                    ['en' => 'Meat & Poultry', 'ar' => 'لحوم ودواجن'],
                    ['en' => 'Bakery', 'ar' => 'مخبوزات'],
                    ['en' => 'Snacks & Sweets', 'ar' => 'تسالي وحلويات'],
                    ['en' => 'Beverages', 'ar' => 'مشروبات'],
                    ['en' => 'Canned Food', 'ar' => 'معلبات'],
                    ['en' => 'Cleaning Supplies', 'ar' => 'منظفات'],
                    ['en' => 'Spices & Condiments', 'ar' => 'بهارات وتوابل'],
                ],
            ],
            [
                'name' => ['en' => 'Pharmacy', 'ar' => 'صيدلية'],
                'subcategories' => [
                    ['en' => 'Medicines', 'ar' => 'أدوية'],
                    ['en' => 'Vitamins & Supplements', 'ar' => 'فيتامينات ومكملات غذائية'],
                    ['en' => 'Skin Care', 'ar' => 'عناية بالبشرة'],
                    ['en' => 'Hair Care', 'ar' => 'عناية بالشعر'],
                    ['en' => 'Baby Care', 'ar' => 'عناية بالطفل'],
                    ['en' => 'Mother Care', 'ar' => 'عناية بالأم'],
                    ['en' => 'Medical Equipment', 'ar' => 'أجهزة طبية'],
                    ['en' => 'Cosmetics', 'ar' => 'مستحضرات تجميل'],
                    ['en' => 'Personal Care', 'ar' => 'عناية شخصية'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $parentCategory = Category::create([
                'name' => $categoryData['name'],
                'is_active' => true,
            ]);

            foreach ($categoryData['subcategories'] as $subData) {
                $childCategory = Category::create([
                    'name' => $subData,
                    'is_active' => true,
                ]);

                CategoryHierarchy::create([
                    'parent_category_id' => $parentCategory->id,
                    'child_category_id' => $childCategory->id,
                ]);
            }
        }
    }
}
