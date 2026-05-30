<?php

namespace Database\Seeders;

use App\Models\Platform\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => ['ar' => 'الباقة الأساسية', 'en' => 'Pay-As-You-Go'],
                'monthly_price' => 0.00,
                'commission_percentage' => 10.00,
                'features' => [
                    'visibility_boost' => false,
                    'advanced_analytics' => false,
                    'marketing_tools' => false,
                    'priority_support' => false,
                ],
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'الباقة الاحترافية', 'en' => 'Pro Plan'],
                'monthly_price' => 500.00,
                'commission_percentage' => 5.00,
                'features' => [
                    'visibility_boost' => true,
                    'advanced_analytics' => true,
                    'marketing_tools' => true,
                    'priority_support' => false,
                ],
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'باقة الشركات', 'en' => 'Enterprise Plan'],
                'monthly_price' => 1500.00,
                'commission_percentage' => 2.00,
                'features' => [
                    'visibility_boost' => true,
                    'advanced_analytics' => true,
                    'marketing_tools' => true,
                    'priority_support' => true,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::create($planData);
        }
    }
}
